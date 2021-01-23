<?php
//TODO esta clase está repetida con USHoursReportTest! A lo caco para sacar más rápido un reporte, hacer las cosas bien
namespace App\Reports;

use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Report;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

/**
 * Class HoursByUSReport
 *
 * @package App\Reports
 */
class HoursByUSReport extends Report
{
    use HasFactory;

    protected $table = 'reports';

    const REPORT_TYPE = 'hours_by_us_report';

    /** @var  array para agrupar las horas de cada US
     *  ticket_id > description (ticket_number + description )
     *      total hours y total tasks
     */
    private $userStories;
    /**
     * @var array para agrupar las horas de tickets que no son US ni subtasks;
     *  ticket_id => description, total hours, total tasks
     */
    private $noUserStories;
    /** @var  array para agrupar las horas trackeadas sin ticket >
     * user_id > hours y tasks */
    private $withoutTicket;
    /** @var  array para mapear subtasks con user stories >
     * subtask_id => user_story_id; de ser posible evitar algunas llamadas a la API */
    private $ticketAssociations;
    //^ de no tener asociacion subtask_id -> false
    /**
     * @var info solicitada a la API
     */
    private $ticketsApiData;

    private $typePercentages;//mantener % de horas y cantidad por tipo de US

    /**
     * @var int tracking the amount of API calls made
     */
    private $apicalls = 0;

    /**
     * @var array information required for the report
     * KEYS: wikiname, space_id, from_date and to_date
     */
    private $requestData;

    private $users;

    public static function forUser(User $user, $requestData)
    {
        return self::create([
            'user_id' => $user->id,
            'request_data' => $requestData,
            'title' => 'Hours by User Story'
        ]);
    }




    function execute()
    {
        $this->running();

        $this->apicalls = 0;
        $this->users = [];
        $startTime = time();

        //reset data
        $this->userStories = [];
        $this->noUserStories = [];
        $this->withoutTicket = [];
        $this->ticketAssociations = [];
        $this->ticketsApiData = [];
        $totalHours = 0;
        $totalTasks = 0;

        $this->requestData = $this->request_data;

        //setting request parameters
        $wikiname = $this->requestData['wikiname'];
        $spaceId = $this->requestData['space_id'];
        $from = $this->requestData['from_date'];
        $to = $this->requestData['to_date'];


        $page = 1;
        do {
            $queryParams = [
                'spaces' => $spaceId,
                'from' => $from,
                'to' => $to,
                'page' => $page,
            ];

            Log::info('report query params '.print_r($queryParams, 1));
            //TODO create an endpoint on AssemblaGateway for tasks action
            $response = AssemblaRequest::get("tasks", $this->user->assembla_key, $this->user->assembla_secret, $queryParams);
            $this->apicalls++;
            $result = json_decode($response->getBody()->getContents(), 1);
            if (!is_array($result)) {
                break;
            }


            foreach ($result as $timeTracked) {
                $timetracked = false;

                if (trim($timeTracked['ticket_id']) != '') {//Tracked time to a ticket
                    if (!array_key_exists($timeTracked['ticket_id'], $this->ticketsApiData)) {
                        $this->_retrieveAndSetTicketInformation($wikiname, $timeTracked['ticket_number']);
                    }



                    if (array_key_exists($timeTracked['ticket_id'], $this->userStories)) {//it's a user story
                        $this->userStories[$timeTracked['ticket_id']]['hours'] += $timeTracked['hours'];
                        $this->userStories[$timeTracked['ticket_id']]['tasks'] += 1;
                        Log::info('Tracking T1 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->userStories[$timeTracked['ticket_id']]['hours'].' US tasks '.$this->userStories[$timeTracked['ticket_id']]['tasks']);
                        $timetracked = true;
                    } else {//subtask

                        //subtask found on ticketAssociations, we can retrieve the user story ID without calling the API
                        if (array_key_exists($timeTracked['ticket_id'], $this->ticketAssociations) && $this->ticketAssociations[$timeTracked['ticket_id']] !== false) {
                            $this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['hours'] += $timeTracked['hours'];
                            $this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['tasks'] += 1;
                            $timetracked = true;

                            Log::info('Tracking T2 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['hours'].' US tasks '.$this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['tasks']);
                        } else {//subtask not found on ticketAssociations, need to retrieve related US from API if exists

                            $userStoryId = $this->_retrieveTicketAssociation($wikiname, $timeTracked['ticket_number']);
                            if ($userStoryId !== false) {
                                if (!array_key_exists($userStoryId, $this->userStories)) {
                                    //TODO create an endpoint on AssemblaGateway for this action
                                    $response = AssemblaRequest::get("spaces/{$wikiname}/tickets/id/{$userStoryId}", $this->user->assembla_key, $this->user->assembla_secret);
                                    $this->apicalls++;
                                    if ($response->getStatusCode() == 200) {
                                        $bodyContents = json_decode($response->getBody()->getContents(), 1);
                                        if ($bodyContents['is_story']) {
                                            $this->userStories[$userStoryId]['description'] = $bodyContents['number'].' '.$bodyContents['summary'];
                                            $this->userStories[$userStoryId]['total_invested_hours'] = $bodyContents['total_invested_hours'];
                                            $this->userStories[$userStoryId]['status'] = $bodyContents['status'];
                                            $this->userStories[$userStoryId]['hours'] = $timeTracked['hours'];
                                            $this->userStories[$userStoryId]['tasks'] = 1;

                                            Log::info('Tracking T3 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->userStories[$userStoryId]['hours'].' US tasks '.$this->userStories[$userStoryId]['tasks']);
                                            $timetracked = true;
                                        } else {
                                            Log::error('hmm it has a subtask but is not a user story?? Ticket number '.$timeTracked['ticket_number'].' wikiname '.$wikiname);
                                        }
                                    }
                                } else {
                                    $this->userStories[$userStoryId]['hours'] += $timeTracked['hours'];
                                    $this->userStories[$userStoryId]['tasks'] += 1;
                                    Log::info('Tracking T4 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->userStories[$userStoryId]['hours'].' US tasks '.$this->userStories[$userStoryId]['tasks']);
                                    $timetracked = true;
                                }

                            } else {
                                if (!array_key_exists($timeTracked['ticket_id'], $this->noUserStories)) {
                                    $this->noUserStories[$timeTracked['ticket_id']]['hours'] = 0;
                                    $this->noUserStories[$timeTracked['ticket_id']]['tasks'] = 0;
                                }

                                $this->noUserStories[$timeTracked['ticket_id']]['hours'] += $timeTracked['hours'];
                                $this->noUserStories[$timeTracked['ticket_id']]['tasks'] += 1;
                                Log::info('Tracking T5 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->noUserStories[$timeTracked['ticket_id']]['hours'].' US tasks '.$this->noUserStories[$timeTracked['ticket_id']]['tasks']);
                                $timetracked = true;
                            }
                        }
                    }
                } else {
                    //no ticket ID
                    if (!array_key_exists($timeTracked['user_id'], $this->withoutTicket)) {
                        $this->withoutTicket[$timeTracked['user_id']]['hours']  = 0;
                        $this->withoutTicket[$timeTracked['user_id']]['tasks'] = 0;
                    }

                    $this->withoutTicket[$timeTracked['user_id']]['hours']  += $timeTracked['hours'];
                    $this->withoutTicket[$timeTracked['user_id']]['tasks'] += 1;
                    Log::info('Tracking T6 '.$timeTracked['hours'].' US hs '.$this->withoutTicket[$timeTracked['user_id']]['hours'].' US tasks '.$this->withoutTicket[$timeTracked['user_id']]['tasks']);
                    $timetracked = true;
                }

                if (!$timetracked) {
                    Log::info('[US time]  time not tracked for entry hs '.$timeTracked['hours'].' '.$timeTracked['ticket_id'].' '.$timeTracked['ticket_number']);
                }

                $totalHours += $timeTracked['hours'];
                $totalTasks += 1;

                Log::info('END Task Data hours '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' user_id '.$timeTracked['user_id'].' id'.$timeTracked['id']);
            }


            if (count($result) === 100) {
                $page++;
            }

        } while(count($result) === 100);



        $reportBody['header'] = [
            'from'          => $from,
            'to'            => $to,
            'total_hours'   => $totalHours,
            'total_tasks'   => $totalTasks,
            'wikiname'      => $wikiname,
        ];

        $reportBody['user_stories'] = ['tickets' => [], 'total_hours' => 0, 'header_columns' => []];
        $reportBody['no_user_stories'] = ['tickets' => [], 'total_hours' => 0, 'header_columns' => []];
        $reportBody['without_ticket'] = ['users' => [], 'total_hours' => 0, 'header_columns' => []];
        $reportBody['types'] = [];
        $reportBody['footer'] = ['total_api_calls' => 0, 'execution_time' => 0];


        $results = [];
        $results[]= '======================================================'.PHP_EOL;
        $results[] = "Desde $from hasta $to".PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;
        $results[] = 'Total Hours '.$totalHours.PHP_EOL;
        $results[] = 'Total Tasks '.$totalTasks.PHP_EOL;


        $results[] = '======================================================'.PHP_EOL;
        $results[] = 'User Stories'.PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;

        $this->typePercentages = [];// Type => hours; count; hours/percentage (sobre total stories hours); count/percentage (sobre total stories)
        $userStoriesTotalHours = 0;
        ksort($this->userStories);
        $results[] = 'ticket,total_hours, hours, tasks, status, type'.PHP_EOL;
        $reportBody['user_stories']['header_columns'] = ['ticket','total hours', 'hours', 'tasks', 'status', 'type'];
        foreach ($this->userStories as $id => $storyData) {
            $reportBody['user_stories']['tickets'][$id] = [
                'description' => $storyData['description'],
                'total_invested_hours' => $storyData['total_invested_hours'],
                'hours' => $storyData['hours'],
                'tasks' => $storyData['tasks'],
                'status' => $storyData['status'],
                'type' => $storyData['type'],
            ];

            $results[] = $storyData['description'].', '.$storyData['total_invested_hours'].', '.$storyData['hours'].', '.$storyData['tasks'].', '.$storyData['status'].', '.$storyData['type'].PHP_EOL;
            $userStoriesTotalHours += $storyData['hours'];
            self::_keepTrackOfTypeData($storyData);
        }

        $reportBody['user_stories']['total_hours'] = $userStoriesTotalHours;
        $results[] = ''.PHP_EOL;
        $results[] = 'User Stories Total Hours: '.$userStoriesTotalHours.PHP_EOL;
        //$results[] = print_r($this->userStories, 1).PHP_EOL;
        //$results[] = print_r($this->noUserStories, 1).PHP_EOL;

        $noUserStoriesTotalHours = 0;
        if ($this->noUserStories) {
            $results[] = '======================================================'.PHP_EOL;
            $results[] = 'Tasks (not a User Story)'.PHP_EOL;
            $results[] = '======================================================'.PHP_EOL;
            ksort($this->noUserStories);
            $results[] = 'ticket,total_hours, hours, tasks, status'.PHP_EOL;
            $reportBody['no_user_stories']['header_columns'] = ['ticket','total hours', 'hours', 'tasks', 'status'];
            foreach ($this->noUserStories as $id => $ticketData) {
                $results[] = $ticketData['description'].', '.$ticketData['total_invested_hours'].', '.$ticketData['hours'].', '.$ticketData['tasks'].', '.$ticketData['status'].PHP_EOL;

                $reportBody['no_user_stories']['tickets'][$id] = [
                    'description' => $ticketData['description'],
                    'total_invested_hours' => $ticketData['total_invested_hours'],
                    'hours' => $ticketData['hours'],
                    'tasks' => $ticketData['tasks'],
                    'status' => $ticketData['status'],
                ];

                $noUserStoriesTotalHours += $ticketData['hours'];
            }

            $results[] = ''.PHP_EOL;
            $results[] = 'Tasks (not a User Story) Total Hours: '.$noUserStoriesTotalHours.PHP_EOL;
            $reportBody['no_user_stories']['total_hours'] = $noUserStoriesTotalHours;
        }


        //$results[] = print_r($this->withoutTicket, 1).PHP_EOL;

        $withoutTicketTotalHours = 0;
        if (count($this->withoutTicket)) {
            $reportBody['without_ticket']['header_columns'] = ['user', 'hours', 'tasks'];
            $results[] = '======================================================'.PHP_EOL;
            $results[] = ' Tracked time without ticket'.PHP_EOL;
            $results[] = '======================================================'.PHP_EOL;
            $results[] = 'username, hours, tasks'.PHP_EOL;
            foreach ($this->withoutTicket as $userId => $data) {
                $username = $this->getUserName($userId);
                $results[] = $username.','.$data['hours'].','.$data['tasks'].PHP_EOL;
                $reportBody['without_ticket']['users'][$userId] = [
                    'username' => $username,
                    'hours' => $data['hours'],
                    'tasks' => $data['tasks']
                ];

                    //$userLine;
                $withoutTicketTotalHours += $data['hours'];
            }

            $results[] = ''.PHP_EOL;
            $results[] = 'Tracked time without ticket Total Hours: '.$withoutTicketTotalHours.PHP_EOL;
            $reportBody['without_ticket']['total_hours'] = $withoutTicketTotalHours;
        }

        $results[] = ''.PHP_EOL;//adding a breakline

        foreach ($this->typePercentages as $type => $typeData) {
            $typeTotalHours = $this->typePercentages[$type]['total_hours'];
            $typeTotalTickets = $this->typePercentages[$type]['total_tickets'];

            $typeTotalHoursPercentage =  ($userStoriesTotalHours != 0)?number_format(($typeTotalHours / $userStoriesTotalHours) * 100, 2) : 0;
            $typeTotalTicketsPercentage = (count($this->userStories) != 0)?  number_format(($typeTotalTickets/ count($this->userStories)) * 100, 2) : 0;

            $results[] = str_pad($type, 20)."\t\t".$typeTotalTickets.' user stories ('.$typeTotalTicketsPercentage.'%)'.PHP_EOL;
            $results[] = str_pad('', 20)."\t\t".$typeTotalHours.' horas ('.$typeTotalHoursPercentage.'%)'.PHP_EOL;

            $reportBody['types'][$type] = [
                'type_count' => $typeTotalTickets,
                'type_count_percentage' => $typeTotalTicketsPercentage,
                'type_hours' => $typeTotalHours,
                'type_hours_percentage' => $typeTotalHoursPercentage
            ];
        }


        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = 'Total Tracked Time '.$totalHours.' vs Total Hours per section (all added) '.($userStoriesTotalHours+$noUserStoriesTotalHours+$withoutTicketTotalHours).PHP_EOL;

        $endTime = time();
        $minutes = round(($endTime - $startTime)/60, 2);
        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = "Execution time ". $minutes ." minutes".PHP_EOL;
        $results[] = 'Total API calls '.$this->apicalls.' (pages '.$page.')'.PHP_EOL;

        $reportBody['footer'] = [
            'total_api_calls' => $this->apicalls,
            'execution_time' => $minutes
        ];
        $this->processed($reportBody);
    }


    /**
     * This beautiful function will retrieve the ticket information using the API
     * If the ticket is a subtask it will fetch the associations to try to get the related user story
     *
     * @param $space
     * @param $ticketNumber
     */
    private function _retrieveAndSetTicketInformation($space, $ticketNumber)
    {
        $assemblaGateway = new AssemblaGateway($this->user);
        /** @var TicketDto $ticketDto */
        $ticketDto = $assemblaGateway->getTicketBySpaceAndNumber($space, $ticketNumber);
        $this->apicalls++;

        if ($ticketDto !== false) {

            $ticketId = $ticketDto->getTicketAssemblaId();
            $this->ticketsApiData[$ticketId] = [
                'is_story' => $ticketDto->isStory(),
                'description' => $ticketDto->getDescription(),
                'total_invested_hours' => $ticketDto->getTotalInvestedHours()
            ];

            if ($ticketDto->isStory() && !array_key_exists($ticketId, $this->userStories)) {
                $this->userStories[$ticketId]['description'] = $ticketDto->getDescription();
                $this->userStories[$ticketId]['total_invested_hours'] = $ticketDto->getTotalInvestedHours();
                $this->userStories[$ticketId]['status'] = $ticketDto->getStatus();
                $this->userStories[$ticketId]['hours'] = 0;
                $this->userStories[$ticketId]['tasks'] = 0;
                $this->userStories[$ticketId]['type'] = ($ticketDto->getType())? $ticketDto->getType() : 'Empty';

            } else {
                $userStoryId = $this->_retrieveTicketAssociation($space, $ticketNumber);
                if ($userStoryId !== false) {
                    if (!array_key_exists($userStoryId, $this->userStories)) {
                        $response = AssemblaRequest::get("spaces/{$space}/tickets/id/{$userStoryId}", $this->user->assembla_key, $this->user->assembla_secret);
                        $this->apicalls++;
                        if ($response->getStatusCode() == 200) {
                            $bodyContents = json_decode($response->getBody()->getContents(), 1);
                            if ($bodyContents['is_story']) {
                                $this->userStories[$bodyContents['id']]['description'] = $bodyContents['number'].' '.$bodyContents['summary'];
                                $this->userStories[$bodyContents['id']]['total_invested_hours'] = $bodyContents['total_invested_hours'];
                                $this->userStories[$bodyContents['id']]['status'] = $bodyContents['status'];
                                $this->userStories[$bodyContents['id']]['hours'] = 0;
                                $this->userStories[$bodyContents['id']]['tasks'] = 0;
                                $this->userStories[$bodyContents['id']]['type'] = self::_getTicketType($bodyContents['custom_fields']);
                            } else {
                                dd('hmm it has a subtask but is not a user story??');
                            }
                        }
                    }

                } else {
                    if (!array_key_exists($ticketId, $this->noUserStories)) {
                        //the task is not a user story neither a subtask since it has no association as subtask
                        $this->noUserStories[$ticketId]['description'] = $ticketDto->getDescription();
                        $this->noUserStories[$ticketId]['total_invested_hours'] = $ticketDto->getTotalInvestedHours();
                        $this->noUserStories[$ticketId]['status'] = $ticketDto->getStatus();
                        $this->noUserStories[$ticketId]['hours'] = 0;
                        $this->noUserStories[$ticketId]['tasks'] = 0;
                    }

                }
            }
        }
    }

    private function _getTicketType($customFields)
    {
        $type = 'Empty';
        if (array_key_exists('Type',$customFields) && trim($customFields['Type']) != '') {
            $type = $customFields['Type'];
        }

        return $type;
    }

    private function _keepTrackOfTypeData($storyData)
    {
        if (array_key_exists('type', $storyData)) {

            $type = $storyData['type'];
        } else {
            $type = 'Empty';
            dd($storyData);
        }

        if (!array_key_exists($type, $this->typePercentages)) {
            $this->typePercentages[$type] = [
                'total_hours' => 0,
                'total_tickets' => 0,
            ];
        }

        $this->typePercentages[$type]['total_hours'] += $storyData['hours'];
        $this->typePercentages[$type]['total_tickets'] += 1;
    }


    private function _retrieveTicketAssociation($space, $ticketNumber)
    {
        $assemblaGateway = new AssemblaGateway($this->user);
        $ticketAssociations = $assemblaGateway->getTicketAssociationsBySpaceAndNumber($space, $ticketNumber);
        $this->apicalls++;
        if ($ticketAssociations !== false) {
            /** @var TicketAssociationDto $association */
            foreach ($ticketAssociations as $association) {
                if ($association->getRelationship() === AssemblaGateway::STORY_RELATION) {
                    //$subtaskId = $association['ticket1_id'];
                    return  $association->getTicket2Id();//returning user story ID
                }
            }
        }

        return false;//the received ticketNumber has no subtask relation
    }

    public static function boot()
    {
        parent::boot();

        // Save the type when creating this model
        static::creating(function ($report) {
            $report->forceFill([
                'type' => HoursByUSReport::REPORT_TYPE,
            ]);
        });
    }

}