<?php
header('Cache-Control: no-cache, must-revalidate');
header('Cache-Control: no-transform');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('X-Frame-Options: DENY'); //prevent clickjacking
header('Content-Security-Policy: "default-src \'self\' https://ajax.googleapis.com https://use.typekit.net/ https://maxcdn.bootstrapcdn.com"');
header('X-Content-Security-Policy: "default-src \'self\' https://ajax.googleapis.com https://use.typekit.net/ https://maxcdn.bootstrapcdn.com"');
header('X-WebKit-CSP: "default-src \'self\' https://ajax.googleapis.com https://use.typekit.net/ https://maxcdn.bootstrapcdn.com"');
