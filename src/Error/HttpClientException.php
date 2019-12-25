<?php

namespace ApolloPhp\Error;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * http客户端异常
 * @author vijay
 */
class HttpClientException extends \Exception implements ClientExceptionInterface
{
}

# end of file
