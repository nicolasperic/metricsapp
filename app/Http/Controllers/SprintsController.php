<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\SprintImporter;
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
            'currentSprints' => Auth::user()->getOpenSprints->where('planner_type',2),
        ]);
    }

    public function show($id)
    {
        $sprint = Auth::user()->sprints()->findOrFail($id);

        return view('sprints.show', [
            'sprint' => $sprint,
        ]);
    }

    public function importSprints($projectId)
    {
        $project = Auth::user()->projects()->findOrFail($projectId);

        try {
            $sprintImporter = new SprintImporter();
            $sprintImporter->importProjectMilestonesAsSprints($project);
            SessionMessage::infoMessage('Milestones were correctly imported');
        } catch (Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }


        return redirect()->route('projects.show', $project);
    }
}
