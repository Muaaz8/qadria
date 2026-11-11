<?php
try {
    $userId = getUserId();
    $clearance_number = generateKarachiClearanceNumber($pdo);
    
    $stmt = $pdo->prepare("INSERT INTO karachi_clearance (karachi_clearance_number, date, shipper, on_account, clearing_agent, importer_name, bl_no, container_no, description, vessel_name, eta, soc, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $clearance_number,
        $_POST['date'] ?? date('Y-m-d'),
        $_POST['shipper'] ?? '',
        $_POST['on_account'] ?? '',
        $_POST['clearing_agent'] ?? '',
        $_POST['importer_name'] ?? '',
        $_POST['bl_no'] ?? '',
        $_POST['container_no'] ?? '',
        $_POST['description'] ?? '',
        $_POST['vessel_name'] ?? '',
        !empty($_POST['eta']) ? $_POST['eta'] : null,
        $_POST['soc'] ?? 'No',
        $userId
    ]);
    
    $_SESSION['success'] = "Record created! Number: " . $clearance_number;
    header("Location: system.php?page=karachi-clearance");
    exit();
} catch(Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: system.php?page=karachi-clearance");
    exit();
}
?>