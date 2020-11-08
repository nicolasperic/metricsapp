<?php

namespace App\Http\Controllers;

use App\Integration\AssemblaGateway;
use App\Project;

use App\Reports\HoursByUSReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{

    public function index()
    {
        return view('settings.index');
    }

    /**
     * Generates report
     */
    public function store()
    {
        $this->validateRequest();

        $user = Auth::user();
        $user->assembla_key = request('assembla_key');
        $user->assembla_secret = request('assembla_secret');
        $user->save();

        return redirect(route('settings.index'));

    }

    /**
     * @return array
     */
    protected function validateRequest()
    {
        return request()->validate([
            'email'   => 'required',
            'assembla_secret' => 'required',
            'assembla_key' => 'required',
        ]);
    }
}
