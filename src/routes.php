<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:35
 */
Route::group(['prefix' => 'apple', 'namespace' => 'CwApp\Controllers'], function () {
    Route::get('auth', 'AuthTokenController@index');
    Route::group(['middleware'=>['web', 'cwapp.auth:'.config('cwapp.app_guard')]], function($router){
        $router->get('show', 'AppleController@show');
        //配置多个应用
        $router->get('create', 'AppleController@create');
        //保存
        $router->post('store', 'AppleController@store');
    });
});