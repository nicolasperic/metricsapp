<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\TicketImporter;
use App\Jobs\SyncMilestone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketsController extends Controller
{
    public function syncTickets($sprintId)
    {
        $sprint = Auth::user()->sprints()->findOrFail($sprintId);
        try {
            SyncMilestone::dispatch(Auth::user(), $sprint);
            SessionMessage::infoMessage("Tickets sync job was added to the queue");
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('sprints.show', $sprint);
    }
}
