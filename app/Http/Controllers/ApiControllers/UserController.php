<?php

namespace App\Http\Controllers\ApiControllers;

use App\Role;
use App\User;
use App\MurugoUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\User  as UserResource;

class UserController extends Controller
{
    // displaying a list of all users
    public function index()
    {
        $users = User::all();
        return response([
            'success' => true,
            'users' => new UserCollection($users)
        ],200);
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }

    //displaying a single user
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response([
                'success' => true,
                'message' => 'user not found'
            ],404);          
        }

    
        return response([
            'success' => true,
            'users' => new UserResource($user)
        ],200);
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }

    public function create_super_admin(Request $request)
    {
        $this->validate($request,[
            'murugo_user_id' => 'required|string',
            'names' => 'required|string',
            'email' => 'required|email'
        ]);

        $murugo_user = MurugoUser::where('murugo_user_id',$request->murugo_user_id)->first();
        if(!$murugo_user)
        {
            $user = User::create([
                'names' => $request->names,
                'email' => $request->email
            ]);

            $murugo_user = MurugoUser::create([
                'murugo_user_id' => $request->murugo_user_id,
                'user_id' => $user->id,
            ]);

            $user->attachRole($request->role_id);

            return response()->json([
                'success' => false,
                'message' => 'user ' .$user->names. ' created and he is a super administrator now' 
            ]);
        }

        if($murugo_user->user_id == null)
        {
            $user = User::create([
                'names' => $request->names,
                'email' => $request->email
            ]);
            $murugo_user->user_id = $user->id;
            $murugo_user->update();
            $user->attachRole($request->role_id);

            return response()->json([
                'success' => true,
                'message' => 'user ' .$user->names. ' created and he is a super administrator now' 
            ]);
        }

        else if($murugo_user->user_id != null)
        {
            $role = Role::find($request->role_id);
             if($role->name != 'superadministrator')
             {
                $murugo_user = MurugoUser::where('murugo_user_id',$request->murugo_user_id)->first();
                $user = User::find($murugo_user->user_id);
                if($user->hasRole('superadministrator'))
                {
                    return response()->json([
                        'success' => true,
                        'message' => 'user is already a super administrator' 
                    ]); 
                }
                $user->attachRole(1);
                return response()->json([
                    'success' => true,
                    'message' => 'role of super administrator assigned to '.$user->names. ' successfully' 
                ]);
             }
             return response()->json([
                'success' => false,
                'message' => 'user is already a super administrator' 
            ]);
        }
        
        
    }
}
