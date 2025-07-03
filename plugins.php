<?php
session_start();
require 'db.php';
require 'functions.php';

$siteTitle = getSetting($pdo, 'site_title', 'Minecraft Plugins');
$logoImg = getSetting($pdo, 'logo', '');
$search = $_GET['q'] ?? '';
$latestFields = '(SELECT version FROM plugin_versions v2 WHERE v2.plugin_id=p.id ORDER BY v2.created_at DESC LIMIT 1) AS version,
    (SELECT mc_version FROM plugin_versions v3 WHERE v3.plugin_id=p.id ORDER BY v3.created_at DESC LIMIT 1) AS mc_version';
if ($search) {
    $stmt = $pdo->prepare("SELECT p.*, $latestFields FROM plugins p LEFT JOIN plugin_versions v ON p.id=v.plugin_id WHERE (p.name LIKE ? OR v.version LIKE ? OR v.mc_version LIKE ?) GROUP BY p.id ORDER BY p.created_at DESC");
    $stmt->execute(['%'.$search.'%','%'.$search.'%','%'.$search.'%']);
} else {
    $stmt = $pdo->query("SELECT p.*, $latestFields FROM plugins p ORDER BY p.created_at DESC");
}
$plugins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plugins - <?= htmlspecialchars($siteTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4" style="background-color:#5d8e76;">
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <?php if ($logoImg): ?>
            <img src="<?= htmlspecialchars($logoImg) ?>" alt="Logo" style="height:40px;">
            <?php endif; ?>
            <span class="ms-2 fw-bold text-dark"><?= htmlspecialchars($siteTitle) ?></span>
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="plugins.php">Plugins</a></li>
                <li class="nav-item"><a class="nav-link" href="updates.php">Updates</a></li>
            </ul>
            <a class="btn btn-outline-secondary" href="admin.php">Admin</a>
        </div>
    </div>
</nav>
<h1>Plugins</h1>
<div class="mb-4">
    <form class="d-flex" method="get">
        <input class="form-control me-2" type="search" name="q" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
</div>
<table class="table">
    <thead>
        <tr><th>Name</th><th>Version</th><th>MC Version</th><th>Description</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($plugins as $p): ?>
        <tr>
            <td><a href="plugin.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a></td>
            <td><?= htmlspecialchars($p['version']) ?></td>
            <td><?= htmlspecialchars($p['mc_version']) ?></td>
            <td><?= htmlspecialchars($p['short_description']) ?></td>
            <td><a class="btn btn-primary" href="plugin.php?id=<?= $p['id'] ?>">View</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<footer class="text-center mt-4">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteTitle) ?></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
