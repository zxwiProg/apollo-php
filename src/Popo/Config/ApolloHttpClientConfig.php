<?php

namespace ApolloPhp\Popo\Config;

/**
 * apollo客户端配置
 * @author vijay
 */
class ApolloHttpClientConfig
{
    /** @var string 应用ID */
    private $apolloAppId;
    /** @var string 服务器地址 */
    private $apolloServerUrl;
    /** @var string apollo配置的cluster */
    private $apolloCluster;
    /** @var string php应用的配置目录 */
    private $appConfigPath;

    /**
     * ApolloClientConfig constructor.
     * @param string $appid 应用ID
     * @param string $serverUrl 服务器地址
     * @param string $cluster apollo配置的cluster
     * @param string $appConfigPath php应用的配置目录
     */
    public function __construct(
        string $appid = "",
        string $serverUrl = "http://127.0.0.1:8080",
        string $cluster = "default",
        string $appConfigPath = ""
    ) {
        $this->apolloAppId = $appid;
        $this->apolloServerUrl = $serverUrl;
        $this->apolloCluster = $cluster;
        $this->appConfigPath = $appConfigPath;
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
    public function setApolloAppId(string $apolloAppId): ApolloHttpClientConfig
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
    public function setApolloServerUrl(string $apolloServerUrl): ApolloHttpClientConfig
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
    public function setApolloCluster(string $apolloCluster): ApolloHttpClientConfig
    {
        $this->apolloCluster = $apolloCluster;
        return $this;
    }

    /**
     * @return string php应用的配置目录
     */
    public function getAppConfigPath(): string
    {
        return $this->appConfigPath;
    }

    /**
     * @param string $appConfigPath php应用的配置目录
     * @return static 对象本身
     */
    public function setAppConfigPath(string $appConfigPath): ApolloHttpClientConfig
    {
        $this->appConfigPath = $appConfigPath;
        return $this;
    }
}

# end of file
