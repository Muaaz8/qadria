<?php
try {
    $userId = getUserId();
    
    $data = [
        'id' => $_POST['id'],
        'job_id' => $_POST['job_id'],
        'clearing_agent' => $_POST['clearing_agent'] ?? '',
        'bonded_carrier' => $_POST['bonded_carrier'] ?? '',
        'importer' => $_POST['importer'] ?? '',
        'dry_port' => $_POST['dry_port'] ?? '',
        'container_size' => $_POST['container_size'] ?? '',
        'container_no' => $_POST['container_no'] ?? '',
        'shipping_line' => $_POST['shipping_line'] ?? '',
        'free_days' => $_POST['free_days'] ?? '',
        'vessel_arrival' => $_POST['vessel_arrival'] ?? '',
        'loading_date' => $_POST['loading_date'] ?? '',
        'lahore_arrival' => $_POST['lahore_arrival'] ?? '',
        'status' => $_POST['status'] ?? 'Pending',
        'vehicles_no' => $_POST['vehicles_no'] ?? '',
        'trucker_cell' => $_POST['trucker_cell'] ?? '',
        'soc' => $_POST['soc'] ?? 'No'
    ];
    
    $result = saveLoadingFile($pdo, $data, $userId);
    
    if ($result) {
        $_SESSION['success'] = "Record updated!";
    } else {
        $_SESSION['error'] = "Failed to update!";
    }
    header("Location: system.php?page=loading-files");
    exit();
} catch(Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: system.php?page=loading-files");
    exit();
}
?>