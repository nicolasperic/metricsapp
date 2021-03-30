<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SprintIterationsController extends Controller
{
    /**
     * Function used to update Project attributes
     * @param $wikiname
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAttribute($wikiname)
    {
        $attributeName = request('attribute_name');
        $isCheckbox = request('is_checkbox');

        $project = Auth::user()->projects()->where('wikiname', $wikiname)->with('sprintIteration')->firstorFail();
        $sprintIteration = $project->sprintIteration;
        $allowedAttributes = [
            'iteration_status' => 1,
            'sprint_duration' => 1,
            'sprint_start_weekday' => 1,
            'sprint_prefix' => 1,
        ];
        if ($sprintIteration->isAutoIterationRunning() && strpos($attributeName, 'sprint_') !== false) {
            return response()->json([], 422);
        }
        if (!array_key_exists($attributeName, $allowedAttributes)) {
            return response()->json([], 422);
        }
        if ($isCheckbox) {
            $attributeValue = (request($attributeName) !== null)?1:0;
        } else {
            $attributeValue = request($attributeName);
        }
        $sprintIteration->{$attributeName} = $attributeValue;
        $sprintIteration->save();

        return response()->json(['id' => $project->id]);
    }

    /**
     * Function used to update Project attributes
     * @param $wikiname
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function startDate($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->with('sprintIteration')->firstorFail();

        if ($project->sprintIteration->isAutoIterationRunning()) {
            return response()->json([], 422);
        }

        $project->sprintIteration->sprint_start_weekday = request('sprint_start_weekday');
        $project->sprintIteration->save();

        return response()->json($project->sprintIteration->getNewMilestoneStartDates(Carbon::now()));
    }

    public function start($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->with('sprintIteration')->firstorFail();

        if ($project->sprintIteration->isAutoIterationRunning()) {
            return response()->json([], 422);
        }

        $startDate = (request('start_date') != null)? request('start_date'): Carbon::now()->format('Y/m/d');

        $project->sprintIteration->start(Auth::user(), $startDate);

        SessionMessage::infoMessage("Sprint Iteration started");

        return redirect()->route('projects.show', $project->wikiname);
    }

    public function stop($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->with('sprintIteration')->firstorFail();

        if (!$project->sprintIteration->isAutoIterationRunning()) {
            return response()->json([], 422);
        }

        $project->sprintIteration->stop(Auth::user());

        SessionMessage::infoMessage("Successfully stopped automated sprint iterations for ".$project->name);

        return redirect()->route('projects.show', $project->wikiname);
    }

    public function sprintModalDynamicContent($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->with('sprintIteration')->firstorFail();


        $startDate = (request('start_date') != null)? request('start_date'): Carbon::now()->format('Y/m/d');


        $responseData = [
            'milestone_title' => $project->sprintIteration->getNewMilestoneUniqueTitle($startDate),
            'milestone_end_date' => $project->sprintIteration->getNewMilestoneEndDate($startDate)
        ];

        return response()->json($responseData);
    }

}
