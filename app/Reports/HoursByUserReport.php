<?php
//TODO esta clase está repetida con USHoursReportTest!
namespace App\Reports;

use App\AssemblaUser;
use App\Importer\UserImporter;
use App\Integration\AssemblaRequest;
use App\Project;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Class HoursByUserReport
 *
 * @package App\Reports
 */
class HoursByUserReport
{
    /**
     * @var array information required for the report
     * KEYS: wikiname, space_id, from_date and to_date
     */
    private $requestData;
    private $projects;
    /**
     * @var User
     */
    private $user;
    
    private $apiCalls;
    private $users;//used to keep the user name on memory and avoid DB calls

    public function __construct($requestData, User $user)
    {
        $this->requestData= $requestData;

        foreach ($this->requestData['projects'] as $projectAssemblaId) {
            $project = Project::getProjectByAssemblaId($projectAssemblaId);
            $this->projects[$project->wikiname] = $projectAssemblaId;
        }


        if ($this->requestData['users']) {
            $this->requestData['users'] = array_combine($this->requestData['users'],$this->requestData['users']);
        }

        $this->user = $user;
    }



    //TODO la lógica de esta función está repetida en USHoursReportTest
    function execute()
    {
        $this->apiCalls = 0;
        $this->users = [];
        $startTime = time();

        Log::info('Starting HoursByUserReport');

        $teamMembers = $this->requestData['users'];

        $projects = $this->projects;

        $hours = array();
        $totalHours = 0;
        $totalTasks = 0;
        $projectHours = array();

        $from = $this->requestData['from_date'];//format > '2020/12/14 00:00';
        $to = $this->requestData['to_date'];//format > '2020/12/20 23:59';
        foreach ($projects as $wikiname => $spaceId) {

            $page = 1;
            do {
                $queryParams = [
                    'spaces' => $spaceId,
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
                    if ($wikiname == 'summa-internal-projects') {
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
        $results = [];
        $results[] = '';
        $results[] = '======================================================'.PHP_EOL;
        $results[] = "Desde $from hasta $to".PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;
        $results[] = 'Total Hours '.$totalHours.PHP_EOL;
        $results[] = 'Total Tasks '.$totalTasks.PHP_EOL;

        Log::info('Results first chunk');

        foreach ($projectHours as $wikiname => $projectData) {
            $results[] = '======================================================'.PHP_EOL;
            $results[] = "\t".$wikiname.PHP_EOL;
            $results[] = '======================================================'.PHP_EOL;
            $totalHours = 0;
            $totalTasks = 0;
            foreach ($projectData as $userId => $userHours) {
                $totalHours += $userHours['hours'];
                $totalTasks += $userHours['tasks'];

                $userName = $this->getUserName($userId);
                $results[] = str_pad($userName, 20)."\t".str_pad($userHours['tasks']. " tasks", 9) ." \t".$userHours['hours']. ' hours'.PHP_EOL;
            }
            $results[] = ''.PHP_EOL;
            $results[] = 'Project total hours '.$totalHours.' in '.$totalTasks.' tasks'.PHP_EOL;
        }
        Log::info('Project hours iteration');

        $results[] = PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;
        $results[] = ' Hours grouped by Users '.PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;

        foreach ($hours as $userId => $hoursData) {
            $userName = $this->getUserName($userId);
            $results[] = str_pad($userName, 20)."\t".str_pad($hoursData['tasks']. " tasks", 9). " \t".$hoursData['hours']. ' hours'.PHP_EOL;
        }
        Log::info('User hours iteration');
        //Log::info(print_r($results, 1));

        $endTime = time();
        $minutes = round(($endTime - $startTime)/60, 2);
        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = "Execution time ". $minutes ." minutes".PHP_EOL;
        $results[] = 'Total API calls '.$this->apiCalls.PHP_EOL;

        return $results;
    }

    /**
     * //TODO this function should be shared by all reports > hierarchy AbstractReport should have it
     * @param $userAssemblaId
     *
     * @return String
     */
    public function getUserName($userAssemblaId) {
        if (array_key_exists($userAssemblaId, $this->users)) {
            return $this->users[$userAssemblaId];
        }

        $userImporter = new UserImporter($this->user);
        $userName = $userImporter->getUserName($userAssemblaId);
        //storing the name on memory
        $this->users[$userAssemblaId] = $userName;

        return $userName;
    }




}