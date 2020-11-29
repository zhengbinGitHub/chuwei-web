@extends('cwapp::layouts.main')
@section('content')
    <div class="layui-form">
        <div class="layui-card-header">应用信息</div>
        <div class="layui-card-body">
            <form class="layui-form base_form_current" action="{{url('apple/store')}}" method="post">
                @csrf
                <input type="hidden" name="tenant_id" value="{{$merchant_id}}">
                <input type="hidden" name="id" value="{{$info->id??0}}">
                @foreach(collect(config('cwapp.clients'))->sortByDesc('sort')->all() as $key=>$item)
                    @php
                        $content = $contents[$item['alias']]??[];
                    @endphp
                <fieldset class="layui-elem-field layui-field-title" @if($key != 0)style="margin-top: 50px; @endif">
                    <legend>{{$item['name']}}</legend>
                </fieldset>
                <input type="hidden" value="{{$item['alias']}}" name=apps[platform][]">
                <div class="layui-form-item credentials">
                    <label for="" class="layui-form-label col-xs-2">开发者ID(AppId)：</label>
                        @if($key == 0)
                            <div class="layui-form-mid layui-word-aux">{{$content['app_id']??''}}</div>
                            <input type="hidden" value="{{$content['app_id']??''}}" class="layui-input col-xs-9" placeholder="请输入应用AppID" lay-verify="required" name="apps[app_id][]">
                        @else
                        <div class="layui-input-block">
                            <input type="text" value="{{$content['app_id']??''}}" class="layui-input col-xs-9" placeholder="请输入应用AppID" lay-verify="required" name="apps[app_id][]">
                        </div>
                        @endif
                </div>
                <div class="layui-form-item credentials" data-id="0">
                    <label for="" class="layui-form-label col-xs-2">开发者密码(AppSecret)：</label>

                        @if($key == 0)
                            <div class="layui-form-mid layui-word-aux">{{$content['app_secret']??''}}</div>
                            <input type="hidden" value="{{$content['app_secret']??''}}" class="layui-input col-xs-9" placeholder="请输入应用AppSecret" lay-verify="required" name="apps[app_secret][]">
                        @else
                        <div class="layui-input-block">
                            <input type="text" value="{{$content['app_secret']??''}}" class="layui-input col-xs-9" placeholder="请输入应用AppSecret" lay-verify="required" name="apps[app_secret][]">
                        </div>
                        @endif
                </div>
                @endforeach

                <div class="layui-form-item  layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button type="submit" lay-submit="" class="layui-btn layui-btn-normal">确 认</button>
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
            $(document).on('click','.J-voucher-add',function(){
                let th = $(this);
                let length = $('.credentials').length;
                if(length >= 5){
                    return layer.msg('最多添加5个关联应用')
                }
                let arrId = $('.credentials').map((index,item) =>{
                    return $(item).attr('data-id');
                })
                let maxId = Math.max(...arrId);
                arrId.length ? $(".G-voucher-main").append(addVoucher(maxId + 1)):$(".G-voucher-main").append(addVoucher(0))
            });
            function addVoucher(id){
                let str = `<div class="layui-form-item credentials" data-id=${id}>
                    <label for="" class="layui-form-label"></label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input col-xs-2" placeholder="请输入应用AppID" lay-verify="required" name="apps[${id}][app_id]">
                        <input type="text" class="layui-input col-xs-2"  placeholder="请输入应用AppSecret" style="margin-left:10px;" name="apps[${id}][app_secret]">
                        <input type="text" class="layui-input col-xs-2"  placeholder="请输入应用平台标示" style="margin-left:10px;" name="apps[${id}][platform]">
                        <a class="layui-btn C-marginLeft-10 J-voucher-delete"> 删 除</a>
                    </div>
                </div>`
                return str;
            }
            $(document).on('click','.J-voucher-delete',function(){
                let th = $(this);
                let length = $(".credentials").length;
                if(length > 1) {
                    th.parents('.credentials').remove();
                } else {
                    layer.msg('最后一个无法删除');
                }
                return false;
            });
            var options = {
                beforeSerialize: editFun,
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
                                }else
                                    parent.location = data.url;
                                element.render();
                            }
                            else
                            if(index == 2){
                                parent.layer.close(index);
                                parent.location.reload();
                            }else if(index == 1){
                                layer.close(index);
                                location.reload();
                            }
                        }, 1000);
                    } else {
                        $(':submit').attr('disabled', false);
                        layer.msg(data.message);
                    }
                }
            };
            function editFun(){
                let checkVal = $("input[name='pz']:checked").val();
                let length = $(".credentials").length;
                let str = `
                    <input type="text" hidden name="apps[0][app_id]" value="">
                    <input type="text" hidden name="apps[0][app_secret]" value="">
                    <input type="text" hidden name="apps[0][platform]" value="">
                    `
                if(!length && checkVal == 0){
                    $(".G-voucher-empty").append(str)
                }
                $(':submit').attr('disabled', true);
            }
            $('.base_form_current').ajaxForm(options);
        });
    </script>
    @stop