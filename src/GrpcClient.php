<?php


namespace swoole\grpc;


use Exception;
use Google\Protobuf\Internal\Message;
use Swoole\Coroutine as co;
use swoole_event;

class GrpcClient extends BaseStub
{
    private $address;
    private $port;


    public function __construct()
    {
        //todo: address form consul
        $address = '127.0.0.1:50051';
        parent::__construct($address, []);
    }

    /**
     * @param Message $argument
     * @param string $route
     * @param string $responseDecodeClass
     * @return Message|\Grpc\StringifyAble|mixed|\swoole_http2_response
     * @throws Exception
     */
    public function getService(string $route, Message $argument, string $responseDecodeClass)
    {
        $result = null;
        go(function () use ($route, $argument, $responseDecodeClass, &$result) {
            $this->start();
            [$reply, $status] = $this->_simpleRequest($route, $argument, [$responseDecodeClass, 'decode']);
            if ($status !== 0) {
                throw new \RuntimeException('服务请求失败！' . $status);
            }

            $result = $reply;
            $this->close();

        });
        swoole_event::wait();
        return $result;
    }
}