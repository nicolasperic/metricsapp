<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
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

    public function starred($id)
    {
        $project = Auth::user()->projects()->findOrFail($id);

        $starred = (request('starred_project') !== null)?1:0;

        Auth::user()->projects()->updateExistingPivot($project->id,['starred' => $starred]);


        return response()->json(['id' => $project->id]);
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
            $projectImporter->importAllAssemblaSpacesAsProjects(Auth::user());
            SessionMessage::infoMessage("Projects were correctly imported");
        } catch (ClientException $e) {

            if ($e->getCode() == 401) {
                $settingsUrl = '<a href="'.url('/settings').'">here</a>';
                SessionMessage::errorMessage('Not authorized! Add your Assembla credentials '.$settingsUrl);

            } else {
                SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');

            }

            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('projects.index');
    }
}
