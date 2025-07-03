<?php
session_start();
require 'db.php';
require 'functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM plugins WHERE id=?');
$stmt->execute([$id]);
$plugin = $stmt->fetch();
if (!$plugin) {
    http_response_code(404);
    echo 'Plugin not found';
    exit;
}

$siteTitle = getSetting($pdo, 'site_title', 'Minecraft Plugins');
$logoImg = getSetting($pdo, 'logo', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($plugin['name']) ?> - <?= htmlspecialchars($siteTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
function showTab(t) {
    document.getElementById('desc').style.display = t==='desc'? 'block':'none';
    document.getElementById('downloads').style.display = t==='dl'? 'block':'none';
}

function filterMC(v){
    document.querySelectorAll('#downloads tbody tr').forEach(tr=>{
        if(!v || tr.dataset.mc.includes(v)) tr.style.display=''; else tr.style.display='none';
    });
}
</script>
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
<h1 class="mb-3 d-flex align-items-center">
    <?php if ($plugin['logo']): ?>
    <img src="<?= htmlspecialchars($plugin['logo']) ?>" alt="logo" style="height:60px;" class="me-2">
    <?php endif; ?>
    <?= htmlspecialchars($plugin['name']) ?>
</h1>
<p class="mb-3 text-white-50 fw-bold"><?= htmlspecialchars($plugin['short_description']) ?></p>
<div class="mb-3">
    <img src="<?= htmlspecialchars(getSetting($pdo,'banner','')) ?>" class="img-fluid" alt="">
</div>
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" href="#" onclick="showTab('desc');return false;">Description</a></li>
    <li class="nav-item"><a class="nav-link" href="#" onclick="showTab('dl');return false;">Downloads</a></li>
</ul>
<div id="desc">
    <?= $plugin['description'] ?>
</div>
<div id="downloads" style="display:none;">
    <div class="mb-2">
        <select id="verFilter" class="form-select w-auto" onchange="filterMC(this.value)">
            <option value="">All Minecraft versions</option>
            <?php foreach(array_keys($mcOpts) as $mv): ?>
            <option value="<?= htmlspecialchars($mv) ?>"><?= htmlspecialchars($mv) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <table class="table">
    <thead><tr><th>Version</th><th>MC Version</th><th></th></tr></thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare('SELECT * FROM plugin_versions WHERE plugin_id=? ORDER BY created_at DESC');
        $stmt->execute([$plugin['id']]);
        $versions = $stmt->fetchAll();
        $mcOpts = [];
        foreach ($versions as $row) {
            foreach (explode(',', $row['mc_version']) as $mv) $mcOpts[$mv] = true;
        }
        foreach ($versions as $row) {
            echo '<tr data-mc="'.htmlspecialchars($row['mc_version']).'"><td>'.htmlspecialchars($row['version']).'</td><td>'.htmlspecialchars($row['mc_version']).'</td><td><a class="btn btn-primary" href="download.php?id='.$row['id'].'">Download</a></td></tr>';
        }
        ?>
    </tbody>
    </table>
</div>
<footer class="text-center mt-4">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteTitle) ?></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
