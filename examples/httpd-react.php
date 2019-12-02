#!/usr/bin/env pogo
<?php

## This example sets up an HTTP server (micro-service-style) using ReactPHP.
## The server will run on a random port, unless you provide a CLI argument.
##
## Usage: pogo reactphp.php [portnum]

#!require react/http: ^0.8.5
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;

$loop = Factory::create();
$server = new Server(function (ServerRequestInterface $request) {
    return new Response(
        200,
        array(
            'Content-Type' => 'text/plain'
        ),
        "Hello world\n"
    );
});
$socket = new \React\Socket\Server(isset($argv[1]) ? $argv[1] : '0.0.0.0:0', $loop);
$server->listen($socket);
echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$loop->run();
