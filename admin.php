<?php
// Admin login and dashboard
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db.php';
require 'functions.php';
$siteLogo = getSetting($pdo, 'logo');
if (!file_exists('config.php')) {
    die('Missing config.php. Copy config.sample.php to config.php.');
}
$config = include 'config.php';
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']);
$page = $_GET['page'] ?? 'plugins';

// Handle login
if (isset($_POST['username'], $_POST['password'])) {
    if ($_POST['username'] === $config['admin_user'] && $_POST['password'] === $config['admin_pass']) {
        $_SESSION['admin'] = true;
        header('Location: admin.php?page=plugins');
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
        if ($isAjax) { exit('OK'); }
        header('Location: admin.php?page=updates');
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'edit_update') {
        $stmt = $pdo->prepare('UPDATE updates SET title=?, content=? WHERE id=?');
        $stmt->execute([$_POST['title'], $_POST['content'], $_POST['id']]);
        if ($isAjax) { exit('OK'); }
        header('Location: admin.php?page=updates');
        exit;
    }
    if (isset($_GET['del_update'])) {
        $stmt = $pdo->prepare('DELETE FROM updates WHERE id=?');
        $stmt->execute([$_GET['del_update']]);
        if ($isAjax) { exit('OK'); }
        header('Location: admin.php?page=updates');
        exit;
    }

    if (isset($_GET['del_plugin'])) {
        $stmt = $pdo->prepare('DELETE FROM plugins WHERE id=?');
        $stmt->execute([$_GET['del_plugin']]);
        if ($isAjax) { exit('OK'); }
        header('Location: admin.php?page=plugins');
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'save_config') {
        setSetting($pdo, 'site_title', $_POST['site_title']);
        foreach (['logo_file' => 'logo', 'favicon_file' => 'favicon', 'banner_file' => 'banner'] as $f => $k) {
            if (isset($_FILES[$f]) && $_FILES[$f]['error'] === UPLOAD_ERR_OK) {
                $path = 'uploads/'.time().'_'.basename($_FILES[$f]['name']);
                move_uploaded_file($_FILES[$f]['tmp_name'], $path);
                setSetting($pdo, $k, $path);
            }
        }
        setSetting($pdo, 'featured1', $_POST['featured1']);
        setSetting($pdo, 'featured2', $_POST['featured2']);
        setSetting($pdo, 'featured3', $_POST['featured3']);
        if ($isAjax) { exit('OK'); }
        header('Location: admin.php?page=config');
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'edit_plugin') {
        $logo='';
        if(isset($_FILES['logo']) && $_FILES['logo']['error']===UPLOAD_ERR_OK){
            $logo='uploads/'.time().'_'.basename($_FILES['logo']['name']);
            move_uploaded_file($_FILES['logo']['tmp_name'],$logo);
        }
        $sql='UPDATE plugins SET name=?, short_description=?, description=?'.($logo?' ,logo=?':'').' WHERE id=?';
        $params=[$_POST['name'],$_POST['short_description'],$_POST['description']];
        if($logo) $params[]=$logo;
        $params[]=$_POST['id'];
        $stmt=$pdo->prepare($sql);
        $stmt->execute($params);
        if ($isAjax) { exit('OK'); }
        header('Location: admin.php?page=plugins');
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_plugin') {
        $logo='';
        if(isset($_FILES['logo']) && $_FILES['logo']['error']===UPLOAD_ERR_OK){
            $logo='uploads/'.time().'_'.basename($_FILES['logo']['name']);
            move_uploaded_file($_FILES['logo']['tmp_name'],$logo);
        }
        $stmt=$pdo->prepare('INSERT INTO plugins (name,short_description,description,logo,created_at) VALUES (?,?,?,?,NOW())');
        $stmt->execute([$_POST['name'],$_POST['short_description'],$_POST['description'],$logo]);
        if ($isAjax) { exit('OK'); }
        header('Location: admin.php?page=plugins');
        exit;
    }

    if(isset($_POST['action']) && $_POST['action']==='edit_version'){
        $stmt=$pdo->prepare('UPDATE plugin_versions SET version=?, mc_version=?, changelog=? WHERE id=?');
        $stmt->execute([$_POST['version'],$_POST['mc_version'],$_POST['changelog'],$_POST['id']]);
        if($isAjax){exit('OK');}
        header('Location: admin.php?page=plugins&manage_plugin='.$_POST['plugin_id']);
        exit;
    }

    if(isset($_GET['del_version'])){
        $stmt=$pdo->prepare('DELETE FROM plugin_versions WHERE id=?');
        $stmt->execute([$_GET['del_version']]);
        if($isAjax){exit('OK');}
        $pid=$_GET['plugin']??'';
        header('Location: admin.php?page=plugins&manage_plugin='.$pid);
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
    <style>
    .page-bg{background:linear-gradient(#5d8e76,#436b58);padding-top:70px;}
    .content-box{background:#fff;background:rgba(255,255,255,0.95);box-shadow:0 0 10px rgba(0,0,0,0.2);border-radius:.5rem;transition:box-shadow .3s;}
    .content-box:hover{box-shadow:0 0 20px rgba(0,0,0,0.3);}
    .navbar.fixed-top{box-shadow:0 0 5px rgba(0,0,0,0.2);}
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100 page-bg">
<div class="card p-4" style="min-width:300px;">
    <div class="text-center mb-2">
        <?php if ($siteLogo): ?>
        <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo" style="height:60px;">
        <?php endif; ?>
    </div>
    <h2 class="mb-3 text-center">Admin Login</h2>
    <?php if (!empty($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
    <form method="post">
        <div class="mb-3"><input class="form-control" name="username" placeholder="Username"></div>
        <div class="mb-3"><input class="form-control" type="password" name="password" placeholder="Password"></div>
        <div class="d-flex justify-content-between">
            <a class="btn btn-secondary" href="index.php">Back</a>
            <button class="btn btn-primary" type="submit">Login</button>
        </div>
    </form>
</div>
</body>
</html>
<?php else:
// Dashboard
$logo = getSetting($pdo, 'logo');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css">
    <style>
    .page-bg{background:linear-gradient(#5d8e76,#436b58);padding-top:70px;}
    .content-box{background:#fff;background:rgba(255,255,255,0.95);box-shadow:0 0 10px rgba(0,0,0,0.2);border-radius:.5rem;transition:box-shadow .3s;}
    .content-box:hover{box-shadow:0 0 20px rgba(0,0,0,0.3);}
    .navbar.fixed-top{box-shadow:0 0 5px rgba(0,0,0,0.2);}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body class="py-4 page-bg">
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <?php if ($logo): ?>
            <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="height:40px;">
            <?php else: ?>Admin<?php endif; ?>
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="admin.php?page=updates">Updates</a></li>
                <li class="nav-item"><a class="nav-link" href="admin.php?page=plugins">Plugins</a></li>
                <li class="nav-item"><a class="nav-link" href="admin.php?page=config">Site config</a></li>
                <li class="nav-item"><a class="nav-link" href="admin.php?page=stats">Statistics</a></li>
                <li class="nav-item"><a class="nav-link" href="admin.php?logout=1">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
<div class="content-box p-4">
<h1>Welcome, admin</h1>
<hr>
<?php if($page==='plugins'): ?>
<h2>Plugins</h2>
<h3>Add plugin</h3>
<form method="post" class="mb-3 ajax" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add_plugin">
    <div class="mb-2"><input class="form-control" name="name" placeholder="Name" required></div>
    <div class="mb-2">Logo: <input type="file" name="logo" class="form-control"></div>
    <div class="mb-2"><input class="form-control" name="short_description" placeholder="Short description" required></div>
    <div class="mb-2"><textarea class="form-control" id="pluginDesc" name="description" placeholder="Long description" required></textarea></div>
    <button class="btn btn-primary" type="submit">Create</button>
</form>

<?php if(isset($_GET['edit_plugin'])):
    $stmt = $pdo->prepare('SELECT * FROM plugins WHERE id=?');
    $stmt->execute([$_GET['edit_plugin']]);
    $edit = $stmt->fetch();
    if($edit): ?>
<hr>
<h3>Edit Plugin</h3>
<form method="post" class="ajax" enctype="multipart/form-data">
    <input type="hidden" name="action" value="edit_plugin">
    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
    <div class="mb-2"><input class="form-control" name="name" value="<?= htmlspecialchars($edit['name']) ?>" required></div>
    <div class="mb-2">Logo: <input type="file" name="logo" class="form-control"></div>
    <div class="mb-2"><input class="form-control" name="short_description" value="<?= htmlspecialchars($edit['short_description']) ?>" required></div>
    <div class="mb-2"><textarea class="form-control" id="pluginEditDesc" name="description" required><?= htmlspecialchars($edit['description']) ?></textarea></div>
    <button class="btn btn-primary" type="submit">Save</button>
</form>
<?php endif; endif; ?>

<?php if(isset($_GET['manage_plugin'])):
    $pid=(int)$_GET['manage_plugin'];
    $pstmt=$pdo->prepare('SELECT * FROM plugins WHERE id=?');
    $pstmt->execute([$pid]);
    $pl=$pstmt->fetch();
    if($pl): ?>
<hr>
<h3>Manage Versions for <?= htmlspecialchars($pl['name']) ?></h3>
<form id="versionForm" enctype="multipart/form-data">
    <input type="hidden" name="plugin_id" value="<?= $pl['id'] ?>">
    <div class="mb-2"><input class="form-control" name="version" placeholder="Version" required></div>
    <div class="mb-2">
        <select class="form-select" name="mc_version[]" multiple required>
            <?php for($v=16;$v<=21;$v++): $ver="1.".$v; ?>
            <option value="<?= $ver ?>"><?= $ver ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="mb-2"><textarea class="form-control" name="changelog" placeholder="Changelog"></textarea></div>
    <div class="mb-2"><input type="file" name="file" accept=".jar" required></div>
    <div class="progress mb-2"><div id="bar" class="progress-bar" style="width:0%"></div></div>
    <button class="btn btn-primary" type="submit">Upload version</button>
</form>
<div id="uploadMsg" class="mt-2"></div>
<table class="table mt-3">
<thead><tr><th>Version</th><th>MC Version</th><th>Changelog</th><th></th></tr></thead>
<tbody>
<?php
    $vs=$pdo->prepare('SELECT * FROM plugin_versions WHERE plugin_id=? ORDER BY created_at DESC');
    $vs->execute([$pl['id']]);
    foreach($vs as $v){
        echo '<tr><td>'.htmlspecialchars($v['version']).'</td><td>'.htmlspecialchars($v['mc_version']).'</td><td>'.htmlspecialchars($v['changelog']).'</td><td><a class="btn btn-sm btn-secondary me-1" href="admin.php?page=plugins&manage_plugin='.$pl['id'].'&edit_version='.$v['id'].'">Edit</a><a class="btn btn-sm btn-danger" href="admin.php?del_version='.$v['id'].'&plugin='.$pl['id'].'">Delete</a></td></tr>';
    }
?>
</tbody>
</table>
<?php if(isset($_GET['edit_version'])):
    $vid=(int)$_GET['edit_version'];
    $vstmt=$pdo->prepare('SELECT * FROM plugin_versions WHERE id=?');
    $vstmt->execute([$vid]);
    $ver=$vstmt->fetch();
    if($ver): ?>
<form method="post" class="ajax mt-3">
    <input type="hidden" name="action" value="edit_version">
    <input type="hidden" name="id" value="<?= $ver['id'] ?>">
    <input type="hidden" name="plugin_id" value="<?= $pl['id'] ?>">
    <div class="mb-2"><input class="form-control" name="version" value="<?= htmlspecialchars($ver['version']) ?>" required></div>
    <div class="mb-2"><input class="form-control" name="mc_version" value="<?= htmlspecialchars($ver['mc_version']) ?>" required></div>
    <div class="mb-2"><textarea class="form-control" name="changelog" required><?= htmlspecialchars($ver['changelog']) ?></textarea></div>
    <button class="btn btn-primary" type="submit">Save</button>
</form>
<?php endif; endif; ?>
<?php endif; endif; ?>



<hr>
<h2 class="mt-4">Plugins</h2>
<table class="table" id="pluginTable">
<thead><tr><th>Name</th><th>Version</th><th>Downloads</th><th></th></tr></thead>
<tbody>
<?php
$stmt = $pdo->query('SELECT p.id,p.name,
    (SELECT version FROM plugin_versions v WHERE v.plugin_id=p.id ORDER BY created_at DESC LIMIT 1) as version,
    (SELECT COUNT(*) FROM downloads d JOIN plugin_versions v2 ON d.version_id=v2.id WHERE v2.plugin_id=p.id) as dl
    FROM plugins p');
foreach ($stmt as $row) {
    $ver = $row['version'] ?? '';
    echo '<tr><td>'.htmlspecialchars($row['name']).'</td><td>'.htmlspecialchars($ver).'</td><td>'.$row['dl'].'</td><td><a class="btn btn-sm btn-secondary me-1" href="admin.php?page=plugins&edit_plugin='.$row['id'].'">Edit</a><a class="btn btn-sm btn-secondary me-1" href="admin.php?page=plugins&manage_plugin='.$row['id'].'">Versions</a><a class="btn btn-sm btn-danger" href="admin.php?del_plugin='.$row['id'].'">Delete</a></td></tr>';
}
?>
</tbody>
</table>
<hr>
<?php elseif($page==='config'): ?>
<h2>Site config</h2>
<?php
    $site_title = getSetting($pdo,'site_title');
    $logo = getSetting($pdo,'logo');
    $favicon = getSetting($pdo,'favicon');
    $banner = getSetting($pdo,'banner');
    $featured1 = getSetting($pdo,'featured1');
    $featured2 = getSetting($pdo,'featured2');
    $featured3 = getSetting($pdo,'featured3');
?>
<form method="post" class="mb-3 ajax" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_config">
    <div class="mb-2"><input class="form-control" name="site_title" value="<?= htmlspecialchars($site_title) ?>" placeholder="Site title"></div>
    <div class="mb-2">Logo: <input type="file" name="logo_file" class="form-control"></div>
    <div class="mb-2">Favicon: <input type="file" name="favicon_file" class="form-control"></div>
    <div class="mb-2">Banner: <input type="file" name="banner_file" class="form-control"></div>
    <div class="mb-2">
        <select class="form-select" name="featured1">
            <option value="">-- Featured plugin 1 --</option>
            <?php foreach($pdo->query('SELECT id,name FROM plugins ORDER BY name') as $pl): ?>
            <option value="<?= $pl['id'] ?>" <?= $featured1==$pl['id']?'selected':'' ?>><?= htmlspecialchars($pl['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-2">
        <select class="form-select" name="featured2">
            <option value="">-- Featured plugin 2 --</option>
            <?php foreach($pdo->query('SELECT id,name FROM plugins ORDER BY name') as $pl): ?>
            <option value="<?= $pl['id'] ?>" <?= $featured2==$pl['id']?'selected':'' ?>><?= htmlspecialchars($pl['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-2">
        <select class="form-select" name="featured3">
            <option value="">-- Featured plugin 3 --</option>
            <?php foreach($pdo->query('SELECT id,name FROM plugins ORDER BY name') as $pl): ?>
            <option value="<?= $pl['id'] ?>" <?= $featured3==$pl['id']?'selected':'' ?>><?= htmlspecialchars($pl['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
<button class="btn btn-primary" type="submit">Save</button>
</form>
<hr>
<?php elseif($page==='updates'): ?>
<h2>Updates</h2>
<?php if(isset($_GET['edit_update'])):
    $stmt = $pdo->prepare('SELECT * FROM updates WHERE id=?');
    $stmt->execute([$_GET['edit_update']]);
    $up = $stmt->fetch();
    if($up): ?>
<form method="post" class="mb-3 ajax">
    <input type="hidden" name="action" value="edit_update">
    <input type="hidden" name="id" value="<?= $up['id'] ?>">
    <div class="mb-2"><input class="form-control" name="title" value="<?= htmlspecialchars($up['title']) ?>" required></div>
    <div class="mb-2"><textarea class="form-control" id="updateContent" name="content" required><?= htmlspecialchars($up['content']) ?></textarea></div>
    <button class="btn btn-primary" type="submit">Save</button>
</form>
<?php endif; else: ?>
<form method="post" class="mb-3 ajax">
    <input type="hidden" name="action" value="add_update">
    <div class="mb-2"><input class="form-control" name="title" placeholder="Title" required></div>
    <div class="mb-2"><textarea class="form-control" id="updateContent" name="content" placeholder="Content" required></textarea></div>
    <button class="btn btn-primary" type="submit">Add update</button>
</form>
<?php endif; ?>
<table class="table">
    <thead><tr><th>Title</th><th>Created</th><th></th></tr></thead>
    <tbody>
    <?php
    $updates = $pdo->query('SELECT * FROM updates ORDER BY created_at DESC')->fetchAll();
    foreach ($updates as $u) {
        echo '<tr><td>'.htmlspecialchars($u['title']).'</td><td>'.$u['created_at'].'</td><td><a href="admin.php?page=updates&edit_update='.$u['id'].'" class="btn btn-sm btn-secondary me-1">Edit</a><a href="admin.php?del_update='.$u['id'].'" class="btn btn-sm btn-danger">Delete</a></td></tr>';
    }
    ?>
    </tbody>
</table>
<?php elseif($page==='stats'): ?>
<h2>Download statistics</h2>
<?php
    $plOpts = $pdo->query('SELECT id,name FROM plugins ORDER BY name')->fetchAll();
    $verOpts = $pdo->query('SELECT id,version FROM plugin_versions ORDER BY created_at DESC')->fetchAll();
?>
<form id="statsFilter" class="row g-2 mb-3">
    <div class="col">
        <select id="filterPlugin" name="plugin" class="form-select">
            <option value="">All plugins</option>
            <?php foreach($plOpts as $pl): ?>
            <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <select id="filterVersion" name="version" class="form-select">
            <option value="">All versions</option>
            <?php foreach($verOpts as $v): ?>
            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['version']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <select id="filterMC" name="mc_version" class="form-select">
            <option value="">All MC versions</option>
            <?php for($v=16;$v<=21;$v++): $mv='1.'.$v; ?>
            <option value="<?= $mv ?>"><?= $mv ?></option>
            <?php endfor; ?>
        </select>
    </div>
</form>
<canvas id="chart" width="400" height="200"></canvas>
<?php endif; ?>
<script>
// Upload with progress bar
const vform=document.getElementById('versionForm');
if(vform){
    vform.addEventListener('submit',e=>{
        e.preventDefault();
        const xhr=new XMLHttpRequest();
        xhr.open('POST','upload.php');
        xhr.upload.onprogress=ev=>{
            document.getElementById('bar').style.width=(ev.loaded/ev.total*100)+'%';
        };
        xhr.onload=()=>{
            document.getElementById('uploadMsg').innerText=xhr.responseText;
            document.getElementById('bar').style.width='0%';
            if(xhr.status==200) location.reload();
        };
        xhr.send(new FormData(vform));
    });
}

// Chart data with filters
let chart;
function loadStats(){
    const params=new URLSearchParams(new FormData(document.getElementById('statsFilter')));
    fetch('stats.php?'+params.toString()).then(r=>r.json()).then(data=>{
        if(!chart){
            chart=new Chart(document.getElementById('chart'),{
                type:'line',
                data:{labels:data.labels,datasets:[{label:'Downloads',data:data.counts}]}
            });
        }else{
            chart.data.labels=data.labels;
            chart.data.datasets[0].data=data.counts;
            chart.update();
        }
    });
}
if(document.getElementById('statsFilter')){
    document.getElementById('statsFilter').addEventListener('change',loadStats);
    loadStats();
}
if(document.getElementById('updateContent')) {
    $('#updateContent').summernote({height:200});
}
if(document.getElementById('pluginDesc')) {
    $('#pluginDesc').summernote({height:150});
}
if(document.getElementById('pluginEditDesc')) {
    $('#pluginEditDesc').summernote({height:150});
}
document.querySelectorAll('form.ajax').forEach(f=>{
    f.addEventListener('submit',ev=>{
        ev.preventDefault();
        fetch('admin.php',{method:'POST',body:new FormData(f),headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(()=>location.reload());
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>
<?php endif; ?>
