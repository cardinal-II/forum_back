<?php
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

ini_set('display_errors',1);
error_reporting(E_ALL);

$f3 = require('lib/base.php');
$f3->config('config.ini');
// Define routes
$f3->config('routes/routes.ini');
$f3->set('AUTOLOAD', 'routes/; utilities/; controllers/; lib/; db/');

$f3->run();
