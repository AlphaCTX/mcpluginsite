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
?>
