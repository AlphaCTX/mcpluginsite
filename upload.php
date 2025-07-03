<?php
// Handle plugin upload via AJAX
session_start();
require 'db.php';
ini_set('upload_max_filesize','20M');
ini_set('post_max_size','20M');

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit('Forbidden');
}

$plugin_id = isset($_POST['plugin_id']) ? (int)$_POST['plugin_id'] : 0;
$name = $_POST['name'] ?? '';
$version = $_POST['version'] ?? '';
$mc_version = '';
if (isset($_POST['mc_version'])) {
    if (is_array($_POST['mc_version'])) {
        $mc_version = implode(',', $_POST['mc_version']);
    } else {
        $mc_version = $_POST['mc_version'];
    }
}
$description = $_POST['description'] ?? '';
$file = $_FILES['file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    exit('Upload error');
}

// Validate file
if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'jar') {
    exit('Only .jar allowed');
}
if ($file['size'] > 20 * 1024 * 1024) {
    exit('File too large');
}


$target = 'uploads/'.time().'_'.basename($file['name']);
move_uploaded_file($file['tmp_name'], $target);

if (!$plugin_id) {
    $stmt = $pdo->prepare('INSERT INTO plugins (name, description, created_at) VALUES (?,?,NOW())');
    $stmt->execute([$name, $description]);
    $plugin_id = $pdo->lastInsertId();
} else if ($description) {
    $stmt = $pdo->prepare('UPDATE plugins SET description=? WHERE id=?');
    $stmt->execute([$description, $plugin_id]);
}

$stmt = $pdo->prepare('INSERT INTO plugin_versions (plugin_id, version, mc_version, file_path, created_at) VALUES (?,?,?,?,NOW())');
$stmt->execute([$plugin_id, $version, $mc_version, $target]);

echo 'Upload successful';
?>
