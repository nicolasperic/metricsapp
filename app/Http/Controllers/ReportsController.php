<?php

namespace App\Http\Controllers;

use App\Integration\AssemblaGateway;
use App\Project;

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
        $projects = Auth::user()->projects;
        return view('reports.index', [
            'projects' => $projects,
            'users' => self::_getUsersInProjects($projects),
            'results' => request('results'),
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

        Log::info('Project '.print_r($requestData, 1));
        $report = new HoursByUSReport($requestData);
        $reportResults = $report->execute();
        //return redirect(route('reports.index', ['results' => $reportResults]));

        $projects = Auth::user()->projects;
        return view('reports.index', [
            'projects' => $projects,
            'users' => self::_getUsersInProjects($projects),
            'results' => $reportResults,
        ]);


            /*return redirect(route('reports.index'))->withErrors([
                'assembla_secret' => 'Ticket number does not exists or is a User Story',
            ])->withInput();*/
    }

    /**
     * @return array
     */
    protected function validateHoursByUsRequest()
    {
        //$from = '2020/01/01 00:00';
        //$to = '2020/09/02 23:59';

        return request()->validate([
            'project'   => 'required',
            //'assembla_secret' => 'required',
            //'assembla_key' => 'required',
            'hours_us_from_date' => 'date',
            'hours_us_to_date' => 'date|after_or_equal:from_date',
        ]);
    }

    public function generateHoursByUserReport()
    {
        $this->validateHoursByUserRequest();

        //$project = Project::getProjectByAssemblaId(request('project'));//fuck! obtener todos los wikinames?
        $requestData = [
            'projects' => request('projects'),
            'users' => request('users'),
            'from_date' => request('hours_user_from_date').' 00:00',
            'to_date' => request('hours_user_to_date').' 23:59',
        ];

        Log::info('Project '.print_r($requestData, 1));
        $report = new HoursByUserReport($requestData);
        $reportResults = $report->execute();

        Log::info('Project report ended without any issue');
        Log::info(print_r($reportResults, 1));
        Log::info('About to redirect');
        //return redirect(route('reports.index', ['results' => array('El reporte fue ejecutado')]));//$reportResults]));
        //return redirect(route('reports.index'));

        //return view('reports.index')->with('results', $reportResults);

        $projects = Auth::user()->projects;
        return view('reports.index', [
            'projects' => $projects,
            'users' => self::_getUsersInProjects($projects),
            'results' => $reportResults,
        ]);
    }

    /**
     * @return array
     */
    protected function validateHoursByUserRequest()
    {
        //$from = '2020/01/01 00:00';
        //$to = '2020/09/02 23:59';
        return request()->validate([
            'projects'   => 'required',
            //'users'      => 'required',
            'hours_user_from_date' => 'date',
            'hours_user_to_date'   => 'date|after_or_equal:from_date',
        ]);
    }
}
