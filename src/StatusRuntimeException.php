<?php


namespace swoole\grpc;


use Throwable;

class StatusRuntimeException extends \Exception
{
    public function __construct($msg, $code = 0, Throwable $previous = null)
    {
        parent::__construct($msg, $code, $previous);

    }
}