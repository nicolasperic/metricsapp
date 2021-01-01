<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ForceAssemblaKeys
{
    public function handle($request, $next)
    {
        if(Auth::user()->assembla_key == null || Auth::user()->assembla_secret == null) {
            Session::flash('message', 'Please enter your Assembla key and secret to begin using the app : )');
            return redirect()->route('settings.index');
        }
        return $next($request);
    }
}