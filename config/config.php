<?php

return [
    'app' => [
        'root' => __DIR__ . DIRECTORY_SEPARATOR . '..',
        'require_list' => __DIR__ . DIRECTORY_SEPARATOR . 'require.php',
    ],
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=promodb',
        'username' => 'root',
        'password' => 'root'
    ]
];
