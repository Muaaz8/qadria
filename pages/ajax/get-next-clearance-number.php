<?php
$next_number = generateKarachiClearanceNumber($pdo);
echo json_encode(['number' => $next_number]);
?>