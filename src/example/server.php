<?php

use Helloworld\HelloRequest;
use swoole\grpc\GrpcServer;
use swoole\grpc\GrpcRouteEntity;

require __DIR__ . '/../../vendor/autoload.php';
require '../GrpcServer.php';
require '../GrpcRouteEntity.php';

require 'Helloworld/HelloRequest.php';
require 'GPBMetadata/Helloworld.php';
require 'Helloworld/HelloReply.php';
require 'handle/Hello.php';
$he = new Hello();
$h = new GrpcRouteEntity('/helloworld.Greeter/SayHello', [HelloRequest::class, null], [$he, 'handle']);
$routeList = ['/helloworld.Greeter/SayHello' => $h];

GrpcServer::getInstance()->setRoute($routeList)->start();