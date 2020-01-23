<?php

use Illuminate\Database\Seeder;

class RecordTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sampleTask = [
            'working on authentication',
            'working on routes protection',
            'working on the location tracking'
        ];
        App\Record::truncate();

        for ($i=0; $i < 3; $i++) { 
           
            $record = App\Record::create([
                'name' => $sampleTask[$i],
                'description' => 'my description '.($i + 1),
                'user_id' => $i + 1,
                'project_id' => $i + 1
            ]);
        }


    }

    
}
