<?php

namespace Tests\Feature\Integration;

use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * @group reports
 */
class TrackedTimeByUserStoryTest
    extends TestCase
{


    /** test  this tests will retrieve the tracked time
     * Este approach del reporte de horas por US fue utilizando el endpoint de tickets!
     * Mala idea iterar tickes, se necesita levantar el tiempo trackeado y de ahí encontrar los tickets
     *
     * TODO asserts; o ver qué hace esta función y clasificar! documentar (Reportes)
     * @deprecated
     */
    function can_get_project_hs_per_us()
    {
        $this->loginWithAssemblaUser();
        $projects = [
            'AD-Barbieri' => 'ce1LaCpjCr6O96aH8tHBnc'
            //'sommiercenter' => 'dxD3_KI5ur6ky6dmr6QqzO',
            ];

        $page = 1;
        $totalHours = 0;
        $totalUS = 0;

        $us = array();
        $subtasks = array();
        do {
            $queryParams = [
                //'spaces' => 'AD-Barbieri',
                'spaces' => 'sommiercenter',
                'page'   => $page,
            ];
            $response = AssemblaRequest::get("spaces/sommiercenter/tickets", $queryParams, $this->user->assembla_key, $this->user->assembla_secret);
            $result = json_decode($response->getBody()->getContents(), 1);
            if (!is_array($result)) {
                break;
            }

            foreach ($result as $ticket) {
                dd($ticket);
                $date = Carbon::parse($ticket['created_on']);
                $may = Carbon::parse('2020-05-01 00:00:00');
                if ($date < $may) {
                    continue;
                }
                if ($ticket['is_story']) {
                    $totalHours += $ticket['total_invested_hours'];
                    $totalUS++;
                    //print $ticket['number'].' '.$ticket['summary'].','.''.$ticket['total_invested_hours'].PHP_EOL;
                    $us[intval($ticket['number'])] = array('summary' => $ticket['number'].' '.$ticket['summary'], 'hours' => $ticket['total_invested_hours']);
                } else {
                    $subtasks[intval($ticket['number'])] = array('summary' => $ticket['number'].' '.$ticket['summary'], 'hours' => $ticket['total_invested_hours']);
                }
                //dd($ticket);
            }
            $page++;

        } while(count($result) === 100);



        ksort($us);

        print PHP_EOL;
        foreach ($us as $number => $data) {
            print $data['summary'].','.$data['hours'].PHP_EOL;
        }

        print PHP_EOL.'Total US '.$totalUS.' hours '.$totalHours.PHP_EOL;


        print PHP_EOL;
        foreach ($subtasks as $number => $data) {
            print $data['summary'].','.$data['hours'].PHP_EOL;
        }
    }

    /** test
     * this is included on the us can_get_projects_time_by_user_story
     * tracked time without a ticket (assembla supports tracking time to a space without ticket info)
     *
     * //TODO asserts
     */
    function barbieri_horas_sinticket()
    {
        $this->loginWithAssemblaUser();
        $from = '2020/01/01 00:00';
        $to = '2020/05/30 23:59';

        $nullTicketHours = 0;
            $page = 1;
            do {
                $queryParams = [
                    'spaces' => 'ce1LaCpjCr6O96aH8tHBnc',
                    'from' => $from,
                    'to' => $to,
                    'page' => $page,
                ];
                $response = AssemblaRequest::get("tasks", $queryParams, $this->user->assembla_key, $this->user->assembla_secret);
                $result = json_decode($response->getBody()->getContents(), 1);

                if (!is_array($result)) {
                    break;
                }


                foreach ($result as $timeTracked) {
                    if ($timeTracked['ticket_id'] == null) {
                        $nullTicketHours += $timeTracked['hours'];
                    }


                }


                $page++;

            } while(count($result) === 100);

        print $nullTicketHours.' horas sin ticket'.PHP_EOL;
    }



    private function _retrieveAndSetTicketInformation($space, $ticketNumber, $ticketArray)
    {
        $user = $this->loginWithAssemblaUser();
        $assemblaGateway = new AssemblaGateway($user);
        /** @var TicketDto $ticketDto */
        $ticketDto = $assemblaGateway->getTicketBySpaceAndNumber($space, $ticketNumber);

        if ($ticketDto !== false) {
            $ticketArray[$ticketDto->getTicketAssemblaId()]['description'] = $ticketDto->getDescription();
            $ticketArray[$ticketDto->getTicketAssemblaId()]['total_invested_hours'] = $ticketDto->getTotalInvestedHours();
        }

        return $ticketArray;
    }


    private function _retrieveTicketAssociation($space, $ticketNumber)
    {
        $user = $this->loginWithAssemblaUser();
        $assemblaGateway = new AssemblaGateway($user);
        $ticketAssociations = $assemblaGateway->getTicketAssociationsBySpaceAndNumber($space, $ticketNumber);
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


    /** test  this tests will retrieve the tracked time by user story
     * TODO asserts y ver qué hace esta función; clasificarla y documentar (Reportes)
     */
    function can_get_projects_time_by_user_story()
    {
        $user = $this->loginWithAssemblaUser();
        $projects = [
            //'AD-Barbieri' => 'ce1LaCpjCr6O96aH8tHBnc',
            //'canaldeautopartes' => 'dpT43eCVCr54kBacwqjQYw',
            //'cemaco' => 'dKs4GwzB8r4Pz7acwqjQYw',
            //'pinturerias-rex' => 'atJlRad84r55JcacwqjQXA',
            //'sommiercenter' => 'dxD3_KI5ur6ky6dmr6QqzO'
            //'summa-internal-projects' => 'bPFF_gQfWr4PjCacwqjQWU'
        ];
        $userStories = [];//array para agrupar las horas de cada US > ticket_id > description (ticket_number + description ), total hours y total tasks
        $noUserStories = [];//array para agrupar las horas de tickets que no son US ni subtasks; ticket_id => desription, total hours, total tasks
        $withoutTicket = [];//array para agrupar las horas trackeadas sin ticket > user_id > hours y tasks

        $ticketAssociations = [];//array para mapear subtasks con user stories > subtask_id => user_story_id; de ser posible evitar algunas llamadas a la API
        //^ de no tener asociacion subtask_id -> false


        $totalHours = 0;
        $totalTasks = 0;


        $from = '2020/06/01 00:00';
        $to = '2020/06/30 23:59';
        foreach ($projects as $wikiname => $spaceId) {

            $page = 1;
            do {
                $queryParams = [
                    'spaces' => $spaceId,
                    'from' => $from,
                    'to' => $to,
                    'page' => $page,
                ];

                $response = AssemblaRequest::get("tasks", $queryParams, $this->user->assembla_key, $this->user->assembla_secret);
                $result = json_decode($response->getBody()->getContents(), 1);
                if (!is_array($result)) {
                    break;
                }

                foreach ($result as $timeTracked) {
                    if (trim($timeTracked['ticket_id']) != '') {//Tracked time to a ticket

                        if (array_key_exists($timeTracked['ticket_id'], $userStories)) {//it's a user story
                            $userStories[$timeTracked['ticket_id']]['hours'] += $timeTracked['hours'];
                            $userStories[$timeTracked['ticket_id']]['tasks'] += 1;
                        } else {//subtask

                            //subtask found on ticketAssociations, we can retrieve the user story ID without calling the API
                            if (array_key_exists($timeTracked['ticket_id'], $ticketAssociations) && $ticketAssociations[$timeTracked['ticket_id']] !== false) {
                                $userStories[$ticketAssociations[$timeTracked['ticket_id']]]['hours'] += $timeTracked['hours'];
                                $userStories[$ticketAssociations[$timeTracked['ticket_id']]]['tasks'] += 1;
                            } else {//subtask not found on ticketAssociations, need to retrieve related US from API if exists

                                $userStoryId = $this->_retrieveTicketAssociation($wikiname, $timeTracked['ticket_number']);
                                if ($userStoryId !== false) {
                                    if (!array_key_exists($userStoryId, $userStories)) {
                                        //TODO retrieve information from US
                                        $userStories = $this->_retrieveAndSetTicketInformation($wikiname, $userStoryId, $userStories);
                                        $userStories[$userStoryId]['hours'] = 0;
                                        $userStories[$userStoryId]['tasks'] = 0;

                                        dd($userStories);
                                    }
                                    $userStories[$userStoryId]['hours'] += $timeTracked['hours'];
                                    $userStories[$userStoryId]['tasks'] += 1;

                                    $ticketAssociations[$timeTracked['ticket_id']] = $userStoryId;//keeping track of subtask relation
                                } else {
                                    if (!array_key_exists($timeTracked['ticket_id'], $noUserStories)) {
                                        //TODO retrieve information from ticket
                                        $noUserStories = $this->_retrieveAndSetTicketInformation($wikiname, $timeTracked['ticket_number'], $noUserStories);
                                        $noUserStories[$timeTracked['ticket_id']]['hours'] = 0;
                                        $noUserStories[$timeTracked['ticket_id']]['tasks'] = 0;
                                    }

                                    $noUserStories[$timeTracked['ticket_id']]['hours'] += $timeTracked['hours'];
                                    $noUserStories[$timeTracked['ticket_id']]['tasks'] += 1;
                                }
                            }
                        }
                    } else {
                        //no ticket ID
                        if (!array_key_exists($timeTracked['user_id'], $withoutTicket)) {
                            $withoutTicket[$timeTracked['user_id']]['hours']  = 0;
                            $withoutTicket[$timeTracked['user_id']]['tasks'] = 0;
                        }

                        $withoutTicket[$timeTracked['user_id']]['hours']  += $timeTracked['hours'];
                        $withoutTicket[$timeTracked['user_id']]['tasks'] += 1;
                    }


                    $totalHours += $timeTracked['hours'];
                    $totalTasks += 1;
                }


                $page++;

            } while(count($result) === 100);
        }

        dd($userStories);



        print '======================================================'.PHP_EOL;
        print "Desde $from hasta $to".PHP_EOL;
        print '======================================================'.PHP_EOL;
        print 'Total Hours '.$totalHours.PHP_EOL;
        print 'Total Tasks '.$totalTasks.PHP_EOL;


    }


    /**
     * La idea de estes reporte es controlar los tickets de
     *  TL, PM, Meets, en los espacios que se quieran mostrando data
     * por espacio + total. Agrupando los tickets por tipo
     *
     * Requerido para el reporte:
     * - fecha desde y hasta para consultar las horas
     * - space + ticket_number ^ticket type (Ej: sommier, 2, Meet)
     *
     * Una única llamada con los ticket ID's (iterar páginas si hay muchas tasks)
     * API doc: https://api-docs.assembla.cc/content/ref/tasks_index.html
     *
     * test
     */
    function can_get_grouped_tickets_report()
    {
        $this->loginWithAssemblaUser();
        //barbieri,3 231480216
        //;cda,517 211385954
        //cemaco,2054; 179091233
        //;grassi,7 232101723
        //rex,66; 183344393
        //MEET: sommier,2; 209542284

        //Retro sommier,1564
        //Barbieri  ce1LaCpjCr6O96aH8tHBnc
        //CDA dpT43eCVCr54kBacwqjQYw
        //CEmaco dKs4GwzB8r4Pz7acwqjQYw
        //Grassi dTomygY3Gr6P7dbK8JiBFu
        //REX atJlRad84r55JcacwqjQXA
        //Sommier dxD3_KI5ur6ky6dmr6QqzO

        //$queryParams = ['ticket_ids' => 231730513];



        $users = [
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
            'c5sp9uUXyr6Ok5cK-zJOy8' => 'Julieta Pisani',
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
        $ticketHours = array();



        $page = 1;
        $from = '2020/11/01 00:00';
        $to = '2020/11/30 23:59';



        /**
        * MEETS
        * SC 2 209542284
        * REX 66 183344393
        * CEMACO 2054 179091233
        * CDA 517 ID 211385954
        * GRASSI 7 232101723
        * BARBIERI 3 231480216
        * TL
        * REX 38    ID 181720573
        * SC 964 ID 231534733
        * GRASSI 11 ID 232105800
        * CEMACO 1249 ID 147514993//
        * BARBIERI 4 ID 231563716
        * CDA 894 ID 231626482
         */
        $meetingTickets = [231480216,211385954,179091233,232101723,183344393,209542284];
        $tlTickets = [181720573,231534733,232105800,147514993,231563716,231626482];
        do {
            $queryParams = [
                'ticket_ids[]' => $meetingTickets,//$tlTickets,//$meetingTickets
                'from' => $from,
                'to' => $to,
                'page' => $page,
            ];
            $response = AssemblaRequest::getMultiple("tasks", $queryParams, $this->user->assembla_key, $this->user->assembla_secret);
            $result = json_decode($response->getBody()->getContents(), 1);

            //dd($result);

            if (!is_array($result)) {
                break;
            }

            foreach ($result as $timeTracked) {

                if (!array_key_exists($timeTracked['user_id'], $hours)) {
                    $hours[$timeTracked['user_id']]['hours']  = 0;
                    $hours[$timeTracked['user_id']]['tasks'] = 0;
                }



                $hours[$timeTracked['user_id']]['hours'] += $timeTracked['hours'];
                $hours[$timeTracked['user_id']]['tasks'] += 1;

                if (!array_key_exists($timeTracked['ticket_id'], $ticketHours)) {
                    $ticketHours[$timeTracked['ticket_id']]['users']  = [];
                    $ticketHours[$timeTracked['ticket_id']]['hours']  = 0;
                    $ticketHours[$timeTracked['ticket_id']]['tasks'] = 0;
                    $space = explode('/',$timeTracked['url'])[2];
                    $ticketHours[$timeTracked['ticket_id']]['description'] = $space.' '. $timeTracked['ticket_number'] . ' ' . $timeTracked['description'];
                }

                $ticketHours[$timeTracked['ticket_id']]['hours'] += $timeTracked['hours'];
                $ticketHours[$timeTracked['ticket_id']]['tasks'] += 1;

                if (!array_key_exists($timeTracked['user_id'], $ticketHours[$timeTracked['ticket_id']]['users'])) {
                    $ticketHours[$timeTracked['ticket_id']]['users'][$timeTracked['user_id']]['hours']  = 0;
                    $ticketHours[$timeTracked['ticket_id']]['users'][$timeTracked['user_id']]['tasks'] = 0;
                }
                $ticketHours[$timeTracked['ticket_id']]['users'][$timeTracked['user_id']]['hours'] += $timeTracked['hours'];
                $ticketHours[$timeTracked['ticket_id']]['users'][$timeTracked['user_id']]['tasks'] += 1;



                $totalHours += $timeTracked['hours'];
                $totalTasks += 1;
            }


            $page++;

        } while(count($result) === 100);



        print PHP_EOL;
        print '======================================================'.PHP_EOL;
        print "Desde $from hasta $to".PHP_EOL;
        print '======================================================'.PHP_EOL;
        print 'Total Hours '.$totalHours.PHP_EOL;
        print 'Total Tasks '.$totalTasks.PHP_EOL;



        print PHP_EOL;
        print '======================================================'.PHP_EOL;
        print 'Horas por usuario'.PHP_EOL;
        print '======================================================'.PHP_EOL;

        foreach ($hours as $userId => $hoursData) {
            $userName = (array_key_exists($userId, $users))?$users[$userId]: $userId;
            print str_pad($userName, 20)."\t".str_pad($hoursData['tasks']. " tasks", 9). " \t".$hoursData['hours']. ' hours'.PHP_EOL;
        }

        print PHP_EOL;
        print '======================================================'.PHP_EOL;
        print 'Horas por ticket'.PHP_EOL;
        print '======================================================'.PHP_EOL;

        foreach ($ticketHours as $ticketId => $hoursData) {
            print $hoursData['description']." ".$hoursData['tasks']. " tasks". " ".$hoursData['hours']. ' hours'.PHP_EOL;
            foreach ($hoursData['users'] as $userId => $userHoursData) {
                $userName = (array_key_exists($userId, $users))?$users[$userId]: $userId;
                print "\t".str_pad($userName, 20)."\t".str_pad($userHoursData['tasks']. " tasks", 9). " \t".$userHoursData['hours']. ' hours'.PHP_EOL;
            }
        }
        //print count($result).' '.$page.PHP_EOL;








        //        $queryString = 'cars[]=Saab&cars[]=Audi';
//        parse_str($queryString, $output);
//        $query = ['cars' => ['Saab', 'Audi']];
//        dd($query);
//
        /*
         * 99 => array:12 [
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
    "updated_at" => "2013-11-07T20:29:29.000Z"
  ]
         */

        //$queryParams = ['ticket_ids' => '231795041', 'ticket_ids' => '231806226', 'ticket_ids' => '231804182', 'ticket_ids' => '231801792', 'ticket_ids' => '231797900', 'ticket_ids' => '231795044'];
        //story 231795041





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
