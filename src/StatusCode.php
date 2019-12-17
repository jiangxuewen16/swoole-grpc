<?php


namespace swoole\grpc;


class StatusCode
{
    const OK = 0;     //成功
    const CANCELLED = 1;      //操作被取消（通常是被调用者取消）
    const UNKNOWN = 2;        //未知错误
    const INVALID_ARGUMENT = 3;       //无效参数
    const DEADLINE_EXCEEDED = 4;      //超过最后期限
    const NOT_FOUND = 5;          //无法找到;某些请求实体(例如文件或者目录)无法找到
    const ALREADY_EXISTS = 6;     //已经存在;某些我们试图创建的实体(例如文件或者目录)已经存在
    const PERMISSION_DENIED = 7;      //权限不足
    const RESOURCE_EXHAUSTED = 8;     //资源耗尽
    const FAILED_PRECONDITION = 9;    //前置条件失败:操作被拒绝
    const ABORTED = 10;           //中途失败:操作中途失败，通常是因为并发问题如时序器检查失败，事务失败等。
    const OUT_OF_RANGE = 11;      //超出范围:操作试图超出有效范围，例如，搜索或者读取超过文件结尾。
    const UNIMPLEMENTED = 12;     //未实现:操作没有实现，或者在当前服务中没有支持/开启。
    const INTERNAL = 13;          //内部错误:意味着某些底层系统期待的不变性被打破。如果看到这些错误，说明某些东西被严重破坏。
    const UNAVAILABLE = 14;       //不可用:服务当前不可用。这大多数可能是一个临时情况，可能通过稍后的延迟重试而被纠正。
    const DATA_LOSS = 15;         //数据丢失:无法恢复的数据丢失或者损坏
    const UNAUTHENTICATED = 16;    //未经认证:请求没有操作要求的有效的认证凭证。
}