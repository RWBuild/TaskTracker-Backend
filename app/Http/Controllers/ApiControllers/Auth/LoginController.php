<?php

namespace App\Http\Controllers\ApiControllers\Auth;

use App\User;
use App\MurugoUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User as UserResource;

class LoginController extends Controller
{
    public function login (Request $request)
    {
        
        $this->validate($request,[
            'murugo_user_id' => 'required'
        ]);

        $murugo_user = MurugoUser::where('murugo_user_id', $request->murugo_user_id)
                        ->first();
        
       //check if murugo user id exists in db                
       if (!$murugo_user) {
           return response()->json([
               'success' => false,
               'message' => 'User not allowed'
           ]);
       }
       
       $user = $murugo_user->user;
       //check if the murugo user is connected to timeTracker user
       if (!$user) {
           
           $this->validate($request,[
               'names' => 'required',
               'email' => 'required|email|unique:users',
               'avatar' => 'required'
           ]);
           $user = User::create([
                'names' => $request->names,
                'email' => $request->email,
                'avatar' => $request->avatar
           ]);

           $murugo_user->user_id = $user->id;
           $murugo_user->save();

       }

       $generated_token = $user->createToken('authToken');

       return response()->json([
           'success' => true,
           'message' => 'Successfully identified',
           'user' => new UserResource($user),
           'access_token' => $generated_token->accessToken,
           'token_expires_at' => $generated_token->token->expires_at
       ]);
       
    }


    public function logout() 
    {
        user()->AauthAcessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'User successfully loged out'
        ]);
    }

    public function auth_user() 
    {
        return new UserResource(user());
    }
}
