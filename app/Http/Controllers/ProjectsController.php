<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    /**
     * Index action that displays user projects
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Contracts\Validation\Factory
     */
    public function index()
    {
        return view('projects.index', [
            'projects' => Auth::user()->projects,
        ]);
    }

    /**
     * Show action that displays the given project page
     *
     * @param $wikiname
     *
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Contracts\Validation\Factory
     */
    public function show($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->firstorFail();

        return view('projects.show', [
            'project' => $project,
        ]);
    }

    /**
     * Ajax called: returns the settings nav item content
     * @param $wikiname
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Support\HtmlString|void
     */
    public function settingsPane($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->with('sprintIteration')->firstorFail();

        return view('projects.partials.settings', [
            'project' => $project,
            'sprintIteration' => $project->sprintIteration,
            'day_of_week' => Carbon::now()->dayOfWeek,
            'start_dates' => $project->sprintIteration->getNewMilestoneStartDates(Carbon::now())//TODO this could be calculated on a helper, no instance actually required last and next weekday
        ]);
    }

    /**
     * Ajax called: returns the project main nav item content
     * @param $wikiname
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Support\HtmlString|void
     */
    public function projectPane($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->firstorFail();
        return view('projects.partials.project', [
            'project' => $project
        ]);
    }

    /**
     * Function used to update Project starred and syncable pivot values (project_user table)
     * @param $wikiname
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePivotAttribute($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->firstorFail();
        $attributeName = request('attribute_name');

        $allowedAttributes = [
            'starred' => 1,
            'syncable' => 1
        ];
        if (!array_key_exists($attributeName, $allowedAttributes)) {
            return response()->json([], 422);
        }
        $attributeValue = (request($attributeName) !== null)?1:0;

        Auth::user()->projects()->updateExistingPivot($project->id,[$attributeName => $attributeValue]);


        return response()->json(['id' => $project->id]);
    }

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

        $project = Auth::user()->projects()->where('wikiname', $wikiname)->firstorFail();
        $allowedAttributes = [
            'estimate_type' => 1,
            'shared' => 1,
        ];

        if (!array_key_exists($attributeName, $allowedAttributes)) {
            return response()->json([], 422);
        }
        if ($isCheckbox) {
            $attributeValue = (request($attributeName) !== null)?1:0;
        } else {
            $attributeValue = request($attributeName);
        }
        $project->{$attributeName} = $attributeValue;
        $project->save();


        return response()->json(['id' => $project->id]);
    }
}
