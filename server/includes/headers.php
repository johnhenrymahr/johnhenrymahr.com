<?php
header('Cache-Control: no-cache, must-revalidate');
header('Cache-Control: no-transform');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('X-Frame-Options: DENY'); //prevent clickjacking
if (IS_PROD) {
    // implement hsts header in prod -- force browser to use https
    // https://www.owasp.org/index.php/HTTP_Strict_Transport_Security_Cheat_Sheet
    header('Strict-Transport-Security: max-age=16070400');
}
