<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:38
 */

namespace CwApp\Controllers;


use CwApp\Models\ApiApp;
use CwApp\Models\OauthClient;
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
            $oauths = OauthClient::query()->where('user_id', $merchant_id)->first();
            if(!isset($oauths->id)){
                $msg = '请先实现Passport生成信息，Oauth 2.0 验证机制！';
                return view('cwapp::proxy-error', compact('msg'));
            }
            $oauths->appid = $oauths->secret.'-'.$merchant_id;
            $oauths->save();

            $info = ApiApp::query()->create([
                'tenant_id' => $merchant_id,
                'status' => 1,
                'app_id' => $oauths->secret.'-'.$merchant_id,
                'app_secret' => $oauths->secret,
                'platform' => config('cwapp.app_default_platform')
            ]);
        }
        $contents = $this->_getContents($lists, $info??[]);
        return view('cwapp::proxy-client', compact('contents', 'merchant_id'));
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
            if($datas['apps']['app_id'][$key] && !$datas['apps']['app_secret'][$key]){
                return response()->json(['status' => 0, 'message' => $datas['apps']['app_id'][$key].' AppSecret为空']);
            }
            if(!$datas['apps']['app_id'][$key] && $datas['apps']['app_secret'][$key]){
                return response()->json(['status' => 0, 'message' => $datas['apps']['app_secret'][$key].' AppID为空']);
            }
            $params = ['tenant_id' => $request->tenant_id, 'app_id' => $datas['apps']['app_id'][$key], 'app_secret' => $datas['apps']['app_secret'][$key], 'platform' => $datas['apps']['platform'][$key]];
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
            return response()->json(['status' => 1, 'url'=>url('apple/client', ['merchant_id' => $request->tenant_id]),'message' => '应用配置成功']);
        }
        return response()->json(['status' => 0, 'message' => '应用配置失败']);
    }
}