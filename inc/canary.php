<?php
session_start();
$ready = false;
$authorized = false;
if (defined('DOC_PATH') && constant('DOC_PATH')) {
    $white_list_path = DOC_PATH . '.whitelist';
    if (is_readable($white_list_path)) {
        if (!isset($_SESSION['whitelist']) || !is_array($_SESSION['whitelist'])) {
            $whitelist = array_map('trim', file($white_list_path, FILE_SKIP_EMPTY_LINES));
        } else {
            $whitelist = $_SESSION['whitelist'];
        }
    }
    $current_ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING);
    if ($current_ip && $whitelist && is_array($whitelist) && !empty($whitelist)) {
        $ready = true;
        $authorized = in_array($current_ip, $whitelist);
    }
}
if (!$ready || !$authorized) {
    include DOC_PATH . 'maint.php';
    die();
}
