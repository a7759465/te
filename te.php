<?php

namespace Te;
require_once "./vendor/autoload.php";
require_once "common.php";


$server = new \Te\Server("tcp://0.0.0.0:12345");

$server->on('connect', function (\Te\Server $server, \Te\TcpConnection $connection) {
    echo "client connected" . PHP_EOL;
});

$server->on('receive', function (\Te\Server $server, $msg, \Te\TcpConnection $connection) {
    echo "recv from client:$msg" . PHP_EOL;
});

//dd($server->_events);

$server->Listen();
$server->Accept();

$server->eventLoop();