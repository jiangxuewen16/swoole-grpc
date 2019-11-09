<?php


use Helloworld\HelloReply;
use Helloworld\HelloRequest;

class Hello
{
    public static function handle(HelloRequest $helloRequest){
        # print_r('xxxxxxxxxxxxxxxxxxxxxx:' . $helloRequest->getName());
        $response_message = new HelloReply();
                       $response_message->setMessage('Hello ' . $helloRequest->getName());
                       return $response_message;
    }
}

