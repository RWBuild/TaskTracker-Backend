<?php

namespace App\Http\Middleware;

use Closure;
use App\OauthClient;
use App\Http\Resources\AuthClient as AuthClientResource;
use Illuminate\Support\Facades\Validator;

class VerifyAppKey
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
            'client_id'=>'integer|required',
            'client_secret'=>'string|required',
        ]);
        $client = OauthClient::where('id',$request->client_id)
               ->where('secret',$request->client_secret)->first();
        if(!$client)
        {
            return response()->json([
                'success' => false,
                'message' => 'Client request not identified',
            ],404);
        }
        
        return $next($request);
    }
}
