<?php

namespace App\Http\Controllers\ApiControllers;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    //assigning a role to a specific user
    public function attachRole(Request $request)
    {
        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);
      
        if ($user->hasRole($role->name)) {
            return response([
                'success' => false,
                'message' => "Role has already been assigned to {$user->names}"
            ],409);
        }

        $user->attachRole($role);
        return response([
            'success' => true,
            'message' => "Role {$role->name} well assigned to user {$user->names} "
        ],200);

    }

    //unassigning a role to a specific user
    public function detachRole(Request $request)
    {
        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);

        if (!$user->hasRole($role->name)) {
            return response([
                'success' => false,
                'message' => "The user {$user->names} doesn't have the role ".$role->name
            ],404);
        }

        $user->detachRole($role);
        return response([
            'success' => true,
            'message' => "Role {$role->name} well detached to user {$user->names} "
        ],200);

    }
}
