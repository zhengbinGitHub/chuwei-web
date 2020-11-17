<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>商家管理平台</title>
    <meta name="csrf_token" content="{{csrf_token()}}"/>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link rel="stylesheet" href="https://at.alicdn.com/t/font_1382198_ti4d3ypz0y.css">
    <!-- Styles -->
    <link rel="stylesheet" href="/cwapp/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/cwapp/css/admin.css" media="all">
    <link rel="stylesheet" href="/cwapp/css/color.css" media="all">
    <link rel="stylesheet" href="/cwapp/css/form.css" media="all">
    <link rel="stylesheet" href="/cwapp/css/flex.css" media="all">
    <link rel="stylesheet" href="/cwapp/css/admin_color.css" media="all">
    @yield('style')

</head>
<body layadmin-themealias="default">
<div class="layui-layout layui-layout-admin" id="LAY_app" >
    <div class="layui-fluid">
        <div class="layui-card">
            @yield('content')
        </div>
    </div>
    <script src="/cwapp/layui/layui.js"></script>
    @yield('script')
</div>
</body>
</html>
