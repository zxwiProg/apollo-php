<?php

namespace ApolloPhp\PsrEnhance\Impl;

use ApolloPhp\Error\HttpClientException;
use ApolloPhp\PsrEnhance\ParallelHttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Pool;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ResponseFactory;

/**
 * 使用Guzzle实现的并发请求
 * @author vijay
 */
class GuzzleParaleHttpClient implements ParallelHttpClientInterface
{
    /** @var ClientInterface Guzzle客户端 */
    private $client;
    /** @var ResponseFactoryInterface 响应创建工厂 */
    private $responseFactory;

    /**
     * GuzzleParaleHttpClient constructor.
     * @param ClientInterface $client Guzzle客户端
     * @param ResponseFactoryInterface $responseFactory 响应创建工厂
     */
    public function __construct(
        ClientInterface $client = null,
        ResponseFactoryInterface $responseFactory = null
    ) {
        $this->client = $client;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return ResponseFactoryInterface 响应创建工厂
     */
    public function getResponseFactory(): ResponseFactoryInterface
    {
        if (!$this->responseFactory) {
            $this->setResponseFactory(new ResponseFactory());
        }
        return $this->responseFactory;
    }

    /**
     * @param ResponseFactoryInterface $responseFactory 响应创建工厂
     * @return static 对象本身
     */
    public function setResponseFactory(ResponseFactoryInterface $responseFactory): GuzzleParaleHttpClient
    {
        $this->responseFactory = $responseFactory;
        return $this;
    }

    /**
     * @return ClientInterface Guzzle客户端
     */
    public function getClient(): ClientInterface
    {
        if (!$this->client) {
            $this->setClient(new Client());
        }
        return $this->client;
    }

    /**
     * @param ClientInterface $client Guzzle客户端
     * @return static 对象本身
     */
    public function setClient(ClientInterface $client): GuzzleParaleHttpClient
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            return $this->getClient()->send($request);
        } catch (\Exception $e) {
            if ($e instanceof ClientException && $e->getCode() == 404) {
                return $this->getResponseFactory()->createResponse($e->getCode());
            } else {
                throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function sendRequests(array $requests): array
    {
        $requestsYield = function () use ($requests) {
            foreach ($requests as $request) {
                yield $request;
            }
        };
        $responses = [];
        $pool = new Pool($this->getClient(), $requestsYield(), [
            "concurrency" => count($responses),
            "fulfilled" => function ($response, $index) use (&$responses) {
                $responses[$index] = $response;
            },
            "rejected" => function ($reason, $index) use (&$responses) {
                $responses[$index] = $this->getResponseFactory()->createResponse(404, $reason);
            },
        ]);
        # 构建请求
        $promise = $pool->promise();
        # 等待请求池完成。
        $promise->wait();
        ksort($responses);
        return $responses;
    }
}

# end of file
