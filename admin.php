<?php
// Admin login and dashboard
session_start();
require 'db.php';
$config = include 'config.php';

// Handle login
if (isset($_POST['username'], $_POST['password'])) {
    if ($_POST['username'] === $config['admin_user'] && $_POST['password'] === $config['admin_pass']) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid login';
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle update actions when admin is logged in
if (isset($_SESSION['admin'])) {
    if (isset($_POST['action']) && $_POST['action'] === 'add_update') {
        $stmt = $pdo->prepare('INSERT INTO updates (title, content, created_at) VALUES (?,?,NOW())');
        $stmt->execute([$_POST['title'], $_POST['content']]);
        header('Location: admin.php#updates');
        exit;
    }
    if (isset($_GET['del_update'])) {
        $stmt = $pdo->prepare('DELETE FROM updates WHERE id=?');
        $stmt->execute([$_GET['del_update']]);
        header('Location: admin.php#updates');
        exit;
    }

    if (isset($_GET['del_plugin'])) {
        $stmt = $pdo->prepare('DELETE FROM plugins WHERE id=?');
        $stmt->execute([$_GET['del_plugin']]);
        header('Location: admin.php#plugins');
        exit;
    }
}

if (!isset($_SESSION['admin'])): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
<h1>Admin Login</h1>
<?php if (!empty($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
<form method="post">
    <div class="mb-3"><input class="form-control" name="username" placeholder="Username"></div>
    <div class="mb-3"><input class="form-control" type="password" name="password" placeholder="Password"></div>
    <button class="btn btn-primary" type="submit">Login</button>
</form>
</body>
</html>
<?php else:
// Dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container py-4">
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#updates">Updates</a></li>
                <li class="nav-item"><a class="nav-link" href="#plugins">Plugins</a></li>
                <li class="nav-item"><a class="nav-link" href="#config">Site config</a></li>
                <li class="nav-item"><a class="nav-link" href="admin.php?logout=1">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<h1>Welcome, admin</h1>
<hr>
<h2 id="plugins">Plugin upload</h2>
<form id="uploadForm" enctype="multipart/form-data">
    <div class="mb-2"><input class="form-control" name="name" placeholder="Name" required></div>
    <div class="mb-2"><input class="form-control" name="version" placeholder="Version" required></div>
    <div class="mb-2"><input class="form-control" name="mc_version" placeholder="MC Version" required></div>
    <div class="mb-2"><textarea class="form-control" name="description" placeholder="Description" required></textarea></div>
    <div class="mb-2"><input type="file" name="file" accept=".jar" required></div>
    <div class="progress mb-2"><div id="bar" class="progress-bar" style="width:0%"></div></div>
    <button class="btn btn-primary" type="submit">Upload</button>
</form>
<div id="uploadMsg" class="mt-2"></div>
<hr>
<h2 class="mt-4">Plugins</h2>
<table class="table" id="pluginTable">
<thead><tr><th>Name</th><th>Version</th><th>Downloads</th><th></th></tr></thead>
<tbody>
<?php
$stmt = $pdo->query('SELECT p.*, (SELECT COUNT(*) FROM downloads d WHERE d.plugin_id=p.id) as dl FROM plugins p');
foreach ($stmt as $row) {
    echo '<tr><td>'.htmlspecialchars($row['name']).'</td><td>'.htmlspecialchars($row['version']).'</td><td>'.$row['dl'].'</td><td><a class="btn btn-sm btn-danger" href="admin.php?del_plugin='.$row['id'].'">Delete</a></td></tr>';
}
?>
</tbody>
</table>
<hr>
<h2 id="updates" class="mt-4">Updates</h2>
<form method="post" class="mb-3">
    <input type="hidden" name="action" value="add_update">
    <div class="mb-2"><input class="form-control" name="title" placeholder="Title" required></div>
    <div class="mb-2"><textarea class="form-control" name="content" placeholder="Content" required></textarea></div>
    <button class="btn btn-primary" type="submit">Add update</button>
</form>
<table class="table">
    <thead><tr><th>Title</th><th>Created</th><th></th></tr></thead>
    <tbody>
    <?php
    $updates = $pdo->query('SELECT * FROM updates ORDER BY created_at DESC')->fetchAll();
    foreach ($updates as $u) {
        echo '<tr><td>'.htmlspecialchars($u['title']).'</td><td>'.$u['created_at'].'</td><td><a href="admin.php?del_update='.$u['id'].'" class="btn btn-sm btn-danger">Delete</a></td></tr>';
    }
    ?>
    </tbody>
</table>
<h2>Download statistics</h2>
<canvas id="chart" width="400" height="200"></canvas>
<script>
// Upload with progress bar
const form=document.getElementById('uploadForm');
form.addEventListener('submit',e=>{
    e.preventDefault();
    const xhr=new XMLHttpRequest();
    xhr.open('POST','upload.php');
    xhr.upload.onprogress=ev=>{
        document.getElementById('bar').style.width=(ev.loaded/ev.total*100)+'%';
    };
    xhr.onload=()=>{
        document.getElementById('uploadMsg').innerText=xhr.responseText;
        document.getElementById('bar').style.width='0%';
    };
    xhr.send(new FormData(form));
});

// Chart data via AJAX
fetch('stats.php').then(r=>r.json()).then(data=>{
    new Chart(document.getElementById('chart'),{
        type:'line',
        data:{labels:data.labels,datasets:[{label:'Downloads',data:data.counts}]},
    });
});
</script>
</body>
</html>
<?php endif; ?>
