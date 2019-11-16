<?php


namespace swoole\grpc;

use swoole\grpc\Grpc\Health\V1\HealthCheckResponse;
use swoole\grpc\Grpc\Health\V1\HealthCheckResponse_ServingStatus;

class GrpcConsulService
{
    public const HEALTH_PATH = '/grpc.health.v1.Health/Check';
    public const UNKNOWN = HealthCheckResponse_ServingStatus::UNKNOWN;
    public const SERVING = HealthCheckResponse_ServingStatus::SERVING;
    public const NOT_SERVING = HealthCheckResponse_ServingStatus::NOT_SERVING;

    private $deregisterCriticalServiceAfter;
    private $timeout;
    private $interval;
    private $appId;

    private $ip;
    private $port;


    /**
     * GrpcConsulService constructor.
     * @param string $deregisterCriticalServiceAfter
     * @param string $timeout
     * @param string $interval
     * @param string|null $appId
     */
    public function __construct(string $deregisterCriticalServiceAfter, string $timeout, string $interval, string $appId = null)
    {
        $this->deregisterCriticalServiceAfter = $deregisterCriticalServiceAfter;
        $this->timeout = $timeout;
        $this->interval = $interval;
        $this->appId = $appId ?: md5(uniqid(microtime(true),true));
    }

    /**
     * 获取服务监测的信息
     * @param string $routeUrl
     * @param string $ip
     * @param int $port
     * @return array
     */
    public function getCheckInfo(string $routeUrl, string $ip, int $port): array
    {
        $url = $this->getCheckUrl($routeUrl);
        $grpcCheck = sprintf('%s:%s%s', $ip, $port, $url);
        return [
            'ID' => $this->appId,
            'Name' => $routeUrl,
            'DeregisterCriticalServiceAfter' => $this->deregisterCriticalServiceAfter,
            'GRPC' => $grpcCheck,
            #'Method' => 'POST',
            #'Header' => ['server-path' => [$routeUrl]],
            'TLSSkipVerify' => true,
            'Timeout' => $this->timeout,
            'Interval' => $this->interval
        ];
    }

    /**
     * 获取监测url
     * @param string $routeUrl
     * @return string
     */
    private function getCheckUrl(string $routeUrl): string
    {
        return self::HEALTH_PATH . $routeUrl;
    }

    /**
     * 获取需要检查的grpc服务地址
     * @param $routeUrl
     * @return string
     */
    public function getGrpcServiceUrl($routeUrl): string
    {
        return str_replace(self::HEALTH_PATH, '', $routeUrl);
    }

    /**
     * 获取健康监测返回数据
     * @param int $status
     * @return string
     */
    public function getHealthCheckResponseMsg(int $status): string
    {
        $responseMessage = new HealthCheckResponse();
        $responseMessage->setStatus($status);
        return Parser::serializeMessage($responseMessage);
    }

    public function getServiceRegisterInfo(string $routeUrl, string $ip, int $port, array $meta = [], array $weights = []): array
    {
        $serviceName = str_replace('/', '.', ltrim($routeUrl, '/'));
        return [
            'ID' => $this->appId,
            'Name' => $serviceName,
            'Tags' => [
                'HTTP', 'GRPC', $routeUrl
            ],
            'Address' => $ip,
            'Port' => $port,
            'Meta' => $meta,
            'EnableTagOverride' => true,
            'Weights' => $weights,
        ];
    }

}