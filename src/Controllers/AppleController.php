<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:38
 */

namespace CwApp\Controllers;


use CwApp\Models\ApiApp;
use Hprose\Http\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AppleController extends Controller
{
    /**
     * @param Request $request
     * @param int $merchant_id
     * @return mixed
     * @throws \Exception
     */
    public function client(Request $request, int $merchant_id)
    {
        if(!config('cwapp.app_guard') || !config('cwapp.app_default_client')){
            return view('cwapp::app-error');
        }
        $name = $request->get('name', config('cwapp.app_platform'));
        $info = ApiApp::query()->where('tenant_id', $merchant_id)->first();
        if(!isset($info->id)){
            $client = $this->_getRpc($merchant_id, $name);
            if($client['client_id']){
                $content[config('cwapp.app_default_client')] = [
                    'app_id' => $client['client_id'],
                    'app_secret' => $client['client_secret'],
                ];
                $info = ApiApp::query()->create(['tenant_id' => $merchant_id, 'status' => 1, 'content' => json_encode($content)]);
            }
        }
        return view('cwapp::apple-client', compact('info', 'contents', 'merchant_id'));
    }

    /**
     * @param $merchant_id
     * @param $name
     * @return array
     * @throws \Exception
     */
    private function _getRpc($merchant_id, $name)
    {
        require_once dirname(dirname(dirname(__FILE__))).'/vendor/autoload.php';
        $urlList = explode(',', config('cwapp.rpc_servers'));
        $client = Client::create($urlList, false);
        $result = $client->user_client(['item_id' => $merchant_id, 'item_type' => 'mall', 'name' => $name]);
        $content = json_decode($result->getContent(), true);
        return ['client_id' => $content['data']['client_id'], 'client_secret' => $content['data']['client_secret']];
    }

    /**
     * 提交
     * @param Request $request
     */
    public function store(Request $request)
    {
        if(!isset($request->tenant_id) || empty($request->tenant_id)){
            return response()->json(['message' => '请登录']);
        }
        $datas = $request->all();
        if(empty($datas['apps'])){
            return response()->json(['message' => '应用信息为空']);
        }
        $params = [];
        foreach ($datas['apps']['platform'] as $key=>$item){
            $params[$item] = ['app_id' => $datas['apps']['app_id'][$key], 'app_secret' => $datas['apps']['app_secret'][$key]];
        }

        $result = ApiApp::query()->updateOrCreate(['id' => $request->id, 'tenant_id' => $request->tenant_id], ['content' => json_encode($params)]);
        if($result){
            return response()->json(['url'=>url('apple/client', ['id' => $request->tenant_id]),'message' => '应用配置成功']);
        }
        return response()->json(['message' => '应用配置失败']);
    }
}