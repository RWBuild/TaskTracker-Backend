<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Resources\AuthClient as AuthClientResource;

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
        $this->validate($request,[
            'auth_client_id'=>'integer|required',
            'auth_client_secret'=>'string|required',
        ]);
        $id = AuthClient::where('id',$request->auth_client_id);
        if(!$id)
        {
            return response()->json([
                'success' => false,
                'message' => 'the auth client id does not exist',
            ]);
        }
        $secret = new AuthClientResource($id);
        $client_secret = $secret->secret;
        if($client_secret != $request->auth_client_secret)
        {
            return response()->json([
                'success' => false,
                'message' => 'the auth client secret does not match',
            ]);
        }
        return $next($request);
    }
}
