<?php

namespace App\Http\Middleware;

use App\Helper\SessionMessage;
use Illuminate\Support\Facades\Auth;

class ForceAssemblaKeys
{
    public function handle($request, $next)
    {
        if(Auth::user()->assembla_key == null || Auth::user()->assembla_secret == null) {
            SessionMessage::infoMessage('Please enter your Assembla key and secret to begin using the app : )');
            return redirect()->route('settings.index');
        }

        if (count(Auth::user()->projects) == 0) {
            $importUrl = '<a href="'.url('/projects/importProjects').'">import</a>';
            SessionMessage::infoMessage('To get started '.$importUrl.' your Assembla spaces');
        }
        return $next($request);
    }
}