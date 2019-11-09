<?php


namespace swoole\grpc;


class GrpcRouteEntity
{
    /**
     * @var string 路由地址
     */
    private $routeUrl;

    /**
     * @var string 解析类
     */
    private $deserialize;

    private $handleMethod;

    /**
     * GrpcRouteEntity constructor.
     * @param string $routeUrl
     * @param array $deserialize
     * @param $handleMethod
     */
    public function __construct(string $routeUrl, array $deserialize, $handleMethod)
    {
        $this->routeUrl = $routeUrl;
        $this->deserialize = $deserialize;
        $this->handleMethod = $handleMethod;
    }


    /**
     * @return string
     */
    public function getRouteUrl(): string
    {
        return $this->routeUrl;
    }

    /**
     * @param string $routeUrl
     */
    public function setRouteUrl(string $routeUrl): void
    {
        $this->routeUrl = $routeUrl;
    }

    /**
     * @return array
     */
    public function getDeserialize(): array
    {
        return $this->deserialize;
    }

    /**
     * @param array $deserialize
     */
    public function setDeserialize(array $deserialize): void
    {
        $this->deserialize = $deserialize;
    }

    /**
     * @return mixed
     */
    public function getHandleMethod()
    {
        return $this->handleMethod;
    }

    /**
     * @param mixed $handleMethod
     */
    public function setHandleMethod($handleMethod): void
    {
        $this->handleMethod = $handleMethod;
    }

}