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

// Load versions early so we can build Minecraft filter options
$vstmt = $pdo->prepare('SELECT * FROM plugin_versions WHERE plugin_id=? ORDER BY created_at DESC');
$vstmt->execute([$id]);
$versions = $vstmt->fetchAll();
$mcOpts = [];
foreach ($versions as $row) {
    foreach (explode(',', $row['mc_version']) as $mv) {
        $mcOpts[$mv] = true;
    }
}

$siteTitle = getSetting($pdo, 'site_title', 'Minecraft Plugins');
$logoImg = getSetting($pdo, 'logo', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($plugin['name']) ?> - <?= htmlspecialchars($siteTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
    .page-bg{background:linear-gradient(#5d8e76,#436b58);min-height:100vh;display:flex;flex-direction:column;}
    .with-navbar{padding-top:90px;}
    .content-box{background:#fff;background:rgba(255,255,255,0.95);box-shadow:0 0 10px rgba(0,0,0,0.2);border-radius:.5rem;transition:box-shadow .3s;}
    .content-box:hover{box-shadow:0 0 20px rgba(0,0,0,0.3);}
    .navbar.fixed-top{box-shadow:0 0 5px rgba(0,0,0,0.2);}
    .content-box img{max-width:100%;height:auto;}
    </style>
    <script>
function showTab(t) {
    document.getElementById('desc').style.display = t==='desc'? 'block':'none';
    document.getElementById('downloads').style.display = t==='dl'? 'block':'none';
}

function highlightLatest(){
    const val=document.getElementById('verFilter').value;
    const rows=Array.from(document.querySelectorAll('#downloads tbody tr[data-id]'));
    rows.forEach(r=>r.classList.remove('table-success'));
    if(!val && rows.length){rows[0].classList.add('table-success');}
}
function filterMC(v){
    document.querySelectorAll('#downloads tbody tr[data-id]').forEach(tr=>{
        if(!v || tr.dataset.mc.includes(v)) tr.style.display=''; else tr.style.display='none';
    });
    document.querySelectorAll('#downloads tbody div.collapse').forEach(c=>c.classList.remove('show'));
    highlightLatest();
}
document.addEventListener('DOMContentLoaded',highlightLatest);
</script>
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
<h1 class="mb-3 d-flex align-items-center">
    <?php if ($plugin['logo']): ?>
    <img src="<?= htmlspecialchars($plugin['logo']) ?>" alt="logo" style="height:60px;" class="me-2">
    <?php endif; ?>
    <?= htmlspecialchars($plugin['name']) ?>
</h1>
<p class="mb-3 text-muted fw-bold"><?= htmlspecialchars($plugin['short_description']) ?></p>
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
        <?php foreach ($versions as $row): ?>
        <tr data-id="<?= $row['id'] ?>" data-mc="<?= htmlspecialchars($row['mc_version']) ?>">
            <td><?= htmlspecialchars($row['version']) ?></td>
            <td><?= htmlspecialchars($row['mc_version']) ?></td>
            <td>
                <a class="btn btn-primary btn-sm" href="download.php?id=<?= $row['id'] ?>">Download</a>
                <?php if($row['changelog']): ?>
                <button class="btn btn-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#log<?= $row['id'] ?>">Changelog</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php if($row['changelog']): ?>
        <tr>
            <td colspan="3" class="bg-light">
                <div class="collapse" id="log<?= $row['id'] ?>">
                    <?= nl2br(htmlspecialchars($row['changelog'])) ?>
                </div>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
    </table>
</div>
</div>
</div>

</main>
<?php renderFooter($pdo); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
