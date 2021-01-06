<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Integration\AssemblaGateway;
use App\Jobs\ProcessHoursByUsersReport;
use App\Jobs\ProcessUserStoryReport;
use App\Project;

use App\Report;
use App\Reports\HoursByUserReport;
use App\Reports\HoursByUSReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use TheSeer\Tokenizer\Exception;

class ReportsController extends Controller
{
    const APPLICATION_KEY = 'a5aa5632989ec768d71d';//https://app.assembla.com/user/edit/manage_clients
    const APPLICATION_SECRET = '497e452c605c29f8971aeb367e6c15a872749efe';

    public function index()
    {
        Log::info('ReportsController index action');
        return $this->_generateIndexView(request('results'));
    }

    public function show($id)
    {
        $report = Auth::user()->reports()->findOrFail($id);

        return view('reports.show', [
            'report' => $report,
        ]);
    }

    /**
     //TODO remove this function, we can probably work with a redirect > return redirect()->route('reports.index');
     */
    private function _generateIndexView($reportResults = null)
    {
        $projects = Auth::user()->projects;
        return view('reports.index', [
            'projects' => $projects,
            'users' => self::_getUsersInProjects($projects),
            'results' => $reportResults,
            'reports' => Auth::user()->lastWeekReports(),
        ]);
    }

    /**
     * TODO move this function to a helper
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

        $reportModel = Report::create([
            'title' => 'Hours by User Story',
            'request_data' => serialize($requestData)
        ]);
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
            'hours_us_to_date' => 'date|after_or_equal:from_date',
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

        $testData = $requestData;
        unset($testData['users']);
        unset($testData['projects']);

        $reportModel = Report::create([
            'title' => 'Hours by Users',
            'request_data' => serialize($testData)
        ]);

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
            'hours_user_to_date'   => 'date|after_or_equal:from_date',
        ]);
    }
}
