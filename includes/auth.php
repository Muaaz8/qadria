<?php
function canUserViewJob($pdo, $user_id, $job_id) {
    if (isAdmin()) return true;
    
    $stmt = $pdo->prepare("SELECT created_by FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();
    if ($job && $job['created_by'] == $user_id) return true;
    
    $stmt = $pdo->prepare("SELECT * FROM user_job_permissions WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$user_id, $job_id]);
    if ($stmt->rowCount() > 0) return true;
    
    return false;
}

function canUserViewBill($pdo, $user_id, $bill_id) {
    if (isAdmin()) return true;
    
    $stmt = $pdo->prepare("SELECT created_by FROM bills WHERE id = ?");
    $stmt->execute([$bill_id]);
    $bill = $stmt->fetch();
    if ($bill && $bill['created_by'] == $user_id) return true;
    
    $stmt = $pdo->prepare("SELECT job_id FROM bills WHERE id = ?");
    $stmt->execute([$bill_id]);
    $bill = $stmt->fetch();
    if ($bill && canUserViewJob($pdo, $user_id, $bill['job_id'])) return true;
    
    return false;
}

function canUserViewInvoice($pdo, $user_id, $invoice_path) {
    if (isAdmin()) return true;
    
    $full_path = __DIR__ . '/../invoices/' . $invoice_path;
    
    if (is_dir($full_path)) {
        return canUserViewInvoiceDirectory($pdo, $user_id, $invoice_path);
    }
    
    $filename = basename($invoice_path);
    $bill_number = pathinfo($filename, PATHINFO_FILENAME);
    
    $stmt = $pdo->prepare("SELECT created_by FROM bills WHERE bill_number = ?");
    $stmt->execute([$bill_number]);
    $bill = $stmt->fetch();
    
    if ($bill && $bill['created_by'] == $user_id) return true;
    
    $stmt = $pdo->prepare("SELECT j.created_by FROM jobs j INNER JOIN bills b ON b.job_id = j.id WHERE b.bill_number = ?");
    $stmt->execute([$bill_number]);
    $job = $stmt->fetch();
    if ($job && $job['created_by'] == $user_id) return true;
    
    return false;
}

function canUserViewInvoiceDirectory($pdo, $user_id, $dir_path) {
    if (isAdmin()) return true;
    
    $full_dir = __DIR__ . '/../invoices/' . $dir_path;
    if (!is_dir($full_dir)) return false;
    
    $files = scandir($full_dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        if (is_file($full_dir . '/' . $file)) {
            $bill_number = pathinfo($file, PATHINFO_FILENAME);
            $stmt = $pdo->prepare("SELECT created_by FROM bills WHERE bill_number = ?");
            $stmt->execute([$bill_number]);
            $bill = $stmt->fetch();
            if ($bill && $bill['created_by'] == $user_id) return true;
            
            $stmt = $pdo->prepare("SELECT j.created_by FROM jobs j INNER JOIN bills b ON b.job_id = j.id WHERE b.bill_number = ?");
            $stmt->execute([$bill_number]);
            $job = $stmt->fetch();
            if ($job && $job['created_by'] == $user_id) return true;
        }
    }
    
    return false;
}

function getUserVisibleJobs($pdo, $user_id) {
    if (isAdmin()) {
        $stmt = $pdo->query("SELECT * FROM jobs ORDER BY id DESC");
        return $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("
            SELECT DISTINCT j.* FROM jobs j
            LEFT JOIN user_job_permissions ujp ON ujp.job_id = j.id
            WHERE j.created_by = ? OR ujp.user_id = ?
            ORDER BY j.id DESC
        ");
        $stmt->execute([$user_id, $user_id]);
        return $stmt->fetchAll();
    }
}

function getUserVisibleBills($pdo, $user_id) {
    if (isAdmin()) {
        $stmt = $pdo->query("SELECT b.*, j.job_number, j.consignee FROM bills b LEFT JOIN jobs j ON b.job_id = j.id ORDER BY b.id DESC");
        return $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("
            SELECT DISTINCT b.*, j.job_number, j.consignee FROM bills b
            LEFT JOIN jobs j ON b.job_id = j.id
            LEFT JOIN user_job_permissions ujp ON ujp.job_id = j.id
            WHERE b.created_by = ? OR j.created_by = ? OR ujp.user_id = ?
            ORDER BY b.id DESC
        ");
        $stmt->execute([$user_id, $user_id, $user_id]);
        return $stmt->fetchAll();
    }
}

function grantJobPermission($pdo, $user_id, $job_id) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_job_permissions (user_id, job_id, granted_by) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $job_id, getUserId()]);
}

function revokeJobPermission($pdo, $user_id, $job_id) {
    $stmt = $pdo->prepare("DELETE FROM user_job_permissions WHERE user_id = ? AND job_id = ?");
    return $stmt->execute([$user_id, $job_id]);
}

function getAllUsers($pdo) {
    $stmt = $pdo->query("SELECT id, username, full_name, email, phone, role FROM users ORDER BY id DESC");
    return $stmt->fetchAll();
}

function getJobsWithPermissionInfo($pdo, $target_user_id) {
    $stmt = $pdo->prepare("
        SELECT j.*, 
               CASE WHEN ujp.id IS NOT NULL THEN 1 ELSE 0 END as has_permission,
               CASE WHEN j.created_by = ? THEN 1 ELSE 0 END as is_owner
        FROM jobs j
        LEFT JOIN user_job_permissions ujp ON ujp.job_id = j.id AND ujp.user_id = ?
        ORDER BY j.id DESC
    ");
    $stmt->execute([$target_user_id, $target_user_id]);
    return $stmt->fetchAll();
}

function getUserInvoiceItems($pdo, $user_id, $current_path) {
    $full_path = __DIR__ . '/../invoices/' . $current_path;
    $items = [];
    
    if (!is_dir($full_path)) return $items;
    
    $dir_contents = scandir($full_path);
    foreach ($dir_contents as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $item_path = $current_path ? $current_path . '/' . $item : $item;
        $is_dir = is_dir($full_path . '/' . $item);
        
        if ($is_dir) {
            if (canUserViewInvoiceDirectory($pdo, $user_id, $item_path)) {
                $items[] = [
                    'name' => $item,
                    'path' => $item_path,
                    'is_dir' => true,
                    'modified' => date('Y-m-d H:i:s', filemtime($full_path . '/' . $item))
                ];
            }
        } else {
            if (canUserViewInvoice($pdo, $user_id, $item_path)) {
                $items[] = [
                    'name' => $item,
                    'path' => $item_path,
                    'is_dir' => false,
                    'modified' => date('Y-m-d H:i:s', filemtime($full_path . '/' . $item))
                ];
            }
        }
    }
    
    usort($items, function($a, $b) {
        if ($a['is_dir'] != $b['is_dir']) return $b['is_dir'] - $a['is_dir'];
        return strcmp($a['name'], $b['name']);
    });
    
    return $items;
}

function deleteInvoiceItem($pdo, $user_id, $path, $type) {
    $full_path = __DIR__ . '/../invoices/' . $path;
    
    if ($type == 'folder') {
        if (!canUserViewInvoiceDirectory($pdo, $user_id, $path)) return false;
        if (is_dir($full_path)) {
            $files = glob($full_path . '/*');
            foreach ($files as $file) if (is_file($file)) unlink($file);
            return rmdir($full_path);
        }
    } else {
        if (!canUserViewInvoice($pdo, $user_id, $path)) return false;
        if (is_file($full_path)) return unlink($full_path);
    }
    return false;
}
?>