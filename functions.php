<?php
function getSetting(PDO $pdo, $key, $default = '') {
    $stmt = $pdo->prepare('SELECT `value` FROM settings WHERE `key`=?');
    $stmt->execute([$key]);
    $row = $stmt->fetchColumn();
    return $row !== false ? $row : $default;
}

function setSetting(PDO $pdo, $key, $value) {
    $stmt = $pdo->prepare('REPLACE INTO settings (`key`,`value`) VALUES (?,?)');
    $stmt->execute([$key, $value]);
}

function getOtherLinks(PDO $pdo): array {
    $raw = trim(getSetting($pdo, 'other_links'));
    $links = [];
    if ($raw) {
        foreach (explode("\n", $raw) as $line) {
            if (strpos($line, '|') !== false) {
                list($label, $url) = array_map('trim', explode('|', $line, 2));
                if ($label && $url) {
                    $links[] = ['label' => $label, 'url' => $url];
                }
            }
        }
    }
    return $links;
}

function renderFooter(PDO $pdo) {
    $links = getOtherLinks($pdo);
    echo '<footer class="bg-dark text-light mt-4 py-4"><div class="container"><div class="row">';
    echo '<div class="col-md-4"><h5>Navigation</h5><ul class="list-unstyled">';
    echo '<li><a class="text-light" href="index.php">Home</a></li>';
    echo '<li><a class="text-light" href="plugins.php">Plugins</a></li>';
    echo '<li><a class="text-light" href="updates.php">Updates</a></li>';
    echo '</ul></div>';
    echo '<div class="col-md-4"><h5>Other links</h5><ul class="list-unstyled">';
    foreach ($links as $l) {
        echo '<li><a class="text-light" href="'.htmlspecialchars($l['url']).'">'.htmlspecialchars($l['label']).'</a></li>';
    }
    echo '</ul></div>';
    echo '<div class="col-md-4 text-center d-flex align-items-end justify-content-center"><div class="w-100">&copy; 2025 AlphaCTX</div></div>';
    echo '</div></div></footer>';
}
?>
