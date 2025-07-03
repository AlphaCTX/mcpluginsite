<?php
// Serve jar and track downloads
session_start();
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM plugin_versions WHERE id=?');
$stmt->execute([$id]);
$version = $stmt->fetch();
if (!$version) {
    http_response_code(404);
    exit('Not found');
}
$pdo->prepare('INSERT INTO downloads (version_id, downloaded_at) VALUES (?, NOW())')->execute([$id]);

if (!is_file($version['file_path'])) {
    exit('Bestand ontbreekt');
}

header('Content-Type: application/java-archive');
header('Content-Disposition: attachment; filename="'.basename($version['file_path']).'"');
readfile($version['file_path']);
?>
