<?php
// Cache masters data in session
// Cache masters data in session - Always reload if not set
if (!isset($_SESSION['masters_cache']) || empty($_SESSION['masters_cache'])) {
    $stmt = $pdo->query("SELECT id, type, code, name, sort_order, is_active FROM masters WHERE is_active = 1 ORDER BY sort_order, name");
    $_SESSION['masters_cache'] = [];
    while($row = $stmt->fetch()) {
        $_SESSION['masters_cache'][$row['type']][] = $row;
    }
}

// ========== Updated getMasters (with description from masters table) 
// ========== Updated getMasters (with description from masters table) 
function getMasters($pdo, $type, $limit = null, $offset = null) {
    $columnMap = [
        'bonded_carrier'        => 'bonded_carrier',
        'do_collected_date'     => 'do_collected_date',
        'customer_name'         => 'customer_name',
        'consignee'             => 'consignee',
        'container_size'        => 'container_size',
        'dry_port'              => 'dry_port',
        'vessel_name'           => 'vessel_name',
        'shipping_line'         => 'shipping_line',
        'freight_forwarder_name'=> 'freight_forwarder_name',
        'depositor'             => 'depositor',
        'description'           => 'description'
    ];

    if (!isset($columnMap[$type])) return [];
    $col = $columnMap[$type];

    // 1. Jobs table se distinct values
    $sql = "SELECT `$col` as name FROM jobs WHERE `$col` != '' AND `$col` IS NOT NULL ORDER BY `$col`";
    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $jobValues = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Linked jobs count + masters info (NO auto‑insert)
    $linkStmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE `$col` = ?");
    $linkedJobsStmt = $pdo->prepare("SELECT GROUP_CONCAT(job_number ORDER BY job_number SEPARATOR ', ') FROM jobs WHERE `$col` = ?");
    $mastersStmt = $pdo->prepare("SELECT id, code, sort_order, is_active, ntn, st_no FROM masters WHERE type = ? AND name = ?");

    $masters = [];
    $index = 0; // ✅ Counter for unique ID generation
    foreach ($jobValues as $val) {
        $linkStmt->execute([$val]);
        $jobCount = (int)$linkStmt->fetchColumn();

        // ✅ Get linked jobs list
        $linkedJobsStmt->execute([$val]);
        $linkedJobs = $linkedJobsStmt->fetchColumn() ?: '';

        $mastersStmt->execute([$type, $val]);
        $masterRow = $mastersStmt->fetch();

        // ✅ FIXED: Ensure every row has a unique ID
        $rowId = $masterRow ? $masterRow['id'] : ('job_' . $type . '_' . $index);
        
        $masters[] = [
            'id'          => $rowId, // ✅ Always has a value now
            'code'        => $masterRow['code'] ?? $val, // ✅ Use name as code fallback
            'name'        => $val,
            'sort_order'  => $masterRow['sort_order'] ?? 0,
            'is_active'   => $masterRow['is_active'] ?? 1,
            'ntn'         => $masterRow['ntn'] ?? '',
            'st_no'       => $masterRow['st_no'] ?? '',
            'job_count'   => $jobCount,
            'linked_jobs' => $linkedJobs, // ✅ Added linked jobs
            'type'        => $type
        ];
        $index++;
    }
    return $masters;
}

// ===================== getMastersCount() =====================
function getMastersCount($pdo, $type) {
    $columnMap = [
        'consignee'             => 'consignee',
        'bonded_carrier'        => 'bonded_carrier',
        'do_collected_date'     => 'do_collected_date',  // ✅ Fixed
        'customer_name'         => 'customer_name',
        'container_size'        => 'container_size',
        'dry_port'              => 'dry_port',
        'vessel_name'           => 'vessel_name',
        'shipping_line'         => 'shipping_line',
        'freight_forwarder_name'=> 'freight_forwarder_name',
        'depositor'             => 'depositor',
        'description'           => 'description'  // ✅ Added description
    ];

    if (!isset($columnMap[$type])) return 0;
    $col = $columnMap[$type];

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT `$col`) as cnt FROM jobs WHERE `$col` != '' AND `$col` IS NOT NULL");
    $stmt->execute();
    return (int)$stmt->fetch()['cnt'];
}


// ===================== Helper to fetch a single master for AJAX =====================
function getMasterById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM masters WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;

    $type = $row['type'];
    $name = $row['name'];

    // ✅ FIX: Updated column map with do_collected_date
    $columnMap = [
        'bonded_carrier'        => 'bonded_carrier',
        'do_collected_date'     => 'do_collected_date',  // ✅ Changed from 'do' to 'do_collected_date'
        'customer_name'         => 'customer_name',
        'consignee'             => 'consignee',
        'container_size'        => 'container_size',
        'dry_port'              => 'dry_port',
        'vessel_name'           => 'vessel_name',
        'shipping_line'         => 'shipping_line',
        'freight_forwarder_name'=> 'freight_forwarder_name',
        'depositor'             => 'depositor',
        'description'           => 'description'
    ];

    $col = isset($columnMap[$type]) ? $columnMap[$type] : null;
    $job_count = 0;
    $linked_jobs = '';

    if ($col) {
        $ljStmt = $pdo->prepare("SELECT GROUP_CONCAT(job_number ORDER BY job_number SEPARATOR ', ') as jobs, COUNT(*) as cnt FROM jobs WHERE `$col` = ?");
        $ljStmt->execute([$name]);
        $lj = $ljStmt->fetch();
        $job_count = (int)($lj['cnt'] ?? 0);
        $linked_jobs = $lj['jobs'] ?? '';
    }

    return [
        'id'          => $row['id'],
        'code'        => $row['code'] ?? '',
        'name'        => $row['name'],
        'ntn'         => $row['ntn'] ?? '',
        'st_no'       => $row['st_no'] ?? '',
        'is_active'   => $row['is_active'] ?? 1,
        'job_count'   => $job_count,
        'linked_jobs' => $linked_jobs
    ];
}



if ($page == 'masters') {
    if (!isAdmin()) {
        $_SESSION['error'] = "Access denied! Only administrators can manage masters.";
        header("Location: system.php?page=dashboard");
        exit();
    }
    
    // Clear cache to get fresh data
    unset($_SESSION['masters_cache']);
    
    $type = isset($_GET['type']) ? $_GET['type'] : 'bonded_carrier';
    $masters = getMasters($pdo, $type);
    $types = [
        'bonded_carrier'=>'Bonded Carrier Name',
        'do_collected_date'=>'D/O', // Changed from 'do' to 'do_collected_date'
        'customer_name'=>'Customer Name',
        'consignee'=>'Consignee',
        'container_size'=>'Container Size',
        'khi_port'=>'KHI Port',
        'dry_port'=>'Dry Port',
        'vessel_name'=>'Vessel Name',
        'shipping_line'=>'Shipping Line',
        'freight_forwarder_name'=>'Freight Forwarder Name',
        'depositor'=>'Depositor',
        'description'=>'Description'
    ];
}

if ($page == 'add-master' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $data = json_decode(file_get_contents('php://input'), true);

    $checkStmt = $pdo->prepare("SELECT id FROM masters WHERE type = ? AND name = ?");
    $checkStmt->execute([$data['type'], $data['name']]);

    if ($checkStmt->rowCount() > 0) {
        $masterId = $checkStmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE masters SET code=?, ntn=?, st_no=?, sort_order=?, is_active=?, description=? WHERE type=? AND name=?");
        $stmt->execute([$data['code'], $data['ntn'] ?? '', $data['st_no'] ?? '', $data['sort_order'] ?? 0, $data['is_active'] ?? 1, $data['description'] ?? '', $data['type'], $data['name']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO masters (type, code, name, ntn, st_no, sort_order, is_active, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['type'], $data['code'], $data['name'], $data['ntn'] ?? '', $data['st_no'] ?? '', $data['sort_order'] ?? 0, $data['is_active'] ?? 1, $data['description'] ?? '']);
        $masterId = $pdo->lastInsertId();
    }

    // ✅ FIX: Update jobs table for do_collected_date
    if($data['type'] == 'do_collected_date') {
        $updateJobs = $pdo->prepare("UPDATE jobs SET do_collected_date = ? WHERE do_collected_date = ?");
        $updateJobs->execute([$data['name'], $data['name']]);
    }

    // ✅ FIX: Update jobs table for description
    if($data['type'] == 'description') {
        $updateJobs = $pdo->prepare("UPDATE jobs SET description = ? WHERE description = ?");
        $updateJobs->execute([$data['name'], $data['name']]);
    }

    if($data['type'] == 'consignee') {
        $updateJobs = $pdo->prepare("UPDATE jobs SET ntn = ?, st_no = ? WHERE consignee = ?");
        $updateJobs->execute([$data['ntn'] ?? '', $data['st_no'] ?? '', $data['name']]);
    }

    $master = getMasterById($pdo, $masterId);
    unset($_SESSION['masters_cache']);
    echo json_encode(['success'=>true, 'master'=>$master]);
    exit();
}

if ($page == 'update-master' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'] ?? '';
    $type = $data['type'] ?? '';
    $newName = $data['name'] ?? '';
    $newCode = $data['code'] ?? '';
    $isActive = $data['is_active'] ?? 1;
    $oldName = $data['old_name'] ?? '';

    // ✅ Column mapping for jobs table
    $columnMap = [
        'bonded_carrier'        => 'bonded_carrier',
        'do_collected_date'     => 'do_collected_date',
        'customer_name'         => 'customer_name',
        'consignee'             => 'consignee',
        'container_size'        => 'container_size',
        'dry_port'              => 'dry_port',
        'vessel_name'           => 'vessel_name',
        'shipping_line'         => 'shipping_line',
        'freight_forwarder_name'=> 'freight_forwarder_name',
        'depositor'             => 'depositor',
        'description'           => 'description'
    ];

    // ✅ STEP 1: Update jobs table if name changed (for ALL types)
    $updatedJobCount = 0;
    if (!empty($oldName) && $oldName !== $newName && isset($columnMap[$type])) {
        $col = $columnMap[$type];
        $updateJobs = $pdo->prepare("UPDATE jobs SET `$col` = ? WHERE `$col` = ?");
        $updateJobs->execute([$newName, $oldName]);
        $updatedJobCount = $updateJobs->rowCount();
        
        // Update consignee NTN/ST_NO
        if ($type == 'consignee') {
            $updateNtnStno = $pdo->prepare("UPDATE jobs SET ntn = ?, st_no = ? WHERE consignee = ?");
            $updateNtnStno->execute([$data['ntn'] ?? '', $data['st_no'] ?? '', $newName]);
        }
        
        // Also update loading_files for bonded_carrier
        if ($type == 'bonded_carrier') {
            $updateLoading = $pdo->prepare("UPDATE loading_files SET bonded_carrier = ? WHERE bonded_carrier = ?");
            $updateLoading->execute([$newName, $oldName]);
        }
    }

    // ✅ STEP 2: Update or Insert into masters table
    $checkMaster = $pdo->prepare("SELECT id FROM masters WHERE id = ?");
    $checkMaster->execute([$id]);
    
    if ($checkMaster->rowCount() > 0) {
        // ✅ Masters table mein record hai - UPDATE karo
        $stmt = $pdo->prepare("UPDATE masters SET code=?, name=?, ntn=?, st_no=?, sort_order=?, is_active=?, description=? WHERE id=?");
        $stmt->execute([
            $newCode, 
            $newName, 
            $data['ntn'] ?? '', 
            $data['st_no'] ?? '', 
            $data['sort_order'] ?? 0, 
            $isActive, 
            $data['description'] ?? '', 
            $id
        ]);
    } else {
        // ✅ Masters table mein record nahi hai (jobs-only data) - INSERT karo ya check karo type+name se
        $checkByName = $pdo->prepare("SELECT id FROM masters WHERE type = ? AND name = ?");
        $checkByName->execute([$type, $oldName]);
        
        if ($checkByName->rowCount() > 0) {
            // Old name se match mila - update karo
            $mId = $checkByName->fetchColumn();
            $stmt = $pdo->prepare("UPDATE masters SET code=?, name=?, ntn=?, st_no=?, is_active=? WHERE id=?");
            $stmt->execute([$newCode, $newName, $data['ntn'] ?? '', $data['st_no'] ?? '', $isActive, $mId]);
            $id = $mId;
        } else {
            // Naya record insert karo
            $stmt = $pdo->prepare("INSERT INTO masters (type, code, name, ntn, st_no, sort_order, is_active, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$type, $newCode, $newName, $data['ntn'] ?? '', $data['st_no'] ?? '', $data['sort_order'] ?? 0, $isActive, $data['description'] ?? '']);
            $id = $pdo->lastInsertId();
        }
    }

    $master = getMasterById($pdo, $id);
    unset($_SESSION['masters_cache']);
    echo json_encode([
        'success' => true, 
        'master' => $master,
        'message' => $updatedJobCount > 0 ? "Updated $updatedJobCount job(s) with new name" : "Master updated successfully"
    ]);
    exit();
}

if ($page == 'delete-master' && isset($_GET['id']) && isAdmin()) {
    $id = $_GET['id'];
    $type = $_GET['type'] ?? '';

    // Agar naam bhi aaya hai to use karo
    $name = $_GET['name'] ?? '';
    
    // Agar sirf ID aayi hai to masters table se naam nikalo
    if (empty($name) && is_numeric($id)) {
        $nameStmt = $pdo->prepare("SELECT name FROM masters WHERE id = ?");
        $nameStmt->execute([$id]);
        $name = $nameStmt->fetchColumn() ?: '';
    }

    $columnMap = [
        'bonded_carrier'        => 'bonded_carrier',
        'do_collected_date'     => 'do_collected_date',
        'customer_name'         => 'customer_name',
        'consignee'             => 'consignee',
        'container_size'        => 'container_size',
        'dry_port'              => 'dry_port',
        'vessel_name'           => 'vessel_name',
        'shipping_line'         => 'shipping_line',
        'freight_forwarder_name'=> 'freight_forwarder_name',
        'depositor'             => 'depositor',
        'description'           => 'description'
    ];

    // Consignee ke liye special handling - delete nahi hone denge agar jobs mein use ho raha hai
    if ($type == 'consignee' && isset($columnMap[$type])) {
        $col = $columnMap[$type];
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE `$col` = ?");
        $countStmt->execute([$name]);
        $jobCount = (int)$countStmt->fetchColumn();
        
        if ($jobCount > 0) {
            $jobStmt = $pdo->prepare("SELECT GROUP_CONCAT(job_number SEPARATOR ', ') FROM jobs WHERE `$col` = ?");
            $jobStmt->execute([$name]);
            $linkedJobs = $jobStmt->fetchColumn() ?: '';
            
            echo json_encode([
                'success' => false, 
                'message' => "Cannot delete! \"$name\" is used in $jobCount job(s): $linkedJobs\n\nPlease reassign those jobs first."
            ]);
            exit();
        }
        
        // Delete from masters
        if (is_numeric($id)) {
            $pdo->prepare("DELETE FROM masters WHERE id = ?")->execute([$id]);
        } else {
            $pdo->prepare("DELETE FROM masters WHERE type = ? AND name = ?")->execute([$type, $name]);
        }
        
        unset($_SESSION['masters_cache']);
        echo json_encode(['success' => true, 'message' => 'Consignee deleted successfully!']);
        exit();
    }

    // Baqi sab types ke liye - directly clear jobs + delete
    if (isset($columnMap[$type]) && !empty($name)) {
        $col = $columnMap[$type];
        
        // Count jobs (just for message)
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE `$col` = ?");
        $countStmt->execute([$name]);
        $jobCount = (int)$countStmt->fetchColumn();
        
        // Clear from jobs table
        $clearStmt = $pdo->prepare("UPDATE jobs SET `$col` = '' WHERE `$col` = ?");
        $clearStmt->execute([$name]);
        $clearedCount = $clearStmt->rowCount();
        
        // Clear from loading_files if bonded_carrier
        if ($type == 'bonded_carrier') {
            $pdo->prepare("UPDATE loading_files SET bonded_carrier = '' WHERE bonded_carrier = ?")->execute([$name]);
        }
        
        // Delete from masters table
        if (is_numeric($id)) {
            $pdo->prepare("DELETE FROM masters WHERE id = ?")->execute([$id]);
        }
        $pdo->prepare("DELETE FROM masters WHERE type = ? AND name = ?")->execute([$type, $name]);
        
        unset($_SESSION['masters_cache']);
        echo json_encode([
            'success' => true, 
            'message' => $clearedCount > 0 
                ? "\"$name\" cleared from $clearedCount job(s) and deleted!" 
                : "\"$name\" deleted successfully!"
        ]);
        exit();
    }
    
    // Fallback: agar kuch match nahi hua to normal delete try karo
    if (is_numeric($id)) {
        $pdo->prepare("DELETE FROM masters WHERE id = ?")->execute([$id]);
        unset($_SESSION['masters_cache']);
        echo json_encode(['success' => true, 'message' => 'Deleted successfully!']);
        exit();
    }
    
    echo json_encode(['success' => false, 'message' => 'Could not delete. Record not found.']);
    exit();
}

// ========== CLEAR VALUE FROM JOBS (for do_collected_date & description) ==========
if ($page == 'clear-job-value' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $type = $data['type'] ?? '';
    $name = $data['name'] ?? '';
    
    if (empty($type) || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Missing fields.']);
        exit();
    }
    
    $columnMap = [
        'do_collected_date'     => 'do_collected_date',
        'description'           => 'description',
        'bonded_carrier'        => 'bonded_carrier',
        'customer_name'         => 'customer_name',
        'consignee'             => 'consignee',
        'container_size'        => 'container_size',
        'dry_port'              => 'dry_port',
        'vessel_name'           => 'vessel_name',
        'shipping_line'         => 'shipping_line',
        'freight_forwarder_name'=> 'freight_forwarder_name',
        'depositor'             => 'depositor'
    ];
    
    if (!isset($columnMap[$type])) {
        echo json_encode(['success' => false, 'message' => 'Invalid type.']);
        exit();
    }
    
    $col = $columnMap[$type];
    
    // Clear the value from jobs table
    $stmt = $pdo->prepare("UPDATE jobs SET `$col` = '' WHERE `$col` = ?");
    $stmt->execute([$name]);
    $affected = $stmt->rowCount();
    
    // Also delete from masters table
    $deleteMaster = $pdo->prepare("DELETE FROM masters WHERE type = ? AND name = ?");
    $deleteMaster->execute([$type, $name]);
    
    unset($_SESSION['masters_cache']);
    echo json_encode([
        'success' => true, 
        'message' => "Value cleared from $affected job(s) and removed from masters."
    ]);
    exit();
}
// ========== delete a job ==========
if ($page == 'delete-job' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $input = json_decode(file_get_contents('php://input'), true);
    $jobNumber = $input['job_number'] ?? '';
    if (empty($jobNumber)) {
        echo json_encode(['success'=>false, 'message'=>'Job number missing.']);
        exit();
    }
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_number = ?");
    $stmt->execute([$jobNumber]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success'=>true, 'message'=>'Job deleted']);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Job not found.']);
    }
    exit();
}

// ========== get single master ==========
if ($page == 'get-master' && isset($_GET['id']) && isAdmin()) {
    $master = getMasterById($pdo, $_GET['id']);
    if ($master) {
        echo json_encode(['success'=>true, 'master'=>$master]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Master not found.']);
    }
    exit();
}

// ========== UPDATE JOB VALUE DIRECTLY ==========
if ($page == 'update-job-value' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $data = json_decode(file_get_contents('php://input'), true);

    $type    = $data['type'] ?? '';
    $oldName = $data['old_name'] ?? '';
    $newName = $data['new_name'] ?? '';

    if (empty($type) || empty($oldName) || empty($newName)) {
        echo json_encode(['success'=>false, 'message'=>'Missing fields.']);
        exit();
    }

    // ✅ FIX: Updated column map with do_collected_date and description
    $columnMap = [
        'bonded_carrier'        => 'bonded_carrier',
        'do_collected_date'     => 'do_collected_date', // Changed from 'do'
        'customer_name'         => 'customer_name',
        'consignee'             => 'consignee',
        'container_size'        => 'container_size',
        'dry_port'              => 'dry_port',
        'vessel_name'           => 'vessel_name',
        'shipping_line'         => 'shipping_line',
        'freight_forwarder_name'=> 'freight_forwarder_name',
        'depositor'             => 'depositor',
        'description'           => 'description' // Added description
    ];

    if (!isset($columnMap[$type])) {
        echo json_encode(['success'=>false, 'message'=>'Invalid type.']);
        exit();
    }

    $col = $columnMap[$type];
    $stmt = $pdo->prepare("UPDATE jobs SET `$col` = ? WHERE `$col` = ?");
    $stmt->execute([$newName, $oldName]);
    $affected = $stmt->rowCount();

    // Update masters table as well
    $pdo->prepare("UPDATE masters SET name = ? WHERE type = ? AND name = ?")->execute([$newName, $type, $oldName]);

    unset($_SESSION['masters_cache']);
    echo json_encode(['success'=>true, 'message'=>"$affected job(s) updated."]);
    exit();
}

// ========== DELETE JOB VALUE DIRECTLY ==========
if ($page == 'delete-job-value' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $data = json_decode(file_get_contents('php://input'), true);

    $type = $data['type'] ?? '';
    $name = $data['name'] ?? '';

    if (empty($type) || empty($name)) {
        echo json_encode(['success'=>false, 'message'=>'Missing fields.']);
        exit();
    }

    // ✅ FIX: Updated column map
    $columnMap = [
        'bonded_carrier'        => 'bonded_carrier',
        'do_collected_date'     => 'do_collected_date',
        'customer_name'         => 'customer_name',
        'consignee'             => 'consignee',
        'container_size'        => 'container_size',
        'dry_port'              => 'dry_port',
        'vessel_name'           => 'vessel_name',
        'shipping_line'         => 'shipping_line',
        'freight_forwarder_name'=> 'freight_forwarder_name',
        'depositor'             => 'depositor',
        'description'           => 'description'
    ];

    if (!isset($columnMap[$type])) {
        echo json_encode(['success'=>false, 'message'=>'Invalid type.']);
        exit();
    }

    $col = $columnMap[$type];

    $stmt = $pdo->prepare("UPDATE jobs SET `$col` = '' WHERE `$col` = ?");
    $stmt->execute([$name]);
    $affected = $stmt->rowCount();

    unset($_SESSION['masters_cache']);
    echo json_encode(['success'=>true, 'message'=>"Value removed from $affected job(s)."]);
    exit();
}

// Optional: consignee name change handled separately
if ($page == 'update-jobs-consignee' && $_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
    $data = json_decode(file_get_contents('php://input'), true);
    $oldName = $data['old_name'] ?? '';
    $newName = $data['new_name'] ?? '';
    if ($oldName && $newName) {
        $stmt = $pdo->prepare("UPDATE jobs SET consignee = ? WHERE consignee = ?");
        $stmt->execute([$newName, $oldName]);
        $count = $stmt->rowCount();
        unset($_SESSION['masters_cache']);
        echo json_encode(['success' => true, 'message' => "Updated $count job(s) successfully!"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid names']);
    }
    exit();
}
?>