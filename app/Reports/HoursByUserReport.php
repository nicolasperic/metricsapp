<?php

namespace App\Reports;

use App\Integration\AssemblaRequest;
use App\Project;
use App\Report;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

/**
 * Class HoursByUserReport
 *
 * Report Body Structure
 * - header > from_date, to_date, total_hours, total_tasks
 *
 * - Project hours
 *      > project name, total_hours, total_tasks, users [ name, total_tasks, total_hours]
 *
 * - Users
 *      > name, total_tasks, total_hours
 *
 * - footer > execution time, api calls (admin only)
 *
 * @package App\Reports
 */
class HoursByUserReport extends Report
{
    use HasFactory;

    protected $table = 'reports';

    const REPORT_TYPE = 'hours_by_user_report';

    
    private $apiCalls;
    private $users;//used to keep the user name on memory and avoid DB calls

    public static function forUser(User $user, $requestData, $title = 'Hours by Users')
    {
        if ($requestData['users']) {
            $requestData['users'] = array_combine($requestData['users'],$requestData['users']);
        }

        return self::create([
            'user_id' => $user->id,
            'request_data' => $requestData,
            'title' => $title
        ]);
    }


    function execute()
    {
        $this->running();

        $projects = [];
        foreach ($this->request_data['projects'] as $projectAssemblaId) {
            $project = Project::getProjectByAssemblaId($projectAssemblaId);
            $projects[$project->wikiname] = $project;
        }


        $this->apiCalls = 0;
        $this->users = [];
        $startTime = time();

        Log::info('Starting HoursByUserReport');

        $teamMembers = $this->request_data['users'];



        $hours = array();
        $totalHours = 0;
        $totalTasks = 0;
        $projectHours = array();

        $from = $this->request_data['from_date'];//format > '2020/12/14 00:00';
        $to = $this->request_data['to_date'];//format > '2020/12/20 23:59';
        foreach ($projects as $wikiname => $project) {

            $page = 1;
            do {
                $queryParams = [
                    'spaces' => $project->project_assembla_id,
                    'from' => $from,
                    'to' => $to,
                    'page' => $page,
                ];
                Log::info('About to get tasks for '.$wikiname.' page '.$page);

                $applicationKey = $this->user->assembla_key;
                $applicationSecret = $this->user->assembla_secret;
                $response = AssemblaRequest::get("tasks", $applicationKey, $applicationSecret, $queryParams);
                $this->apiCalls++;
                $result = json_decode($response->getBody()->getContents(), 1);
                Log::info('Response for '.$wikiname.' is '.$response->getStatusCode());
                if (!is_array($result)) {
                    break;
                }

                foreach ($result as $timeTracked) {
                    if ($wikiname == 'summa-internal-projects') {//TODO this is hardcoded here, validate if project is "shared
                        if ($teamMembers && !array_key_exists($timeTracked['user_id'], $teamMembers)) {
                            continue;
                        }
                    }
                    if (!array_key_exists($timeTracked['user_id'], $hours)) {
                        $hours[$timeTracked['user_id']]['hours']  = 0;
                        $hours[$timeTracked['user_id']]['tasks'] = 0;
                    }

                    if (!array_key_exists($wikiname, $projectHours)) {
                        $projectHours[$wikiname] = array();
                    }

                    if (!array_key_exists($timeTracked['user_id'], $projectHours[$wikiname])) {
                        $projectHours[$wikiname][$timeTracked['user_id']] = ['hours' => 0, 'tasks' => 0];
                    }


                    $projectHours[$wikiname][$timeTracked['user_id']]['hours'] += $timeTracked['hours'];
                    $projectHours[$wikiname][$timeTracked['user_id']]['tasks'] += 1;

                    $hours[$timeTracked['user_id']]['hours'] += $timeTracked['hours'];
                    $hours[$timeTracked['user_id']]['tasks'] += 1;
                    $totalHours += $timeTracked['hours'];
                    $totalTasks += 1;
                }


                $page++;

            } while(count($result) === 100);
        }

        Log::info('API Calls ended! Report output');


        $reportBody = [];
        $reportBody['header'] = [
            'from' => $from,
            'to' => $to,
            'total_hours' => $totalHours,
            'total_tasks' => $totalTasks,
        ];
        $reportBody['projects'] = [];
        $reportBody['users'] = [];

        foreach ($projectHours as $wikiname => $projectData) {
            $totalHours = 0;
            $totalTasks = 0;
            $projectUsers = [];
            foreach ($projectData as $userId => $userHours) {
                $totalHours += $userHours['hours'];
                $totalTasks += $userHours['tasks'];

                $userName = $this->getUserName($userId);
                $results[] = str_pad($userName, 20)."\t".str_pad($userHours['tasks']. " tasks", 9) ." \t".$userHours['hours']. ' hours'.PHP_EOL;

                $projectUsers[$userId] = [
                    'username' => $userName,
                    'total_hours' => $userHours['hours'],
                    'total_tasks' => $userHours['tasks'],
                ];
            }

            $reportBody['projects'][$wikiname] = [
                'wikiname' => $wikiname,
                'total_hours' => $totalHours,
                'total_tasks' => $totalTasks,
                'users' => $projectUsers
            ];

        }

        //Hours grouped by users
        foreach ($hours as $userId => $hoursData) {
            $userName = $this->getUserName($userId);
            $results[] = str_pad($userName, 20)."\t".str_pad($hoursData['tasks']. " tasks", 9). " \t".$hoursData['hours']. ' hours'.PHP_EOL;
            $reportBody['users'][$userId] = [
                'username' => $userName,
                'total_hours' => $hoursData['hours'],
                'total_tasks' => $hoursData['tasks'],
            ];
        }


        $endTime = time();
        $minutes = round(($endTime - $startTime)/60, 2);

        $reportBody['footer'] = [
            'execution_time' => $minutes,
            'total_api_calls' => $this->apiCalls
        ];

        $this->processed($reportBody);

    }

    public static function boot()
    {
        parent::boot();

        // Save the type when creating this model
        static::creating(function ($report) {
            $report->forceFill([
                'type' => HoursByUserReport::REPORT_TYPE,
            ]);
        });
    }




}