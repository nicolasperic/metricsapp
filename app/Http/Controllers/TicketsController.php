<?php

namespace App\Http\Controllers;

use App\Importer\TicketImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    public function importTickets($sprintId)
    {
        $sprint = Auth::user()->sprints()->findOrFail($sprintId);

        $ticketImporter = new TicketImporter();
        $ticketImporter->importMilestoneTickets($sprint);

        return redirect()->route('sprints.show', $sprint);
    }
}
