<?php

namespace App\Http\Middleware;

use Closure;
use App\Role;
use App\User;

class UserAndRoleChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->validate([
            'role_id' => 'required|numeric',
            'user_id' => 'required|numeric',
        ]);

        $role = Role::find($request->role_id);
        $user = User::find($request->user_id);

        if ($role->name == 'superadministrator') {
            return response([
                'success' => false,
                'message' => "You can't assign the {$role->name} to another user."
            ]);            
        }

        if (!$role) {
           return response([
               'success' => false,
               'message' => 'Role not found'
           ],404); 
        }

        if (!$user) {
            return response([
                'success' => false,
                'message' => 'User not found'
            ],404); 
        }

        return $next($request);
    }
}
