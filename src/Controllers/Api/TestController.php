<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-12-01
 * Time: 11:16
 */

namespace ChuWei\Client\Web\Controllers\Api;


use ChuWei\Client\Web\Models\ApiApp;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    /**
     * @var ApiApp
     */
    private $apiApp;

    /**
     * TestController constructor.
     */
    public function __construct(ApiApp $apiApp)
    {
        $this->apiApp = $apiApp;
    }

    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $info = $this->apiApp->where('app_id', $request->appid)->first();
        if(!$info->id){
            return response()->json(['status' => 0, 'message' => '应用信息为空']);
        }
        return response()->json(['status' => 1, 'message' => 'ok', 'data' => ['app_id' => $info->app_id, 'app_secret' => $info->app_secret]]);
    }
}