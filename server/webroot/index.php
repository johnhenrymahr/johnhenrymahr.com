<?php
date_default_timezone_set('America/Chicago');
define('SERVER_ROOT', dirname(realpath(__DIR__)) . "/");
$server_name = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING);
define('PROD', strpos($server_name, 'johnhenrymahr.com') !== false);
require SERVER_ROOT . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$assembler = $graph->get('Assembler');
$dataProvider = $graph->get('DataProvider');
