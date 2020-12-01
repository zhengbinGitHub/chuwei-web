# cw100_partner
通过appid打通商户
＃ 应用开发使用说明 
＃ 项目下载及配置 
《1.》composer require chuwei/cwapp
《2.》php artisan vendor:publish --provider="CwApp\ApplePackageServiceProvider" --tag=public
《3.》config/app.php providers添加 \CwApp\ApplePackageServiceProvider::class
《4.》php artisan migrate
《5.》后台菜单配置 应用配置 添加多个应用：url('apple/client', ['merchant_id' => 商户ID])
《6.》环境变量配置 env('APP_GUARD')授权设备 env('APP_DEFAULT_CLIENT') 默认应用服务 env('APP_PLATFORM')默认平台名称
《7.》获取token验证认证指令牌get获取url/api/client/token?appid=appid&platform=platform
《8.》测试地址url/api/test 头部添加参数X-Auth-Token、X-Auth-Appid、X-Auth-Platform；获取数据 {"code":0,"message":"ok","data":{"app_id":"126jsuyABV4595fc5a888e2c52","app_secret":"126bQaX1MfegxDK4FGHgD6ACYhbsD9mctXePTTh3Jzs5fc5a888e2c6d"}}
