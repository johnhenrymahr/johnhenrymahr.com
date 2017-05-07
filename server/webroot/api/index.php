<?php
date_default_timezone_set('America/Chicago');
define('APP_PATH', '{{serverApp}}');
define('INCLUDES', APP_PATH . 'includes/');
require APP_PATH . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$contactHandler = $graph->get('ContactHandler');
$cvHandler = $graph->get('CvHandler');
$api = $graph->get('Api');
try {
    $api->handler('contact', $contactHandler); // bind handler to request parameter component. i.e. component=contact
    $api->handler('cv', $cvHandler); // bind handler to request parameter component. i.e. component=cv
    $api->init();
    $api->respond(); // will respond with 404 if no component found
} catch (Exception $e) {
    http_response_code('502'); // bad gateway
}
