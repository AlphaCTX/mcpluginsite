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
    echo '<footer class="bg-dark text-light mt-4 py-4">';
    echo '  <div class="container">';
    echo '    <div class="row">';

    // Linkerkolom: Navigation (links uitgelijnd)
    echo '      <div class="col-md-4">';
    echo '        <h5>Navigation</h5>';
    echo '        <ul class="list-unstyled">';
    echo '          <li><a class="text-light" href="index.php">Home</a></li>';
    echo '          <li><a class="text-light" href="plugins.php">Plugins</a></li>';
    echo '          <li><a class="text-light" href="updates.php">Updates</a></li>';
    echo '        </ul>';
    echo '      </div>';

    // Middenkolom: Logo + copyright (gecentreerd)
    echo '      <div class="col-md-4 text-center">';
    echo '        <img src="uploads/1751537562_logo-128x128.png" alt="Logo" class="mb-2" style="max-width:80px;">';
    echo '        <div>&copy; 2025 AlphaCTX</div>';
    echo '      </div>';

    // Rechterkolom: Other links als inline-block, float:right
    echo '      <div class="col-md-4">';
    echo '        <div style="display:inline-block; float:right; text-align:left;">';
    echo '          <h5 class="mb-2">Other links</h5>';
    echo '          <ul class="list-unstyled mb-0">';
    foreach ($links as $l) {
        echo '<li><a class="text-light" target="_blank" href="' . htmlspecialchars($l['url']) . '">'
             . htmlspecialchars($l['label']) . '</a></li>';
    }
    echo '          </ul>';
    echo '        </div>';
    echo '        <div style="clear:both;"></div>';
    echo '      </div>';

    echo '    </div>';
    echo '  </div>';
    echo '</footer>';
}
?>
