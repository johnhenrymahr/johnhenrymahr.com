<?php
date_default_timezone_set('America/Chicago');
define('SERVER_ROOT', dirname(realpath(__DIR__)) . "/");
define('INCLUDES', SERVER_ROOT . "includes/");
$server_name = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING);
define('PROD', strpos($server_name, 'johnhenrymahr.com') !== false);
require SERVER_ROOT . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$config = $graph->get('Config');
$assembler = $graph->get('Assembler');
$dataProvider = $graph->get('DataProvider');
$output = $graph->get('Output');
$assets = $graph->get('Assets');
?>
<!doctype html>
<html  lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>John Henry Mahr</title>
        <meta name="description" content="John Henry Mahr and JHM Consulting: Contract Development.">
        <meta name="author" content="John Henry Mahr" >
        <meta name="keywords" content="html5, JavaScript, CSS3, Responsive Design, Semantic Markup, Backbone, Marionette, React, Angular">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <link rel="stylesheet" href="<?php echo $assets->get('css')?>">
    </head>
   <body>
   <script type="application/javascript">
     window.jhmData = <?php $output([$dataProvider, 'getBootstrapData'])->toJSON();?>
   </script>
   <?php
switch (strtolower($config->get('pagestate.homepage'))) {
    case 'down':
        include INCLUDES . 'site-down.php';
        break;

    case 'up':
    default:
        try {
            echo $output([$assembler, 'assemble'], 'jhm-core');
        } catch (Exception $e) {
            include INCLUDES . 'site-down.php';
        }
        break;
}

?>
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
   <script src="https://use.typekit.net/zhf5ttk.js"></script>
   <script type="application/javascript" src="<?php echo $assets->get('js')?>"></script>
  <?php
if (constant('PROD') === true) {
    include INCLUDES . 'analytics.php';
}
?>
    </body>
</html>