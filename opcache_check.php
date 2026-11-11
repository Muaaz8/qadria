<?php
include('config/database.php');

echo "<h2>Creating Cache</h2>";

$types = [
    'bonded_carrier' => "SELECT DISTINCT bonded_carrier as name FROM jobs WHERE bonded_carrier != '' AND bonded_carrier IS NOT NULL LIMIT 500",
    'customer_name' => "SELECT DISTINCT customer_name as name FROM jobs WHERE customer_name != '' AND customer_name IS NOT NULL LIMIT 500",
    'consignee' => "SELECT DISTINCT consignee as name FROM jobs WHERE consignee != '' AND consignee IS NOT NULL LIMIT 500",
    'container_size' => "SELECT DISTINCT container_size as name FROM jobs WHERE container_size != '' AND container_size IS NOT NULL LIMIT 100",
    'dry_port' => "SELECT DISTINCT dry_port as name FROM jobs WHERE dry_port != '' AND dry_port IS NOT NULL LIMIT 500",
    'vessel_name' => "SELECT DISTINCT vessel_name as name FROM jobs WHERE vessel_name != '' AND vessel_name IS NOT NULL LIMIT 500",
    'shipping_line' => "SELECT DISTINCT shipping_line as name FROM jobs WHERE shipping_line != '' AND shipping_line IS NOT NULL LIMIT 500",
];

foreach($types as $key => $sql) {
    echo "Loading $key... ";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
    Cache::set("masters_{$key}", $data, 86400);
    echo "✅ " . count($data) . " records cached<br>";
}

// Also store all in one
$all_masters = [];
foreach($types as $key => $sql) {
    $all_masters[$key . 's'] = Cache::get("masters_{$key}");
}
Cache::set('all_masters_data', $all_masters, 86400);

echo "<br><strong>✅ DONE! Total cache files: " . count(glob(__DIR__ . '/cache/*.cache')) . "</strong>";
?>