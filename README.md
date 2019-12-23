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
- php-curl扩展
- linux系统上需启用crontab，执行命令service cron start
<br>

## 如何使用：

1、在php的应用的相关位置添加如下配置：

```php
$config = [
    'apollo_server_url'  => 'http://172.17.18.211:38080',       // apollo的服务器地址
    'apollo_app_id'      => 'event-analysis-1',                 // apollo上的appid
    'apollo_cluster'     => 'default',                          // apollo上的cluster
    'apollo_namespaces'  => ['mysql', 'redis'],                 // apollo上的命名空间
    'app_config_path'    => '/var/www/demo/config',             // php应用的配置文件目录
    'app_log_path'       => '/var/www/demo/log',                // php应用的日志文件目录
    'app_pull_interval'  => 10,                                 // 定时刷新间隔：5秒，10秒，20秒，30秒，60秒
];
```

2、在php的应用入口（如index.php文件等）添加如下代码：

```php
// 这里先加载vendor的自动加载文件
require '/var/www/demo/vendor/autoload.php';                

// 用上面的配置并启动获取脚本（第2个参数为vendor所在目录，第3个参数为php的cli的指令位置）
ApolloPhp\ApolloConfig::listen($config, '/var/www/demo/vendor', '/usr/bin/php');
```

3、在其它地方获取配置：

```php
$namespace = 'redis';
ApolloPhp\ApolloConfig::get($config, $namespace);
```
<br>

## apollo配置中心如何做配置：

需要说明的是，apollo配置中心的配置以键值对形式存在，所以，为了方便apollo-php在代码层面做解析，apollo-php拟定了一个统一的配置方式，以下举例说明。

比如，现在我们要配置redis的链接参数信息，首先我们需要在apollo配置中心配置一个redis的namespace，然后在该namespace，可以做如下配置：
```php
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
