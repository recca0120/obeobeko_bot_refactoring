<?php

namespace App\Http\Middleware;

use Closure;
use App\OauthClient;

class APIKeySecretMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $api_key = $request->api_key;
        $api_secret = $request->api_secret;

        $oauth_client = OauthClient::find($api_key);

        if ($oauth_client === null) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('error.error'),
            ]);
        }

        if ($oauth_client->secret != $api_secret) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('error.error'),
            ]);
        }

        return $next($request);
    }
}
