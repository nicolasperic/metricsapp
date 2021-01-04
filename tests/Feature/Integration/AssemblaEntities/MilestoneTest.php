<?php

namespace Tests\Feature\Integration;

use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class MilestoneTest
    extends TestCase
{
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
            $tickets = $assemblaGateway->getTicketsForMilestone($space, $milestoneId, $queryParams);

            if ($tickets === false) {
                break;
            }
            /*print PHP_EOL.count($result).PHP_EOL;
            foreach ($result as $ticket) {
                $isStory = ($ticket['is_story'])? 'US' : 'T';
               print $isStory.'#'.$ticket['number'].' '.$ticket['summary'].PHP_EOL;
            }*/

            $queryParams['page'] = ++$page;
        } while(count($tickets) === AssemblaRequest::PER_PAGE);

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
    /** @test */
    function can_get_all_milestones_for_a_space()
    {
        $space = 'sommiercenter';

        $assemblaGateway = new AssemblaGateway();
        $sprints = $assemblaGateway->getMilestonesForSpace($space);



        foreach ($sprints as $sprint) {
            print PHP_EOL.print_r($sprint, 1).PHP_EOL;
        }

        print 'Found '. count($sprints).' milestones '.PHP_EOL;


    }
}
