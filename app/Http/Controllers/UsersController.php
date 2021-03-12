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
     //TODO move this under Controllers/Assembla ProjectsController syncUsers
    public function syncUsers($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->firstorFail();

        try {
            SyncSpaceUsers::dispatch(Auth::user(), $project);
            SessionMessage::infoMessage('Users sync job was added to the queue');
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('projects.show', $project->wikiname);
    }

    public function notifications()
    {
        return Auth::user()->unreadNotifications()->limit(5)->get()->toArray();
    }

    public function markNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->map(function($notification) {
            $notification->markAsRead();
        });

        return response()->json(['result' => 'success']);
    }
}
