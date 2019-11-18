<?php


namespace swoole\grpc;


use Exception;
use Google\Protobuf\Internal\Message;
use Swoft\Consul\Exception\ClientException;
use Swoft\Consul\Exception\ServerException;
use Swoft\Consul\Health;
use Swoole\Coroutine;

class GrpcClient extends BaseStub
{

    private $grpcRoute;

    /**
     * GrpcClient constructor.
     * @param string $route
     * @param Health $consulHealth
     * @throws ClientException
     * @throws ServerException
     */
    public function __construct(string $route, Health $consulHealth)
    {
        //todo: address form consul
        $grpcRouteName = GrpcConsulService::parseGrpcRoute($route);
        $this->grpcRoute = $route;
        $services = $this->getServiceAddr($grpcRouteName, $consulHealth);
        $address = $services['Service']['Address'];
        $port = $services['Service']['Port'];
        parent::__construct(sprintf('%s:%s', $address, $port), []);
    }

    /**
     * @param Message $argument
     * @param string $responseDecodeClass
     * @return Message|mixed|
     * @throws Exception
     */
    private function request(Message $argument, string $responseDecodeClass)
    {
        $this->start();
        [$reply, $status] = $this->_simpleRequest($this->grpcRoute, $argument, [$responseDecodeClass, 'decode']);
        if ($status !== 0) {
            throw new \RuntimeException('服务请求失败！' . $status);
        }
        $this->close();

        return $reply;
    }

    public function getService($requestObj, string $responseDecodeClass)
    {
        $channel = new Coroutine\Channel(1);
        $obj = $this;
        go(static function () use ($obj, $channel, $requestObj, $responseDecodeClass) {
            $responseData = $obj->request($requestObj, $responseDecodeClass);
            $channel->push($responseData);
        });

        $result = $channel->pop(3.0);
        if ($result === false) {
            throw new \RuntimeException('服务请求失败！');
        }

        return $result;
    }

    /**
     * @param string $route
     * @param Health $consulHealth
     * @return array|mixed
     * @throws ClientException
     * @throws ServerException
     */
    private function getServiceAddr(string $route, Health $consulHealth): array
    {
        $services = $consulHealth->service($route)->getResult();
        if (empty($services)) {
            throw new ServerException('无可用的服务');
        }
        return $this->lvs($services);
    }

    /**
     * 均衡器 todo:需要实现更智能的均衡
     * @param array $services
     * @return array
     */
    private function lvs(array $services): array
    {
        return $services[array_rand($services, 1)];
    }
}