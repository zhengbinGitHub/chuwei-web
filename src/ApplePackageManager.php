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
        return $this->_getSign($params, $this->config['app_secret']);
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
        if($sign != $this->_getSign($params($params), $secretKey)) return false;
        return true;
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