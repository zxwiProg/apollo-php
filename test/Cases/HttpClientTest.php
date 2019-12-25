<?php

namespace ApolloPhp\Cases;

use ApolloPhp\Api\Impl\ApolloHttpClient;
use ApolloPhp\Enum\PullStatus;
use ApolloPhp\Error\HttpClientException;
use ApolloPhp\Popo\ApolloPullParam;
use ApolloPhp\Popo\Config\ApolloHttpClientConfig;
use ApolloPhp\Popo\PullConfigResult;
use ApolloPhp\PsrEnhance\ParallelHttpClientInterface;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\RequestFactory;

/**
 * http客户端测试
 * @author vijya
 */
class HttpClientTest extends TestCase
{
    /**
     * 测试流程
     * @param ApolloHttpClientConfig $config 测试配置
     * @param string[] $namespaces 测试拉的名空间列表
     * @param PullConfigResult|PullConfigResult[] $expected 期望输出
     * @return void
     * @dataProvider normalData
     */
    public function testNormal(ApolloHttpClientConfig $config, array $namespaces, $expected)
    {
        $client = new ApolloHttpClient($config);
        $params = array_map(function ($namespace) {
            if (is_string($namespace)) {
                return new ApolloPullParam($namespace);
            } else {
                return $namespace;
            }
        }, $namespaces);
        if (count($params) > 1) {
            $ret = $client->pullConfigs($params);
        } else {
            $ret = $client->pullConfig($params[0]);
        }
        self::assertEquals($expected, $ret, "配置与预期不一致，确认服务器配置和用例一致");
    }

    /**
     * 测试用例
     * @return array 测试用例
     */
    public function normalData()
    {
        $config = new ApolloHttpClientConfig("php-unit-test-case", "http://172.17.18.211:38080", "DEV");
        $applicationConfig = new PullConfigResult(
            PullStatus::SUCCESS(),
            [
                "db.master.user" => "root",
                "db.master.pwd" => "dev",
                "db.master.host" => "127.0.0.1",
                "db.master.port" => "3306",
            ]
        );
        $applicationConfig->setNamespace("application")
            ->setReleaseKey("20191224090511-5145438afa268100");

        $commonConfig = new PullConfigResult(
            PullStatus::SUCCESS(),
            [
                "redis.master.port" => "6379",
                "redis.master.auth" => "rc_redis",
                "redis.master.host" => "127.0.0.1",
            ]
        );
        $commonConfig->setNamespace("common")
            ->setReleaseKey("20191224091038-75e0438afa268101");
        return [
            [
                $config,
                [
                    "application",
                    "common",
                ],
                [
                    $applicationConfig,
                    $commonConfig,
                ],
            ],
            [
                $config,
                [new ApolloPullParam("common", "20191224091038-75e0438afa268101")],
                (new PullConfigResult(PullStatus::NOT_MODIFY()))->setNamespace("common"),
            ],
            [
                $config,
                [new ApolloPullParam("application", "20191224091038-75e0438afa268101")],
                $applicationConfig,
            ],
            [
                $config,
                [new ApolloPullParam("notFound")],
                (new PullConfigResult(PullStatus::NOT_FOUND()))->setNamespace("notFound"),
            ],
            [
                new ApolloHttpClientConfig("php-unit-test-case", "http://172.17.18.211:3880", "DEV"),
                ["common"],
                (new PullConfigResult(PullStatus::ERROR()))->setNamespace("common"),
            ],
            [
                new ApolloHttpClientConfig("php-unit-test-case", "http://172.17.18.211:3880", "DEV"),
                [
                    "common",
                    "app",
                ],
                [
                    (new PullConfigResult(PullStatus::NOT_FOUND()))->setNamespace("common"),
                    (new PullConfigResult(PullStatus::NOT_FOUND()))->setNamespace("app"),
                ],
            ],
        ];
    }

    /**
     * 测试拉多个时抛出异常的情况
     * @return void
     */
    public function testPullConfigs()
    {
        $config = new ApolloHttpClientConfig();
        $config->setApolloServerUrl("http://172.17.18.211:3880")
            ->setApolloAppId("php-unit-test-case")
            ->setApolloCluster("DEV")
            ->setAppConfigPath("/tmp/");
        $client = new ApolloHttpClient($config);
        $rk = uniqid("rk-");
        $ip = "8.8.8.8";
        $namespace = uniqid("namespace-");

        $url = "{$config->getApolloServerUrl()}/configs/{$config->getApolloAppId()}/{$config->getApolloCluster()}";
        $query = [
            "releaseKey" => $rk,
            "ip" => $ip,
        ];
        $url .= "/{$namespace}?" . http_build_query($query);
        $request = (new RequestFactory())->createRequest("GET", $url);

        $httpMock = $this->getMockForAbstractClass(ParallelHttpClientInterface::class);
        $httpMock->expects(self::once())
            ->method("sendRequests")
            ->with(new class($request) extends Constraint {
                private $request;

                /**
                 *  constructor.
                 * @param RequestInterface $request 预期的输入
                 */
                public function __construct(RequestInterface $request)
                {
                    $this->request = $request;
                }

                /**
                 * -
                 * @param RequestInterface[] $other 对比数据
                 * @return bool
                 */
                protected function matches($other): bool
                {
                    if (!is_array($other) || count($other) != 1) {
                        return false;
                    }
                    $req = $other[0];
                    if (!($req instanceof RequestInterface)) {
                        return false;
                    }
                    if ($req->getMethod() != $this->request->getMethod()) {
                        return false;
                    }
                    if (strval($req->getUri()) != strval($this->request->getUri())) {
                        return false;
                    }
                    return true;
                }

                /**
                 * @inheritDoc
                 */
                public function toString(): string
                {
                    return "";
                }
            })
            ->willThrowException(new HttpClientException());

        $client->setHttpClient($httpMock);
        $param = new ApolloPullParam("app");
        $param->setNamespace($namespace)
            ->setReleaseKey($rk)
            ->setClientIp($ip);
        $result = $client->pullConfigs([$param]);

        $expect = [(new PullConfigResult(PullStatus::ERROR()))->setNamespace($namespace)];
        self::assertEquals(count($expect), count($result));
        /** @var PullConfigResult $item */
        foreach ($expect as $i => $item) {
            self::assertEquals($item->getConfigurations(), $result[$i]->getConfigurations());
            self::assertEquals($item->getStatus(), $result[$i]->getStatus());
            self::assertEquals($item->getReleaseKey(), $result[$i]->getReleaseKey());
            self::assertEquals($item->getNamespace(), $result[$i]->getNamespace());
        }
    }
}

# end of file
