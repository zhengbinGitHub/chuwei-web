<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOauthClientAppidColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if(Schema::hasTable('oauth_clients')) {
            if (!Schema::hasColumn('oauth_clients', 'appid')) {
                Schema::table('oauth_clients', function (Blueprint $table) {
                    $table->string('appid')->after('user_id')->nullable()->comment('应用APPID');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
