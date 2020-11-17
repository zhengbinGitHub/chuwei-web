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
        $result = $this->validate($token);
        if(0 != $result['code']) {
            return json_encode($result);
        }
        return $next($request);
    }


    /**
     * @return array|mixed|string|null
     */
    protected function getAuthToken() {
        $token = Request::header('X-Auth-Token');
        if(empty($token)) {
            $token = Input::get('auth_token');
        }
        return $token;
    }

    /**
     * @param $token
     * @return string
     * name: libo
     * Date: 2020/9/29
     * 验证token
     */
    public function validate($token)
    {
        /**** api传来的token ****/
        if(!isset($token) || empty($token))
        {
            $msg['code'] = '400';
            $msg['msg'] = '非法请求';
            return $msg;
        }
        $platform = Request::header('X-Auth-Platform');
        $appId = Request::header('X-Auth-Appid');
        //对比token
        $explode = explode('.',$token);//以.分割token为数组
        if(!empty($explode[0]) && !empty($explode[1]) && !empty($explode[2]) && !empty($explode[3]) )
        {
            $info = $explode[0].'.'.$explode[1].'.'.$explode[2];//信息部分
            $true_signature = hash_hmac('md5', $info, ApiApp::query()->where(['app_id' => $appId, 'platform' => $platform])->value('app_secret'));//正确的签名
            if(time() > $explode[2])
            {
                $msg['code'] = '401';
                $msg['msg'] = 'Token已过期,请重新登录';
                return $msg;
            }
            if ($true_signature == $explode[3])
            {
                $msg['code'] = '200';
                $msg['msg'] = 'Token合法';
                return $msg;
            }
            else
            {
                $msg['code'] = '400';
                $msg['msg'] = 'Token不合法';
                return $msg;
            }
        }
        else
        {
            $msg['code'] = '400';
            $msg['msg'] = 'Token不合法';
            return $msg;
        }
        $msg['code'] = 0;
        $msg['msg'] = 'Token合法';
        return $msg;
    }
}