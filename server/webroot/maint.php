<?php
$protocol = "HTTP/1.0";
if ("HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"]) {
    $protocol = "HTTP/1.1";
}
header("$protocol 503 Service Unavailable", true, 503);
header("Retry-After: 3600");
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>John Henry Mahr</title>
        <meta name="description" content="John Henry Mahr and JHM Consulting: Contract Development.">
        <meta name="author" content="John Henry Mahr" >
        <meta name="keywords" content="html5, JavaScript, CSS3, Responsive Design, Semantic Markup, Backbone, Marionette, React, Angular">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    </head>
    <body>
        <h1>We will be back soon!</h1>
        <div class="content">johnhenrymahr is currently down for maintenance. Please try back soon.</div>
    </body>
</html>
