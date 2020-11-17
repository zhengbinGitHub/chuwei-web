@extends('cwapp::layouts.main')
@section('content')
    <div class="layui-form">
        <div class="layui-card-header">应用信息</div>
        <div class="layui-card-body">
            <div class="layui-form-item">
                <label class="layui-form-label ">AppID：</label>
                <div class="layui-input-inline col-xs-4">
                    <input type="text" name="data[appid]" id="appid_value" value="{{$info->app_id}}" placeholder="AppID"  autocomplete="off" class="layui-input wid_290">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">AppSecret：</label>
                <div class="layui-input-inline col-xs-4">
                    <input type="text" name="data[appid]" id="appid_value" value="{{$info->app_secret}}" placeholder="AppSecret"  autocomplete="off" class="layui-input wid_290">
                </div>
            </div>
            <div class="layui-form-item  layui-layout-admin">
                <div class="layui-input-block">
                    <div class="layui-footer" style="left: 0;">
                        <a class="layui-btn layui-btn-primary" id="layui-form-close"> 返回 </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop
@section('script')
    @stop