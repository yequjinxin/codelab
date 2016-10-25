<?php
$config = array(
    'debug' => true,
    'db' => array(
        'mysql' => array(
            'driver' => 'mysqli',
            'user' => 'root',
            'pwd' => '',
            'host' => '127.0.0.1',
            'db' => 'codelab',
            'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=codelab;charset=utf8;'
        )
    ),
    // weibo
    'appKey' => '3416928080',
    'appSecret' => 'c23eba5f81922a0b377f1b08c496f027',
    'exec' => array(
        'php' => 'E:/app/php/php.exe',
    ),
);