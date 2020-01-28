<?php

namespace App\Http\Middleware;

use Closure;
use App\Role;

class SuperAdminLimit
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
            'role_id' => 'required'
        ]);
        $role = Role::find($request->role_id);
        if(!$role)
        {
            return response([
                'success' => false,
                'message' => 'role does not exist'
            ]);
        }
        if($role->users->count() >= 2)
        {
            return response([
                'success' => false,
                'super admins' => "we can't have more than two super administrators"
            ]);
        }

        return $next($request);
        
    }
}
