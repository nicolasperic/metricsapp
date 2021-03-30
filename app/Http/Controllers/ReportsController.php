<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Jobs\ProcessHoursByUsersReport;
use App\Jobs\ProcessSprintsReport;
use App\Jobs\ProcessUserStoryReport;
use App\Project;
use App\Report;
use App\Reports\HoursByUserReport;
use App\Reports\HoursByUSReport;
use App\Reports\SprintsReport;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    const APPLICATION_KEY = 'a5aa5632989ec768d71d';//https://app.assembla.com/user/edit/manage_clients
    const APPLICATION_SECRET = '497e452c605c29f8971aeb367e6c15a872749efe';

    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects()->with(['sprints','assemblaUsers'])->get();

        $currentSprintsIds = $user->starredProjects->map( function( $project ) {
            return (count($project->sprints)) ? $project->sprints[0]: null;

        })->pluck('sprint_assembla_id')->filter();

        return view('reports.index', [
            'projects' => $projects,
            'sprints' => $this->_mergeProjectsSprints($projects),//Auth::user()->sprints,
            'currentSprints' => $currentSprintsIds,
            'users' => $this->_getUsersInProjects($projects),
            'results' => request('results'),
            'reports' => $user->lastWeekReports(),
        ]);
    }

    /**
     * This function will return an array of all merged sprints containing
     * - name, sprint assembla id and project na,e
     *
     * @param $projects
     *
     * @return mixed
     */
    private function _mergeProjectsSprints($projects)
    {

        $sprints = $projects->map( function ($project) {
            return $project->sprints->map(function($sprint) use($project) {
                return [
                    'name' => $sprint->name,
                    'sprint_assembla_id' => $sprint->sprint_assembla_id,
                    'project_name' => $project->name
                ];
            });
        })->filter()->collapse();

        return $sprints;



    }

    /**
     * TODO move _getUsersInProjects function to a helper
     * This function will return an array with the assembla users on each project
     * @param $projects
     *
     * @return array
     */
    private function _getUsersInProjects($projects)
    {
        $users = [];

        foreach ($projects as $project) {
            foreach ($project->assemblaUsers as $assemblaUser) {
                $users[$assemblaUser->user_assembla_id] = $assemblaUser->name;
            }
        }

        asort($users);

        return $users;
    }

    public function weekly()
    {
        $user = Auth::user();
        $projects = $user->projects;
        return view('reports.weekly', [
            'projects' => $projects,
            'users' => self::_getUsersInProjects($projects),
            'selectedProjects' => unserialize($user->weekly_report_projects),
            'selectedUsers' => unserialize($user->weekly_report_users),
        ]);
    }

    public function weeklyStore()
    {
        $this->validateHoursByUserRequest();


        $user = Auth::user();
        $user->weekly_report_projects = serialize(request('projects'));
        $user->weekly_report_users = serialize(request('users'));
        $user->save();

        SessionMessage::infoMessage('Weekly report saved');

        return redirect()->route('reports.weekly');
    }

    public function show($id)
    {
        $report = Auth::user()->reports()->findOrFail($id);
        return view($report->getView(), [
            'report' => $report,
        ]);
    }

    /**
     * Generates Hours by User Story report
     */
    public function generateHoursByUsReport()
    {
        $this->validateHoursByUsRequest();

        $project = Project::getProjectByAssemblaId(request('project'));
        $requestData = [
            'wikiname' => $project->wikiname,
            'space_id' => request('project'),
            'from_date' => request('hours_us_from_date').' 00:00',
            'to_date' => request('hours_us_to_date').' 23:59',
        ];

        $reportModel = HoursByUSReport::forUser(Auth::user(), $requestData);
        Auth::user()->reports()->save($reportModel);
        ProcessUserStoryReport::dispatch($requestData, $reportModel);
        SessionMessage::infoMessage('The report has been added to the queue');

        return redirect()->route('reports.index');
    }

    /**
     * @return array
     */
    protected function validateHoursByUsRequest()
    {
        return request()->validate([
            'project'   => 'required',
            'hours_us_from_date' => 'date',
            'hours_us_to_date' => 'date|after_or_equal:hours_us_from_date|valid_date_range:hours_us_from_date,6',
        ],[
            'hours_us_to_date.after_or_equal' => 'To date must be a date after or equal From date.',
            'hours_us_to_date.valid_date_range' => 'Dates range cannot be larger than six months.'
        ]);
    }

    public function generateHoursByUserReport()
    {
        $this->validateHoursByUserRequest();

        $requestData = [
            'projects' => request('projects'),
            'users' => request('users'),
            'from_date' => request('hours_user_from_date').' 00:00',
            'to_date' => request('hours_user_to_date').' 23:59',
        ];

        $reportModel = HoursByUserReport::forUser(Auth::user(), $requestData);
        Auth::user()->reports()->save($reportModel);
        ProcessHoursByUsersReport::dispatch($requestData, $reportModel);
        SessionMessage::infoMessage('The report has been added to the queue');

        return redirect()->route('reports.index');
    }

    /**
     * @return array
     */
    protected function validateHoursByUserRequest()
    {
        return request()->validate([
            'projects'   => 'required',
            'hours_user_from_date' => 'date',
            'hours_user_to_date'   => 'date|after_or_equal:hours_user_from_date|valid_date_range:hours_us_from_date,12',
        ],[
            'hours_user_to_date.after_or_equal' => 'To date must be a date after or equal From date.',
            'hours_user_to_date.valid_date_range' => 'Dates range cannot be larger than twelve months.'
        ]);
    }



    public function generateSprintsReport()
    {
        $this->validateSprintsRequest();

        $requestData = [
            'sprints' => request('sprints'),
        ];

        $reportModel = SprintsReport::forUser(Auth::user(), $requestData);

        Auth::user()->reports()->save($reportModel);
        ProcessSprintsReport::dispatch($requestData, $reportModel);
        SessionMessage::infoMessage('The report has been added to the queue');

        return redirect()->route('reports.index');
    }

    /**
     * @return array
     */
    protected function validateSprintsRequest()
    {
        return request()->validate([
            'sprints'   => 'required|array|max:12',
        ]);
    }
}
