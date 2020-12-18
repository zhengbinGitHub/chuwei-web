<?php
namespace ChuWei\Client\Web;

use ChuWei\Client\Web\Lib\CurlRequest;
use ChuWei\Client\Web\Models\ApiApp;
use Illuminate\Support\Facades\Cache;

/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-12-17
 * Time: 18:11
 */

class ProxyWebManage
{
    /**
     * 获取商户APPID
     * @param $merchantId
     * @param $platform
     * @return int
     */
    public function getApiAppid($merchantId, $platform)
    {
        $appId = ApiApp::query()->where(['tenant_id' => $merchantId, 'platform' => $platform])->value('app_id');
        return $appId?:'';
    }

    /**
     * 获取商户ID
     * @param $appid
     * @return int
     */
    public function getTenantId($appid)
    {
        $tenantId = ApiApp::query()->where('app_id', $appid)->value('tenant_id');
        return $tenantId?:0;
    }

    /**
     * 授权token
     * @param $merchantId
     * @param $platform
     * @throws \Exception
     */
    public function getAuthToken($merchantId, $platform)
    {
        $cacheKey = 'sold_token:merchant-'.$platform.'-'.$merchantId;
        $merchantStr = Cache::store('redis')->get($cacheKey);
        if(empty($merchantStr)) {
            $appid = $this->getApiAppid($merchantId, $platform);
            if(!$appid){
                return response()->json(['status' => 0, 'message' => '应用信息为空']);
            }
            $token = $this->getToken($appid, $platform);
            if(!$token) return response()->json(['status' => 0, 'message' => '获取TOKEN失败']);
            Cache::store('redis')->put($cacheKey, json_encode(['token' => $token, 'appid' => $appid]), now()->addMinutes(10));
        } else {
            $merchantArr = json_decode($merchantStr, true);
            $token = $merchantArr['token'];
            $appid = $merchantArr['appid'];
        }
        return response()->json([
            'status' => 1,
            'message' => '应用信息',
            'data' => [
                'token' => $token,
                'appid' => $appid
            ]
        ]);
    }

    /**
     * token
     * @param $appid
     * @param $platform
     * @return string
     */
    private function getToken($appid, $platform)
    {
        $url = config('cwapp.app_check_urls')[$platform]['url']??'';
        if(!$url) return '';
        $result = CurlRequest::curl_request($url . '/proxy/auth/token?appid='.$appid);
        if(1 == $result['status'] && $result['data']['token']){
            return $result['data']['token'];
        }
        return '';
    }
}