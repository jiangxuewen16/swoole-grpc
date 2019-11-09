<?php


namespace swoole\grpc;


use swoole_http_request;
use swoole_http_response;
use swoole_http_server;

class GrpcServer
{
    private static $instance;
    private $address;
    private $port;
    private $options = [
        'log_level' => SWOOLE_LOG_INFO,
        'trace_flags' => 0,
        'worker_num' => 1,
        'open_http2_protocol' => true
    ];
    private $routeList = [];

    private const SWOOLE_CLOSE = '>>>SWOOLE|CLOSE<<<';          //swoole结束字符

    private function __construct(string $address, string $port, array $options = [])
    {
        $this->address = $address;
        $this->port = $port;
        $this->options = array_merge($this->options, $options);
    }

    public static function getInstance(string $address = '0.0.0.0', string $port = '50051', array $options = []): GrpcServer
    {
        if (self::$instance) {
            return self::$instance;
        }
        return new self($address, $port, $options);
    }

    public function start(): void
    {
        $http = new swoole_http_server($this->address, $this->port, SWOOLE_BASE);
        $http->set($this->options);
        # $http->on('workerStart', array($this, 'workerStart'));
        $http->on('request', function (swoole_http_request $request, swoole_http_response $response) use ($http) {
            $path = $request->server['request_uri'];
            if (empty($path) || $path === self::SWOOLE_CLOSE){
                $this->failure($response, 404);
                return;
            }

            $route = $this->routeList[$path];
            if (empty($route)) {
                $this->failure($response, 404);
            }
            $deserialize = $route->getDeserialize();
            $handleMethod = $route->getHandleMethod();

            $request_message = Parser::deserializeMessage($deserialize, $request->rawContent());
            $response_message = call_user_func([$handleMethod[0], $handleMethod[1]], $request_message);
            $this->success($response, $response_message);
        });
        $http->start();
    }

    private function workerStart(swoole_http_server $server): void
    {

        echo 'php swoole grpc worker start success! worker id:' . $server->worker_id;
    }

    private function request(swoole_http_request $request, swoole_http_response $response): void
    {
        $path = $request->server['request_uri'];
        if (empty($path)) {
            $this->failure($response);
            return;
        }

        $route = $this->routeList[$path];
        if (empty($route)) {
            $this->failure($response, 404);
        }
        $deserialize = $route->getDeserialize();
        $handleMethod = $route->getHandleMethod();
        # $requestMsg = Parser::deserializeMessage([HelloRequest::class, null], $request->rawContent());
        $requestMsg = Parser::deserializeMessage($deserialize, $request->rawContent());
        //执行消息处理操作
        $responseMsg = call_user_func([$handleMethod[0], $handleMethod[1]], $requestMsg);
        # $responseMsg = $handleMethod($requestMsg);

        $this->success($response, $responseMsg);
    }


    private function success(swoole_http_response $response, $responseMsg): void
    {
        $response->header('content-type', 'application/grpc');
        $response->header('trailer', 'grpc-status, grpc-message');
        $trailer = [
            'grpc-status' => '0',
            'grpc-message' => 'success'
        ];
        foreach ($trailer as $trailer_name => $trailer_value) {
            $response->trailer($trailer_name, $trailer_value);
        }
        $response->end(Parser::serializeMessage($responseMsg));
    }

    private function failure(swoole_http_response $response, int $responseCode = 400): void
    {
        $response->status($responseCode);
        $response->end('Bad Request');
    }

    public function setRoute(array $routeList): GrpcServer
    {
        $this->routeList = $routeList;
        return $this;
    }
}