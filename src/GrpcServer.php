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
    private $routeList = [];        //grpc路由列表

    public const SWOOLE_CLOSE = '>>>SWOOLE|CLOSE<<<';          //swoole结束字符

    /**
     * GrpcServer constructor.
     * @param string $address
     * @param string $port
     * @param array $options
     */
    private function __construct(string $address, string $port, array $options = [])
    {
        $this->address = $address;
        $this->port = $port;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * GrpcServer 单例
     * @param string $address
     * @param string $port
     * @param array $options
     * @return GrpcServer
     */
    public static function getInstance(string $address = '0.0.0.0', string $port = '50051', array $options = []): GrpcServer
    {
        if (self::$instance) {
            return self::$instance;
        }
        return new self($address, $port, $options);
    }

    /**
     * grpc 服务启动
     */
    public function start(): void
    {
        $http = new swoole_http_server($this->address, $this->port, SWOOLE_BASE);
        $http->set($this->options);
        $http->on('workerStart', function (swoole_http_server $server) use ($http) {
            $this->workerStart($server);
        });
        $http->on('request', function (swoole_http_request $request, swoole_http_response $response) use ($http) {
            $this->request($request, $response);
        });
        $http->start();
    }

    /**
     * grpc 服务启动处理
     * @param swoole_http_server $server
     */
    private function workerStart(swoole_http_server $server): void
    {

        echo 'php swoole grpc worker start success! worker id:' . $server->worker_id;
    }

    /**
     * grpc请求处理
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     */
    private function request(swoole_http_request $request, swoole_http_response $response): void
    {
        $path = $request->server['request_uri'];
        if (empty($path) || $path === self::SWOOLE_CLOSE) {
            $this->failure($response, 400);
            return;
        }

        if (!isset($this->routeList[$path]) || !$this->routeList[$path]) {
            $this->failure($response, 404);
            return;
        }

        $route = $this->routeList[$path];
        $deserialize = $route->getDeserialize();        //解析类
        $handleMethod = $route->getHandleMethod();      //处理方法

        $request_message = Parser::deserializeMessage($deserialize, $request->rawContent());
        $response_message = call_user_func([$handleMethod[0], $handleMethod[1]], $request_message);
        $this->success($response, $response_message);
    }

    /**
     * 成功返回
     * @param swoole_http_response $response
     * @param $responseMsg
     */
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

    /**
     * 失败返回
     * @param swoole_http_response $response
     * @param int $responseCode
     */
    private function failure(swoole_http_response $response, int $responseCode = 400): void
    {
        $response->status($responseCode);
        $response->end('Bad Request');
    }

    /**
     * 设置grpc路由
     * @param array $routeList
     * @return GrpcServer
     */
    public function setRoute(array $routeList): GrpcServer
    {
        $this->routeList = $routeList;
        return $this;
    }
}