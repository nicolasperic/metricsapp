<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\TicketImporter;
use App\Jobs\SyncMilestone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketsController extends Controller
{
    /**
     * @param $sprintAssemblaId
     *
     * *TODO move this under Controllers/Assembla/
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncTickets($sprintAssemblaId)
    {
        $sprint = Auth::user()->sprints()->where('sprint_assembla_id', $sprintAssemblaId)->firstOrFail();
        try {
            SyncMilestone::dispatch(Auth::user(), $sprint);
            SessionMessage::infoMessage("Tickets sync job was added to the queue");
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        $project = $sprint->getProject();
        return redirect()->route('sprints.show', [$project->wikiname, $sprint->sprint_assembla_id]);
    }
}
