<?php
try {
    $job_id = intval($_POST['job_id']);
    $parent_bill_id = intval($_POST['parent_bill_id']);
    $revision_number = intval($_POST['revision_number']);
    $bill_type = $_POST['bill_type'] ?? 'forwarding';
    $userId = getUserId();
    
    if (!canUserViewJob($pdo, $userId, $job_id)) {
        $_SESSION['error'] = "Permission denied!";
        header("Location: system.php?page=bills");
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?"); 
    $stmt->execute([$job_id]); 
    $job = $stmt->fetch();
    
    $year = date('Y');
    $bill_number = "TP-" . ($job['job_number'] ?? 'JOB') . "-R" . $revision_number;
    
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $advance_amount = floatval($_POST['advance_amount'] ?? 0);
    $balance_amount = $total_amount - $advance_amount;
    $balance_words = numberToWords($balance_amount) . " Rupees Only";
    
    $stmt = $pdo->prepare("INSERT INTO bills (bill_number, job_id, bill_type, is_revised, revision_number, parent_bill_id, total_amount, advance_amount, balance_amount, balance_words, notes, created_by) VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$bill_number, $job_id, $bill_type, $revision_number, $parent_bill_id, $total_amount, $advance_amount, $balance_amount, $balance_words, $_POST['notes'] ?? '', $userId]);
    $bill_id = $pdo->lastInsertId();
    
    $headings = isset($_POST['heading']) ? $_POST['heading'] : [];
    $heading_texts = isset($_POST['heading_text']) ? $_POST['heading_text'] : [];
    $amounts = isset($_POST['amount']) ? $_POST['amount'] : [];
    $statuses = isset($_POST['receipt_status']) ? $_POST['receipt_status'] : [];
    
    for ($i = 0; $i < count($headings); $i++) {
        $final_heading = '';
        if (!empty($heading_texts[$i])) {
            $final_heading = $heading_texts[$i];
        } elseif (!empty($headings[$i])) {
            $final_heading = $headings[$i];
        }
        
        if (!empty($final_heading)) {
            $status = isset($statuses[$i]) ? $statuses[$i] : 'Unreceipted';
            $amount_value = textAmountToNumber($amounts[$i] ?? '');
            
            $stmt = $pdo->prepare("INSERT INTO bill_items (bill_id, heading, amount, receipt_status, bill_type, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$bill_id, $final_heading, $amount_value, $status, $bill_type, $i]);
            
            if ($amount_value > 0 && !empty($final_heading)) {
                saveUserRecentHeading($pdo, $bill_type, $final_heading, $userId);
            }
        }
    }
    
    $stmt = $pdo->prepare("SELECT * FROM bills WHERE id = ?"); 
    $stmt->execute([$bill_id]); 
    $bill_data = $stmt->fetch();
    $stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id = ? ORDER BY sort_order"); 
    $stmt->execute([$bill_id]); 
    $items_data = $stmt->fetchAll();
    
    $consignee = $job['consignee'] ?? '';
    $customer_name = $job['customer_name'] ?? '';
    
    $job_details = [
        'job_number' => $job['job_number'] ?? '',
        'bl_number' => $job['bl_number'] ?? '',
        'container_number' => $job['container_number'] ?? '',
        'container_size' => $job['container_size'] ?? '',
        'vessel' => $job['vessel_name'] ?? '',
        'port_of_loading' => $job['port_of_loading'] ?? '',
        'port_of_discharge' => $job['port_of_discharge'] ?? '',
        'arrival_date' => $job['vessel_arrival_date'] ?? '',
        'shipping_line' => $job['shipping_line'] ?? '',
        'clearing_agent' => $job['customer_name'] ?? '',
        'free_time' => $job['free_time'] ?? '',
        'bonded_carrier' => $job['bonded_carrier'] ?? '',
        'do_collected_date' => $job['do_collected_date'] ?? '',
        'khi_port' => $job['khi_port'] ?? '',
        'dry_port' => $job['dry_port'] ?? '',
        'freight_forwarder_name' => $job['freight_forwarder_name'] ?? '',
        'depositor' => $job['depositor'] ?? '',
        'detention_tariff' => $job['detention_tariff'] ?? '',
        'soc' => $job['soc'] ?? 'No',
        'mobile_no' => $job['mobile_no'] ?? '',
        'description' => $job['description'] ?? ''
    ];
    
    $pdf_path = saveInvoiceHTML($bill_data, $items_data, $year, $consignee, $customer_name, $job_details);
    $stmt = $pdo->prepare("UPDATE bills SET pdf_path = ? WHERE id = ?"); 
    $stmt->execute([$pdf_path, $bill_id]);
    
    $_SESSION['success'] = "Revised bill created! Bill Number: " . $bill_number;
    header("Location: system.php?page=view-bill&id=" . $bill_id);
    exit();
} catch (Exception $e) { 
    $_SESSION['error'] = "Error: " . $e->getMessage(); 
    header("Location: system.php?page=bills"); 
    exit(); 
}
?>