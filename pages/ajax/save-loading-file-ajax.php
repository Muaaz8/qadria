<?php
try {
    $userId = getUserId();
    
    $data = [
        'id' => $_POST['id'] ?? '',
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
        'created_at' => $_POST['created_at'] ?? '',
        'lahore_arrival' => $_POST['lahore_arrival'] ?? '',
        'status' => $_POST['status'] ?? 'Pending',
        'vehicles_no' => $_POST['vehicles_no'] ?? '',
        'trucker_cell' => $_POST['trucker_cell'] ?? '',
        'soc' => $_POST['soc'] ?? 'No'
    ];
    
    $result = saveLoadingFile($pdo, $data, $userId);
    
    if ($result) {
        if (empty($data['id'])) {
            $stmt = $pdo->prepare("SELECT lf.*, j.job_number as job_number_ref, j.consignee, j.customer_name FROM loading_files lf LEFT JOIN jobs j ON lf.job_id = j.id WHERE lf.id = ?");
            $stmt->execute([$result]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'record' => $record]);
        } else {
            $stmt = $pdo->prepare("SELECT lf.*, j.job_number as job_number_ref, j.consignee, j.customer_name FROM loading_files lf LEFT JOIN jobs j ON lf.job_id = j.id WHERE lf.id = ?");
            $stmt->execute([$data['id']]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'record' => $record]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save record!']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>