<?php
namespace ApolloPhp;

/**
 * apollo客户端请求类
 * @copyright   Copyright(c) 2019
 * @author      iProg
 * @version     1.0
 */
class ApolloClient
{
    protected $appConfigPath;                  // php应用的配置目录

    protected $apolloServerUrl;                // apollo服务端地址
    protected $apolloAppId;                    // apollo配置的appid
    protected $apolloNamespaces;               // apollo配置的namespaces
    protected $apolloCluster = 'default';      // apollo配置的cluster
    protected $apolloReqUrl;                   // 请求apollo的服务器地址

    protected $clientIp = '127.0.0.1';         // 绑定IP做灰度发布用
    
    protected $notifications = [];             // 记录配置中心namespace是否有更新
    protected $pullTimeout = 10;               // 获取某个namespace配置的请求超时时间
    protected $intervalTimeout = 60;           // 每次请求获取apollo配置变更时的超时时间
    
    /**
     * Apollo客户端构造函数
     * @param  array  $config  客户端配置
     */
    public function __construct(array $config)
    {
        $this->apolloAppId      = $config['apollo_app_id'];
        $this->apolloNamespaces = $config['apollo_namespaces'];
        $this->apolloServerUrl  = rtrim($config['apollo_server_url'], '/');

        // 生成请求apollo的基本uri
        $this->apolloReqUrl = $this->apolloServerUrl . '/configs/' . $this->apolloAppId;

        // 设置配置获取后保存的目录
        $this->appConfigPath = dirname($_SERVER['SCRIPT_FILENAME']);
        if (isset($config['app_config_path']) && !empty($config['app_config_path'])) {
            $this->appConfigPath = rtrim($config['app_config_path'], '/');
        }

        // 初始化命名空间通知中心
        foreach ($this->apolloNamespaces as $namespace) {
            $this->notifications[$namespace] = ['namespaceName' => $namespace, 'notificationId' => -1];
        }
    }

    /**
     * 设置配置的cluster.
     * @param string $cluster  配置的cluster
     */
    public function setCluster(string $cluster)
    {
        $this->apolloCluster = $cluster;
    }

    /**
     * 设置灰度ip.
     * @param string $ip  灰度ip
     */
    public function setClientIp(string $ip)
    {
        $this->clientIp = $ip;
    }

    /**
     * 设置某个namespace配置的请求超时时间.
     * @param int  $pullTimeout  请求超时时间
     */
    public function setPullTimeout(int $pullTimeout)
    {
        $pullTimeout = intval($pullTimeout);
        if ($pullTimeout >= 1 && $pullTimeout <= 300) {
            $this->pullTimeout = $pullTimeout;
        }
    }

    /**
     * 设置每次请求获取apollo配置变更时的超时时间
     * @param int  $intervalTimeout  配置变更时的超时时间
     */
    public function setIntervalTimeout(int $intervalTimeout)
    {
        $intervalTimeout = intval($intervalTimeout);
        if ($intervalTimeout >= 1 && $intervalTimeout <= 300) {
            $this->intervalTimeout = $intervalTimeout;
        }
    }

    /**
     * 获取单个namespace的配置文件路径
     * @param   string    $namespace   配置命名空间
     * @return  string
     */
    public function getConfigFile(string $namespace) : string
    {
        return $this->appConfigPath . DIRECTORY_SEPARATOR . 'apollo.' . $namespace . '.php';
    }

    /**
     * 获取key
     * @param   string  $configFilePath  配置文件路径
     * @return  string
     */
    private function _getReleaseKey(string $namespace) : string
    {
        $configFilePath = $this->getConfigFile($namespace);
        if (!file_exists($configFilePath)) {
            return '';
        }

        $releaseKey = '';
        $lastConfig = require $configFilePath;
        if (is_array($lastConfig) && isset($lastConfig['releaseKey'])) {
            $releaseKey = $lastConfig['releaseKey'];
        }

        return $releaseKey;
    }

    /**
     * 获取单个namespace的配置-无缓存的方式
     * @param   string   $namespace   配置命名空间
     * @return  boolean
     */
    public function pullConfig(string $namespace) : bool
    {
        $releaseKey = $this->_getReleaseKey($namespace);

        // 请求apollo
        $args = http_build_query(['ip' => $this->clientIp, 'releaseKey' => $releaseKey]);
        $url = $this->apolloReqUrl . '/' . $this->apolloCluster . '/' . $namespace . '?' . $args;
        $ret = ApolloCurl::get($url, [], $this->pullTimeout);

        // 304直接返回true
        if ($ret['httpCode'] == 304) {
            return true;
        }
        // 非200直接返回false
        if ($ret['httpCode'] != 200) {
            $errMsg = $ret['respData'] ?: $ret['respError'];
            error_log('[' . date('Y-m-d H:i:s') . '] pull config of namespace[' . $namespace . '] error:' . $errMsg);
            return false;
        }
        // 200直接返回结果
        $result = json_decode($ret['respData'], true);
        if ($result && is_array($result)) {
            $newConfig = ApolloConfig::parseConfig($result['configurations']);
            $content  = '<?php' . PHP_EOL . PHP_EOL;
            $content .= 'return '  . var_export($newConfig, true)  . ';' . PHP_EOL . PHP_EOL;
            $content .= '?>' . PHP_EOL;
            $configFilePath = $this->getConfigFile($namespace);
            file_put_contents($configFilePath, $content);
        }
        return true;
    }

    /**
     * 获取多个namespace的配置-无缓存的方式
     * @param array  $namespaces  配置命名空间
     * @return array
     */
    public function pullConfigBatch(array $namespaces) : array
    {
        if (!$namespaces) {
            return [];
        }

        $reqList = $respList = [];

        // 处理请求数据
        foreach ($namespaces as $namespace) {
            $releaseKey = $this->_getReleaseKey($namespace);
            $args = http_build_query(['ip' => $this->clientIp, 'releaseKey' => $releaseKey]);
            $url = $this->apolloReqUrl . '/' . $this->apolloCluster . '/' . $namespace . '?' . $args;
            $reqList[$namespace] = ['url' => $url, 'data' => []];
        }

        $response = ApolloCurl::multiCurl($reqList, $this->pullTimeout);
        if (empty($response) || !is_array($response)) return [];
        
        // 处理返回结果
        foreach ($reqList as $namespace => $info) {
            $respList[$namespace] = true;
            if (!isset($response[$namespace])) {
                $respList[$namespace] = false;
                continue;
            }
            if ($response[$namespace]['httpCode'] == 304) {
                continue;
            }
            if ($response[$namespace]['httpCode'] != 200) {
                $respList[$namespace] = false;
                $errMsg = $response[$namespace]['respData'] ?: $response[$namespace]['respError'];
                error_log('[' . date('Y-m-d H:i:s') . '] pull config of namespace[' . $namespace . '] error:' . $errMsg);
                continue;
            }
            $result = json_decode($response[$namespace]['respData'], true);
            if ($result && is_array($result)) {
                $newConfig = ApolloConfig::parseConfig($result['configurations']);
                $content  = '<?php' . PHP_EOL . PHP_EOL;
                $content .= 'return '  . var_export($newConfig, true)  . ';' . PHP_EOL . PHP_EOL;
                $content .= '?>' . PHP_EOL;
                $configFilePath = $this->getConfigFile($namespace);
                file_put_contents($configFilePath, $content);
            }
        }
        return $respList;
    }

    /**
     * 监听配置文件变化(linux)
     */
    public function start() : void
    {
        $this->pullConfigBatch(array_keys($this->notifications));
    }

    /**
     * 监听配置文件变化(windows)
     */
    public function winStart() : void
    {
        $query = ['appId' => $this->apolloAppId, 'cluster' => $this->apolloCluster];
        while (true) {
            $query['notifications'] = json_encode(array_values($this->notifications));
            $url = $this->apolloServerUrl . '/notifications/v2?' . http_build_query($query);
            $ret = ApolloCurl::get($url, [], $this->intervalTimeout);
            
            if ($ret['httpCode'] == 200) {
                $response = json_decode($ret['respData'], true);
                $changeList = [];
                foreach ($response as $r) {
                    if ($r['notificationId'] != $this->notifications[$r['namespaceName']]['notificationId']) {
                        $changeList[$r['namespaceName']] = $r['notificationId'];
                    }
                }
                $responseList = $this->pullConfigBatch(array_keys($changeList));
                foreach ($responseList as $namespaceName => $result) {
                    $result && ($this->notifications[$namespaceName]['notificationId'] = $changeList[$namespaceName]);
                }
            } elseif ($ret['httpCode'] != 304) {
                $errMsg = $ret['respData'] ?: $ret['respError'];
                error_log('[' . date('Y-m-d H:i:s') . '] pull notifications error:' . $errMsg);
            }
        }
    }

}
