<?php
session_start();
require 'db.php';
require 'functions.php';
$siteTitle = getSetting($pdo, 'site_title', 'Minecraft Plugins');
$logoImg = getSetting($pdo, 'logo', '');
$updates = $pdo->query('SELECT * FROM updates ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Updates - <?= htmlspecialchars($siteTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4" style="background-color:#5d8e76;">
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <?php if ($logoImg): ?>
            <img src="<?= htmlspecialchars($logoImg) ?>" alt="Logo" style="height:40px;">
            <?php else: ?>Home<?php endif; ?>
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
<h1>Updates</h1>
<?php foreach ($updates as $u): ?>
<div class="mb-4">
    <h3><?= htmlspecialchars($u['title']) ?></h3>
    <div><?= $u['content'] ?></div>
    <small class="text-muted"><?= $u['created_at'] ?></small>
</div>
<?php endforeach; ?>
<footer class="text-center mt-4">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteTitle) ?></footer>
</body>
</html>
