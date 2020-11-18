# cw100_partner
通过appid打通商户
＃ 应用开发使用说明 
＃ 项目下载及配置 
《1.》composer install chuwei/cwapp

《2.》php artisan vendor:publish --provider="CwApp\ApplePackageServiceProvider" --tag=public

《3.》config/app.php providers添加 \CwApp\ApplePackageServiceProvider::class

《4.》php artisan migrate

《5.》后台菜单配置 应用配置 开启本项目应用：url('apple/show') 添加多个应用：url('apple/create')

《6.》请求应用token {domain}/apple/auth 返回｛'code':1, 'data':{'token':'', 'expires_in': '有效时间'}｝；数据请求验证添加中间件: cwapp-api:auth