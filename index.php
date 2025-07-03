<?php
// Public plugin list
session_start();
require 'db.php';
require 'functions.php';

$siteTitle = getSetting($pdo, 'site_title', 'Minecraft Plugins');
$bannerImg = getSetting($pdo, 'banner', '');
$logoImg = getSetting($pdo, 'logo', '');
$featuredIds = [
    getSetting($pdo, 'featured1'),
    getSetting($pdo, 'featured2'),
    getSetting($pdo, 'featured3')
];

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
$featured = [];
foreach ($featuredIds as $fid) {
    if ($fid) {
        $stmtF = $pdo->prepare("SELECT p.*, $latestFields FROM plugins p WHERE p.id=?");
        $stmtF->execute([$fid]);
        $row = $stmtF->fetch();
        if ($row) $featured[] = $row;
    }
}
if (empty($featured)) {
    $featured = $pdo->query("SELECT p.*, $latestFields FROM plugins p ORDER BY p.created_at DESC LIMIT 3")->fetchAll();
}
$recent = $pdo->query('SELECT p.*, v.version FROM plugin_versions v JOIN plugins p ON v.plugin_id=p.id ORDER BY v.created_at DESC LIMIT 5')->fetchAll();
$latestUpdate = $pdo->query('SELECT * FROM updates ORDER BY created_at DESC LIMIT 1')->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($siteTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
.page-bg{background:linear-gradient(#5d8e76,#436b58);padding-top:70px;}
.content-box{background:#fff;background:rgba(255,255,255,0.95);box-shadow:0 0 10px rgba(0,0,0,0.2);border-radius:.5rem;transition:box-shadow .3s;}
.content-box:hover{box-shadow:0 0 20px rgba(0,0,0,0.3);}
.banner-img{width:100%;height:300px;object-fit:cover;}
.navbar.fixed-top{box-shadow:0 0 5px rgba(0,0,0,0.2);}
</style>
</head>
<body class="py-4 page-bg">
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
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

<div class="container">

<?php if ($bannerImg): ?>
<div class="mb-4 position-relative">
    <img src="<?= htmlspecialchars($bannerImg) ?>" class="banner-img" alt="Banner">
    <div class="position-absolute top-50 start-50 translate-middle">
        <div class="bg-white bg-opacity-75 p-3 rounded">
        <div id="featuredCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach($featured as $i => $f): ?>
                <div class="carousel-item <?= $i===0?'active':'' ?> text-center">
                    <?php if($f['logo']): ?><img src="<?= htmlspecialchars($f['logo']) ?>" style="height:80px;" class="mb-2" alt="logo"><?php endif; ?>
                    <h5><a href="plugin.php?id=<?= $f['id'] ?>" class="text-dark text-decoration-none fw-bold"><?= htmlspecialchars($f['name']) ?></a></h5>
                    <p><?= htmlspecialchars($f['short_description']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="content-box p-4 mb-4">


<?php if ($latestUpdate): ?>
<div id="updates" class="mb-4">
    <h2>Latest update</h2>
    <h4><?= htmlspecialchars($latestUpdate['title']) ?></h4>
    <div><?= $latestUpdate['content'] ?></div>
</div>
<?php endif; ?>

<div class="mb-4">
    <h2>Recently updated Plugins</h2>
    <ul>
        <?php foreach ($recent as $r): ?>
            <li><a href="plugin.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></a> (<?= htmlspecialchars($r['version']) ?>)</li>
        <?php endforeach; ?>
    </ul>
</div>

<h2>All Plugins</h2>
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
            <td><a class="btn btn-primary" href="download.php?id=<?= $p['id'] ?>">Download</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

</div>

<footer class="text-center mt-4">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteTitle) ?></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
