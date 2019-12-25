<?php

namespace ApolloPhp\Api\Impl;

use ApolloPhp\Api\ApolloServerInterface;
use ApolloPhp\Config\ApolloConfigInterface;
use ApolloPhp\Config\Impl\ApolloConfig;
use ApolloPhp\Enum\PullStatus;
use ApolloPhp\Popo\ApolloPullParam;
use ApolloPhp\Popo\Config\ApolloHttpClientConfig;
use ApolloPhp\Popo\PullConfigResult;
use ApolloPhp\PsrEnhance\Impl\GuzzleParaleHttpClient;
use ApolloPhp\PsrEnhance\ParallelHttpClientInterface;
use DreamCat\Array2Class\Array2ClassConverter;
use DreamCat\Array2Class\Array2ClassInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\RequestFactory;

/**
 * http的阿波罗客户端
 * @author vijay
 */
class ApolloHttpClient implements ApolloServerInterface
{
    /** @var string 应用ID */
    private $apolloAppId;
    /** @var string 服务器地址 */
    private $apolloServerUrl;
    /** @var string apollo配置的cluster */
    private $apolloCluster;
    /** @var string php应用的配置目录 */

    private $httpClient;
    /** @var RequestFactoryInterface 请求工厂 */
    private $requestFactory;
    /** @var Array2ClassInterface 数组转类的转换器 */
    private $array2class;


    /**
     * ApolloHttpClient constructor.
     * @param ApolloHttpClientConfig $clientConfig 配置信息
     * @param ParallelHttpClientInterface $httpClient http客户端
     * @param RequestFactoryInterface $requestFactory 请求工厂
     * @param Array2ClassInterface $array2Class 数组转类的转换器
     */
    public function __construct(
        ApolloHttpClientConfig $clientConfig,
        ParallelHttpClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null,
        Array2ClassInterface $array2Class = null
    ) {
        $this->setApolloAppId($clientConfig->getApolloAppId());
        $this->setApolloServerUrl($clientConfig->getApolloServerUrl());
        $this->setApolloCluster($clientConfig->getApolloCluster());
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->array2class = $array2Class;
    }

    /**
     * @return Array2ClassInterface 数组转类的转换器
     */
    public function getArray2class(): Array2ClassInterface
    {
        if (!$this->array2class) {
            $this->setArray2class(new Array2ClassConverter());
        }
        return $this->array2class;
    }

    /**
     * @param Array2ClassInterface $array2class 数组转类的转换器
     * @return static 对象本身
     */
    public function setArray2class(Array2ClassInterface $array2class): ApolloHttpClient
    {
        $this->array2class = $array2class;
        return $this;
    }

    /**
     * @return ParallelHttpClientInterface http客户端
     */
    public function getHttpClient(): ParallelHttpClientInterface
    {
        if (!$this->httpClient) {
            $this->setHttpClient(new GuzzleParaleHttpClient());
        }
        return $this->httpClient;
    }

    /**
     * @param ParallelHttpClientInterface $httpClient http客户端
     * @return static 对象本身
     */
    public function setHttpClient(ParallelHttpClientInterface $httpClient): ApolloHttpClient
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * @return RequestFactoryInterface 请求工厂
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        if (!$this->requestFactory) {
            $this->setRequestFactory(new RequestFactory());
        }
        return $this->requestFactory;
    }

    /**
     * @param RequestFactoryInterface $requestFactory 请求工厂
     * @return static 对象本身
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory): ApolloHttpClient
    {
        $this->requestFactory = $requestFactory;
        return $this;
    }

    /**
     * @return string 应用ID
     */
    public function getApolloAppId(): string
    {
        return $this->apolloAppId;
    }

    /**
     * @param string $apolloAppId 应用ID
     * @return static 对象本身
     */
    public function setApolloAppId(string $apolloAppId): ApolloHttpClient
    {
        $this->apolloAppId = $apolloAppId;
        return $this;
    }

    /**
     * @return string 服务器地址
     */
    public function getApolloServerUrl(): string
    {
        return $this->apolloServerUrl;
    }

    /**
     * @param string $apolloServerUrl 服务器地址
     * @return static 对象本身
     */
    public function setApolloServerUrl(string $apolloServerUrl): ApolloHttpClient
    {
        $this->apolloServerUrl = rtrim($apolloServerUrl, '/');
        return $this;
    }

    /**
     * @return string apollo配置的cluster
     */
    public function getApolloCluster(): string
    {
        return $this->apolloCluster;
    }

    /**
     * @param string $apolloCluster apollo配置的cluster
     * @return static 对象本身
     */
    public function setApolloCluster(string $apolloCluster): ApolloHttpClient
    {
        $this->apolloCluster = $apolloCluster;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pullConfig(ApolloPullParam $apolloPullParam): PullConfigResult
    {
        try {
            return ($this
                ->parseResponse(
                    $this->getHttpClient()
                        ->sendRequest($this->createRequest($apolloPullParam))
                ))->setNamespace($apolloPullParam->getNamespace());
        } catch (ClientExceptionInterface $e) {
            return (new PullConfigResult(PullStatus::ERROR()))->setNamespace($apolloPullParam->getNamespace());
        }
    }

    /**
     * @inheritDoc
     */
    public function pullConfigs(array $apolloPullParams): array
    {
        try {
            $requests = array_map(function (ApolloPullParam $apolloPullParam) {
                return $this->createRequest($apolloPullParam);
            }, $apolloPullParams);
            $result = array_map([
                $this,
                "parseResponse",
            ], $this->getHttpClient()->sendRequests($requests));
            return array_map(function (ApolloPullParam $apolloPullParam, PullConfigResult $configResult) {
                return $configResult->setNamespace($apolloPullParam->getNamespace());
            }, $apolloPullParams, $result);
        } catch (ClientExceptionInterface $e) {
            return array_map(function (ApolloPullParam $apolloPullParam) {
                return (new PullConfigResult(PullStatus::ERROR()))->setNamespace($apolloPullParam->getNamespace());
            }, $apolloPullParams);
        }
    }

    /**
     * 根据返回的响应生成拉取结果
     * @param ResponseInterface $response 响应数据
     * @return PullConfigResult 拉取结果
     */
    protected function parseResponse(ResponseInterface $response): PullConfigResult
    {
        switch ($response->getStatusCode()) {
            case 200:
                $body = json_decode(strval($response->getBody()), true);
                /** @var PullConfigResult $result */
                $result = $this->getArray2class()->convert($body, PullConfigResult::class);
                return $result->setStatus(PullStatus::SUCCESS());
            case 304:
                return new PullConfigResult(PullStatus::NOT_MODIFY());
            default:
                return new PullConfigResult(PullStatus::NOT_FOUND());
        }
    }

    /**
     * 根据名空间和版本号创建请求对象
     * @param ApolloPullParam $param 拉取参数
     * @return RequestInterface 请求对象
     */
    protected function createRequest(ApolloPullParam $param): RequestInterface
    {
        $url = "{$this->getApolloServerUrl()}/configs/{$this->getApolloAppId()}/{$this->getApolloCluster()}";
        $query = [
            "releaseKey" => $param->getReleaseKey(),
            "ip" => $param->getClientIp(),
        ];
        $url .= "/{$param->getNamespace()}?" . http_build_query($query);
        return $this->getRequestFactory()
            ->createRequest("GET", $url);
    }
}

# end of file
