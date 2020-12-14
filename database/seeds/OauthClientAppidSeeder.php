<?php

use Illuminate\Database\Seeder;

class OauthClientAppidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $lists = \Illuminate\Support\Facades\DB::table('oauth_clients')->get();
        foreach ($lists as $item){
            echo $item->id.'-'.$item->user_id . PHP_EOL;
            if(!$item->appid){
                \Illuminate\Support\Facades\DB::table('oauth_clients')->where('id', $item->id)
                    ->update(['appid' => $item->secret .'-'. $item->user_id]);
            }
        }
    }
}
