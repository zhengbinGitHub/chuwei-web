<?php
namespace CwApp;
use CwApp\Middleware\CwAppApiMiddleware;
use CwApp\Middleware\CwAppAuthMiddleware;
use Illuminate\Support\ServiceProvider;

/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 17:34
 */

class ApplePackageServiceProvider extends ServiceProvider
{
    /**
     * 注册信息
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register()
    {
        $this->app->make('CwApp\Controllers\AppleController');
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/cwapp.php', 'cwapp');
        }
    }

    /**
     * 覆盖发布文件 php artisan vendor:publish --force
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cwapp');

        // 把静态资源发布到laravel public/cwapp 目录下
        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . 'public' => public_path('cwapp'),
        ], 'public');

        //数据库表
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'cwapp-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/cwapp'),
            ], 'cwapp-views');
        }


        $this->addMiddlewareAlias('cwapp.auth', CwAppAuthMiddleware::class);
        $this->addMiddlewareAlias('cwapp-api.auth', CwAppApiMiddleware::class);

        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }

    # 添加中间件的别名方法
    protected function addMiddlewareAlias($name, $class)
    {
        $router = $this->app['router'];

        if (method_exists($router, 'aliasMiddleware')) {
            return $router->aliasMiddleware($name, $class);
        }

        return $router->middleware($name, $class);
    }

    /**
     * Register Passport's migration files.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        return $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}