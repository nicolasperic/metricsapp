<?php

namespace Tests\Feature\Integration;

use App\Dto\ProjectDto;
use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
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
        $response = AssemblaRequest::get('users/cvixt811Gr4PBcacwqjQYw/picture');

        $authenticatedUserImagePath = $response->getHeaderLine('X-Guzzle-Redirect-History');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('https://s3.amazonaws.com/assembla-avatars/1e7f71fc/cvixt811Gr4PBcacwqjQYw:1571509138', $authenticatedUserImagePath);

    }

    /**  TODO assets tarea */
    function can_get_a_all_pages_of_tickets_for_a_milestone()
    {
        $space = 'sommiercenter';
        $milestoneId = 12982775;
        $assemblaGateway = new AssemblaGateway();
        $page = 0;

        do {
            $response = $assemblaGateway->getTicketsForMilestone($space, $milestoneId, $page);
            $result = json_decode($response->getBody()->getContents(), 1);
            print PHP_EOL.count($result).PHP_EOL;
            foreach ($result as $ticket) {
                $isStory = ($ticket['is_story'])? 'US' : 'T';
                print $isStory.'#'.$ticket['number'].' '.$ticket['summary'].PHP_EOL;
            }
            $page++;
        } while(false && count($result) === 100);


        dd($page);
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



    function can_retrieve_space_tools()
    {
        //$response = AssemblaRequest::get("spaces/sommiercenter/space_tools");
        //$response = AssemblaRequest::get("spaces/sommiercenter/users");
        $response = AssemblaRequest::get("tasks");
        $result = json_decode($response->getBody()->getContents(), 1);
        dd($result);

    }

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
        $result = json_decode($response->getBody()->getContents(), 1);
        dd($result);
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
}
