<?php
try {
    $id = $_POST['id'];
    
    $stmt = $pdo->prepare("UPDATE karachi_clearance SET date = ?, shipper = ?, on_account = ?, clearing_agent = ?, importer_name = ?, bl_no = ?, container_no = ?, description = ?, vessel_name = ?, eta = ?, soc = ?, status = ? WHERE id = ?");
    
    $stmt->execute([
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
        $_POST['status'] ?? 'active',
        $id
    ]);
    
    $_SESSION['success'] = "Record updated!";
    header("Location: system.php?page=karachi-clearance");
    exit();
} catch(Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: system.php?page=karachi-clearance");
    exit();
}
?>