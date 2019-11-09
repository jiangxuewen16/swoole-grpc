<?php


namespace swoole\grpc;


use swoole_http_request;
use swoole_http_response;

class HttpHandle
{


    public static function request(swoole_http_request $request, swoole_http_response $response , $a): bool
    {
        $request_message = Parser::deserializeMessage($a, $request->rawContent());
//        if ($request_message) {
//            $response_message = new HelloReply();
//            $response_message->setMessage('Hello ' . $request_message->getName());
//            $response->header('content-type', 'application/grpc');
//            $response->header('trailer', 'grpc-status, grpc-message');
//            $trailer = [
//                "grpc-status" => "0",
//                "grpc-message" => ""
//            ];
//            foreach ($trailer as $trailer_name => $trailer_value) {
//                $response->trailer($trailer_name, $trailer_value);
//            }
//            $response->end(Parser::serializeMessage($response_message));
//            return true;
//        }
//        return false;
        return true;
    }

}