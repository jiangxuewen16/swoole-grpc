<?php


namespace swoole\grpc;

use swoole\grpc\Grpc\Health\V1\HealthCheckResponse;
use swoole\grpc\Grpc\Health\V1\HealthCheckResponse_ServingStatus;

class GrpcServiceHealth
{
    public const HEALTH_PATH = '/grpc.health.v1.Health/Check';
    public const UNKNOWN = HealthCheckResponse_ServingStatus::UNKNOWN;
    public const SERVING = HealthCheckResponse_ServingStatus::SERVING;
    public const NOT_SERVING = HealthCheckResponse_ServingStatus::NOT_SERVING;

    public $deregisterCriticalServiceAfter = '3s';
    public $timeout = '3s';
    public $interval = '10s';

    /**
     * GrpcServiceHealth constructor.
     * @param string $deregisterCriticalServiceAfter
     * @param string $timeout
     * @param string $interval
     */
    public function __construct(string $deregisterCriticalServiceAfter, string $timeout, string $interval)
    {
        $this->deregisterCriticalServiceAfter = $deregisterCriticalServiceAfter;
        $this->timeout = $timeout;
        $this->interval = $interval;
    }

    /**
     * 获取服务监测的信息
     * @param string $routeUrl
     * @param string $ip
     * @param string $port
     * @return array
     */
    public function getCheckInfo(string $routeUrl, string $ip, string $port): array
    {
        $url = $this->getCheckUrl($routeUrl);
        $grpcCheck = sprintf('%s:%s%s', $ip, $port, $url);
        return [
            'ID' => $routeUrl,
            'Name' => $routeUrl,
            'DeregisterCriticalServiceAfter' => $this->deregisterCriticalServiceAfter,
            #'HTTP' => sprintf('http://%s:%s%s', $localIps[0], $port, '/grpc.health.v1.Health/Check'),
            #'HTTP' => sprintf('http://%s:%s', $localIps[0], $port),
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

}