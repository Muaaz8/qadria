<?php
$job_id = $_GET['job_id'];
$record = getLoadingFileByJobId($pdo, $job_id);
header('Content-Type: application/json');
echo json_encode($record);
?>