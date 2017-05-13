<?php
date_default_timezone_set('America/Chicago');
define('APP_PATH', '{{serverApp}}');
define('INCLUDES', APP_PATH . 'includes/');
require APP_PATH . 'vendor/autoload.php';
try {
    $graph = new \JHM\Graph();
    $config = $graph->get('Config');
    $contactStorage = $graph->get('ContactStorage');
    $ga = $graph->get('Ga');
    $ga->init();
    $token = filter_input(INPUT_GET, 't', FILTER_SANITIZE_STRING);
    if ($token) {
        $data = $contactStorage->validateDownloadToken($token);
        if (is_array($data)) {
            $storagePath = $config->getStorage('downloads') . $data['fileId'];
            if (is_readable($storagePath)) {
                $hash = md5_file($storagePath);
                if ($hash !== $data['md5_hash']) {
                    http_response_code('500');
                    exit;
                }
                //send analytics
                $ga->trackPageHit($token, 'JohnHenryMahr: Download a File');
                //send file to user
                header('Content-Description: File Transfer');
                if (isset($data['fileMimeType']) && !empty($data['fileMimeType'])) {
                    header('Content-Type: ' . $data['fileMimeType']);
                }
                header('Content-Disposition: attachment; filename=' . $data['fileId']);
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($storagePath));
                ob_clean();
                flush();
                readfile($storagePath);
                exit;
            } else {
                http_response_code('404'); // not found
            }
        } else {
            http_response_code('403'); // forbidden
        }
    } else {
        http_response_code('400'); // bad request
        exit;
    }

} catch (Exception $e) {
    http_response_code('502'); // bad gateway
}
