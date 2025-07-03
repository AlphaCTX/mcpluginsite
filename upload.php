<?php
// Handle plugin upload via AJAX
session_start();
require 'db.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit('Forbidden');
}

$name = $_POST['name'] ?? '';
$version = $_POST['version'] ?? '';
$mc_version = $_POST['mc_version'] ?? '';
$description = $_POST['description'] ?? '';
$file = $_FILES['file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    exit('Fout bij upload');
}

// Validate file
if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'jar') {
    exit('Alleen .jar toegestaan');
}
if ($file['size'] > 5 * 1024 * 1024) {
    exit('Bestand te groot');
}

$target = 'uploads/'.time().'_'.basename($file['name']);
move_uploaded_file($file['tmp_name'], $target);

$stmt = $pdo->prepare('INSERT INTO plugins (name, version, mc_version, description, file_path, created_at) VALUES (?,?,?,?,?,NOW())');
$stmt->execute([$name, $version, $mc_version, $description, $target]);

echo 'Upload succesvol';
?>
