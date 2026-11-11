<?php
$userId = getUserId();
$bill_id = $_GET['id'];

if (!canUserViewBill($pdo, $userId, $bill_id)) {
    $_SESSION['error'] = "Permission denied!";
    header("Location: system.php?page=bills");
    exit();
}

if (isAdmin()) {
    $stmt = $pdo->prepare("SELECT pdf_path FROM bills WHERE id = ?");
    $stmt->execute([$bill_id]);
} else {
    $stmt = $pdo->prepare("SELECT pdf_path FROM bills WHERE id = ? AND created_by = ?");
    $stmt->execute([$bill_id, $userId]);
}
$bill = $stmt->fetch();

if ($bill) {
    if (!empty($bill['pdf_path']) && file_exists($bill['pdf_path'])) {
        unlink($bill['pdf_path']);
    }
    $pdo->prepare("DELETE FROM bill_items WHERE bill_id = ?")->execute([$bill_id]);
    $pdo->prepare("DELETE FROM bills WHERE id = ?")->execute([$bill_id]);
    $_SESSION['success'] = "Bill deleted!";
} else {
    $_SESSION['error'] = "Bill not found!";
}
header("Location: system.php?page=bills");
exit();
?>