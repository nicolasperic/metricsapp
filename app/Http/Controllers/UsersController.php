<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\UserImporter;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class UsersController extends Controller
{
    public function importUsers($spaceId)
    {
        $project = Auth::user()->projects()->findOrFail($spaceId);

        try {
            $userImporter = new UserImporter();
            $userImporter->importSpaceUsers($project);
            SessionMessage::infoMessage('Users were correctly imported');
        } catch (Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('projects.show', $project);
    }
}
