# apollo-php

[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Apollo-Client License](https://img.shields.io/badge/apollo--client--license-MIT-blue.svg?maxAge=2592000)](https://secure.php.net/)
[![Apollo-Client Copyright](https://img.shields.io/badge/copyright-2345.com-lightgrey.svg?maxAge=2592000)](https://secure.php.net/)
<br>

## 安装

```shell
composer require iprog/apollo-php
```
<br>

## 依赖

- **PHP 7.1** or later
<br>

## 如何使用：
可以起一个定时任务，然后运行如下脚本即可，也可以将如下脚本写在一个死循环里面进行循环拉取，
具体各个项目可根据自己的情况决定自己的运用方案

```php
require_once __DIR__ . "/xxx/vendor/autoload.php";

$config = new ApolloPhp\Popo\Config\ApolloHttpClientConfig();
$config->setApolloServerUrl("http://172.17.18.211:3880")
    ->setApolloAppId("php-unit-test-case")
    ->setApolloCluster("DEV");
    
$apolloRedisParam = new ApolloPhp\Popo\ApolloPullParam('redis');
$apolloRedisParam->setClientIp('127.0.0.1');
$apolloRedisParam->setReleaseKey('');  

$apolloMysqlParam = new ApolloPhp\Popo\ApolloPullParam('mysql');
$apolloMysqlParam->setClientIp('127.0.0.1');
$apolloMysqlParam->setReleaseKey(''); 
  
$apolloPullParams = [$apolloRedisParam, $apolloMysqlParam];
$client = new ApolloPhp\Api\Impl\ApolloHttpClient($config);
$apolloConfigResult = $client->pullConfigs($apolloPullParams); 

// 这里会将拉取的配置保存在php对应的配置目录里面
foreach ($apolloConfigResult as $result) {
    $apolloConfig = new ApolloPhp\Config\Impl\ApolloConfig('D://config');
    $apolloConfig->parseConfig($result);
}
          
```

## apollo配置中心如何做配置：

需要说明的是，apollo配置中心的配置以键值对形式存在，所以，为了方便apollo-php在代码层面做解析，apollo-php拟定了一个统一的配置方式，以下举例说明。

比如，现在我们要配置redis的链接参数信息，首先我们需要在apollo配置中心配置一个redis的namespace，然后在该namespace，可以做如下配置：
```txt
dev.master.host=127.0.0.1
dev.master.port=6379
dev.master.pwd=#343kdjer$
dev.slave.host=127.0.0.1
dev.slave.port=6379
dev.slave.pwd=#343kdjer$
test.master.host=127.0.0.1
test.master.port=6379
test.master.pwd=#343kdjer$
test.slave.host=127.0.0.1
test.slave.port=6379
test.slave.pwd=#343kdjer$
prod.master.host=127.0.0.1
prod.master.port=6379
prod.master.pwd=#343kdjer$
prod.slave.host=127.0.0.1
prod.slave.port=6379
prod.slave.pwd=#343kdjer$
```
可以从上面的配置看出，键使用点（.）做分割，则apollo-php解析后，会形成如下配置文件：
```php
<?php

return [
    'dev' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'pwd' => '#343kdjer$'
        ],
        'slave'  => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'pwd' => '#343kdjer$'
        ],
    ],
    'test' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'pwd' => '#343kdjer$'
        ],
        'slave'  => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'pwd' => '#343kdjer$'
        ],
    ],
    'prod' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'pwd' => '#343kdjer$'
        ],
        'slave'  => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'pwd' => '#343kdjer$'
        ],
    ],
];

?>
```
