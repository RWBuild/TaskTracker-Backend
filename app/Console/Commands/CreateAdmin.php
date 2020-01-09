<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is going to create an admin user in database with his murugo_user_id';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $username = $this->ask('Enter your user name');
        $pass = $this->secret("Enter Your password");

        if ($username == "promesse" and $pass== 1234) {
            $this->info("successfully logged in");
        }
        else{
            $this->error("ypu are not allowed");
        }

    }
}
