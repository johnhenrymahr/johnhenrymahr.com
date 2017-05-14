<?php
date_default_timezone_set('America/Chicago');
define('APP_PATH', '{{serverApp}}');
define('INCLUDES', APP_PATH . 'includes/');
require APP_PATH . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$downloadHandler = $graph->get('DownloadHandler');
$api = $graph->get('Api');
try {
    $api->defaultHandler($downloadHandler);
    $api->init();
    $api->respond();
} catch (Exception $e) {
    http_response_code('502'); // bad gateway
}
