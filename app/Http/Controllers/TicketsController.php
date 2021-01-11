<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\TicketImporter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketsController extends Controller
{
    public function importTickets($sprintId)
    {
        $sprint = Auth::user()->sprints()->findOrFail($sprintId);

        try {
            $ticketImporter = new TicketImporter(Auth::user());
            $ticketImporter->importMilestoneTickets($sprint);
            SessionMessage::infoMessage('Tickets were correctly imported');
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }


        return redirect()->route('sprints.show', $sprint);
    }
}
