<?php
try {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM karachi_clearance WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = "Record deleted!";
    header("Location: system.php?page=karachi-clearance");
    exit();
} catch(Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: system.php?page=karachi-clearance");
    exit();
}
?>