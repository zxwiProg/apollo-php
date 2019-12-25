<?php

namespace ApolloPhp\Popo;

use ApolloPhp\Enum\PullStatus;

/**
 * 拉配置的结果数据
 * @author vijay
 */
class PullConfigResult
{
    /** @var PullStatus 状态 */
    private $status;
    /** @var string[] 配置内容 */
    private $configurations;
    /**
     * @var string 名空间
     * @from namespaceName
     */
    private $namespace;
    /** @var string 版本号 */
    private $releaseKey;

    /**
     * PullConfigResult constructor.
     * @param PullStatus $pullStatus 状态
     * @param array $configs 配置内容
     */
    public function __construct(PullStatus $pullStatus = null, array $configs = null)
    {
        $this->status = $pullStatus;
        $this->configurations = $configs;
    }

    /**
     * @return string 名空间
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace 名空间
     * @return PullConfigResult
     */
    public function setNamespace(string $namespace): PullConfigResult
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string 版本号
     */
    public function getReleaseKey(): ?string
    {
        return $this->releaseKey;
    }

    /**
     * @param string $releaseKey 版本号
     * @return PullConfigResult
     */
    public function setReleaseKey(string $releaseKey): PullConfigResult
    {
        $this->releaseKey = $releaseKey;
        return $this;
    }

    /**
     * @return PullStatus 状态
     */
    public function getStatus(): PullStatus
    {
        return $this->status;
    }

    /**
     * @param PullStatus $status 状态
     * @return static 对象本身
     */
    public function setStatus(PullStatus $status): PullConfigResult
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string[] 配置内容
     */
    public function getConfigurations(): ?array
    {
        return $this->configurations;
    }

    /**
     * @param array $configurations 配置内容
     * @return static 对象本身
     */
    public function setConfigurations(array $configurations): PullConfigResult
    {
        $this->configurations = $configurations;
        return $this;
    }
}

# end of file
