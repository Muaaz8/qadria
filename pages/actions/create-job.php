<?php
try {
    $jobNumber = generateJobNumber($pdo);
    $jobDate = !empty($_POST['job_date']) ? $_POST['job_date'] : date('Y-m-d');
    $userId = getUserId();
    
    $bonded_carrier = !empty($_POST['bonded_carrier']) ? $_POST['bonded_carrier'] : ($_POST['bonded_carrier_text'] ?? '');
    $customer_name = !empty($_POST['customer_name']) ? $_POST['customer_name'] : ($_POST['customer_name_text'] ?? '');
    $consignee_val = !empty($_POST['consignee']) ? $_POST['consignee'] : ($_POST['consignee_text'] ?? '');
    $container_size_val = !empty($_POST['container_size']) ? $_POST['container_size'] : ($_POST['container_size_text'] ?? '');
    $khi_port_val = !empty($_POST['khi_port']) ? $_POST['khi_port'] : ($_POST['khi_port_text'] ?? '');
    $dry_port_val = !empty($_POST['dry_port']) ? $_POST['dry_port'] : ($_POST['dry_port_text'] ?? '');
    $vessel_name_val = !empty($_POST['vessel_name']) ? $_POST['vessel_name'] : ($_POST['vessel_name_text'] ?? '');
    $shipping_line_val = !empty($_POST['shipping_line']) ? $_POST['shipping_line'] : ($_POST['shipping_line_text'] ?? '');
    $freight_forwarder_val = !empty($_POST['freight_forwarder_name']) ? $_POST['freight_forwarder_name'] : ($_POST['freight_forwarder_name_text'] ?? '');
    $depositor_val = !empty($_POST['depositor']) ? $_POST['depositor'] : ($_POST['depositor_text'] ?? '');
    
    $stmt = $pdo->prepare("INSERT INTO jobs (job_number, original_documents_date, job_date, bonded_carrier, do_collected_date, customer_name, consignee, bl_number, container_number, container_size, khi_port, dry_port, port_of_loading, port_of_discharge, vessel_name, vessel_arrival_date, shipping_line, freight_forwarder_name, depositor, free_time, detention_tariff, soc, mobile_no, description, remarks, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $jobNumber, 
        !empty($_POST['original_documents_date']) ? $_POST['original_documents_date'] : null,
        $jobDate, 
        $bonded_carrier, 
        !empty($_POST['do_collected_date']) ? $_POST['do_collected_date'] : null,
        $customer_name,
        $consignee_val, 
        $_POST['bl_number'] ?? '', 
        $_POST['container_number'] ?? '', 
        $container_size_val, 
        $khi_port_val, 
        $dry_port_val,
        $_POST['port_of_loading'] ?? '', 
        $_POST['port_of_discharge'] ?? '',
        $vessel_name_val, 
        !empty($_POST['vessel_arrival_date']) ? $_POST['vessel_arrival_date'] : null, 
        $shipping_line_val, 
        $freight_forwarder_val, 
        $depositor_val, 
        $_POST['free_time'] ?? '',
        $_POST['detention_tariff'] ?? '', 
        $_POST['soc'] ?? 'No', 
        $_POST['mobile_no'] ?? '', 
        $_POST['description'] ?? '', 
        $_POST['remarks'] ?? '', 
        $userId
    ]);
    
    $_SESSION['success'] = "Job created successfully! Job Number: " . $jobNumber;
    header("Location: system.php?page=create-job");
    exit();
} catch(Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: system.php?page=create-job");
    exit();
}
?>