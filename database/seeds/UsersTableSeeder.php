<?php

use Illuminate\Database\Seeder;
<<<<<<< HEAD
=======
use Faker\Generator as Faker;
>>>>>>> e717a5fa07b57a213127f1fa759a2ab764b28886
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
<<<<<<< HEAD

        foreach ($murugo_user_list as $murugo_user_id) {

=======
        

        foreach ($murugo_user_list as $murugo_user_id) {
           $faker = new Faker();
>>>>>>> e717a5fa07b57a213127f1fa759a2ab764b28886
           $murugoUser = App\MurugoUser::create([
            'murugo_user_id' => $murugo_user_id
           ]);

           $user = App\User::create([
<<<<<<< HEAD
            'names' => Str::random(10),
            'email' => Str::random(10).'@gmail.com',
=======
            'names' => $faker->firstName.' '.$faker->flastName,
            'email' => $faker->unique()->safeEmail,
>>>>>>> e717a5fa07b57a213127f1fa759a2ab764b28886
            'avatar' => Str::random(10).'png'
           
           ]);

           $murugoUser->user_id = $user->id;
           $murugoUser->save();
        }

        
    }
}
