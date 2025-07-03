<?php
// Return JSON with download counts per day for last 7 days
session_start();
require 'db.php';

$sql = "SELECT DATE(d.downloaded_at) as d, COUNT(*) as c FROM downloads d JOIN plugin_versions v ON d.version_id=v.id WHERE d.downloaded_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$params = [];
if (!empty($_GET['plugin'])) {
    $sql .= " AND v.plugin_id=?";
    $params[] = (int)$_GET['plugin'];
}
if (!empty($_GET['version'])) {
    $sql .= " AND v.id=?";
    $params[] = (int)$_GET['version'];
}
if (!empty($_GET['mc_version'])) {
    $sql .= " AND v.mc_version LIKE ?";
    $params[] = '%' . $_GET['mc_version'] . '%';
}
$sql .= " GROUP BY d ORDER BY d";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$labels = [];
$counts = [];
foreach ($rows as $r) {
    $labels[] = $r['d'];
    $counts[] = (int)$r['c'];
}
header('Content-Type: application/json');
echo json_encode(['labels'=>$labels,'counts'=>$counts]);
?>
