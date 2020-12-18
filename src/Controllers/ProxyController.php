<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:38
 */

namespace ChuWei\Client\Web\Controllers;


use ChuWei\Client\Web\Models\ApiApp;
use ChuWei\Client\Web\Models\Merchant;
use ChuWei\Client\Web\Models\OauthClient;
use ChuWei\Client\Web\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class ProxyController extends Controller
{
    /**
     * @param Request $request
     * @param int $merchant_id
     * @return mixed
     * @throws \Exception
     */
    public function client(Request $request, int $merchant_id)
    {
        if(!config('cwapp.app_guard') || !config('cwapp.app_default_platform')){
            return view('cwapp::proxy-error');
        }
        $lists = ApiApp::query()->where('tenant_id', $merchant_id)->get();
        if(0 == count($lists)){
            $secret = $this->app_secret($merchant_id);
            $info = ApiApp::query()->create([
                'tenant_id' => $merchant_id,
                'status' => 1,
                'app_id' => $this->app_id($merchant_id),
                'app_secret' => $secret,
                'platform' => config('cwapp.app_default_platform')
            ]);
            if($info){
                if('mall' == config('cwapp.app_default_platform')){
                    $this->saveMerchant($merchant_id, $secret);
                } elseif ('fuwu'  == config('cwapp.app_default_platform')){
                    $this->saveTenant($merchant_id, $secret);
                }
            }
        }
        $contents = $this->_getContents($lists, $info??[]);
        return view('cwapp::proxy-client', compact('contents', 'merchant_id'));
    }

    /**
     * @param $merchantId
     * @param $token
     */
    private function saveMerchant($merchantId, $token)
    {
        try {
            $key = Merchant::query()->where('id', $merchantId)->value('key');
            if (!$key)
                Merchant::query()->where('id', $merchantId)->update(['key' => $token]);
        }catch (\Exception $e){
            return;
        }
    }

    /**
     * @param $tenantId
     * @param $token
     */
    private function saveTenant($tenantId, $token)
    {
        try {
            $key = Tenant::query()->where('id', $tenantId)->value('key');
            if (!$key)
                Tenant::query()->where('id', $tenantId)->update(['key' => $token]);
        }catch (\Exception $e){
            return;
        }
    }

    /**
     * 配置信息
     * @param $lists
     * @param $info
     * @return array
     */
    private function _getContents($lists, $info)
    {
        $contents = [];
        if(0 < count($lists)){
            foreach ($lists as $item){
                $contents[$item->platform] = [
                    'id' => $item->id,
                    'app_id' => $item->app_id,
                    'app_secret' => $item->app_secret,
                    'platform' => $item->platform,
                ];
            }
        } else {
            $contents[config('cwapp.app_default_platform')] = [
                'id' => $info->id,
                'app_id' => $info->app_id,
                'app_secret' => $info->app_secret,
                'platform' => $info->platform,
            ];
        }
        return $contents;
    }

    /**
     * 提交
     * @param Request $request
     */
    public function store(Request $request)
    {
        if(!isset($request->tenant_id) || empty($request->tenant_id)){
            return response()->json(['status' => 0, 'message' => '请登录']);
        }
        $datas = $request->all();
        if(empty($datas['apps'])){
            return response()->json(['status' => 0, 'message' => '应用信息为空']);
        }
        $isSuccess = true;
        foreach ($datas['apps']['platform'] as $key=>$item){
            if(!$datas['apps']['app_id'][$key] && !$datas['apps']['app_secret'][$key]) continue;

            if($datas['apps']['app_id'][$key] && !$datas['apps']['app_secret'][$key]){
                return response()->json(['status' => 0, 'message' => $datas['apps']['app_id'][$key].' AppSecret为空']);
            }
            if(!$datas['apps']['app_id'][$key] && $datas['apps']['app_secret'][$key]){
                return response()->json(['status' => 0, 'message' => $datas['apps']['app_secret'][$key].' AppID为空']);
            }
            //核查appid
            $result = $this->checkApp($datas['apps']['app_id'][$key], $datas['apps']['app_secret'][$key], $datas['apps']['platform'][$key]);
            if(0 == $result['status'] && $result['message']){
                return response()->json(['status' => 0, 'message' => $result['message']]);
            }
            $params = ['tenant_id' => $datas['tenant_id'], 'app_id' => $datas['apps']['app_id'][$key], 'app_secret' => $datas['apps']['app_secret'][$key], 'platform' => $datas['apps']['platform'][$key]];
            if($datas['apps']['id'][$key] == 0){
                if(!ApiApp::query()->create(array_merge($params, ['status' => 1]))){
                    $isSuccess = false;
                    break;
                }
            } else {
                ApiApp::query()->where('id', $datas['apps']['id'][$key])->update($params);
            }
        }
        if($isSuccess){
            return response()->json(['status' => 1, 'url'=>url('proxy/client', ['merchant_id' => $datas['tenant_id']]),'message' => '应用配置成功']);
        }
        return response()->json(['status' => 0, 'message' => '应用配置失败']);
    }

    /**
     * @param string $appid
     * @param string $appsecret
     * @param string $platform
     */
    private function checkApp(string $appid, string $appsecret, string $platform)
    {
        $url = config('cwapp.app_check_urls')[$platform]['url']??'';
        if(!$url) return ['status' => 0, 'message' => ''];
        try {
            $result = $this->curl_request($url . '/' . $appid);
            if(0 == $result['status']){
                return ['status' => 0, 'message' => $result['message']];
            }
            if($result['data']['app_secret'] != $appsecret){
                return ['status' => 0, 'message' => 'app_secret不对'];
            }
            return ['status' => 1];
        }catch (\Exception $e){
            return ['status' => 0, 'message' => $e->getMessage()];
        }
    }

    /**
     * 应用详情
     * @param Request $request
     * @param $appid
     * @return mixed
     */
    public function show(Request $request, $appid)
    {
        $info = ApiApp::query()->where('app_id', $appid)->first(['id', 'app_id', 'app_secret']);
        if(!isset($info->id)){
            return response()->json(['status' => 0, 'message' => 'APPID信息不存在']);
        }
        return response()->json(['status' => 1, 'message' => 'ok', 'data' => ['app_id' => $info->app_id, 'app_secret' => $info->app_secret]]);
    }

    //参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
    private function curl_request($url, $post='', $cookie='', $returnCookie=0)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
           return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
          return json_decode($data, true);
        }
    }

    /**
     * @param $length
     * @return string
     */
    private function app_id(int $tenantId) {
        return md5($tenantId . uniqid() . Str::random(40));
    }

    /**
     * @param $tenantId
     * @return string
     */
    private function app_secret(int $tenantId)
    {
        return $tenantId . Str::random(40) . uniqid();
    }

    /**
     * 生成随机字串
     * @param number $length 长度，默认为16，最长为32字节
     * @return string
     */
    private function getNonceStr($length = 16)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }
}