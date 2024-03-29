<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $murugo_user_list = explode(',',env('MURUGO_USER_SAMPLE'));       
        
        foreach ($murugo_user_list as $murugo_user_id) {

           $check_exist = App\MurugoUser::where('murugo_user_id',$murugo_user_id)->first();
           
           if (! $check_exist) {
            
              $murugoUser = App\MurugoUser::create([
                'murugo_user_id' => $murugo_user_id
              ]);
           }
           
        }
        
    }

}