<?php

namespace ApolloPhp\Config;

use ApolloPhp\Popo\PullConfigResult;

/**
 * apollo配置解析接口
 * @author author
 */
interface ApolloConfigInterface
{
    /**
     * 解析配置信息
     * @param PullConfigResult $apolloConfig   拉取回来的配置结果信息
     * @return boolean
     */
    public function parseConfig(PullConfigResult $apolloConfig): bool;
}

# end of file
