@extends('cwapp::layouts.main')
@section('content')
    <div class="layui-form">
        <div class="layui-card-header">应用信息</div>
        <div class="layui-card-body">
            <blockquote style="text-align: center;">
                请在环境变量.env配置下 APP_GUARD 默认授权设备（如: admin）、APP_DEFAULT_CLIENT 默认开通应用服务别名
            </blockquote>
        </div>
    </div>
    @stop
@section('script')
    @stop