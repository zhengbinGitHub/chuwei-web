@extends('cwapp::layouts.main')
@section('content')
    <div class="layui-form">
        <div class="layui-card-header">应用信息</div>
        <div class="layui-card-body">
            <form class="layui-form base_form_current" action="{{url('apple/store')}}" method="post">
                @csrf
                <div class="layui-form-item">
                    <div class="layui-form-item G-voucher-main-hidden">
                        <label for="" class="layui-form-label">添加AppID</label>
                        <div class="layui-input-block">
                            <div class="layui-upload" style="display: flex;align-items: flex-end;">
                                <button type="button" class="layui-btn J-voucher-add" id="" lay-data="">
                                    添加AppID
                                </button>
                                <label for="">最多添加5个关联应用</label>
                            </div>
                        </div>
                    </div>
                    <!--凭证循环模块-->
                    <div class="G-voucher-main">
                        @forelse($lists as $key=>$item)
                            <div class="layui-form-item credentials" data-id="{{$key}}">
                                <label for="" class="layui-form-label"></label>
                                <div class="layui-input-block">
                                    <input type="text" value="{{$item->app_id}}" class="layui-input col-xs-3" placeholder="请输入应用AppID" lay-verify="required" name="apps[{{$key}}][app_id]">
                                    <input type="text" value="{{$item->app_secret}}" class="layui-input col-xs-3" placeholder="请输入应用AppSecret" style="margin-left:10px;" name="apps[{{$key}}][app_secret]">
                                    <input type="text" value="{{$item->platform}}" class="layui-input col-xs-1" placeholder="请输入应用平台标示" style="margin-left:10px;" name="apps[{{$key}}][platform]">
                                    <a class="layui-btn C-marginLeft-10 J-voucher-delete"> 删 除</a>
                                </div>
                            </div>
                        @empty
                            <div class="layui-form-item credentials" data-id="0">
                                <label for="" class="layui-form-label"></label>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input col-xs-3" placeholder="请输入应用AppID" lay-verify="required" name="apps[0][app_id]">
                                    <input type="text" class="layui-input col-xs-3" placeholder="请输入应用AppSecret" style="margin-left:10px;" name="apps[0][app_secret]">
                                    <input type="text" class="layui-input col-xs-1" placeholder="请输入应用平台标示" style="margin-left:10px;" name="apps[0][platform]">
                                    <a class="layui-btn C-marginLeft-10 J-voucher-delete"> 删 除</a>
                                </div>
                            </div>
                        @endforelse

                    </div>
                    <div class="G-voucher-empty" hidden></div>
                </div>
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