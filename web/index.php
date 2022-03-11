<?php

use app\Application;

header('Content-Type: application/json');

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Application.php';
$config = require __DIR__ . DIRECTORY_SEPARATOR .'..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$app = new Application($config);
$app->run();
