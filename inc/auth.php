<?php
session_start();
$pass = 'thesmoketest99';
$message = '';
if (isset($_POST['thesecret'])) {
    $secret = filter_input(INPUT_POST, 'thesecret', FILTER_SANITIZE_STRING);
    if (md5($secret) === md5($pass)) {
        $_SESSION['auth'] = true;
        $message = '';
    } else {
        $message = '<div style="color: red">Incorrect pass phrase/div>';
    }
}
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    echo '<h1>Secured Page</h1>';
    echo $message;
    echo '<form method="POST"><label for="thesecret">Enter phrase</label><br /><input type="password" id="thesceret" name="thesecret" /><input type="submit" value="submit"></form>';
    exit();
}
