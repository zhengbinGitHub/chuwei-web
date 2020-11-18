<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:35
 */
Route::group(['namespace' => 'CwApp\Controllers'], function () {
    Route::get('apple/auth', 'AuthTokenController@index')->name('apple.auth');
    Route::group(['middleware'=>['web', 'cwapp.auth:'.config('cwapp.app_guard')]], function($router){
        $router->get('apple/show', 'AppleController@show')->name('apple.show');
        //配置多个应用
        $router->get('apple/create', 'AppleController@create')->name('apple.create');
        //保存
        $router->post('apple/store', 'AppleController@store')->name('apple.store');
    });
});