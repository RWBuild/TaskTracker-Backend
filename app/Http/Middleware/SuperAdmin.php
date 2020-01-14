<?php

namespace App\Http\Middleware;

use Closure;

class SuperAdmin
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

        if (!user()->hasRole('superadministrator')) {
            return response([
                'success' => false,
                'message' => 'Permission denied'
            ],403);
        }
        return $next($request);
    }
}
