<?php

namespace Tests\Feature\Integration;

use App\Dto\ProjectDto;
use App\Dto\TicketDto;
use App\Dto\TicketTimeDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class AssemblaGatewayTest
    extends TestCase
{
    /** @test */
    function can_retrieve_a_ticket_by_space_and_number()
    {
        $assemblaGateway = new AssemblaGateway();

        $response = $assemblaGateway->getTicketBySpaceAndNumber('sommiercenter', '1022');
        $bodyContents = json_decode($response->getBody()->getContents(), 1);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1022, $bodyContents['number']);
        $this->assertEquals('[US] MSI Estrategia de Rollback', $bodyContents['summary']);
        $this->assertEquals('Sommier Center', $bodyContents['space_name']);

    }

    /** @test */
    function can_validate_a_ticket_exists_by_space_and_number()
    {
        $assemblaGateway = new AssemblaGateway();
        $existingTicket = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '1022');
        $notExistingTicket = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '12341234');

        $this->assertEquals(1022, $existingTicket['number']);
        $this->assertEquals(false, $notExistingTicket);
    }

    /** @test */
    function can_validate_a_ticket_exists_and_data_matches_by_space_and_number()
    {
        $assemblaGateway = new AssemblaGateway();

        $existingTicketSubtask = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '1023', ['is_story' => false]);
        $existingTicketUS = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '1022', ['is_story' => true]);
        $existingTicketNotUS = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '1024', ['is_story' => true]);

        $this->assertEquals(1023, $existingTicketSubtask['number']);
        $this->assertEquals(false, $existingTicketSubtask['is_story']);
        $this->assertEquals(1022, $existingTicketUS['number']);
        $this->assertEquals(true, $existingTicketUS['is_story']);
        $this->assertEquals(false, $existingTicketNotUS);
    }

    /** @test */
    function can_retrieve_a_ticket_and_use_a_dto()
    {
        $assemblaGateway = new AssemblaGateway();

        $response = $assemblaGateway->getTicketBySpaceAndNumber('sommiercenter', '1198');
        $responseData = json_decode($response->getBody()->getContents(), 1);

        $ticketDto = new TicketDto($responseData);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1198, $ticketDto->getNumber());
        $this->assertEquals('[US] Deploy a producción 09/04/2020 tag 1.1.28', $ticketDto->getSummary());
        $this->assertEquals('Sommier Center', $ticketDto->getSpaceName());
    }

    /** @test */
    function can_retrieve_a_ticket_associations_by_space_and_number()
    {
        $assemblaGateway = new AssemblaGateway();

        $response = $assemblaGateway->getTicketAssociationsBySpaceAndNumber('sommiercenter', '1117');
        $bodyContents = json_decode($response->getBody()->getContents(), 1);

        $subtaskId = '231717985';
        $userstoryId = '231438936';
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($subtaskId, $bodyContents[0]['ticket1_id']);
        $this->assertEquals($userstoryId, $bodyContents[0]['ticket2_id']);
        $this->assertEquals(AssemblaGateway::STORY_RELATION, $bodyContents[0]['relationship']);
    }

    //TODO assets tarea
    function can_list_all_spaces()
    {
        $assemblaGateway = new AssemblaGateway();
        $response = $assemblaGateway->getSpaces();
        $result = json_decode($response->getBody()->getContents(), 1);
        foreach ($result as $spaceData) {
            $projectDto = new ProjectDto($spaceData);
            print PHP_EOL.$projectDto->toString().PHP_EOL;
        }

    }

    /** @test */
    function can_get_authenticated_user()
    {
        $assemblaGateway = new AssemblaGateway();
        $response = $assemblaGateway->getAuthenticatedUser();
        $result = json_decode($response->getBody()->getContents(), 1);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('nicoperic', $result['login']);
        $this->assertEquals('nperic@summasolutions.net', $result['email']);
        /*
         * TODO create assembla_user table with assembla_id, login, name, picture, email
           array:7 [
          "id" => "cvixt811Gr4PBcacwqjQYw"
          "login" => "nicoperic"
          "name" => "Nicolás Peric"
          "picture" => "https://www.assembla.com/v1/users/cvixt811Gr4PBcacwqjQYw/picture"
          "email" => "nperic@summasolutions.net"
          "organization" => ""
          "phone" => ""
]
         */
    }

    /** @test */
    function can_get_authenticated_user_image()
    {

        $response = AssemblaRequest::get('users/dzBlqaLhKr5O16acwqEsg8');
        $result = json_decode($response->getBody()->getContents(), 1);
        //dd($result);
        $response = AssemblaRequest::get('users/cvixt811Gr4PBcacwqjQYw/picture');

        $authenticatedUserImagePath = $response->getHeaderLine('X-Guzzle-Redirect-History');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('https://s3.amazonaws.com/assembla-avatars/1e7f71fc/cvixt811Gr4PBcacwqjQYw:1571509138', $authenticatedUserImagePath);

    }

    /**  @test */
    function can_get_all_pages_of_tickets_for_a_milestone()
    {
        $space = 'sommiercenter';
        $milestoneId = 12982775;
        $assemblaGateway = new AssemblaGateway();
        $page = 1;

        $queryParams = [
            'page' => $page,
            'ticket_status' => 'all',
            'sort_by' => 'id',
            'sort_order' => 'desc'
        ];

        do {
            $response = $assemblaGateway->getTicketsForMilestone($space, $milestoneId, $queryParams);
            $result = json_decode($response->getBody()->getContents(), 1);

            $this->assertEquals(200, $response->getStatusCode());
            if ($response->getStatusCode() !== 200) {
                break;
            }
            /*print PHP_EOL.count($result).PHP_EOL;
            foreach ($result as $ticket) {
                $isStory = ($ticket['is_story'])? 'US' : 'T';
               print $isStory.'#'.$ticket['number'].' '.$ticket['summary'].PHP_EOL;
            }*/

            $queryParams['page'] = ++$page;
        } while(count($result) === AssemblaRequest::PER_PAGE);

        $this->assertEquals(4, $page);
    }

    //TODO assets tarea
    function can_get_a_list_of_tickets_for_a_milestone()
    {
        ///GET /v1/spaces/[space_id]/tickets/milestone/[milestone_id]	Get the list of tickets for a milestone
        $space = 'sommiercenter';
        $milestoneId = 12982775;
        $assemblaGateway = new AssemblaGateway();
        $response = $assemblaGateway->getTicketsForMilestone($space, $milestoneId);

        $result = json_decode($response->getBody()->getContents(), 1);
        //print print_r($result[0], 1);
        print PHP_EOL;
        foreach ($result as $ticket) {


            $complexity = '';
            if (array_key_exists('custom_fields', $ticket) && is_array($ticket['custom_fields'])) {
                $complexity = $ticket['custom_fields']['Complexity'];
            }

            print '#'.$ticket['number'].' '.$ticket['summary'].' '.$complexity.PHP_EOL;
        }

        return;
       // dd(count($result));

        $this->assertEquals(200, $response->getStatusCode());
    }


    //TODO assets tarea
    function can_get_all_milestones_for_a_space()
    {
        $space = 'sommiercenter';

        $assemblaGateway = new AssemblaGateway();
        $response = $assemblaGateway->getMilestonesForSpace($space);

        $result = json_decode($response->getBody()->getContents(), 1);

        foreach ($result as $milestone) {
            print PHP_EOL.print_r($milestone, 1).PHP_EOL;
        }

        print 'Found '. count($result).' milestones '.PHP_EOL;


    }



    /** test  this tests will retrieve the tracked time
     TODO asserts y ver qué hace esta tarea
     */
    function can_retrieve_tasks()
    {

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

        $queryParams = ['ticket_ids' => ['231806226','231804182']];
        //dd($queryParams);
        //$queryParams = ['ticket_ids' => '231804182'];
        $page = 1;

        $hours = array();
        $totalHours = 0;
        $totalTasks = 0;
        do {
            $queryParams = [
                'spaces' => 'dxD3_KI5ur6ky6dmr6QqzO',
                'from' => '2020/04/01 00:00',
                'to' => '2020/04/30 23:59',
                'page' => $page,
            ];
            $response = AssemblaRequest::get("tasks", $queryParams);
            $result = json_decode($response->getBody()->getContents(), 1);
            print count($result).' '.$page.PHP_EOL;

            foreach ($result as $timeTracked) {
                if (!array_key_exists($timeTracked['user_id'], $hours)) {
                    $hours[$timeTracked['user_id']]['hours']  = 0;
                    $hours[$timeTracked['user_id']]['tasks'] = 0;
                }
                $hours[$timeTracked['user_id']]['hours'] += $timeTracked['hours'];
                $hours[$timeTracked['user_id']]['tasks'] += 1;
                $totalHours += $timeTracked['hours'];
                $totalTasks += 1;
            }


            $page++;

        } while(count($result) === 100);

        print 'Total Hours '.$totalHours.PHP_EOL;
        print 'Total Tasks '.$totalTasks.PHP_EOL;
        dd($hours);

    }

    /** @test */
    function can_retrieve_tracked_time_and_use_a_dto()
    {
        $queryParams = ['ticket_ids' => '231804182'];
        $response = AssemblaRequest::get("tasks", $queryParams);
        $result = json_decode($response->getBody()->getContents(), 1);


        $ticketTimeDto = new TicketTimeDto($result[0]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1289, $ticketTimeDto->getTicketNumber());
        $this->assertEquals('[T][MSI] Deshabilitar Reservations', $ticketTimeDto->getDescription());
        $this->assertEquals(0.5, $ticketTimeDto->getHours());
    }

    /**
     * TODO esta tarea tiene la funcionalidad para trackear time; si hacemos el Tracking esto es vital
     */
    function can_track_time()
    {
        $params = ['user_task' => [
            'space_id' => 'dxD3_KI5ur6ky6dmr6QqzO',//sommiercenter
            'ticket_number' => '1291',
            'user_id' => 'cvixt811Gr4PBcacwqjQYw',
            'hours' => 1,
            'description' => 'Tracking hours for testing purposes',
            'begin_at' => '2020-04-26T23:37:00.000Z',
            'end_at' => '2020-04-26T23:37:00.000Z',
        ]];
        //$response = AssemblaRequest::post("tasks", $params);
        //$result = json_decode($response->getBody()->getContents(), 1);
        //dd($result);
    }
    /*
     *
    97 => array:12 [
    "id" => 105009667
    "description" => "[Global] SommierCenter - Team Meetings"
    "url" => "/spaces/sommiercenter/tickets/2"
    "hours" => "1.5"
    "begin_at" => "2020-04-08T23:39:00.000Z"
    "end_at" => "2020-04-08T23:39:00.000Z"
    "space_id" => "dxD3_KI5ur6ky6dmr6QqzO"
    "ticket_number" => 2
    "ticket_id" => 209542284
    "user_id" => "d8r95QiVer6zj-aH8tHBnc"
    "created_at" => "2020-04-21T23:39:27.000Z"
    "updated_at" => "2020-04-22T00:29:46.000Z"
  ]
     */


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
        ];
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
            'summa-internal-projects' => 'bPFF_gQfWr4PjCacwqjQWU'
        ];

        $hours = array();
        $totalHours = 0;
        $totalTasks = 0;
        $projectHours = array();

        $from = '2020/07/20 00:00';
        $to = '2020/07/26 23:59';
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

    /** test  this tests will retrieve the tracked time
     * TODO asserts; o ver qué hace esta función y clasificar! documentar (Reportes)
     */
    function can_get_project_hs_per_us()
    {
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
            $response = AssemblaRequest::get("spaces/sommiercenter/tickets", $queryParams);
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
     * //TODO asserts
     */
    function barbieri_horas_sinticket()
    {
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
                $response = AssemblaRequest::get("tasks", $queryParams);
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
        $assemblaGateway = new AssemblaGateway();
        $response = $assemblaGateway->getTicketBySpaceAndNumber($space, $ticketNumber);

        if ($response->getStatusCode() == 200) {
            $bodyContents = json_decode($response->getBody()->getContents(), 1);

            $ticketArray[$bodyContents['id']]['description'] = $bodyContents['number'].' '.$bodyContents['summary'];
            $ticketArray[$bodyContents['id']]['total_invested_hours'] = $bodyContents['total_invested_hours'];
        }

        return $ticketArray;
    }


    private function _retrieveTicketAssociation($space, $ticketNumber)
    {
        $assemblaGateway = new AssemblaGateway();
        $response = $assemblaGateway->getTicketAssociationsBySpaceAndNumber($space, $ticketNumber);
        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $association) {
                if ($association['relationship'] === AssemblaGateway::STORY_RELATION) {
                    //$subtaskId = $association['ticket1_id'];
                    return  $association['ticket2_id'];//returning user story ID
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

                $response = AssemblaRequest::get("tasks", $queryParams);
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
