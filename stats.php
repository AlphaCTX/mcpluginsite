<?php
// Return JSON with download counts per day for last 7 days
session_start();
require 'db.php';

$rows = $pdo->query("SELECT DATE(downloaded_at) as d, COUNT(*) as c FROM downloads WHERE downloaded_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY d ORDER BY d")->fetchAll();

$labels = [];
$counts = [];
foreach ($rows as $r) {
    $labels[] = $r['d'];
    $counts[] = (int)$r['c'];
}
header('Content-Type: application/json');
echo json_encode(['labels'=>$labels,'counts'=>$counts]);
?>
