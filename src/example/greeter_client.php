<?php

use Helloworld\GreeterClient;
use Helloworld\HelloRequest;

require __DIR__ . '/../../vendor/autoload.php';
require 'Helloworld/GreeterClient.php';
require 'Helloworld/HelloRequest.php';
require 'GPBMetadata/Helloworld.php';
require 'Helloworld/HelloReply.php';

$name = !empty($argv[1]) ? $argv[1] : 'Swoole';

go(function () use ($name) {
    $greeterClient = new GreeterClient('127.0.0.1:50051');
    $greeterClient->start();
    $request = new HelloRequest();
    $request->setName($name);
    list($reply, $status) = $greeterClient->SayHello($request);
    $message = $reply->getMessage();
    echo "{$message}\n";
    $greeterClient->close();
});