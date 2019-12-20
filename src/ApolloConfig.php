<?php
namespace Octopus\ApolloPhp;

class ApolloConfig
{
    /**
     * 生成启动脚本
     * @param array   $config      配置信息
     *                              [
     *                                   'serverUrl' => 'http://172.17.18.211:38080',
     *                                   'appId'     => 'event-analysis-1',
     *                                   'cluster'   => 'default',
     *                                   'namespaces'=> ['redis', 'mysql'],
     *                                   'configPath'=> '/var/www/optopus/config',
     *                              ]
     * @param string  $vendorPath  composer的vendor路径
     * @param string  $phpCli      php的cli路径
     */
    public static function run($config, $vendorPath = '', $phpCli = '')
    {
        $apolloScript = 'apollo_script.php';
        $filename = $config['configPath'] . DIRECTORY_SEPARATOR . 'apollo_config_lock';

        $code  = '<?php' . PHP_EOL . PHP_EOL;
        $code .= 'require "' . $vendorPath . '/autoload.php";' . PHP_EOL . PHP_EOL;
        $code .= 'ini_set("memory_limit", "128M");' . PHP_EOL . PHP_EOL;
        $code .= '$serverUrl = "' . $config["serverUrl"] . '";' . PHP_EOL;
        $code .= '$appId = "' . $config["appId"] . '";' . PHP_EOL;
        $code .= '$namespaces = ' . var_export($config["namespaces"], true) . ';' . PHP_EOL;
        $code .= '$configPath = "' . $config["configPath"] . '";' . PHP_EOL;
        $code .= '$cluster = "' . $config["cluster"] . '";' . PHP_EOL;
        $code .= '$apollo = new Octopus\ApolloPhp\ApolloClient(' . PHP_EOL;
        $code .= '    $serverUrl,' . PHP_EOL;
        $code .= '    $appId,' . PHP_EOL;
        $code .= '    array_values($namespaces),' . PHP_EOL;
        $code .= '    $configPath' . PHP_EOL;
        $code .= ');' . PHP_EOL;
        $code .= '$apollo->setCluster($cluster);' . PHP_EOL;
        $code .= '$restart = true;' . PHP_EOL;
        $code .= 'do {' . PHP_EOL;
        $code .= '    $error = $apollo->start();' . PHP_EOL;
        $code .= '    if ($error) echo("error:" . $error . "\n");' . PHP_EOL;
        $code .= '} while ($error && $restart);' . PHP_EOL . PHP_EOL;
        $code .= '?>' . PHP_EOL;

        file_put_contents($apolloScript, $code);

        if (!file_exists($filename)) {
            file_put_contents($filename, 1);
            $script = 'nohup ' . $phpCli . ' ' . $config['configPath'] . '/' . $apolloScript . ' >/dev/null 2>&1 &';
            system($script, $return_status);
        }
        
    }

    /**
     * 获取某个namespace配置
     * @param array   $config      配置信息
     *                              [
     *                                   'serverUrl' => 'http://172.17.18.211:38080',
     *                                   'appId'     => 'event-analysis-1',
     *                                   'cluster'   => 'default',
     *                                   'namespaces'=> ['redis', 'mysql'],
     *                                   'configPath'=> '/var/www/optopus/config',
     *                              ]
     * @param string  $namespace  apollo的命名空间
     */
    public static function get($config, $namespace)
    {
        $serverUrl = $config["serverUrl"];
        $appId = $config["appId"];
        $namespaces = $config["namespaces"];
        $configPath = $config["configPath"];
        $cluster = $config["cluster"];
        $apollo = new ApolloClient($serverUrl, $appId, $namespaces, $configPath);
        $apollo->setCluster($cluster);
        $configFilePath = $apollo->getConfigFile($namespaceName);
        return require($configFilePath);    
    }

}
