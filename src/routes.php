<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:35
 */
Route::group(['namespace' => 'CwApp\Controllers'], function () {
    Route::group(['middleware'=>['web', 'cwapp.auth:'.config('cwapp.app_guard')]], function($router){
        //配置多个应用
        $router->get('proxy/client/{merchant_id}', 'ProxyController@client')->name('proxy.client');
        //保存
        $router->post('proxy/store', 'ProxyController@store')->name('proxy.store');
    });
});

//api
Route::group(['namespace' => 'CwApp\Controllers\Api', 'prefix' => 'api'], function ($router){
    $router->get('proxy/auth/token', 'AuthTokenController@index');
    $router->get('test', 'TestController@index')->middleware('cwapp-api.auth');
});