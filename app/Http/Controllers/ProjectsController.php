<?php

namespace App\Http\Controllers;

use App\Importer\ProjectImporter;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

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

    /**
     * This function is used for importing user projects (assembla spaces)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importProjects()
    {
        $projectImporter = new ProjectImporter();

        try {
            $projectImporter->importAllAssemblaSpacesAsProjects();

        } catch (ClientException $e) {
            Session::flash('alert-class', 'alert-danger');//alert-danger, alert-info, alert-warning
            if ($e->getCode() == 401) {
                $settingsUrl = '<a href="'.url('/settings').'">here</a>';
                Session::flash('message', 'Not authorized! Add your Assembla credentials '.$settingsUrl);
            } else {
                Session::flash('message', 'Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            }

            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('projects.index');
    }
}
