<?php

namespace App\Http\Controllers;

use App\Importer\SprintImporter;
use Illuminate\Support\Facades\Auth;


class SprintsController extends Controller
{
    public function index()
    {
        return view('sprints.index', [
            'sprints' => Auth::user()->sprints,
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

        $sprintImporter = new SprintImporter();
        $sprintImporter->importProjectMilestonesAsSprints($project);

        return redirect()->route('projects.show', $project);
    }
}
