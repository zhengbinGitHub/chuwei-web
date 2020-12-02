<?php
namespace CwApp\Tests;
use CwApp\Models\ApiApp;
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-12-01
 * Time: 22:47
 */

class ClientTest extends TestCase
{
    private $token;

    public function setUp() :void
    {
        parent::setUp();
        $url = config('cwapp.app_test_url') . '/client/token';
        $info = ApiApp::query()->where('platform', config('cwapp.app_test_platform'))->first();
        if(!isset($info->id)){
            $this->assertEquals(1, 0);
        }
        $response = $this->json('get', $url, ['appid' => $info->app_id, 'platform' => 'fuwu']);
        $result = $response->getOriginalContent();
        $this->token = $result['data']['token'];
    }

    public function testToken()
    {
        $url = config('cwapp.app_test_url') . '/test';
        $response = $this->json('get', $url, [], ['X-Auth-Token' => $this->token, 'X-Auth-Appid' => $this->appid, 'X-Auth-Platform' => config('cwapp.app_test_platform')]);
        $result = $response->getOriginalContent();
        $this->assertJson($result);
    }
}