<?php

namespace App\Http\Middleware;

use App;
use Config;
use Closure;
use Session;
use App\Helpers\LanguageHelper;
use Illuminate\Support\Facades\Auth;

class Language
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
        $auth_user = Auth::user();

        if ($auth_user !== null) {
            App::setLocale($auth_user->locale);
        } elseif (Session::has('applocale') and in_array(Session::get('applocale'), Config::get('languages.support'))) {
            App::setLocale(Session::get('applocale'));
        } else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            $agent_locale = LanguageHelper::getAgentLocale();
            App::setLocale($agent_locale);
        }

        return $next($request);
    }
}
