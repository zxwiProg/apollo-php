<?php
namespace ApolloPhp;

/**
 * http请求类
 * @copyright   Copyright(c) 2019
 * @author      iProg
 * @version     1.0
 */
class ApolloCurl
{
	/**
     * 发起get请求
     *
     * @param string  $url       请求地址
     * @param array   $options   请求选项
     * @param int     $timeout   超时时间
     * @param int     $header    设定返回信息中是否包含响应信息头
     * @return mixed 
     */
	public static function get(string $url, array $options = [], int $timeout = 30, int $header = 0) : array
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        	curl_setopt($ch, CURLOPT_HEADER, $header);
        	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        	if (!empty($options)) {
            	curl_setopt_array($ch, $options);
        	}
        	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        	//https请求 不验证证书和host
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$respData  = curl_exec($ch);
		$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        	$respError = curl_error($ch);
		curl_close($ch);
		unset($ch);
        	return ['httpCode' => $httpCode, 'respData' => $respData, 'respError' => $respError];
 	}
	
	/**
     	 * 发起带有请求头的post请求，头样式：
     	 * $headerData[] = "Connection: keep-alive"; 
     	 * $headerData[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
     	 *
     	 * @param string  $url        请求地址
     	 * @param array   $postData   请求参数
     	 * @param array   $headerData 请求头选项
     	 * @param int     $timeout    超时时间
     	 * @param int     $header     设定返回信息中是否包含响应信息头
     	 * @return mixed 
     	 */
	public static function post(string $url, array $postData = [], array $headerData = [], $timeout = 30, $header = 0) : array
	{
		$httph = curl_init($url);
		curl_setopt($httph,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($httph, CURLOPT_TIMEOUT,$timeout);
		curl_setopt($httph, CURLOPT_HEADER, $header);
		curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, false);

		if (!empty($postData)) {
		    curl_setopt($httph, CURLOPT_POST, 1);
		    curl_setopt($httph, CURLOPT_POSTFIELDS, $postData);
		}

		if (!empty($headerData)) {
		    curl_setopt($httph, CURLOPT_HTTPHEADER, $headerData);
		}
		
		$respData  = curl_exec($httph);
		$httpCode  = curl_getinfo($httph, CURLINFO_HTTP_CODE);
        	$respError = curl_error($httph);
		curl_close($httph);
		unset($httph);
        	return ['httpCode' => $httpCode, 'respData' => $respData, 'respError' => $respError];
	}

	/**
     	 * 发起多个http请求
	 * @param  array   $reqList   请求列表
	 *                            [
     	 *               		    'key1' => ['url' => 'http://', 'data' => ['a'=>1, 'b'=>2]],
     	 *               		    'key2' => ['url' => 'http://', 'data' => ['a'=>3, 'b'=>4]] 
	 * 			      ]
	 * @param  int     $timeout   响应超时时间
     	 * @return mixed
     	 */
    	public static function multiCurl(array $reqList, int $timeout = 30) : array
    	{
	    $curlArray = [];
            try {
            $mch = curl_multi_init(); 
            foreach($reqList as $key => $info) {
                if(!is_array($info) || !isset($info['url'])) {
                    continue;
				}
				
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $info['url']);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				if (isset($info['data']) && !empty($info['data'])){
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $info['data']);
				}
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $curlArray[$key] = $ch;
                curl_multi_add_handle($mch, $curlArray[$key]);
            }
 
	    $active = NULL;
			
            do { 
                usleep(100);
                $mrc = curl_multi_exec($mch, $active);
	    } while($mrc == CURLM_CALL_MULTI_PERFORM);
			
	    while ($active && $mrc == CURLM_OK) {
		if (curl_multi_select($mch) == -1) {
			usleep(200);
		}
		do {
			$mrc = curl_multi_exec($mch, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	    }

	    $response = [];
	    foreach ($reqList as $key => $info) {
		$ch = $curlArray[$key];
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$respData = curl_multi_getcontent($ch);
		$respError = curl_error($ch);
		curl_multi_remove_handle($mch, $ch);
		curl_close($ch);
		$response[$key] = [
			'httpCode'  => $httpCode,
			'respData'  => $respData,
			'respError' => $respError,
		];
	    }
	    curl_multi_close($mch);  
	    unset($mch);   
            return $response;
        } catch (\Exception $e) {
	    error_log('[' . date('Y-m-d H:i:s') . '] curlMuti运行错误：' . $e->getMessage());
	    return [];
        }
    }
}
