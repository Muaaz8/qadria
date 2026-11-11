<?php
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($job_id) {
    $stmt = $pdo->prepare("SELECT id, job_number, consignee, customer_name, bonded_carrier, dry_port, container_size, container_number, shipping_line, vessel_arrival_date, soc FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($job);
} else {
    echo json_encode(null);
}
?>