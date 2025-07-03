<?php
// Public plugin list
session_start();
require 'db.php';

$stmt = $pdo->query('SELECT * FROM plugins ORDER BY created_at DESC');
$plugins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Minecraft Plugins</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
<h1>Plugin lijst</h1>
<table class="table">
    <thead>
        <tr><th>Naam</th><th>Versie</th><th>MC Versie</th><th>Beschrijving</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($plugins as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['version']) ?></td>
            <td><?= htmlspecialchars($p['mc_version']) ?></td>
            <td><?= nl2br(htmlspecialchars($p['description'])) ?></td>
            <td><a class="btn btn-primary" href="download.php?id=<?= $p['id'] ?>">Download</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="admin.php">Admin login</a>
</body>
</html>
