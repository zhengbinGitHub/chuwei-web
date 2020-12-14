<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-06
 * Time: 13:30
 */

namespace CwApp\Controllers\Api;

use CwApp\Models\ApiApp;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthTokenController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $appId = $request->get('appid', '');
        if(empty($appId)){
            return $this->returnMsg(0, 'APPID参数缺少');
        }
        $platform = $request->get('platform', '');
        if(empty($platform)){
            return $this->returnMsg(0, 'PLATFORM参数缺少');
        }
        $apiInfo = ApiApp::query()->where(['app_id' => $appId, 'platform' => $platform])->first(['app_secret', 'status']);
        if(!isset($apiInfo->app_secret)){
            return $this->returnMsg(0, '应用信息为空');
        }
        if(0 == $apiInfo->status){
            return $this->returnMsg(0, '应用已关闭');
        }
        $time = time();
        $end_time = time() + 7200;
        $info = $appId. '.' .$time.'.'. $end_time;//设置token过期时间为一天
        //根据以上信息信息生成签名
        $signature = hash_hmac('md5', $info, $apiInfo->app_secret);
        //最后将这两部分拼接起来，得到最终的Token字符串
        $token = $info . '.' . $signature;
        return $this->returnMsg(1, 'ok', ['token' => $token]);
    }

    /**
     * @param $code
     * @param string $msg
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    private function returnMsg($code, $msg = '', $data = null)
    {
        $return_data['status'] = $code;
        $return_data['message']  = $msg;
        $return_data['data'] = $data;
        return response()->json($return_data);
    }
}