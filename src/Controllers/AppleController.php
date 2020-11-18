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
use Illuminate\Support\Facades\DB;

class AppleController extends Controller
{
    /**
     * 显示应用信息
     * @param Request $request
     */
    public function show(Request $request)
    {
        $info = ApiApp::query()
            ->updateOrCreate(
                ['tenant_id' => $request->tenant_id, 'parent_id' => 0],
                [
                    'app_id' => $this->app_code($request->tenant_id),
                    'app_secret' => $this->app_code($request->tenant_id),
                ]
            );
        return view('cwapp::apple-show', compact('info'));
    }

    /**
     * 创建
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $lists = ApiApp::query()->where('tenant_id', $request->tenant_id)->whereNotIn('parent_id', [0])->get(['id', 'app_id', 'app_secret', 'platform']);
        return view('cwapp::apple-create', compact('lists'));
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
        $params = $notifyUrl = [];
        $id = ApiApp::query()
            ->where(['tenant_id' => $request->tenant_id, 'parent_id' => 0])->value('id');
        if(0 == $id){
            return response()->json(['message' => '请先配置默认应用', 'url' => url('apple/show')]);
        }
        foreach ($datas['apps'] as $key=>$item){
            if(empty($item['app_id']) || empty($item['app_secret']) || empty($item['notify_url'])){
                ++$key;
                return response()->json(['message' => "第{$key}个应用AppID、AppSecret、Notify_url信息为空"]);
            }
            if(isset($notifyUrl[$item['notify_url']])){
                ++$key;
                return response()->json(['message' => "第{$key}个应用Notify_url信息重复"]);
            }
            $notifyUrl[$item['notify_url']] = $item['notify_url'];

            $params[$key] = [
                'parent_id' => $id,
                'tenant_id' => $request->tenant_id,
                'app_id' => $item['app_id'],
                'app_secret' => $item['app_secret'],
                'notify_url' => $item['notify_url'],
                'status' => 1,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }
        unset($notifyUrl);
        DB::beginTransaction();
        ApiApp::query()->where('tenant_id', $request->tenant_id)->whereNotIn('platform', [0])->delete();
        if(ApiApp::query()->insert($params)){
            DB::commit();
            return response()->json(['url'=>url('apple/create'),'message' => '应用配置成功']);
        }
        DB::rollBack();
        return response()->json(['message' => '应用配置失败']);
    }

    /**
     * @param $length
     * @return string
     */
    private function app_code($tenantId, $length = 10) {
        $code = config('cwapp.app_prefix'). $this->make_aid($length) . uniqid();
        return $tenantId . $code;
    }

    /**
     * @param $length
     * @param $seed
     * @return string
     */
    function make_aid( $length = 4 )
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
}