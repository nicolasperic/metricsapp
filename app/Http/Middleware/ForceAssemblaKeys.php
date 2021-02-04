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
            SessionMessage::infoMessage('Please enter your Assembla key and secret to begin using the app : )');
            return redirect()->route('settings.index');
        }

        /*if (count($user->projects) == 0 && !(Session::has('message') && strpos(Session::get('message'),'Projects sync') !== false)) {
            $importUrl = '<a href="'.route('projects.sync').'">import</a>';
            SessionMessage::infoMessage('To get started '.$importUrl.' your Assembla spaces');
        }*/

        return $next($request);
    }
}