# apollo-php

[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)


## 安装

```shell
composer require iprog/apollo-php
```

## 依赖

- **PHP 7.1** or later
- php-curl扩展
- linux系统上需启用crontab，执行命令service cron start
<br>


## 如何使用：

1、在php的应用的相关位置添加如下配置：

```php
$config = [
    'serverUrl'     => 'http://172.17.18.211:38080',       // apollo的服务器地址
    'appId'         => 'event-analysis-1',                 // apollo上的appid
    'cluster'       => 'default',                          // apollo上的cluster
    'namespaces'    => ['mysql', 'redis'],                 // apollo上的命名空间
    'configPath'    => '/mnt/d/apollo-php/config',         // php应用的配置文件目录
    'app_log_path'  => '/var/www/optopus/log',             // php应用的日志文件目录
];
```

2、在php的应用入口（如index.php文件等）添加如下代码：

```php
// 这里先加载vendor的自动加载文件
require '/app/xxx/vendor/autoload.php';                

// 用上面的配置并启动获取脚本（第2个参数为vendor所在目录，第3个参数为php的cli的指令位置）
ApolloPhp\ApolloConfig::listen($config, '/var/www/app', '/usr/bin/php');
```

3、在其它地方获取配置：

```php
$namespace = 'redis';
ApolloPhp\ApolloConfig::get($config, $namespace);
```
