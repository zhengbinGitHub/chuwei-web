<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 18:06
 */

namespace CwApp\Middleware;

use Closure;
use CwApp\Models\ApiApp;

class CwAppApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $this->getAuthToken();
        if(!$token){
            return $this->returnMsg(0, '头部X-Auth-Token参数为空');
        }
        $request->platform = request()->header('X-Auth-Platform');
        $request->appid = request()->header('X-Auth-Appid');
        if(!$request->platform || !$request->appid){
            return $this->returnMsg(0, '头部X-Auth-Platform／X-Auth-Appid参数为空');
        }
        try{
            $appInfo = ApiApp::query()->where(['app_id' => $request->appid, 'platform' => $request->platform])->first(['tenant_id', 'app_id', 'app_secret']);
            if(!isset($appInfo->tenant_id)){
                return $this->returnMsg(0, '应用信息为空');
            }
            $request->headers->set('tenantId', $appInfo->tenant_id??0);
            $this->validate($token, $appInfo->app_secret);
        }catch (\Exception $ex){
            return $this->returnMsg(0, $ex->getMessage());
        }
        return $next($request);
    }

    /**
     * @param $code
     * @param $message
     * @param null $data
     * @return false|string
     */
    private function returnMsg($code, $message, $data = null)
    {
        return response()->json(['status' => $code, 'message' => $message, 'data' => $data]);
    }

    /**
     * @return array|mixed|string|null
     */
    protected function getAuthToken() {
        $token = request()->header('Authorization');
        if(empty($token)) {
            $token = request()->header('auth_token');
        }
        return str_replace('Bearer ', '', $token);
    }

    /**
     * 验证token
     * @param $token
     * @param $appSecret
     * @throws \Exception
     */
    public function validate($token, $appSecret)
    {
        /**** api传来的token ****/
        if(!isset($token) || empty($token))
        {
            throw new \Exception('非法请求', 400);
        }
        //对比token
        $explode = explode('.',$token);//以.分割token为数组
        if(!empty($explode[0]) && !empty($explode[1]) && !empty($explode[2]) && !empty($explode[3]) )
        {
            $info = $explode[0].'.'.$explode[1].'.'.$explode[2];//信息部分
            $true_signature = hash_hmac('md5', $info, $appSecret);//正确的签名
            if ($true_signature != $explode[3])
            {
                throw new \Exception('Token不合法', 400);
            }
        }
        else
        {
            throw new \Exception('Token签名串不合法', 400);
        }
    }
}