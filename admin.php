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
        $error = 'Ongeldige login';
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
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
<h1>Welkom, admin</h1>
<a class="btn btn-secondary" href="admin.php?logout=1">Logout</a>
<hr>
<h2>Plugin upload</h2>
<form id="uploadForm" enctype="multipart/form-data">
    <div class="mb-2"><input class="form-control" name="name" placeholder="Naam" required></div>
    <div class="mb-2"><input class="form-control" name="version" placeholder="Versie" required></div>
    <div class="mb-2"><input class="form-control" name="mc_version" placeholder="MC Versie" required></div>
    <div class="mb-2"><textarea class="form-control" name="description" placeholder="Beschrijving" required></textarea></div>
    <div class="mb-2"><input type="file" name="file" accept=".jar" required></div>
    <div class="progress mb-2"><div id="bar" class="progress-bar" style="width:0%"></div></div>
    <button class="btn btn-primary" type="submit">Upload</button>
</form>
<div id="uploadMsg" class="mt-2"></div>
<hr>
<h2>Plugins</h2>
<table class="table" id="pluginTable">
<thead><tr><th>Naam</th><th>Versie</th><th>Downloads</th></tr></thead>
<tbody>
<?php
$stmt = $pdo->query('SELECT p.*, (SELECT COUNT(*) FROM downloads d WHERE d.plugin_id=p.id) as dl FROM plugins p');
foreach ($stmt as $row) {
    echo '<tr><td>'.htmlspecialchars($row['name']).'</td><td>'.htmlspecialchars($row['version']).'</td><td>'.$row['dl'].'</td></tr>';
}
?>
</tbody>
</table>
<h2>Download statistieken</h2>
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
