<?php
// Serve jar and track downloads
session_start();
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM plugins WHERE id=?');
$stmt->execute([$id]);
$plugin = $stmt->fetch();
if (!$plugin) {
    http_response_code(404);
    exit('Niet gevonden');
}

// Log download
$pdo->prepare('INSERT INTO downloads (plugin_id, downloaded_at) VALUES (?, NOW())')->execute([$id]);

if (!is_file($plugin['file_path'])) {
    exit('Bestand ontbreekt');
}

header('Content-Type: application/java-archive');
header('Content-Disposition: attachment; filename="'.basename($plugin['file_path']).'"');
readfile($plugin['file_path']);
?>
