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
            $oauths = OauthClient::query()->firstOrCreate([
                'user_id' => $merchant_id,
            ], [
                'name' => 'Stock access',
                'secret' => Str::random(40),
                'redirect' => 'http://localhost',
                'personal_access_client' => 1,
                'password_client' => 0,
                'revoked' => 0
            ]);
            $appid = md5($oauths->secret.'-'.$merchant_id.'-'.time());
            $info = ApiApp::query()->create([
                'tenant_id' => $merchant_id,
                'status' => 1,
                'app_id' => $appid,
                'app_secret' => $oauths->secret,
                'platform' => config('cwapp.app_default_platform')
            ]);
            if($info){
                if('mall' == config('cwapp.app_default_platform')){
                    $this->saveMerchant($merchant_id, $oauths->secret);
                } elseif ('fuwu'  == config('cwapp.app_default_platform')){
                    $this->saveTenant($merchant_id, $oauths->secret);
                }
                OauthClient::query()->where('id', $oauths->id)->update(['appid' => $appid]);
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
}