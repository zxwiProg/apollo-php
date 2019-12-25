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
