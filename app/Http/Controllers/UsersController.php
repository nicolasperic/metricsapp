<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\UserImporter;
use App\Jobs\SyncSpaceUsers;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class UsersController extends Controller
{
    public function syncUsers($spaceId)
    {
        $project = Auth::user()->projects()->findOrFail($spaceId);

        try {
            SyncSpaceUsers::dispatch(Auth::user(), $project);
            SessionMessage::infoMessage('Users sync job was added to the queue');
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('projects.show', $project);
    }

    public function notifications()
    {
        return Auth::user()->unreadNotifications()->limit(5)->get()->toArray();
    }
}
