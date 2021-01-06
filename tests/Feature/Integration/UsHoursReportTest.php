<?php

namespace Tests\Feature\Integration;


use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * @group reports
 */
class UsHoursReportTest extends TestCase
{
    private $userStories;//array para agrupar las horas de cada US > ticket_id > description (ticket_number + description ), total hours y total tasks
    private $noUserStories;//array para agrupar las horas de tickets que no son US ni subtasks; ticket_id => description, total hours, total tasks
    private $withoutTicket;//array para agrupar las horas trackeadas sin ticket > user_id > hours y tasks
    private $ticketAssociations;//array para mapear subtasks con user stories > subtask_id => user_story_id; de ser posible evitar algunas llamadas a la API
    //^ de no tener asociacion subtask_id -> false
    private $ticketsApiData;//info solicitada a la API

    private $typePercentages;//mantener % de horas y cantidad por tipo de US

    private $apicalls = 0;
    /** @test */
    function can_get_projects_time_by_user_story()
    {
        $this->apicalls = 0;
        $startTime = time();
        //TODO add logic to remove hardcoded users
        $users = [
            'c5sp9uUXyr6Ok5cK-zJOy8' => 'Julieta Pisani',
            'd8r95QiVer6zj-aH8tHBnc' => 'Franco Aller',
            'cvixt811Gr4PBcacwqjQYw' => 'Nicolás Peric',
            'aAbtrS7fKr6y_dcP_HzTya' => 'Barbara Irizaga',
            'dNWJBO9war45rbacwqjQXA' => 'Elina Perez',
            'cc2NS0ZTSr4RS_acwqjQYw' => 'Jonatan Mayorano',
            'dBYqHcg2Cr5PRcdmr6CpXy' => 'Santiago Tolosa',
            'brVttgsFOr543cdmr6QqzO' => 'Emanuel Arcos',
            'buOwlo1uer45NdacwqjQWU' => 'Martín Granate',
            'ajLyFEiVir6A3ccK-zJOy8' => 'Federico Ackerley',
            'athUCe0pCr5OFcacwqEsg8' => 'Mariano Zunini',
            'c6u2Cuuu4r6AFdbK8JiBFu' => 'Martin Perrotta',
            'aW_vfY1FGr6ioeaH8tHBnc' => 'Brenda Herrada',
            'aVzzeMlw0r6RhdaIC_Qgzw' => 'Nicolas Lavaggi',
            'aSD9Sgwzqr6OoBaH8tHBnc' => 'Ezequiel Alvian',
            'aDiA_Cb2Wr6iNcacwqjQYw' => 'Matias Rodriguez',
            'a5Uwc0GEyr45yTacwqEsg8' => 'Alejandro Borria',
            'bYoBk2IxKr5PNcdmr6QqzO' => 'Diego Piu',
            'b_V2Si_JCr6lldaH8tHBnc' => 'Matias Wagner',
            'dUHuyGkPGr44k-acwqEsg8' => 'Pedro Rigoli',
            'ddsWca79Wr44oYacwqjQXA' => 'Nicolas Alejandro Gandara',
            'dzBlqaLhKr5O16acwqEsg8' => 'Esteban Campos',
            'arrHT2RRer54rQdmr6QqzO' => 'Mariana Rodriguez',
            'cC7iHg0oSr6BBdaIC_Qgzw' => 'Ariel Benítez',
            'blHTwYuger44kaacwqjQYw' => 'Ezequiel Gomez',
            'cxNMFQ6Fer5zdcacwqjQYw' => 'Andres Campos',
            'c1n5gcr0Or6B_cbQarZsNG' => 'Delfina Labate',
            'dDTcSCiNSr64ojaIC_Qgzw' => 'Julián García'
        ];

        $this->userStories = [];
        $this->noUserStories = [];
        $this->withoutTicket = [];
        $this->ticketAssociations = [];
        $this->ticketsApiData = [];

        $projects = [
            'AD-Barbieri' => 'ce1LaCpjCr6O96aH8tHBnc',
            //'canaldeautopartes' => 'dpT43eCVCr54kBacwqjQYw',
            //'cemaco' => 'dKs4GwzB8r4Pz7acwqjQYw',
            //'pinturerias-rex' => 'atJlRad84r55JcacwqjQXA',
            //'sommiercenter' => 'dxD3_KI5ur6ky6dmr6QqzO',
            //'summa-internal-projects' => 'bPFF_gQfWr4PjCacwqjQWU',
            //'Grupo-Grassi' => 'dTomygY3Gr6P7dbK8JiBFu'
        ];



        $totalHours = 0;
        $totalTasks = 0;


        $from = '2020/12/01 00:00';
        $to = '2020/12/31 23:59';
        foreach ($projects as $wikiname => $spaceId) {

            $page = 1;
            do {
                $queryParams = [
                    'spaces' => $spaceId,
                    'from' => $from,
                    'to' => $to,
                    'page' => $page,
                ];

                $response = AssemblaRequest::get("tasks", $queryParams);
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
                            Log::info('Tracking T1 '.$timeTracked['hours'].' hours | ticket number '.$timeTracked['ticket_number'].' US hs '.$this->userStories[$timeTracked['ticket_id']]['hours'].' US tasks '.$this->userStories[$timeTracked['ticket_id']]['tasks']);
                            $timetracked = true;
                        } else {//subtask

                            //subtask found on ticketAssociations, we can retrieve the user story ID without calling the API
                            if (array_key_exists($timeTracked['ticket_id'], $this->ticketAssociations) && $this->ticketAssociations[$timeTracked['ticket_id']] !== false) {
                                $this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['hours'] += $timeTracked['hours'];
                                $this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['tasks'] += 1;
                                $timetracked = true;
                                Log::info('Tracking T2 '.$timeTracked['hours'].' hours | ticket number '.$timeTracked['ticket_number'].' US hs '.$this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['hours'].' US tasks '.$this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['tasks']);
                            } else {//subtask not found on ticketAssociations, need to retrieve related US from API if exists

                                $userStoryId = $this->_retrieveTicketAssociation($wikiname, $timeTracked['ticket_number']);
                                if ($userStoryId !== false) {
                                    if (!array_key_exists($userStoryId, $this->userStories)) {
                                        $response = AssemblaRequest::get("spaces/{$wikiname}/tickets/id/{$userStoryId}");
                                        $this->apicalls++;
                                        if ($response->getStatusCode() == 200) {
                                            $bodyContents = json_decode($response->getBody()->getContents(), 1);
                                            if ($bodyContents['is_story']) {
                                                $this->userStories[$userStoryId]['description'] = $bodyContents['number'].' '.$bodyContents['summary'];
                                                $this->userStories[$userStoryId]['total_invested_hours'] = $bodyContents['total_invested_hours'];
                                                $this->userStories[$userStoryId]['status'] = $bodyContents['status'];
                                                $this->userStories[$userStoryId]['hours'] = $timeTracked['hours'];
                                                $this->userStories[$userStoryId]['tasks'] = 1;

                                                Log::info('Tracking T3 '.$timeTracked['hours'].' hours |  ticket number '.$timeTracked['ticket_number'].' US hs '.$this->userStories[$userStoryId]['hours'].' US tasks '.$this->userStories[$userStoryId]['tasks']);
                                                $timetracked = true;
                                            } else {
                                                dd('hmm it has a subtask but is not a user story??');
                                            }
                                        }
                                    } else {
                                        $this->userStories[$userStoryId]['hours'] += $timeTracked['hours'];
                                        $this->userStories[$userStoryId]['tasks'] += 1;
                                        Log::info('Tracking T4 '.$timeTracked['hours'].' hours | ticket number '.$timeTracked['ticket_number'].' US hs '.$this->userStories[$userStoryId]['hours'].' US tasks '.$this->userStories[$userStoryId]['tasks']);
                                        $timetracked = true;
                                    }

                                } else {
                                    if (!array_key_exists($timeTracked['ticket_id'], $this->noUserStories)) {
                                        $this->noUserStories[$timeTracked['ticket_id']]['hours'] = 0;
                                        $this->noUserStories[$timeTracked['ticket_id']]['tasks'] = 0;
                                    }

                                    $this->noUserStories[$timeTracked['ticket_id']]['hours'] += $timeTracked['hours'];
                                    $this->noUserStories[$timeTracked['ticket_id']]['tasks'] += 1;
                                    Log::info('Tracking T5 '.$timeTracked['hours'].' hours | ticket number '.$timeTracked['ticket_number'].' US hs '.$this->noUserStories[$timeTracked['ticket_id']]['hours'].' US tasks '.$this->noUserStories[$timeTracked['ticket_id']]['tasks']);
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

                    Log::info('END Task Data hours '.$timeTracked['hours'].' ticket number '.$timeTracked['ticket_number'].' user_id '.$timeTracked['user_id'].' id'.$timeTracked['id']);
                }


                $page++;

            } while(count($result) === 100);
        }




        print PHP_EOL;
        print '======================================================'.PHP_EOL;
        print "Desde $from hasta $to".PHP_EOL;
        print '======================================================'.PHP_EOL;
        print 'Total Hours '.$totalHours.PHP_EOL;
        print 'Total Tasks '.$totalTasks.PHP_EOL;


        print '======================================================'.PHP_EOL;
        print 'User Stories'.PHP_EOL;
        print '======================================================'.PHP_EOL;

        $this->typePercentages = [];// Type => hours; count; hours/percentage (sobre total stories hours); count/percentage (sobre total stories)
        ksort($this->userStories);
        $userStoriesTotalHours = 0;
        print 'ticket,total_hours, hours, tasks, status, type'.PHP_EOL;
        foreach ($this->userStories as $id => $storyData) {
            print $storyData['description'].', '.$storyData['total_invested_hours'].', '.$storyData['hours'].', '.$storyData['tasks'].', '.$storyData['status'].', '.$storyData['type'].PHP_EOL;
            $userStoriesTotalHours += $storyData['hours'];

            self::_keepTrackOfTypeData($storyData);
        }

        print PHP_EOL.'User Stories Total Hours: '.$userStoriesTotalHours.PHP_EOL;
        //print print_r($this->userStories, 1).PHP_EOL;
        //print print_r($this->noUserStories, 1).PHP_EOL;

        $noUserStoriesTotalHours = 0;
        if (count($this->noUserStories)) {
            print '======================================================'.PHP_EOL;
            print 'Tasks (not a User Story)'.PHP_EOL;
            print '======================================================'.PHP_EOL;
            ksort($this->noUserStories);
            print 'ticket,total_hours, hours, tasks, status'.PHP_EOL;
            foreach ($this->noUserStories as $id => $ticketData) {
                print $ticketData['description'].', '.$ticketData['total_invested_hours'].', '.$ticketData['hours'].', '.$ticketData['tasks'].', '.$ticketData['status'].PHP_EOL;
                $noUserStoriesTotalHours += $ticketData['hours'];
            }

            print PHP_EOL.'Tasks (not a User Story) Total Hours: '.$noUserStoriesTotalHours.PHP_EOL;
        }



        $withoutTicketTotalHours = 0;
        if (count($this->withoutTicket)) {
            //print print_r($this->withoutTicket, 1).PHP_EOL;

            print '======================================================'.PHP_EOL;
            print ' Tracked time without ticket'.PHP_EOL;
            print '======================================================'.PHP_EOL;
            print 'username, hours, tasks'.PHP_EOL;

            foreach ($this->withoutTicket as $userId => $data) {
                $username = (array_key_exists($userId, $users))?$users[$userId]: $userId;
                print $username.','.$data['hours'].','.$data['tasks'].PHP_EOL;
                $withoutTicketTotalHours += $data['hours'];
            }

            print PHP_EOL.'Tracked time without ticket Total Hours: '.$withoutTicketTotalHours.PHP_EOL;
        }


        print PHP_EOL;
        foreach ($this->typePercentages as $type => $typeData) {
            $typeTotalHours = $this->typePercentages[$type]['total_hours'];
            $typeTotalTickets = $this->typePercentages[$type]['total_tickets'];

            $typeTotalHoursPercentage =  ($userStoriesTotalHours != 0)?number_format(($typeTotalHours / $userStoriesTotalHours) * 100, 2) : 0;
            $typeTotalTicketsPercentage = (count($this->userStories) != 0)?  number_format(($typeTotalTickets/ count($this->userStories)) * 100, 2) : 0;

            print str_pad($type, 20)."\t\t".$typeTotalTickets.' user stories ('.$typeTotalTicketsPercentage.'%)'.PHP_EOL;
            print str_pad('', 20)."\t\t".$typeTotalHours.' horas ('.$typeTotalHoursPercentage.'%)'.PHP_EOL;
        }

        print PHP_EOL.'Total Tracked Time '.$totalHours.' vs Total Hours per section (all added) '.($userStoriesTotalHours+$noUserStoriesTotalHours+$withoutTicketTotalHours).PHP_EOL;

        $endTime = time();
        $minutes = round(($endTime - $startTime)/60, 2);
        print "Execution time ". $minutes ." minutes".PHP_EOL;
        print 'Total APi calls '.$this->apicalls.PHP_EOL;
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
        $assemblaGateway = new AssemblaGateway();
        /** @var TicketDto $ticketDto */
        $ticketDto = $assemblaGateway->getTicketBySpaceAndNumber($space, $ticketNumber);
        $this->apicalls++;

        if ($ticketDto !== false) {
            $ticketId = $ticketDto->getTicketAssemblaId();
            $ticketData = [
                'description'           => $ticketDto->getDescription(),
                'total_invested_hours'  => $ticketDto->getTotalInvestedHours(),
                'status'                => $ticketDto->getStatus(),
                'hours'                 => 0,
                'tasks'                 => 0,
                'type'                  => ($ticketDto->getType())? $ticketDto->getType() : 'Empty'
            ];
            $this->ticketsApiData[$ticketId] = [
                'is_story' => $ticketDto->isStory(),
                'description' => $ticketDto->getDescription(),
                'total_invested_hours' => $ticketDto->getTotalInvestedHours()
            ];


            if ($ticketDto->isStory() && !array_key_exists($ticketId, $this->userStories)) {
                $this->userStories[$ticketId] = $ticketData;
            } else {
                $userStoryId = $this->_retrieveTicketAssociation($space, $ticketNumber);
                if ($userStoryId !== false) {
                    if (!array_key_exists($userStoryId, $this->userStories)) {
                        $response = AssemblaRequest::get("spaces/{$space}/tickets/id/{$userStoryId}");//TODO create a function in AssemblaGateway for this endpoint
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
                        $this->noUserStories[$ticketId] = $ticketData;
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
        $type = $storyData['type'];

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
        $assemblaGateway = new AssemblaGateway();
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

    /** @test  this tests will retrieve the tracked time
     * //TODO esta es la tarea que ejecuto para tener las horas del equipo discrimiando por: documentar y clasificar (Reportes)
     *
     * - Proyecto
     * - Team member
     */
    function can_get_projects_time_weekly_by_user()
    {
        $teamMembers = [
            // 'd8r95QiVer6zj-aH8tHBnc' => 'Franco Aller',
            'cvixt811Gr4PBcacwqjQYw' => 'Nicolás Peric',
            'dNWJBO9war45rbacwqjQXA' => 'Elina Perez',
            'cc2NS0ZTSr4RS_acwqjQYw' => 'Jonatan Mayorano',
            // 'ajLyFEiVir6A3ccK-zJOy8' => 'Federico Ackerley',
            'buOwlo1uer45NdacwqjQWU' => 'Martín Granate',
            'aAbtrS7fKr6y_dcP_HzTya' => 'Barbara Irizaga',
            'dDTcSCiNSr64ojaIC_Qgzw' => 'Julián García',
        ];
        //TODO add logic to remove hardcoded users
        $users = [
            'c5sp9uUXyr6Ok5cK-zJOy8' => 'Julieta Pisani',
            'd8r95QiVer6zj-aH8tHBnc' => 'Franco Aller',
            'cvixt811Gr4PBcacwqjQYw' => 'Nicolás Peric',
            'aAbtrS7fKr6y_dcP_HzTya' => 'Barbara Irizaga',
            'dNWJBO9war45rbacwqjQXA' => 'Elina Perez',
            'cc2NS0ZTSr4RS_acwqjQYw' => 'Jonatan Mayorano',
            'dBYqHcg2Cr5PRcdmr6CpXy' => 'Santiago Tolosa',
            'brVttgsFOr543cdmr6QqzO' => 'Emanuel Arcos',
            'buOwlo1uer45NdacwqjQWU' => 'Martín Granate',
            'ajLyFEiVir6A3ccK-zJOy8' => 'Federico Ackerley',
            'athUCe0pCr5OFcacwqEsg8' => 'Mariano Zunini',
            'c6u2Cuuu4r6AFdbK8JiBFu' => 'Martin Perrotta',
            'aW_vfY1FGr6ioeaH8tHBnc' => 'Brenda Herrada',
            'aVzzeMlw0r6RhdaIC_Qgzw' => 'Nicolas Lavaggi',
            'aSD9Sgwzqr6OoBaH8tHBnc' => 'Ezequiel Alvian',
            'aDiA_Cb2Wr6iNcacwqjQYw' => 'Matias Rodriguez',
            'a5Uwc0GEyr45yTacwqEsg8' => 'Alejandro Borria',
            'bYoBk2IxKr5PNcdmr6QqzO' => 'Diego Piu',
            'b_V2Si_JCr6lldaH8tHBnc' => 'Matias Wagner',
            'dUHuyGkPGr44k-acwqEsg8' => 'Pedro Rigoli',
            'ddsWca79Wr44oYacwqjQXA' => 'Nicolas Alejandro Gandara',
            'dzBlqaLhKr5O16acwqEsg8' => 'Esteban Campos',
            'arrHT2RRer54rQdmr6QqzO' => 'Mariana Rodriguez',
            'cC7iHg0oSr6BBdaIC_Qgzw' => 'Ariel Benítez',
            'blHTwYuger44kaacwqjQYw' => 'Ezequiel Gomez',
            'cxNMFQ6Fer5zdcacwqjQYw' => 'Andres Campos',
            'c1n5gcr0Or6B_cbQarZsNG' => 'Delfina Labate',
            'dDTcSCiNSr64ojaIC_Qgzw' => 'Julián García'
        ];
        $projects = [
            'AD-Barbieri' => 'ce1LaCpjCr6O96aH8tHBnc',
            'canaldeautopartes' => 'dpT43eCVCr54kBacwqjQYw',
            'cemaco' => 'dKs4GwzB8r4Pz7acwqjQYw',
            'pinturerias-rex' => 'atJlRad84r55JcacwqjQXA',
            'sommiercenter' => 'dxD3_KI5ur6ky6dmr6QqzO',
            'summa-internal-projects' => 'bPFF_gQfWr4PjCacwqjQWU',
            'Grupo-Grassi' => 'dTomygY3Gr6P7dbK8JiBFu'
        ];

        $hours = array();
        $totalHours = 0;
        $totalTasks = 0;
        $projectHours = array();

        $from = '2020/12/01 00:00';
        $to = '2020/12/31 23:59';
        foreach ($projects as $wikiname => $spaceId) {

            $page = 1;
            do {
                $queryParams = [
                    'spaces' => $spaceId,
                    'from' => $from,
                    'to' => $to,
                    'page' => $page,
                ];
                $response = AssemblaRequest::get("tasks", $queryParams);
                $result = json_decode($response->getBody()->getContents(), 1);
                if (!is_array($result)) {
                    break;
                }

                foreach ($result as $timeTracked) {
                    if ($wikiname == 'summa-internal-projects') {
                        if (!array_key_exists($timeTracked['user_id'], $teamMembers)) {
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

        print PHP_EOL;
        print '======================================================'.PHP_EOL;
        print "Desde $from hasta $to".PHP_EOL;
        print '======================================================'.PHP_EOL;
        print 'Total Hours '.$totalHours.PHP_EOL;
        print 'Total Tasks '.$totalTasks.PHP_EOL;


        foreach ($projectHours as $wikiname => $projectData) {
            print '======================================================'.PHP_EOL;
            print "\t".$wikiname.PHP_EOL;
            print '======================================================'.PHP_EOL;
            $totalHours = 0;
            $totalTasks = 0;
            foreach ($projectData as $userId => $userHours) {
                $totalHours += $userHours['hours'];
                $totalTasks += $userHours['tasks'];

                $userName = (array_key_exists($userId, $users))?$users[$userId]: $userId;
                print str_pad($userName, 20)."\t".str_pad($userHours['tasks']. " tasks", 9) ." \t".$userHours['hours']. ' hours'.PHP_EOL;
            }
            print ''.PHP_EOL;
            print 'Project total hours '.$totalHours.' in '.$totalTasks.' tasks'.PHP_EOL;
        }

        print PHP_EOL;
        print '======================================================'.PHP_EOL;

        foreach ($hours as $userId => $hoursData) {
            $userName = (array_key_exists($userId, $users))?$users[$userId]: $userId;
            print str_pad($userName, 20)."\t".str_pad($hoursData['tasks']. " tasks", 9). " \t".$hoursData['hours']. ' hours'.PHP_EOL;
        }

    }



/*
99 => array:12 [
"id" => 23605573
"description" => "Category page: left-nav filters"
"url" => "/spaces/cemaco/tickets/77"
"hours" => "8.0"
"begin_at" => "2013-11-07T20:29:29.000Z"
"end_at" => "2013-11-07T20:29:29.000Z"
"space_id" => "dKs4GwzB8r4Pz7acwqjQYw"
"ticket_number" => 77
"ticket_id" => 69478723
"user_id" => "dmax02RC4r4OkUacwqjQWU"
"created_at" => "2013-11-07T20:29:29.000Z"
"updated_at" => "2013-11-07T20:29:29.000Z"*/
}
