<?php
$job_id = $_GET['job_id'];
$userId = getUserId();

if (!canUserViewJob($pdo, $userId, $job_id)) {
    echo '<div class="error-message">Access denied!</div>';
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM bills WHERE job_id = ? ORDER BY created_at DESC");
$stmt->execute([$job_id]);
$bills = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT job_number, consignee FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

$html = '<h3>Bill Summary for Job #' . htmlspecialchars($job['job_number'] ?? '') . '</h3>';
$html .= '<p><strong>Consignee:</strong> ' . htmlspecialchars($job['consignee'] ?? '') . '</p>';
$html .= '<div class="table-container"><table class="data-table"><thead><tr><th>Bill #</th><th>Type</th><th>Total</th><th>Balance</th><th>Date</th></tr></thead><tbody>';
foreach ($bills as $bill) {
    $html .= '<tr><td>' . htmlspecialchars($bill['bill_number'] ?? '') . '</td><td>' . ucfirst($bill['bill_type'] ?? '') . '</td><td>' . formatCurrency($bill['total_amount'] ?? 0) . '</td><td>' . formatCurrency($bill['balance_amount'] ?? 0) . '</td><td>' . date('M d, Y', strtotime($bill['created_at'] ?? 'now')) . '</td></tr>';
}
$html .= '</tbody></table></div>';
echo $html;
?>