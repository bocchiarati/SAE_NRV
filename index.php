<?php
declare(strict_types=1);

use iutnc\nrv\repository\NrvRepository;

session_start();

require_once 'vendor/autoload.php';

NrvRepository::setConfig(__DIR__ . '/config/nrv.db.ini');

$repo = NrvRepository::getInstance();

$action = "";
if(isset($_GET['action'])) {
    $action = $_GET['action'];
}else if (isset($_POST['action'])) {
    $action = $_POST['action'];
}

$dispatcher = new \iutnc\nrv\dispatch\Dispatcher($action);
$dispatcher->run();



//////////////// TESTS //////////////////