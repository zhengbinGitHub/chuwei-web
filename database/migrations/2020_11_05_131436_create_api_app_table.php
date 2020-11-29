<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('api_apps')) {
            Schema::create('api_apps', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('tenant_id')->index()->comment('商户ID');
                $table->text('content')->nullable(false)->comment('应用json串');
                $table->tinyInteger('status')->default(1)->comment('状态 1开启 0关闭');
                $table->timestamps();
            });
            DB::statement('ALTER TABLE `api_apps` ENGINE=INNODB COMMENT = "开放配置信息"');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_apps');
    }
}
