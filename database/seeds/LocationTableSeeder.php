<?php

use Illuminate\Database\Seeder;

class LocationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Location::truncate();
        $location = App\Location::create([
            'longitude' => 1234456,
            'latitude' => 3445567,
            'radius' => 200
        ]);
    }
}
