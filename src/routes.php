<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:35
 */
Route::group(['namespace' => 'ChuWei\Client\Web\Controllers'], function () {
    Route::group(['middleware'=>['web', 'cwapp.auth:'.config('cwapp.app_guard')]], function($router){
        //配置多个应用
        $router->get('proxy/client/{merchant_id}', 'ProxyController@client')->name('proxy.client');
        //保存
        $router->post('proxy/store', 'ProxyController@store')->name('proxy.store');
    });
    //详情
    Route::get('proxy/show/{appid}', 'ProxyController@show')->name('proxy.show');
});

Route::group(['namespace' => 'ChuWei\Client\Web\Controllers\Api', 'prefix' => 'api'], function ($router){
    $router->get('proxy/auth/token', 'AuthTokenController@index');
});