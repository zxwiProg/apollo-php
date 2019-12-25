<?php

namespace ApolloPhp\Config\Impl;

use ApolloPhp\Config\ApolloConfigInterface;
use ApolloPhp\Popo\PullConfigResult;

/**
 * 阿波罗配置解析实现类
 * @author iProg
 */
class ApolloConfig implements ApolloConfigInterface
{
    /** @var string php应用的配置目录 */
    private $appConfigPath;
    
    /** @var PullConfigResult 拉取回来的apollo配置结果信息 */
    private $apolloConfig;
    
    /**
     * ApolloConfig constructor.
     * @param string $appConfigPath  php应用的配置目录
     */
    public function __construct(string $appConfigPath = '')
    {
        $this->appConfigPath = $appConfigPath;
    }

    /**
     * @return string php应用的配置目录
     */
    public function getAppConfigPath(): string
    {
        return $this->appConfigPath;
    }

    /**
     * @param string $appConfigPath  php应用的配置目录
     * @return static 对象本身
     */
    public function setAppConfigPath(string $appConfigPath): ApolloConfig
    {
        $this->appConfigPath = $appConfigPath;
        return $this;
    }

    /**
     * 解析apollo返回的配置
     * @param PullConfigResult $apolloConfig   拉取回来的配置结果信息
     * @return bool
     */
    public function parseConfig(PullConfigResult $apolloConfig) : bool
    {
        $appConfigPath = $this->getAppConfigPath();
        if (empty($appConfigPath) && empty($apolloConfig)) {
            return false;
        }

        $originConfig = $apolloConfig->getConfigurations();
        $configFileName = rtrim($appConfigPath, '/') . '/' . $apolloConfig->getNamespace() . '.php';

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

        if (!empty($newConfig)) {
            $content  = '<?php' . PHP_EOL . PHP_EOL;
            $content .= 'return '  . $this->myVarExport($newConfig) . ';' . PHP_EOL . PHP_EOL;
            $content .= '?>' . PHP_EOL;
            file_put_contents($configFileName, $content);
        }

        return true;
    }

    /**
     * 返回$expression的美化形式
     * @param   array   $expression   要美化的数组
     * @return  string
     */
    public function myVarExport(array $expression) : string
    {
        $export = var_export($expression, true);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [true, ']$1', ' => ['], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        return $export;
    }
}

# end of file
