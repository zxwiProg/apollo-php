<?php

namespace ApolloPhp\Popo;

/**
 * apollo拉取参数
 * @author vijay
 */
class ApolloPullParam
{
    /** @var string 名空间 */
    private $namespace;
    /** @var string 上次的拉取版本号 */
    private $releaseKey;
    /** @var string 绑定IP做灰度发布用 */
    private $clientIp;
    

    /**
     * ApolloPullParams constructor.
     * @param string $namespace 名空间
     * @param string $releaseKey 上次的拉取版本号
     * @param string $clientIp 绑定IP做灰度发布用
     */
    public function __construct(string $namespace, string $releaseKey = "", string $clientIp = "")
    {
        $this->namespace = $namespace;
        $this->releaseKey = $releaseKey;
        $this->clientIp = $clientIp;
    }

    /**
     * @return string 绑定IP做灰度发布用
     */
    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    /**
     * @param string $clientIp 绑定IP做灰度发布用
     * @return static 对象本身
     */
    public function setClientIp(string $clientIp): ApolloPullParam
    {
        $this->clientIp = $clientIp;
        return $this;
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
     * @return static 对象本身
     */
    public function setNamespace(string $namespace): ApolloPullParam
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string 上次的拉取版本号
     */
    public function getReleaseKey(): string
    {
        return $this->releaseKey;
    }

    /**
     * @param string $releaseKey 上次的拉取版本号
     * @return static 对象本身
     */
    public function setReleaseKey(string $releaseKey): ApolloPullParam
    {
        $this->releaseKey = $releaseKey;
        return $this;
    }
}

# end of file
