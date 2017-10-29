<?php
ini_set('display_errors', 1);
ini_set('expose_php', 0);
date_default_timezone_set('America/Chicago');
define('APP_PATH', realpath('{{serverApp}}') . '/');
define('WEB_ROOT', '{{webroot}}');
define('INCLUDES', APP_PATH . 'includes/');
define('DOC_PATH', realpath(dirname(__FILE__)) . '/');
define('IS_PROD', '{{isProd}}');
/*{{preheader}}*/
require APP_PATH . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$config = $graph->get('Config');
$assembler = $graph->get('Assembler');
$dataProvider = $graph->get('DataProvider');
$output = $graph->get('Output');
$assets = $graph->get('Assets');
try {
    $pageCSS = $assets->get('css');
    $pageData = $output(array($dataProvider, 'getBootstrapData'))->toJSON() . "\n";
    $pageContent = $output(array($assembler, 'assemble'), 'jhm-core');
} catch (Exception $e) {
    include DOC_PATH . 'error.php';
    die();
}
require INCLUDES . 'headers.php';
?>
<!DOCTYPE html>
<html lang="en">
    <!-- {{__version__}} -->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>John Henry Mahr</title>
        <meta name="description" content="John Henry Mahr and JHM Consulting: Contract Development.">
        <meta name="author" content="John Henry Mahr" >
        <meta name="keywords" content="html5, JavaScript, CSS3, Responsive Design, Semantic Markup, Backbone, Marionette, React, Angular">
        <meta name="viewport" content="width=device-width, initial-scale=1">
         {{analytics}}
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo $pageCSS; ?>">
    </head>
   <body>
   {{auth}}
   <script type="application/javascript">
     window.jhmData = <?php echo $pageData; ?>; // eslint-disable-line
   </script>
   <?php
echo $pageContent;
?>
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
   <script src="https://use.typekit.net/zhf5ttk.js"></script>
   <script type="application/javascript" src="<?php echo $assets->get('js'); ?>"></script>
  </body>
</html>