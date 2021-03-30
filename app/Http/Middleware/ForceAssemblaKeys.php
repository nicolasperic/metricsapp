<?php

namespace App\Http\Middleware;

use App\Helper\SessionMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class ForceAssemblaKeys
{
    public function handle($request, $next)
    {
        $user = Auth::user();
        if($user->assembla_key == null || $user->assembla_secret == null) {
            $assemblaCredentialsURL = '<a href="https://app.assembla.com/user/edit/manage_clients" target="_blank">here</a>';
            SessionMessage::infoMessage('Please enter your Assembla key and secret to begin using the app. Find your key '.$assemblaCredentialsURL);
            return redirect()->route('settings.index');
        }

        return $next($request);
    }
}