<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2020-11-04
 * Time: 18:03
 */
return [
    'app_token_url' => env('APP_TOKEN_URL', 'http://localhost'),
    'app_test_platform' => env('APP_TEST_PLATFORM', 'fuwu'),
    'app_prefix' => env('APP_PREFIX', ''),
    'app_guard' => env('APP_GUARD', ''),
    'app_default_platform' => env('APP_DEFAULT_PLATFORM', ''),
    'app_check_urls' => [
        'mall' => ['url' => env('MALL_CHECK_URL',''), 'name' => '智慧门店系统'],
        'fuwu' => ['url' => env('FUWU_CHECK_URL',''), 'name' => '服务系统'],
        'erp' => ['url' => env('ERP_CHECK_URL',''), 'name' => '进销存系统']
    ],
    'clients' => [
        ['name' => '智慧门店开发信息', 'alias' => 'mall', 'sort' => env('APP_DEFAULT_PLATFORM')=='mall'?1:0],
        ['name' => '舒适到家开发信息', 'alias' => 'fuwu', 'sort' => env('APP_DEFAULT_PLATFORM')=='fuwu'?1:0],
        ['name' => '店酷云进销存开发信息', 'alias' => 'erp', 'sort' => env('APP_DEFAULT_PLATFORM')=='erp'?1:0],
    ],
];