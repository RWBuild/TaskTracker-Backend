<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
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
           $faker = new Faker();
           $murugoUser = App\MurugoUser::create([
            'murugo_user_id' => $murugo_user_id
           ]);

           $user = App\User::create([
            'names' => $faker->firstName.' '.$faker->flastName,
            'email' => $faker->unique()->safeEmail,
            'avatar' => Str::random(10).'png'
           
           ]);

           $murugoUser->user_id = $user->id;
           $murugoUser->save();
        }

        
    }
}
