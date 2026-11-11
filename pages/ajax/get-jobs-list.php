<?php
header('Content-Type: application/json');
if (isAdmin()) {
    $jobs = $pdo->query("SELECT id, job_number, consignee, customer_name, bonded_carrier, dry_port, container_size, container_number, shipping_line, vessel_arrival_date, soc FROM jobs ORDER BY job_number DESC")->fetchAll();
} else {
    $jobs = getUserVisibleJobs($pdo, getUserId());
}
echo json_encode($jobs);
?>