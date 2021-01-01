<?php

namespace App\Http\Controllers;

use App\Importer\UserImporter;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function importUsers($spaceId)
    {
        $project = Auth::user()->projects()->findOrFail($spaceId);

        $userImporter = new UserImporter();
        $userImporter->importSpaceUsers($project);

        return redirect()->route('projects.show', $project);
    }
}
