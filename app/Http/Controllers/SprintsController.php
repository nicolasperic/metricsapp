<?php

namespace App\Http\Controllers;

use App\Sprint;
use Illuminate\Support\Facades\Auth;


class SprintsController extends Controller
{
    public function index()
    {
        return view('sprints.index', [
            'sprints' => Auth::user()->sprints,
        ]);
    }
    public function show($id)
    {
        $sprint = Auth::user()->sprints()->findOrFail($id);

        return view('sprints.show', [
            'sprint' => $sprint,
        ]);
    }
}
