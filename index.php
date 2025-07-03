<?php
// Public plugin list
session_start();
require 'db.php';

$search = $_GET['q'] ?? '';
if ($search) {
    $stmt = $pdo->prepare('SELECT * FROM plugins WHERE name LIKE ? OR version LIKE ? OR mc_version LIKE ? ORDER BY created_at DESC');
    $stmt->execute(['%'.$search.'%','%'.$search.'%','%'.$search.'%']);
} else {
    $stmt = $pdo->query('SELECT * FROM plugins ORDER BY created_at DESC');
}
$plugins = $stmt->fetchAll();
$featured = $pdo->query('SELECT p.*, COUNT(d.id) as dl FROM plugins p LEFT JOIN downloads d ON d.plugin_id=p.id GROUP BY p.id ORDER BY dl DESC LIMIT 3')->fetchAll();
$recent = $pdo->query('SELECT * FROM plugins ORDER BY created_at DESC LIMIT 5')->fetchAll();
$latestUpdate = $pdo->query('SELECT * FROM updates ORDER BY created_at DESC LIMIT 1')->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Minecraft Plugins</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Home</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Plugins</a></li>
                <li class="nav-item"><a class="nav-link" href="#updates">Updates</a></li>
            </ul>
            <a class="btn btn-outline-secondary" href="admin.php">Admin</a>
        </div>
    </div>
</nav>

<div class="mb-4">
    <form class="d-flex" method="get">
        <input class="form-control me-2" type="search" name="q" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
</div>

<div class="mb-4 p-4 bg-light">
    <h2>Featured</h2>
    <div class="row">
        <?php foreach ($featured as $f): ?>
        <div class="col-md-4">
            <h5><?= htmlspecialchars($f['name']) ?></h5>
            <p><?= htmlspecialchars($f['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($latestUpdate): ?>
<div id="updates" class="mb-4">
    <h2>Latest update</h2>
    <h4><?= htmlspecialchars($latestUpdate['title']) ?></h4>
    <p><?= nl2br(htmlspecialchars(substr($latestUpdate['content'],0,200))) ?></p>
</div>
<?php endif; ?>

<div class="mb-4">
    <h2>Recently updated Plugins</h2>
    <ul>
        <?php foreach ($recent as $r): ?>
            <li><?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['version']) ?>)</li>
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
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['version']) ?></td>
            <td><?= htmlspecialchars($p['mc_version']) ?></td>
            <td><?= nl2br(htmlspecialchars($p['description'])) ?></td>
            <td><a class="btn btn-primary" href="download.php?id=<?= $p['id'] ?>">Download</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<footer class="text-center mt-4">&copy; <?= date('Y') ?> Plugin Site</footer>
</body>
</html>
