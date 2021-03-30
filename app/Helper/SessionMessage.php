<?php

namespace App\Helper;

use Illuminate\Support\Facades\Session;

class SessionMessage {

    public static function infoMessage($message)
    {
        Session::flash('alert-class', 'alert-info');
        Session::flash('message', $message);
    }

    public static function warningMessage($message)
    {
        Session::flash('alert-class', 'alert-warning');
        Session::flash('message', $message);
    }

    public static function errorMessage($message)
    {
        Session::flash('alert-class', 'alert-danger');
        Session::flash('message', $message);
    }
}