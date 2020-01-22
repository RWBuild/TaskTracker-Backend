<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AdminStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasktracker:admin {--delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to create  the super administrator of the system';

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
        


        //allowed to create a super admin via console
        list($username,$password) = explode(',',env('ADMIN_CREATOR'));
        
        //info for admin to be created
        list($admin_murugo_user_id,$admin_email,$admin_names) = explode(',',env('SUPER_ADMIN_INFO'));

        $admin_creator_user_name = $this->ask('username of admin creator');
        $admin_creator_password = $this->secret('password of admin creator. [this will be invisible while typing]');

        if (!($admin_creator_user_name == $username and $admin_creator_password == $password)) {
            return $this->error('username or password of Admin creator is wrong');
        }



        //check if the murugo_user_id of admin already exist
        $superAdmin = \App\MurugoUser::where('murugo_user_id',$admin_murugo_user_id)->first();

        //if the admin creator want to delete the super admin
        if ($this->option('delete')) {
            if (!$superAdmin) {
               return $this->error('Please create first the admin by typing: php artisan tasktracker:admin');
            }
            $superAdminRole = \App\Role::whereName('superadministrator')->first();
            $user = $superAdmin->user;
            $user->detachRole($superAdminRole->id);
            
            return $this->info("Admin successfully removed");
        }

        if (!$superAdmin) {//if not, create super admin with his murugo info
            $user = \App\User::create([
                'names' => $admin_names,
                'email' => $admin_email
            ]);
            \App\MurugoUser::create([
                'murugo_user_id' => $admin_murugo_user_id,
                'user_id' => $user->id
            ]);
            
            $superAdminRole = \App\Role::whereName('superadministrator')->first();

            if (!$superAdminRole) { //admin role doesn't exist
                return $this->error("superadministrator role doesn't exist.please run : php artisan db:seed --class=LaratrustSeeder to create it");
            }
            $user->attachRole($superAdminRole);

            return $this->info("Admin successfully created");
        }
        //if user admin is already in database but he has not the role of superadministrator
        else if($user=$superAdmin->user) {
            //return $this->info('good');
            if (!$user->hasRole('superadministrator')) {
                $superAdminRole = \App\Role::whereName('superadministrator')->first();
                $user->attachRole($superAdminRole);
                return $this->info("superadministrator role well assigned to ".$user->names);
            }
        }
        
        return $this->error('Admin already exist.type : php artisan asktracker:admin --delete , to delete the admin');


    }
}
