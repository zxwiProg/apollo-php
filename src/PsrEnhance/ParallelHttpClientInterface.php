<?php

namespace ApolloPhp\PsrEnhance;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 可并发请求的http客户端接口
 * @author vijay
 */
interface ParallelHttpClientInterface extends ClientInterface
{
    /**
     * 并发请求
     * @param RequestInterface[] $requests 请求列表
     * @return ResponseInterface[] 响应列表
     * @throws \Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequests(array $requests): array;
}

# end of file
