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
        $validator = Validator::make($request->all(),[
            'client_id'=>'required|numeric',
            'client_secret'=>'string|required',
        ]);
        $id = OauthClient::where('id',$request->client_id)->first();
        return response([
            "ok" => $request->client_id
        ]);
        if(!$id)
        {
            return response()->json([
                'success' => false,
                'message' => 'Client request not identified',
            ]);
        }
        $secret = $id->secret;
        if($secret != $request->client_secret)
        {
            return response()->json([
                'success' => false,
                'message' => 'Client request not identified',
            ]);
        }
        return $next($request);
    }
}
