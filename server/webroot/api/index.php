<?php
date_default_timezone_set('America/Chicago');
define('APP_PATH', '{{serverApp}}');
define('INCLUDES', APP_PATH . 'includes/');
require APP_PATH . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$contactHandler = $graph->get('contactHandler');
$api = $graph->get('Api');
$api->handler('contact', $contactHandler);
$api->init();
$api->respond();
