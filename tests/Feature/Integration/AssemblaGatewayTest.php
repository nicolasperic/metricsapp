<?php

namespace Tests\Feature\Integration;

use App\Dto\ProjectDto;
use App\Dto\TicketDto;
use App\Dto\TicketTimeDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class AssemblaGatewayTest
    extends TestCase
{
    use RefreshDatabase;

    /**
     * TODO define assets for can_list_all_spaces test
     */
    function can_list_all_spaces()
    {
        $user = $this->loginWithAssemblaUser();
        $assemblaGateway = new AssemblaGateway($user);
        $spaces = $assemblaGateway->getSpaces();

        if ($spaces) {
            foreach ($spaces as $projectDto) {
                print PHP_EOL.$projectDto->toString().PHP_EOL;
            }

        }

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
        $this->loginWithAssemblaUser();
        $queryParams = ['ticket_ids' => '231804182'];
        $response = AssemblaRequest::get("tasks", $this->user->assembla_key, $this->user->assembla_secret, $queryParams);
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

}
