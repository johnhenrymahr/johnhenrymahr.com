<?php
date_default_timezone_set('America/Chicago');
define('APP_PATH', realpath('{{serverApp}}') . '/');
define('INCLUDES', APP_PATH . 'includes/');
require APP_PATH . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$logger = $graph->get('Logger');
$contactHandler = $graph->get('ContactHandler');
$cvHandler = $graph->get('CvHandler');
$downloadActivationHandler = $graph->get('ActivateDownloadHandler');
$api = $graph->get('Api');
$api->setResponse(new \Symfony\Component\HttpFoundation\JsonResponse());
try {
    $api->handler('contact', $contactHandler); // bind handler to request parameter component. i.e. component=contact
    $api->handler('cv', $cvHandler); // bind handler to request parameter component. i.e. component=cv
    $api->handler('activate', $downloadActivationHandler);
    $api->init();
    $api->respond(); // will respond with 404 if no component found
} catch (\JHM\JhmException $e) {
    $logger->log('ERROR', 'API threw a fatal JHM Exception', ['exception' => $e->getMessage()]);
    http_response_code('502');
} catch (Exception $e) {
    $logger->log('ERROR', 'API threw a fatal Generic Exception', ['exception' => $e->getMessage()]);
    http_response_code('502'); // bad gateway
}
