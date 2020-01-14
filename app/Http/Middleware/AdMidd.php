<?php

namespace App\Http\Middleware;

use Closure;

class AdMidd
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
        return response(['nsme' => 'yes']);
        return $next($request);
    }
}
