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
    </script>
</head>
<body class="container py-4">
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Home</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="plugins.php">Plugins</a></li>
                <li class="nav-item"><a class="nav-link" href="updates.php">Updates</a></li>
            </ul>
            <a class="btn btn-outline-secondary" href="admin.php">Admin</a>
        </div>
    </div>
</nav>
<h1><?= htmlspecialchars($plugin['name']) ?></h1>
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
    <table class="table">
    <thead><tr><th>Version</th><th>MC Version</th><th></th></tr></thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare('SELECT * FROM plugin_versions WHERE plugin_id=? ORDER BY created_at DESC');
        $stmt->execute([$plugin['id']]);
        foreach ($stmt as $row) {
            echo '<tr><td>'.htmlspecialchars($row['version']).'</td><td>'.htmlspecialchars($row['mc_version']).'</td><td><a class="btn btn-primary" href="download.php?id='.$row['id'].'">Download</a></td></tr>';
        }
        ?>
    </tbody>
    </table>
</div>
<footer class="text-center mt-4">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteTitle) ?></footer>
</body>
</html>
