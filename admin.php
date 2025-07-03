<?php
// Admin login and dashboard
session_start();
require 'db.php';
require 'functions.php';
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
    if (isset($_POST['action']) && $_POST['action'] === 'edit_update') {
        $stmt = $pdo->prepare('UPDATE updates SET title=?, content=? WHERE id=?');
        $stmt->execute([$_POST['title'], $_POST['content'], $_POST['id']]);
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
        header('Location: admin.php#config');
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'edit_plugin') {
        $stmt = $pdo->prepare('UPDATE plugins SET name=?, description=? WHERE id=?');
        $stmt->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
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
    <div class="mb-2">
        <select class="form-select" name="plugin_id">
            <option value="0">New plugin</option>
            <?php foreach($pdo->query('SELECT id,name FROM plugins ORDER BY name') as $pl): ?>
            <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-2"><input class="form-control" name="name" placeholder="Name"></div>
    <div class="mb-2"><input class="form-control" name="version" placeholder="Version" required></div>
    <div class="mb-2">
        <select class="form-select" name="mc_version[]" multiple required>
            <?php for($v=16;$v<=21;$v++): $ver="1.".$v; ?>
            <option value="<?= $ver ?>"><?= $ver ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="mb-2"><textarea class="form-control" id="pluginDesc" name="description" placeholder="Description"></textarea></div>
    <div class="mb-2"><input type="file" name="file" accept=".jar" required></div>
    <div class="progress mb-2"><div id="bar" class="progress-bar" style="width:0%"></div></div>
    <button class="btn btn-primary" type="submit">Upload</button>
</form>
<div id="uploadMsg" class="mt-2"></div>

<?php if(isset($_GET['edit_plugin'])): 
    $stmt = $pdo->prepare('SELECT * FROM plugins WHERE id=?');
    $stmt->execute([$_GET['edit_plugin']]);
    $edit = $stmt->fetch();
    if($edit): ?>
<hr>
<h2>Edit Plugin</h2>
<form method="post">
    <input type="hidden" name="action" value="edit_plugin">
    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
    <div class="mb-2"><input class="form-control" name="name" value="<?= htmlspecialchars($edit['name']) ?>" required></div>
    <div class="mb-2"><textarea class="form-control" id="pluginEditDesc" name="description" required><?= htmlspecialchars($edit['description']) ?></textarea></div>
    <button class="btn btn-primary" type="submit">Save</button>
</form>
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
    echo '<tr><td>'.htmlspecialchars($row['name']).'</td><td>'.htmlspecialchars($row['version']).'</td><td>'.$row['dl'].'</td><td><a class="btn btn-sm btn-secondary me-1" href="admin.php?edit_plugin='.$row['id'].'#plugins">Edit</a><a class="btn btn-sm btn-danger" href="admin.php?del_plugin='.$row['id'].'">Delete</a></td></tr>';
}
?>
</tbody>
</table>
<hr>
<h2 id="config" class="mt-4">Site config</h2>
<?php
    $site_title = getSetting($pdo,'site_title');
    $logo = getSetting($pdo,'logo');
    $favicon = getSetting($pdo,'favicon');
    $banner = getSetting($pdo,'banner');
    $featured1 = getSetting($pdo,'featured1');
    $featured2 = getSetting($pdo,'featured2');
    $featured3 = getSetting($pdo,'featured3');
?>
<form method="post" class="mb-3" enctype="multipart/form-data">
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
<h2 id="updates" class="mt-4">Updates</h2>
<?php if(isset($_GET['edit_update'])):
    $stmt = $pdo->prepare('SELECT * FROM updates WHERE id=?');
    $stmt->execute([$_GET['edit_update']]);
    $up = $stmt->fetch();
    if($up): ?>
<form method="post" class="mb-3">
    <input type="hidden" name="action" value="edit_update">
    <input type="hidden" name="id" value="<?= $up['id'] ?>">
    <div class="mb-2"><input class="form-control" name="title" value="<?= htmlspecialchars($up['title']) ?>" required></div>
    <div class="mb-2"><textarea class="form-control" id="updateContent" name="content" required><?= htmlspecialchars($up['content']) ?></textarea></div>
    <button class="btn btn-primary" type="submit">Save</button>
</form>
<?php endif; else: ?>
<form method="post" class="mb-3">
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
        echo '<tr><td>'.htmlspecialchars($u['title']).'</td><td>'.$u['created_at'].'</td><td><a href="admin.php?edit_update='.$u['id'].'#updates" class="btn btn-sm btn-secondary me-1">Edit</a><a href="admin.php?del_update='.$u['id'].'" class="btn btn-sm btn-danger">Delete</a></td></tr>';
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
if(document.getElementById('updateContent')) {
    $('#updateContent').summernote({height:200});
}
if(document.getElementById('pluginDesc')) {
    $('#pluginDesc').summernote({height:150});
}
if(document.getElementById('pluginEditDesc')) {
    $('#pluginEditDesc').summernote({height:150});
}
</script>
</body>
</html>
<?php endif; ?>
