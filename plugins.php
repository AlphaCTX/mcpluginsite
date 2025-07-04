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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plugins - <?= htmlspecialchars($siteTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
    .page-bg{background:linear-gradient(#5d8e76,#436b58);min-height:100vh;display:flex;flex-direction:column;}
    .with-navbar{padding-top:90px;}
    .content-box{background:#fff;background:rgba(255,255,255,0.95);box-shadow:0 0 10px rgba(0,0,0,0.2);border-radius:.5rem;transition:box-shadow .3s;}
    .content-box:hover{box-shadow:0 0 20px rgba(0,0,0,0.3);}
    .navbar.fixed-top{box-shadow:0 0 5px rgba(0,0,0,0.2);}
    </style>
</head>
<body class="page-bg with-navbar">
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <?php if ($logoImg): ?>
            <img src="<?= htmlspecialchars($logoImg) ?>" alt="Logo" style="height:40px;">
            <?php endif; ?>
            <span class="ms-2 fw-bold text-dark"><?= htmlspecialchars($siteTitle) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="plugins.php">Plugins</a></li>
                <li class="nav-item"><a class="nav-link" href="updates.php">Updates</a></li>
            </ul>
            <a class="btn btn-outline-secondary" href="admin.php">Admin</a>
        </div>
    </div>
</nav>

<main class="flex-grow-1 py-4">

<div class="container">
<div class="content-box p-4">
<h1>Plugins</h1>
<div class="mb-4">
    <form class="d-flex" method="get">
        <input class="form-control me-2" type="search" name="q" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
</div>
<div class="table-responsive">
<table class="table">
    <thead>
        <tr><th>Name</th><th>Version</th><th>MC Version</th><th>Description</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($plugins as $p): ?>
        <tr>
            <td>
                <?php if($p['logo']): ?>
                <img src="<?= htmlspecialchars($p['logo']) ?>" alt="logo" style="height:40px;" class="me-2">
                <?php endif; ?>
                <a class="text-decoration-none fw-bold text-dark" href="plugin.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
            </td>
            <td><?= htmlspecialchars($p['version']) ?></td>
            <td><?= htmlspecialchars($p['mc_version']) ?></td>
            <td><?= htmlspecialchars($p['short_description']) ?></td>
            <td><a class="btn btn-primary" href="plugin.php?id=<?= $p['id'] ?>">View</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
</div>
</div>

</div>
</main>
<?php renderFooter($pdo); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
