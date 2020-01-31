<?php
namespace App\Classes;

use App\Role;
use App\User;
use App\MurugoUser;

class SuperAdminHelper
{
    public $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function get_murugo_user()
    {
       return MurugoUser::where('murugo_user_id',$this->request->murugo_user_id)->first(); 
    }

    public function create_user()
    {
        return User::create([
            'names' => $this->request->names,
            'email' => $this->request->email
        ]);
    }

    public function create_murugo_user()
    {
        return MurugoUser::create([
            'murugo_user_id' => $this->request->murugo_user_id,
            'user_id' => $this->create_user()->id,
        ]);
    }

    public function attach_role()
    {
        return attachRole($this->request->role_id);
    }

    public function attach_role_Super_Administartor()
    {
        return attachRole(1);
    }

    public function not_murugo_user()
    {
        $this->create_user();
        $this->create_murugo_user();
        $this->create_user()->attach_role();
        return response()->json([
            'success' => false,
            'message' => 'user ' .$this->create_user()->names. ' created and he is a super administrator now' 
        ]);
    }

    public function not_user()
    {
        $this->create_user();
        $this->get_murugo_user()->user_id = $this->create_user()->id;
        $this->get_murugo_user()->update();
        $this->create_user()->attach_role();
    }

    public function get_role()
    {
        return Role::find($this->request->role_id);
    }

    public function find_user()
    {
        return User::find($this->get_murugo_user()->user_id);
    }

    public function is_user()
    {
        $this->find_user()->attachRole(1);
    }

    public function super_admin()
    {
       if($this->find_user()->hasRole('superadministrator'))
       {
            return response()->json([
                'success' => true,
                'message' => 'user is already a super administrator' 
            ]);
       } 
    }

    public function response()
    {
       if(!$this->get_murugo_user())
       {
           return $this->not_murugo_user();
       }
       
       if($this->get_murugo_user()->user_id == null)
       {
           $this->not_user();
           return response()->json([
                'success' => true,
                'message' => 'user ' .$this->create_user()->names. ' created and he is a super administrator now' 
            ]);
       }

       if($this->get_role()->name != 'superadministrator')
       {
           $this->super_admin();
           $this->is_user();
           return response()->json([
            'success' => true,
            'message' => 'role of super administrator assigned to '.$this->find_user()->names. ' successfully' 
        ]);
       }
        return response()->json([
            'success' => false,
            'message' => 'user is already a super administrator' 
        ]);
    }
}