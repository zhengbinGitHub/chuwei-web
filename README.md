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