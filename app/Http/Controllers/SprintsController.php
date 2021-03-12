<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Jobs\SprintIteration;
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

    public function show($wikiname, $id)
    {
        $sprint = Auth::user()->sprints()->with(['tickets.ticketTimes', 'tickets.subtasks', 'projects.assemblaUsers'])->where('sprint_assembla_id', $id)->firstOrFail();

        return view('sprints.show', [
            'sprint' => $sprint,
        ]);
    }

    /**
     * This function is used to dispatch a Milestone Sync for the received project
     * and then a current mylestone ticket sync
     *
     * @param $wikiname
     *
     * @return \Illuminate\Http\RedirectResponse
     * @internal param $projectId
     *TODO move this under Controllers/Assembla ProjectsController syncProjectSprints
     */
    public function syncSprints($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->firstorFail();
        try {
            SyncSpaceMilestones::dispatch(Auth::user(), $project);
            SessionMessage::infoMessage("Milestones sync job was added to the queue");
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('projects.show', $project->wikiname);

    }

    /**
     * This function will trigger a job that will update all syncable spaces
     * current milestones : )
     *
     * *TODO move this under Controllers/Assembla/
     */
    public function syncAllCurrentSprints()
    {
        SyncUserSyncableSpaces::dispatch(Auth::user());
        SessionMessage::infoMessage("Current spaces sync job was added to the queue");
        return redirect()->route('sprints.current');
    }

    //TODO remove this function and the milestine with CO button on the milestone show page
    public function sprintIteration($sprintAssemblaId)
    {
        $sprint = Auth::user()->sprints()->where('sprint_assembla_id', $sprintAssemblaId)->firstOrFail();

        //SprintIteration::dispatch(Auth::user(), $sprint, //today);
        SessionMessage::infoMessage("Milestone iteration job was added to the queue");
        $project = $sprint->getProject();
        return redirect()->route('sprints.show', [$project->wikiname, $sprint->sprint_assembla_id]);
    }
}
