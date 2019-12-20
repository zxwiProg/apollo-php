# apollo-php

[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)


## 安装

最好的安装方法是通过 [Composer](http://getcomposer.org/) 包管理器 :

```shell
composer require iprog/apollo-php
```

------

## 依赖

- **PHP71** or later

------
<br>

## 如何使用：

1、在php的应用入口（如index.php文件等）添加如下代码：

```php
require '/app/xxx/vendor/autoload.php';                // 这里加载vendor的自动加载

$config = [
	'serverUrl' => 'http://172.17.18.211:38080',       // apollo的服务器地址
	'appId'     => 'event-analysis-1',                 // apollo上的appid
    'cluster'   => 'default',                          // apollo上的cluster
    'namespaces'=> ['mysql', 'redis'],                 // apollo上的命名空间
    'configPath'=> '/mnt/d/apollo-php/config',         // php应用的配置文件目录
];

ApolloPhp\ApolloConfig::run(
	$config, 
	'/var/www/app',                                    // composer的vendor所在目录
	'/usr/bin/php'                                     // php客户端cli的指令目录
);
```

2、在其它地方获取配置：

```php
require '/app/xxx/vendor/autoload.php';                // 这里加载vendor的自动加载

$config = [
	'serverUrl' => 'http://172.17.18.211:38080',       // apollo的服务器地址
	'appId'     => 'event-analysis-1',                 // apollo上的appid
    'cluster'   => 'default',                          // apollo上的cluster
    'namespaces'=> ['mysql', 'redis'],                 // apollo上的命名空间
    'configPath'=> '/mnt/d/apollo-php/config',         // php应用的配置文件目录
];

$namespace = 'redis';
ApolloPhp\ApolloConfig::get($config, $namespace);
var_dump($namespace);
```