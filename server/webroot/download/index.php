<?php
date_default_timezone_set('America/Chicago');
define('APP_PATH', realpath('{{serverApp}}') . '/');
define('INCLUDES', APP_PATH . 'includes/');
require APP_PATH . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$downloadHandler = $graph->get('DownloadHandler');
$logger = $graph->get('Logger');
$api = $graph->get('Api');
try {
    $api->defaultHandler($downloadHandler);
    $api->init();
    $api->respond();
} catch (\JHM\JhmException $e) {
    $logger->log('ERROR', 'Download handler threw a fatal JHM Exception', ['exception' => $e->getMessage()]);
    http_response_code('502');
} catch (\Exception $e) {
    $logger->log('ERROR', 'Download handler threw a fatal Generic Exception', ['exception' => $e->getMessage()]);
    http_response_code('502'); // bad gateway
}
