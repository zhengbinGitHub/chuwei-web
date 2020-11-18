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
}