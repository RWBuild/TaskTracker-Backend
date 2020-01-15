<?php

namespace App\Http\Controllers\ApiControllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\User  as UserResource;

class UserController extends Controller
{

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
}
