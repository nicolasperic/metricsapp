<?php

namespace App\Http\Controllers;

use App\Importer\ProjectImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    public function index()
    {
        return view('projects.index', [
            'projects' => Auth::user()->projects,
        ]);
    }

    public function show($id)
    {
        $project = Auth::user()->projects()->findOrFail($id);

        return view('projects.show', [
            'project' => $project,
        ]);
    }

    public function importProjects()
    {
        $projectImporter = new ProjectImporter();
        $projectImporter->importAllAssemblaSpacesAsProjects();

        return redirect()->route('projects.index');
    }
}
