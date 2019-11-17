<?php


namespace swoole\grpc;


use Grpc\Health\V1\HealthCheckRequest;
use Grpc\Health\V1\HealthCheckResponse;
use Grpc\Health\V1\HealthCheckResponse_ServingStatus;

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
        $this->appId = $appId ?: md5(uniqid(microtime(true), true));
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
        $grpcCheck = sprintf('%s:%s/%s', $ip, $port, $routeUrl);
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

    /**
     * 解析健康检查请求数据
     * @param string $requestBody
     * @return HealthCheckRequest
     */
    public function parserHealthRequestBody(string $requestBody): HealthCheckRequest
    {
        return Parser::deserializeMessage([HealthCheckRequest::class, null], $requestBody);
    }

}