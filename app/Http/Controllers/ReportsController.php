<?php

namespace App\Http\Controllers;

use App\Integration\AssemblaGateway;
use App\Project;

use App\Reports\HoursByUSReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    const APPLICATION_KEY = 'a5aa5632989ec768d71d';//https://app.assembla.com/user/edit/manage_clients
    const APPLICATION_SECRET = '497e452c605c29f8971aeb367e6c15a872749efe';

    public function index()
    {
        return view('reports.index', [
            'projects' => Auth::user()->projects,
            'results' => request('results'),
        ]);
    }

    /**
     * Generates report
     */
    public function store()
    {
        $this->validateRequest();

        $project = Project::getProjectByAssemblaId(request('project'));
        $requestData = [
            'wikiname' => $project->wikiname,
            'space_id' => request('project'),
            'from_date' => request('from_date'),
            'to_date' => request('to_date'),
        ];

        Log::info('Project '.print_r($requestData, 1));
        $report = new HoursByUSReport($requestData);
        $reportResults = $report->execute();
        return redirect(route('reports.index', ['results' => $reportResults]));


            /*return redirect(route('reports.index'))->withErrors([
                'assembla_secret' => 'Ticket number does not exists or is a User Story',
            ])->withInput();*/
    }

    /**
     * @return array
     */
    protected function validateRequest()
    {
        //$from = '2020/01/01 00:00';
        //$to = '2020/09/02 23:59';

        return request()->validate([
            'project'   => 'required',
            //'assembla_secret' => 'required',
            //'assembla_key' => 'required',
            'from_date' => 'date',
            'to_date' => 'date|after_or_equal:from_date',
        ]);
    }
}
