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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Updates - <?= htmlspecialchars($siteTitle) ?></title>
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
<h1>Updates</h1>
<?php foreach ($updates as $u): ?>
<div class="mb-4">
    <h3><?= htmlspecialchars($u['title']) ?></h3>
    <div><?= $u['content'] ?></div>
    <small class="text-muted"><?= $u['created_at'] ?></small>
</div>
<?php endforeach; ?>
</div>

</div>

</main>
<?php renderFooter($pdo); ?>
</body>
</html>
