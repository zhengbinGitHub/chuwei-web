<?php
namespace CwApp;
use CwApp\Models\ApiApp;

/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-18
 * Time: 21:51
 */

class ApplePackageManager
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 获取签名
     * @return mixed
     */
    public function getSignature(array $params)
    {
        $appSecret = $params['app_secret'];
        return $this->_getSign($params, $appSecret);
    }

    /**
     * 验证签名
     * @param array $params
     */
    public function checkSignature(array $params)
    {
        try{
            $sign = $params['sign'];
            unset($params['sign']);
            $result = $this->_checkSign($params, $sign);
            if($result){
                return true;
            }
        } catch (\Exception $ex){
            app('log')->info('cwapp checkSignature exception', $ex->getMessage());
        }
        return false;
    }

    /**
     * 签名验证
     * @param $params
     * @param $sign
     * @return bool
     */
    private function _checkSign($params, $sign)
    {
        if(empty($sign)) return false;
        $secretKey = $this->_getSecretKey($params['app_id']);
        if(empty($secretKey)) return false;
        if($sign != $this->_getSign($params, $secretKey)) return false;
        return true;
    }

    /**
     * 生成随机字串
     * @param number $length 长度，默认为16，最长为32字节
     * @return string
     */
    public function getNonceStr($length = 16)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    /**
     * 获取消息推送地址
     * @param $appId
     * @return bool
     */
    public function getNotifyUrl($appId)
    {
        if(!$appId) return false;
        $notifyUrl = ApiApp::query()->where('app_id', $appId)->value('notify_url');
        if(!$notifyUrl){
            return false;
        }
        return $notifyUrl;
    }

    /**
     * 获取签名
     * @param $appId
     * @return bool
     */
    private function _getSecretKey($appId)
    {
        $appSecret = ApiApp::query()->where('app_id', $appId)->value('app_secret');
        if(!$appSecret){
            return false;
        }
        return $appSecret;
    }

    /**
     * 获取签名
     * @param $params
     * @param $secretKey
     * @return string
     */
    protected function _getSign($params, $secretKey)
    {
        ksort($params);
        $stringToBeSigned = $secretKey;
        foreach ($params as $k => $v) {
            if (is_string($v) && '@' !== substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $secretKey;

        return strtoupper(md5($stringToBeSigned));
    }

    /**
     * CURL-post方式获取数据
     * @param string $url URL
     * @param array  $data POST数据
     * @param string $proxy 是否代理
     * @param int    $timeout 请求时间
     * @param array $header header信息
     */
    public function post($url, $data, $proxy = null, $timeout = 10, $header=null) {
        if (!$url) {
            if(isset($data['app_id'])){
                $url = $this->getNotifyUrl($data['app_id']);
            }
        }
        if(!$url){
            return false;
        }
        if ($data) {
            $data = http_build_query($data);
        }
        $ssl = stripos($url,'https://') === 0 ? true : false;
        $curl = curl_init();
        if (!is_null($proxy)) curl_setopt ($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        if(isset($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        }
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_POST, true); //发送一个常规的Post请求
        curl_setopt($curl,  CURLOPT_POSTFIELDS, $data);//Post提交的数据包
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数。
        if (is_array($header))
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置请求的Header

        $content = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        if ($curl_errno > 0){
            $error = sprintf("curl error=%s, errno=%d.", curl_error($curl), $curl_errno);
            curl_close($curl);
            throw new Exception($error);
        }
        curl_close($curl);
        return $content;
    }
}