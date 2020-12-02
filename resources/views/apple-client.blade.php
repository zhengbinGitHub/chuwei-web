@extends('cwapp::layouts.main')
@section('content')
    <div class="layui-form">
        <div class="layui-card-body">
            <form class="layui-form base_form_current" action="{{url('apple/store')}}" method="post">
                @csrf
                <input type="hidden" name="tenant_id" value="{{$merchant_id}}">
                <input type="hidden" name="id" value="{{$info->id??0}}">
                @php
                    $index = 0;
                @endphp
                @foreach(collect(config('cwapp.clients'))->sortByDesc('sort')->all() as $key=>$item)
                    @php
                        $content = $contents[$item['alias']]??[];
                    @endphp
                    <fieldset class="layui-elem-field layui-field-title" @if($index != 0)style="margin-top: 50px; @endif">
                        <legend>{{$item['name']}}</legend>
                    </fieldset>
                    <input type="hidden" value="{{$item['alias']}}" name=apps[platform][]">
                    <input type="hidden" value="{{$content['id']??0}}" name=apps[id][]">
                    <div class="layui-form-item credentials">
                        <label for="" class="layui-form-label col-xs-2">开发者ID(AppId)：</label>
                        @if($index == 0)
                            <div class="layui-form-mid layui-word-aux">{{$content['app_id']??''}}</div>
                            <input type="hidden" value="{{$content['app_id']??''}}" class="layui-input col-xs-9" placeholder="请输入应用AppID" name="apps[app_id][]">
                        @else
                            <div class="layui-input-block">
                                <input type="text" maxlength="32" value="{{$content['app_id']??''}}" class="layui-input col-xs-9" placeholder="请输入应用AppID" name="apps[app_id][]">
                            </div>
                        @endif
                    </div>
                    <div class="layui-form-item credentials" data-id="0">
                        <label for="" class="layui-form-label col-xs-2">开发者密码(AppSecret)：</label>
                        @if($index == 0)
                            <div class="layui-form-mid layui-word-aux">{{$content['app_secret']??''}}</div>
                            <input type="hidden" value="{{$content['app_secret']??''}}" class="layui-input col-xs-9" placeholder="请输入应用AppSecret" name="apps[app_secret][]">
                        @else
                            <div class="layui-input-block">
                                <input type="text" value="{{$content['app_secret']??''}}" class="layui-input col-xs-9" placeholder="请输入应用AppSecret" name="apps[app_secret][]">
                            </div>
                        @endif
                    </div>
                    @php
                        ++$index;
                    @endphp
                @endforeach

                <div class="layui-form-item  layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button type="submit" lay-submit="" class="layui-btn layui-btn-normal">确 认</button>
                            <a class="layui-btn layui-btn-primary" id="layui-form-close"> 返回 </a>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@stop
@section('script')
    <script src="/cwapp/jquery/1.12.3/jquery-1.12.3.js"></script>
    <script src="/cwapp/jqueryform/jquery-form.js"></script>
    <script>
        layui.use(['form','table','element'], function(){
            var form = layui.form
                ,$ = layui.jquery
                ,element = layui.element;
            form.render();
            //关闭页面
            $('#layui-form-close').on('click', function(){
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                parent.layer.close(index); //再执行关闭
            });
            var options = {
                beforeSerialize:function () {
                    $(':submit').attr('disabled', true);
                },
                success: function(data) {
                    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                    if (data.error == undefined) {
                        layer.msg(data.message);
                        setTimeout(function() {
                            if (data.url) {
                                parent.layer.close(index);//关闭弹出的子页面窗口
                                if(index == undefined){
                                    window.location = data.url;
                                    element.render();
                                    return false;
                                }
                                parent.location = data.url;
                                element.render();
                            }
                            else {
                                if (index == 2) {
                                    parent.layer.close(index);
                                    parent.location.reload();
                                } else if (index == 1) {
                                    layer.close(index);
                                    location.reload();
                                }
                            }
                        }, 1000);
                    } else {
                        $(':submit').attr('disabled', false);
                        layer.msg(data.message);
                    }
                }
            };
            $('.base_form_current').ajaxForm(options);
        });
    </script>
@stop