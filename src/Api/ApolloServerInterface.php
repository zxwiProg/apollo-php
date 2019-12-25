<?php

namespace ApolloPhp\Api;

use ApolloPhp\Popo\ApolloPullParam;
use ApolloPhp\Popo\PullConfigResult;

/**
 * apollo服务器接口
 * @author vijay
 */
interface ApolloServerInterface
{
    /**
     * 获取配置信息
     * @param ApolloPullParam $apolloPullParam 拉取数据的参数
     * @return PullConfigResult
     */
    public function pullConfig(ApolloPullParam $apolloPullParam): PullConfigResult;

    /**
     * 获取多个名空间的配置
     * @param ApolloPullParam[] $apolloPullParams 参数列表
     * @return PullConfigResult[]
     */
    public function pullConfigs(array $apolloPullParams): array;
}

# end of file
