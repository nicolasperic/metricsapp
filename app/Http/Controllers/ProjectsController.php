<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\ProjectImporter;
use App\Jobs\SyncSpaces;
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

    public function syncable($id)
    {
        $project = Auth::user()->projects()->findOrFail($id);

        $syncable = (request('syncable_project') !== null)?1:0;

        Auth::user()->projects()->updateExistingPivot($project->id,['syncable' => $syncable]);


        return response()->json(['id' => $project->id]);
    }

    public function shared($id)
    {
        $project = Auth::user()->projects()->findOrFail($id);
        $shared = (request('shared_project') !== null)?1:0;
        $project->shared = $shared;
        $project->save();


        return response()->json(['id' => $project->id]);
    }

    public function estimate($id)
    {
        $project = Auth::user()->projects()->findOrFail($id);
        $estimateType = request('estimate_type');
        $project->estimate_type = $estimateType;
        $project->save();


        return response()->json(['id' => $project->id]);
    }

    /**
     * This function is used for importing user projects (assembla spaces)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncProjects()
    {
        try {
            SyncSpaces::dispatch(Auth::user());
            SessionMessage::infoMessage("Projects sync job was added to the queue");
        } catch (ClientException $e) {

            if ($e->getCode() == 401) {
                //TODO this wont happen since we are dispatching the job, we could althoug add the same exception on the job and dispatch a failure alert
                $settingsUrl = '<a href="'.url('/settings').'">here</a>';
                SessionMessage::errorMessage('Not authorized! Update your Assembla credentials '.$settingsUrl);

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

    public function settingsPane($id)
    {
        $project = Auth::user()->projects()->findOrFail($id);
        return view('projects.partials.settings', [
            'project' => $project
        ]);
    }

    public function projectPane($id)
    {
        $project = Auth::user()->projects()->findOrFail($id);
        return view('projects.partials.project', [
            'project' => $project
        ]);

    }
}
