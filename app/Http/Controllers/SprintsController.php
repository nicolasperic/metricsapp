<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\SprintImporter;
use App\Jobs\SyncSpaceMilestones;
use App\Jobs\SyncUserSyncableSpaces;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class SprintsController extends Controller
{
    public function index()
    {
        return view('sprints.index', [
            'openSprints' => Auth::user()->getOpenSprints,
            'closedSprints' => Auth::user()->getClosedSprints,
        ]);
    }

    public function current()
    {
        return view('sprints.current', [
            'currentSprints' => Auth::user()->starredProjectsCurrentSprints(),
        ]);
    }

    public function show($id)
    {
        $sprint = Auth::user()->sprints()->with(['tickets.ticketTimes', 'tickets.subtasks', 'projects.assemblaUsers'])->findOrFail($id);

        return view('sprints.show', [
            'sprint' => $sprint,
        ]);
    }

    /**
     * This function is used to dispatch a Milestone Sync for the received project
     * and then a current mylestone ticket sync
     *
     * @param $projectId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncSprints($projectId)
    {
        $project = Auth::user()->projects()->findOrFail($projectId);
        try {
            SyncSpaceMilestones::dispatch(Auth::user(), $project);
            SessionMessage::infoMessage("Milestones sync job was added to the queue");
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('projects.show', $project);

    }

    /**
     * This function will trigger a job that will update all syncable spaces
     * current milestones : )
     */
    public function syncAllCurrentSprints()
    {
        SyncUserSyncableSpaces::dispatch(Auth::user());
        SessionMessage::infoMessage("Current projects sync job was added to the queue");
        return redirect()->route('sprints.current');
    }
}
