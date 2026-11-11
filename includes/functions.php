<?php
// Make sure this file has the getUserId function
function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function formatCurrency($amount) {
    return 'PKR ' . number_format(floatval($amount), 2);
}

function numberToWords($num) {
    if($num == 0) return "Zero";
    $ones = array("", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen");
    $tens = array("", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety");
    
    if ($num < 20) return $ones[$num];
    if ($num < 100) return $tens[floor($num / 10)] . ($num % 10 ? " " . $ones[$num % 10] : "");
    if ($num < 1000) return $ones[floor($num / 100)] . " Hundred" . ($num % 100 ? " " . numberToWords($num % 100) : "");
    if ($num < 100000) return numberToWords(floor($num / 1000)) . " Thousand" . ($num % 1000 ? " " . numberToWords($num % 1000) : "");
    if ($num < 10000000) return numberToWords(floor($num / 100000)) . " Lakh" . ($num % 100000 ? " " . numberToWords($num % 100000) : "");
    return numberToWords(floor($num / 10000000)) . " Crore" . ($num % 10000000 ? " " . numberToWords($num % 10000000) : "");
}

function textAmountToNumber($text) {
    $text = strtolower(trim($text));
    if (is_numeric($text)) return floatval($text);
    $text = str_replace(',', '', $text);
    $text = str_replace(' ', '', $text);
    preg_match('/(\d+(?:\.\d+)?)/', $text, $matches);
    if (!empty($matches)) return floatval($matches[1]);
    return 0;
}

function createInvoiceFolder($year, $consignee, $clearing_agent) {
    global $base_dir;
    
    $consignee_clean = trim($consignee);
    $clearing_agent_clean = trim($clearing_agent);
    
    if (!empty($consignee_clean) && !empty($clearing_agent_clean)) {
        $folder_name = $consignee_clean . " - " . $clearing_agent_clean;
    } elseif (!empty($consignee_clean)) {
        $folder_name = $consignee_clean;
    } elseif (!empty($clearing_agent_clean)) {
        $folder_name = $clearing_agent_clean;
    } else {
        $folder_name = "Unknown_Invoice";
    }
    
    $folder_name = str_replace('/', '_', $folder_name);
    $folder_name = str_replace('\\', '_', $folder_name);
    $folder_name = str_replace(':', '_', $folder_name);
    $folder_name = str_replace('*', '_', $folder_name);
    $folder_name = str_replace('?', '_', $folder_name);
    $folder_name = str_replace('"', '_', $folder_name);
    $folder_name = str_replace('<', '_', $folder_name);
    $folder_name = str_replace('>', '_', $folder_name);
    $folder_name = str_replace('|', '_', $folder_name);
    $folder_name = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $folder_name);
    
    $year_path = $base_dir . $year . '/';
    $folder_path = $year_path . $folder_name . '/';
    
    if (!file_exists($year_path)) mkdir($year_path, 0777, true);
    if (!file_exists($folder_path)) mkdir($folder_path, 0777, true);
    
    return $folder_path;
}

function getUserRecentHeadings($pdo, $bill_type, $user_id) {
    $stmt = $pdo->prepare("SELECT DISTINCT heading FROM user_recent_headings WHERE bill_type = ? AND user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$bill_type, $user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function saveUserRecentHeading($pdo, $bill_type, $heading, $user_id) {
    if (empty($heading)) return;
    
    $stmt = $pdo->prepare("SELECT id FROM user_recent_headings WHERE bill_type = ? AND heading = ? AND user_id = ?");
    $stmt->execute([$bill_type, $heading, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE user_recent_headings SET created_at = NOW() WHERE bill_type = ? AND heading = ? AND user_id = ?");
        $stmt->execute([$bill_type, $heading, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_recent_headings (bill_type, heading, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$bill_type, $heading, $user_id]);
    }
}

function getCombinedHeadings($pdo, $bill_type, $user_id) {
    $default_headings = getBillTypeHeadings($bill_type);
    $recent_headings = getUserRecentHeadings($pdo, $bill_type, $user_id);
    
    $combined = [];
    foreach ($recent_headings as $heading) {
        $combined[] = $heading;
    }
    foreach ($default_headings as $heading) {
        if (!in_array($heading, $combined)) {
            $combined[] = $heading;
        }
    }
    return $combined;
}

function getSavedHeadings($pdo, $bill_type = null) {
    if ($bill_type) {
        $stmt = $pdo->prepare("SELECT DISTINCT heading FROM bill_items WHERE bill_type = ? OR bill_type IS NULL ORDER BY id DESC LIMIT 100");
        $stmt->execute([$bill_type]);
    } else {
        $stmt = $pdo->prepare("SELECT DISTINCT heading FROM bill_items ORDER BY id DESC LIMIT 100");
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getPreviousBillItems($pdo, $job_id, $bill_type, $user_id) {
    $stmt = $pdo->prepare("
        SELECT bi.heading, bi.amount, bi.receipt_status 
        FROM bills b 
        JOIN bill_items bi ON b.id = bi.bill_id 
        WHERE b.job_id = ? AND b.bill_type = ? 
        AND b.created_by = ?
        ORDER BY b.created_at DESC, bi.sort_order 
        LIMIT 50
    ");
    $stmt->execute([$job_id, $bill_type, $user_id]);
    return $stmt->fetchAll();
}

function getJobBillsSummary($pdo, $job_id) {
    $stmt = $pdo->prepare("
        SELECT b.id, b.bill_number, b.bill_type, b.total_amount, b.balance_amount, 
               b.is_revised, b.created_at, b.created_by,
               COUNT(bi.id) as items_count
        FROM bills b 
        LEFT JOIN bill_items bi ON b.id = bi.bill_id 
        WHERE b.job_id = ? 
        GROUP BY b.id 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$job_id]);
    return $stmt->fetchAll();
}

function saveInvoiceHTML($bill, $items, $year, $consignee, $clearing_agent, $job_details) {
    global $base_dir;
    $folder_path = createInvoiceFolder($year, $consignee, $clearing_agent);
    $filename = $bill['bill_number'] . '.html';
    $full_path = $folder_path . $filename;
    
    $filtered_items = array_filter($items, function($item) {
        return floatval($item['amount'] ?? 0) > 0;
    });
    
    $logo_absolute_path = __DIR__ . '/../img/logo.png';
    $logo_exists = file_exists($logo_absolute_path) ? 'img/logo.png' : '';
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Qadria International - Invoice ' . htmlspecialchars($bill['bill_number'] ?? '') . '</title><style>
        body { font-family: Arial, sans-serif; margin:20px; }
        .invoice-container { max-width:1000px; margin:0 auto; }
        .header { text-align:center; border-bottom:2px solid #8e181e; padding-bottom:20px; margin-bottom:30px; }
        .logo-img { height:50px; margin-bottom:10px; }
        .title { text-align:center; font-size:18px; font-weight:bold; color:#8e181e; margin:20px 0; }
        .bill-info { display:flex; justify-content:space-between; margin-bottom:30px; padding:15px; background:#f8fafd; border-radius:8px; }
        .shipment-details { margin-bottom:30px; padding:15px; background:#fff8e1; border-radius:8px; }
        .shipment-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }
        .items-table { width:100%; border-collapse:collapse; margin-bottom:30px; }
        .items-table th { background:#8e181e; color:white; padding:10px; text-align:left; }
        .items-table td { padding:10px; border-bottom:1px solid #ddd; }
        .total-section { text-align:right; margin-top:20px; padding-top:20px; border-top:2px solid #8e181e; }
        .footer { text-align:center; margin-top:50px; font-size:10px; color:#666; }
        @media print { .no-print { display:none; } }
    </style></head><body><div class="invoice-container"><div class="header">';
    
    if ($logo_exists) {
        $html .= '<img class="logo-img" src="' . $logo_exists . '" alt="Logo">';
    } else {
        $html .= '<h2>QADIRA INTERNATIONAL</h2>';
    }
    
    $html .= '<p>Suite # 123, Business Center, Karachi, Pakistan</p><p>Tel: 32420877 | Email: Qadriainternational5@gmail.com</p></div>';
    $html .= '<div class="title">' . ($bill['is_revised'] ?? false ? 'REVISED ' : '') . strtoupper($bill['bill_type'] ?? '') . ' BILL</div>';
    $html .= '<div class="bill-info"><div><p><strong>Customer Name:</strong> ' . htmlspecialchars($clearing_agent ?? '') . '</p><p><strong>Consignee:</strong> ' . htmlspecialchars($consignee ?? '') . '</p></div>';
    $html .= '<div><p><strong>Bill Id:</strong> ' . htmlspecialchars($bill['bill_number'] ?? '') . '</p><p><strong>Date:</strong> ' . date('d-m-Y', strtotime($bill['created_at'] ?? 'now')) . '</p>';
    $html .= '<p><strong>Job #:</strong> ' . htmlspecialchars($job_details['job_number'] ?? '') . '</p></div></div>';
    $html .= '<div class="shipment-details"><h4>SHIPMENT DETAILS</h4><div class="shipment-grid">';
    $html .= '<p><strong>BL Number:</strong> ' . htmlspecialchars($job_details['bl_number'] ?? '-') . '</p>';
    $html .= '<p><strong>Container Number:</strong> ' . htmlspecialchars($job_details['container_number'] ?? '-') . '</p>';
    $html .= '<p><strong>Container Size:</strong> ' . htmlspecialchars($job_details['container_size'] ?? '-') . '</p>';
    $html .= '<p><strong>Vessel Name:</strong> ' . htmlspecialchars($job_details['vessel'] ?? '-') . '</p>';
    $html .= '<p><strong>Port of Loading:</strong> ' . htmlspecialchars($job_details['port_of_loading'] ?? '-') . '</p>';
    $html .= '<p><strong>Port of Discharge:</strong> ' . htmlspecialchars($job_details['port_of_discharge'] ?? '-') . '</p>';
    $html .= '<p><strong>Vessel Arrival Date:</strong> ' . (!empty($job_details['arrival_date']) ? date('d-m-Y', strtotime($job_details['arrival_date'])) : '-') . '</p>';
    $html .= '<p><strong>Shipping Line:</strong> ' . htmlspecialchars($job_details['shipping_line'] ?? '-') . '</p>';
    $html .= '<p><strong>Free Time:</strong> ' . htmlspecialchars($job_details['free_time'] ?? '-') . '</p>';
    $html .= '<p><strong>SOC:</strong> ' . htmlspecialchars($job_details['soc'] ?? 'No') . '</p>';
    $html .= '<p><strong>Description:</strong> ' . htmlspecialchars($job_details['description'] ?? '-') . '</p></div></div>';
    
    $html .= '<table class="items-table"><thead><tr><th>#</th><th>Description</th><th>Status</th><th>Amount (PKR)</th></tr></thead><tbody>';
    $counter = 1;
    foreach ($filtered_items as $item) {
        $status = $item['receipt_status'] ?? 'Unreceipted';
        $html .= '<tr><td>' . $counter++ . '</td><td>' . htmlspecialchars($item['heading'] ?? '') . '</td><td>' . htmlspecialchars($status) . '</td><td class="text-right">' . number_format($item['amount'] ?? 0, 2) . '</td></tr>';
    }
    
    $html .= '</tbody></table><div class="total-section">';
    $html .= '<p>Total: PKR ' . number_format($bill['total_amount'] ?? 0, 2) . '</p>';
    $html .= '<p>Advance: PKR ' . number_format($bill['advance_amount'] ?? 0, 2) . '</p>';
    $html .= '<p><strong>Balance: PKR ' . number_format($bill['balance_amount'] ?? 0, 2) . '</strong></p>';
    $html .= '<p><small>' . htmlspecialchars($bill['balance_words'] ?? '') . '</small></p></div>';
    $html .= '<div class="footer"><p>Thank You For Your Business!</p></div></div></body></html>';
    
    file_put_contents($full_path, $html);
    return $full_path;
}

function getBillTypeHeadings($bill_type) {
    $forwarding_headings = [
        "Examination Joint", "TP Charges + Delivery", "Urgent D/O Expenses", "Empty Transportation",
        "M. A Land TP Charges", "M. A Land Delivery Expanse", "License Fee (25,000 * 2)",
        "Load Port Storage Charges", "Detention Charges", "Lahore Returning Expense",
        "Trakker Payment", "Weight Charges", "Empty LHR Drop-Off Charges", "OverWeight Challan",
        "Freight Charges", "Delivery Expenses", "Custom Expense", "Service Charges", "Misc. Expense"
    ];
    
    $detention_headings = [
        "Container Repairing & Washing Expense", "Container Demurrage Expense", 
        "Container Damage & Cleaning Expense", "Empty Back Date Expense", "Transportation",
        "Driver Payment", "Vehicle Detention", "Late D/O", "Security Deposit",
        "Container Rent Expense", "Container Placement Expense", "Lift Off / Lift On Expense"
    ];
    
    if ($bill_type == 'forwarding') return $forwarding_headings;
    elseif ($bill_type == 'detention') return $detention_headings;
    return [];
}

function generateBillNumber($pdo, $job_number, $year) {
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE job_number = ?");
    $stmt->execute([$job_number]);
    $job = $stmt->fetch();
    $job_id = $job ? $job['id'] : 0;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bills WHERE job_id = ?");
    $stmt->execute([$job_id]);
    $count = $stmt->fetchColumn() + 1;
    
    $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);
    $bill_number = "TP-" . $sequence . "-" . $year;
    
    $check = $pdo->prepare("SELECT id FROM bills WHERE bill_number = ?");
    $check->execute([$bill_number]);
    if($check->rowCount() > 0) {
        $bill_number = "TP-" . $sequence . "-" . $year . "-" . rand(10, 99);
    }
    return $bill_number;
}

function generateKarachiClearanceNumber($pdo) {
    $stmt = $pdo->prepare("SELECT karachi_clearance_number FROM karachi_clearance ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $last = $stmt->fetchColumn();
    
    $num = 0;
    if ($last && preg_match('/(\d+)/', $last, $matches)) {
        $num = intval($matches[1]);
    }
    $next_num = $num + 1;
    return str_pad($next_num, 2, '0', STR_PAD_LEFT) . "/KHI/CLR";
}

function getKarachiClearance($pdo, $search = '', $start_date = '', $end_date = '') {
    $sql = "SELECT * FROM karachi_clearance WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (karachi_clearance_number LIKE ? OR shipper LIKE ? OR bl_no LIKE ? OR container_no LIKE ?)";
        $sp = "%$search%";
        $params = [$sp, $sp, $sp, $sp];
    }
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
    }
    $sql .= " ORDER BY id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getMasters($pdo, $type) {
    $stmt = $pdo->prepare("SELECT * FROM masters WHERE type = ? AND is_active = 1 ORDER BY sort_order");
    $stmt->execute([$type]);
    return $stmt->fetchAll();
}

function generateJobNumber($pdo) {
    $stmt = $pdo->prepare("SELECT MAX(CAST(job_number AS UNSIGNED)) FROM jobs WHERE job_number REGEXP '^[0-9]+$'");
    $stmt->execute();
    $max = $stmt->fetchColumn();
    $nextNum = ($max ? $max : 0) + 1;
    return str_pad($nextNum, 3, '0', STR_PAD_LEFT);
}

function checkExistingBills($pdo, $job_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bills WHERE job_id = ?");
    $stmt->execute([$job_id]);
    return $stmt->fetchColumn() > 0;
}

function getExistingBillsList($pdo, $job_id) {
    $stmt = $pdo->prepare("SELECT bill_number, bill_type, total_amount, created_at FROM bills WHERE job_id = ? ORDER BY created_at DESC");
    $stmt->execute([$job_id]);
    return $stmt->fetchAll();
}

function getLoadingFilesGroupedByDate($pdo, $search = '', $start_date = '', $end_date = '') {
    $sql = "SELECT lf.*, j.job_number as job_number_ref FROM loading_files lf LEFT JOIN jobs j ON lf.job_id = j.id WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (j.job_number LIKE ? OR lf.clearing_agent LIKE ? OR lf.importer LIKE ?)";
        $sp = "%$search%";
        $params = [$sp, $sp, $sp];
    }
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND (lf.loading_date BETWEEN ? AND ?)";
        $params[] = $start_date;
        $params[] = $end_date;
    }
    $sql .= " ORDER BY lf.loading_date DESC, lf.id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
    
    $grouped = [];
    foreach ($records as $record) {
        $date = $record['loading_date'] ?? 'No Date';
        if (!isset($grouped[$date])) $grouped[$date] = [];
        $grouped[$date][] = $record;
    }
    return $grouped;
}

function getLoadingFileById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT lf.*, j.job_number as job_number_ref FROM loading_files lf LEFT JOIN jobs j ON lf.job_id = j.id WHERE lf.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getLoadingFileByJobId($pdo, $job_id) {
    $stmt = $pdo->prepare("SELECT * FROM loading_files WHERE job_id = ?");
    $stmt->execute([$job_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveLoadingFile($pdo, $data, $user_id) {
    $table_date = !empty($data['loading_date']) ? $data['loading_date'] : date('Y-m-d');
    
    if (empty($data['id'])) {
        $stmt = $pdo->prepare("INSERT INTO loading_files (job_id, clearing_agent, bonded_carrier, importer, dry_port, container_size, container_no, shipping_line, free_days, vessel_arrival, loading_date, created_at, lahore_arrival, status, vehicles_no, trucker_cell, soc, created_by, table_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $created_at = !empty($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $stmt->execute([
            $data['job_id'], $data['clearing_agent'], $data['bonded_carrier'], $data['importer'],
            $data['dry_port'], $data['container_size'], $data['container_no'], $data['shipping_line'],
            $data['free_days'], $data['vessel_arrival'], $data['loading_date'], $created_at,
            $data['lahore_arrival'], $data['status'], $data['vehicles_no'], $data['trucker_cell'],
            $data['soc'], $user_id, $table_date
        ]);
        return $pdo->lastInsertId();
    } else {
        $stmt = $pdo->prepare("UPDATE loading_files SET clearing_agent=?, bonded_carrier=?, importer=?, dry_port=?, container_size=?, container_no=?, shipping_line=?, free_days=?, vessel_arrival=?, loading_date=?, lahore_arrival=?, status=?, vehicles_no=?, trucker_cell=?, soc=?, table_date=? WHERE id=?");
        $stmt->execute([
            $data['clearing_agent'], $data['bonded_carrier'], $data['importer'], $data['dry_port'],
            $data['container_size'], $data['container_no'], $data['shipping_line'], $data['free_days'],
            $data['vessel_arrival'], $data['loading_date'], $data['lahore_arrival'], $data['status'],
            $data['vehicles_no'], $data['trucker_cell'], $data['soc'], $table_date, $data['id']
        ]);
        return $data['id'];
    }
}

function deleteLoadingFile($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM loading_files WHERE id = ?");
    return $stmt->execute([$id]);
}

function exportJobsToExcel($jobs, $start_date, $end_date) {
    global $exports_dir;
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "jobs_export_{$start_date}_to_{$end_date}_{$timestamp}.xls";
    $filepath = $exports_dir . $filename;
    
    $html = '<html><head><meta charset="UTF-8"><title>Jobs Export</title></head><body>';
    $html .= '<h2>Qadria International - Jobs Report</h2>';
    $html .= '<table border="1"><thead><tr><th>Job #</th><th>Job Date</th><th>Consignee</th><th>Customer Name</th><th>BL #</th><th>Status</th></tr></thead><tbody>';
    foreach($jobs as $job) {
        $html .= '<tr><td>' . htmlspecialchars($job['job_number'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['job_date'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['consignee'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['customer_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['bl_number'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['status'] ?? '') . '</td></tr>';
    }
    $html .= '</tbody></table></body></html>';
    file_put_contents($filepath, $html);
    return $filepath;
}

function exportJobsToPDF($jobs, $start_date, $end_date) {
    global $exports_dir;
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "jobs_export_{$start_date}_to_{$end_date}_{$timestamp}.html";
    $filepath = $exports_dir . $filename;
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Jobs Export</title><style>body{font-family:Arial;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;}th{background:#8e181e;color:white;}</style></head><body>';
    $html .= '<h2>Qadria International - Jobs Report</h2>';
    $html .= '<table><thead><tr><th>Job #</th><th>Job Date</th><th>Consignee</th><th>Customer Name</th><th>BL #</th><th>Status</th></tr></thead><tbody>';
    foreach($jobs as $job) {
        $html .= '<tr><td>' . htmlspecialchars($job['job_number'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['job_date'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['consignee'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['customer_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['bl_number'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($job['status'] ?? '') . '</td></tr>';
    }
    $html .= '</tbody></table></body></html>';
    file_put_contents($filepath, $html);
    return $filepath;
}

function exportBillsToExcel($bills, $start_date, $end_date) {
    global $exports_dir;
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "bills_export_{$start_date}_to_{$end_date}_{$timestamp}.xls";
    $filepath = $exports_dir . $filename;
    
    $html = '<html><head><meta charset="UTF-8"><title>Bills Export</title></head><body>';
    $html .= '<h2>Qadria International - Bills Report</h2>';
    $html .= '<table border="1"><thead><tr><th>Bill #</th><th>Job #</th><th>Type</th><th>Total Amount</th><th>Balance</th><th>Date</th></tr></thead><tbody>';
    foreach($bills as $bill) {
        $html .= '<tr><td>' . htmlspecialchars($bill['bill_number'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($bill['job_number'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($bill['bill_type'] ?? '') . '</td>';
        $html .= '<td>' . number_format($bill['total_amount'] ?? 0, 2) . '</td>';
        $html .= '<td>' . number_format($bill['balance_amount'] ?? 0, 2) . '</td>';
        $html .= '<td>' . htmlspecialchars($bill['created_at'] ?? '') . '</td></tr>';
    }
    $html .= '</tbody></table></body></html>';
    file_put_contents($filepath, $html);
    return $filepath;
}

function exportBillsToPDF($bills, $start_date, $end_date) {
    global $exports_dir;
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "bills_export_{$start_date}_to_{$end_date}_{$timestamp}.html";
    $filepath = $exports_dir . $filename;
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Bills Export</title><style>body{font-family:Arial;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;}th{background:#8e181e;color:white;}</style></head><body>';
    $html .= '<h2>Qadria International - Bills Report</h2>';
    $html .= '<table><thead><tr><th>Bill #</th><th>Job #</th><th>Type</th><th>Total Amount</th><th>Balance</th><th>Date</th></tr></thead><tbody>';
    foreach($bills as $bill) {
        $html .= '<tr><td>' . htmlspecialchars($bill['bill_number'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($bill['job_number'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($bill['bill_type'] ?? '') . '</td>';
        $html .= '<td>PKR ' . number_format($bill['total_amount'] ?? 0, 2) . '</td>';
        $html .= '<td>PKR ' . number_format($bill['balance_amount'] ?? 0, 2) . '</td>';
        $html .= '<td>' . htmlspecialchars($bill['created_at'] ?? '') . '</td></tr>';
    }
    $html .= '</tbody></table></body></html>';
    file_put_contents($filepath, $html);
    return $filepath;
}

function createZipArchive($files, $zip_name) {
    global $exports_dir;
    $zip_path = $exports_dir . $zip_name;
    $zip = new ZipArchive();
    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($files as $file) {
            if (file_exists($file)) $zip->addFile($file, basename($file));
        }
        $zip->close();
        return $zip_path;
    }
    return false;
}
?>