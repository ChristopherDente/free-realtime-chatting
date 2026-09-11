<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Chat.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    9095
);

echo "WebSocket server running on port 9095...\n";
$server->run();
