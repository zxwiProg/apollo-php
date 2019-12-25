<?php

namespace ApolloPhp\Enum;

use MyCLabs\Enum\Enum;

/**
 * 拉配置的结果
 * @author vijay
 * @method static PullStatus SUCCESS()
 * @method static PullStatus NOT_FOUND()
 * @method static PullStatus NOT_MODIFY()
 * @method static PullStatus ERROR()
 */
class PullStatus extends Enum
{
    /** @var int 拉配置成功 */
    const SUCCESS = 0;
    /** @var int 拉取失败 */
    const NOT_FOUND = 1;
    /** @var int 没有变化 */
    const NOT_MODIFY = 2;
    /** @var int 发生错误 */
    const ERROR = 3;
}

# end of file
