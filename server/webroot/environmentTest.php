<?php
/*
this file is a functional test to be run on a particular enviroment. It tests things like sending mail and database interactions to make sure they are working. It should probably be deleted after it is run successfully.
 */

ini_set('display_errors', 1);
date_default_timezone_set('America/Chicago');
define('APP_PATH', realpath('{{serverApp}}') . '/');
define('INCLUDES', APP_PATH . 'includes/');
require APP_PATH . 'vendor/autoload.php';
$graph = new \JHM\Graph();
$contactStorage = $graph->get('ContactStorage');

$email = 'testemail@mail.com';
$name = 'Joe Testman';
$company = 'MFC Corp';
$phone = '6128789898';

echo '<h1>Environment Test: ' . gethostname() . '</h1>';

if ($contactStorage->isReady()) {

    echo '<h2>Contact Storage Class</h2>';

    echo '<h3>Creating contact record</h3>';

    $cid = $contactStorage->addContact($email, $name, $company, $phone);

    if ($cid) {
        echo "<p><strong>OK</strong> ID: {$cid}</p>";
        echo '<h3>Adding a messge</h3>';
        $mid = $contactStorage->addMessage($cid, 'Environment Test', 'Adding a message to see if it works');
        if ($mid) {
            echo '<p><strong>OK</strong></p>';
        } else {
            echo '<p>Add message failed</p>';
        }
        echo '<h2>Create download token</h2>';
        $token = $contactStorage->addDownloadRecord($cid, $email, 'testfile.txt');
        if ($token) {
            echo '<p><strong>OK</strong></p>';
            var_dump($token);
            echo '<h3>Get inactive token</h3>';
            $inactiveRecord = $contactStorage->getInactiveToken($token);
            if ($inactiveRecord) {
                echo '<p><strong>OK</strong></p>';
                var_dump($inactiveRecord);
                echo '<h3>Activate token</h3>';
                $activateToken = $contactStorage->activateDownloadToken($inactiveRecord['id']);
                if ($activateToken) {
                    echo '<p><strong>OK</strong></p>';
                    echo '<h3>Validate Token</h3>';
                    $validateToken = $contactStorage->validateDownloadToken($token);
                    if ($validateToken) {
                        echo '<p><strong>OK</strong></p>';
                        var_dump($validateToken);
                    } else {
                        echo '<p>Could not validate token</p>';
                    }
                } else {
                    echo '<p>Could not activate token';
                }
            } else {
                echo '<p>Could not get inactive token</p>';
            }

            echo '<h3>Delete Token</h3>';
            $deleteToken = $contactStorage->removeDownloadToken($token);
            if ($deleteToken) {
                echo '<p><strong>OK</strong></p>';
                var_dump($deleteToken);
            } else {
                echo '<p>Delete token failed</p>';
            }

        } else {
            echo '<p>add download token failed</p>';
        }

    } else {
        echo '<p>Add contact record failed</p>';
    }

} else {
    echo '<p>Storage not ready on this server</p>';
}
