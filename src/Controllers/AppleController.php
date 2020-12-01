<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:38
 */

namespace CwApp\Controllers;


use CwApp\Models\ApiApp;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

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
        if(!config('cwapp.app_guard') || !config('cwapp.app_default_platform')){
            return view('cwapp::apple-error');
        }
        $lists = ApiApp::query()->where('tenant_id', $merchant_id)->get();
        if(0 == count($lists)){
            $info = ApiApp::query()->create([
                'tenant_id' => $merchant_id,
                'status' => 1,
                'app_id' => $this->app_id($merchant_id),
                'app_secret' => $this->app_secret($merchant_id),
                'platform' => config('cwapp.app_default_platform')
            ]);
        }
        $contents = $this->_getContents($lists, $info??[]);
        return view('cwapp::apple-client', compact('contents', 'merchant_id'));
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
     * @param $length
     * @return string
     */
    private function app_id(int $tenantId, $length = 10) {
        $code = config('cwapp.app_prefix'). $this->make_aid($length) . uniqid();
        return $tenantId . $code;
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
     * @param $length
     * @param $seed
     * @return string
     */
    private function make_aid( $length = 4 )
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
        // 在 $chars 中随机取 $length 个数组元素键名
        $keys = array_rand( $chars, $length );

        $password = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            // 将 $length 个数组元素连接成字符串
            $password .= $chars[$keys[$i]];
        }
        return $password;
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
        $isSuccess = true;
        $params = [];
        foreach ($datas['apps']['platform'] as $key=>$item){
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
            return response()->json(['url'=>url('apple/client', ['merchant_id' => $request->tenant_id]),'message' => '应用配置成功']);
        }
        return response()->json(['message' => '应用配置失败']);
    }
}