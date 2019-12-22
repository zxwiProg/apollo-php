<?php
namespace ApolloPhp;

/**
 * apollo相关配置处理类
 * @copyright   Copyright(c) 2019
 * @author      iProg
 * @version     1.0
 */
class ApolloConfig
{
    const APOLLO_AUTO_SCRIPT_LOCK_FILE = 'apollo_auto_script.lock';

    /**
     * 生成启动脚本
     * @param array   $config      配置信息
     *                             [
     *                                  'apollo_server_url' => 'http://172.17.18.211:38080',
     *                                  'apollo_app_id'     => 'event-analysis-1',
     *                                  'apollo_cluster'    => 'default',
     *                                  'apollo_namespaces' => ['redis', 'mysql'],
     *                                  'app_config_path'   => '/var/www/optopus/config',
     *                                  'app_log_path'      => '/var/www/optopus/log',
     *                             ]
     * @param string  $vendorPath  composer的vendor路径
     * @param string  $phpCli      php的cli路径
     */
    public static function listen(array $config, string $vendorPath = '', string $phpCli = '') : void
    {
        $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? true : false;

        $appLogPath = rtrim($config['app_log_path'], '/') . DIRECTORY_SEPARATOR . 'apollo_runtime.log';

        ini_set('log_errors', 1); 
        ini_set('error_log', $appLogPath);

        $startFunc = $isWin ? '$apollo->winStart();' : '$apollo->start();';

        $code  = '<?php' . PHP_EOL . PHP_EOL;
        $code .= 'require "' . rtrim($vendorPath, '/'). '/vendor/autoload.php";' . PHP_EOL . PHP_EOL;
        $code .= 'ini_set("memory_limit", "128M");' . PHP_EOL;
        $code .= 'ini_set("log_errors", 1);' . PHP_EOL;
        $code .= 'ini_set("error_log", "' . $appLogPath . '");' . PHP_EOL . PHP_EOL;
        $code .= '$config = ' . var_export($config, true) . ';' . PHP_EOL . PHP_EOL;
        $code .= '$apollo = new ApolloPhp\ApolloClient($config);' . PHP_EOL . PHP_EOL;
        $code .= '$cluster = "' . $config["apollo_cluster"] . '";' . PHP_EOL;
        $code .= '$apollo->setCluster($cluster);' . PHP_EOL;
        $code .= $startFunc . PHP_EOL . PHP_EOL;
        $code .= '?>' . PHP_EOL;

        $appConfigPath = rtrim($config['app_config_path'], '/');
        
        $apolloScript = $appConfigPath . DIRECTORY_SEPARATOR . 'apollo_auto_script.php';
        chmod($apolloScript, 0755);
        file_put_contents($apolloScript, $code);

        // 启动脚本（windows是死循环，linux是定时任务）
        $lockFile = $appConfigPath . DIRECTORY_SEPARATOR . self::APOLLO_AUTO_SCRIPT_LOCK_FILE;
        if (!file_exists($lockFile)) {
            file_put_contents($lockFile, 1);
            $sh = 'nohup ' . $phpCli . ' ' . $appConfigPath . '/' . $apolloScript . ' >/dev/null 2>&1 &';
            !$isWin && $sh = 'crontab -l > /tmp/conf && echo "* * * * * {$sh} " >> /tmp/conf && crontab /tmp/conf && rm -f /tmp/conf';
            $errMsg = system($sh, $status);
            error_log('[' . date('Y-m-d H:i:s') . '][status：' . $status  . '] apollo脚本运行错误：' . $errMsg);
        }
    }

    /**
     * 获取某个namespace配置
     * @param array   $config      配置信息
     *                             [
     *                                  'apollo_server_url' => 'http://172.17.18.211:38080',
     *                                  'apollo_app_id'     => 'event-analysis-1',
     *                                  'apollo_cluster'    => 'default',
     *                                  'apollo_namespaces' => ['redis', 'mysql'],
     *                                  'app_config_path'   => '/var/www/optopus/config',
     *                                  'app_log_path'      => '/var/www/optopus/log',
     *                             ]
     * @param string  $namespace  apollo的命名空间
     */
    public static function get(array $config, string $namespace) : array
    {
        $config = [];
        $cluster = $config["apollo_cluster"] ?? 'default';

        $apollo = new ApolloClient($config);
        $apollo->setCluster($cluster);

        // apollo取回来的日志保存在app的配置文件目录，这里从配置目录获取配置
        $configFilePath = $apollo->getConfigFile($namespace);
        file_exists($configFilePath) && $config = require($configFilePath);

        return $config;
    }

    /**
     * 解析apollo返回的配置
     * @param array   $originConfig      配置信息
     *                                   [
     *                                      'mysql.test.host'       => '172.0.0.1',
	 *                                      'mysql.test.mysql.port' => 22,
	 *                                      'mysql.prod.host'       => '172.0.0.1',
	 *                                      'mysql.prod.port'       => 22,
     *                                      'mysql.dev.host'        => '172.0.0.1',
	 *                                      'mysql.dev.port'        => 22,
     *                                   ]
     * @return array
     */
    public static function parseConfig(array $originConfig) : array
    {
        if (empty($originConfig) || !is_array($originConfig)) {
            return [];
        }
        
        $newConfig = [];
        foreach ($originConfig as $keys => $value) {
            $keys = explode('.', $keys);
            if (empty($keys) || !is_array($keys)) {
                continue;
            }
            $codeStr = '$newConfig';
            foreach ($keys as $key) {
                $codeStr .= '["' . $key . '"]';
            }
            $codeStr .= '="' . $value . '";';
            eval($codeStr);
        }
    
        return $newConfig;
    }

}
