<?php

namespace Tests\Feature\Integration;

use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class TicketTest
    extends TestCase
{
    use RefreshDatabase;
    /** @test */
    function can_retrieve_a_ticket_by_space_and_number()
    {
        $user = $this->loginWithAssemblaUser();
        $assemblaGateway = new AssemblaGateway($user);

        /** @var TicketDto $ticketDto */
        $ticketDto = $assemblaGateway->getTicketBySpaceAndNumber('sommiercenter', '1022');


        $this->assertEquals(1022, $ticketDto->getNumber());
        $this->assertEquals('[US] MSI Estrategia de Rollback', $ticketDto->getSummary());
        $this->assertEquals('Sommier Center', $ticketDto->getSpaceName());

    }

    /** @test */
    function can_validate_a_ticket_exists_by_space_and_number()
    {
        $user = $this->loginWithAssemblaUser();
        $assemblaGateway = new AssemblaGateway($user);
        /** @var TicketDto $existingTicket */
        $existingTicket = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '1022');
        $notExistingTicket = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '12341234');

        $this->assertEquals(1022, $existingTicket->getNumber());
        $this->assertEquals(false, $notExistingTicket);
    }

    /** @test */
    function can_validate_a_ticket_exists_and_data_matches_by_space_and_number()
    {
        $assemblaGateway = new AssemblaGateway($this->loginWithAssemblaUser());

        /** @var TicketDto $existingTicketSubtask */
        $existingTicketSubtask = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '1023', ['is_story' => false]);
        /** @var TicketDto $existingTicketUS */
        $existingTicketUS = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '1022', ['is_story' => true]);
        /** @var TicketDto $existingTicketNotUS */
        $existingTicketNotUS = $assemblaGateway->validateTicketExistsBySpaceAndNumber('sommiercenter', '1024', ['is_story' => true]);

        $this->assertEquals(1023, $existingTicketSubtask->getNumber());
        $this->assertEquals(false, $existingTicketSubtask->isStory());
        $this->assertEquals(1022, $existingTicketUS->getNumber());
        $this->assertEquals(true, $existingTicketUS->isStory());
        $this->assertEquals(false, $existingTicketNotUS);
    }

    /** @test */
    function can_retrieve_a_ticket_and_use_a_dto()
    {
        $assemblaGateway = new AssemblaGateway($this->loginWithAssemblaUser());

        $ticketDto = $assemblaGateway->getTicketBySpaceAndNumber('sommiercenter', '1578');

        $this->assertEquals(1578, $ticketDto->getNumber());
        $this->assertEquals('[US] Análisis Integración con Producteca', $ticketDto->getSummary());
        $this->assertEquals('Sommier Center', $ticketDto->getSpaceName());
        $this->assertEquals(5, $ticketDto->getEstimate());
        $this->assertEquals(0, $ticketDto->getComplexity());
        $this->assertEquals('Requirement', $ticketDto->getType());
    }

    /** @test */
    function can_retrieve_a_ticket_associations_by_space_and_number()
    {
        $assemblaGateway = new AssemblaGateway($this->loginWithAssemblaUser());

        $ticketAssociations = $assemblaGateway->getTicketAssociationsBySpaceAndNumber('sommiercenter', '1117');
        /** @var TicketAssociationDto $ticketAssociation */
        $ticketAssociation = $ticketAssociations[0];

        $subtaskId = '231717985';
        $userstoryId = '231438936';

        $this->assertEquals($subtaskId, $ticketAssociation->getTicket1Id());
        $this->assertEquals($userstoryId, $ticketAssociation->getTicket2Id());
        $this->assertEquals(AssemblaGateway::STORY_RELATION, $ticketAssociation->getRelationship());
    }


    /** test */
    function can_retrieve_a_ticket_tracked_time_by_space_and_number()
    {
        $user = $this->loginWithAssemblaUser();
        $assemblaGateway = new AssemblaGateway($user);

        $queryParams = [
            'ticket_ids' => '229233721',
            'from' => '2020/12/01 00:00',
            'to' => '2020/12/15 00:00',
            'page' => 1,
        ];
        $response = AssemblaRequest::get("tasks", $queryParams, $user->assembla_key, $user->assembla_secret);
        $content = json_decode($response->getBody()->getContents(), 1);
        dd($content);


        $ticketAssociations = $assemblaGateway->getTicketAssociationsBySpaceAndNumber('sommiercenter', '1117');
        /** @var TicketAssociationDto $ticketAssociation */
        $ticketAssociation = $ticketAssociations[0];

        $subtaskId = '231717985';
        $userstoryId = '231438936';

        $this->assertEquals($subtaskId, $ticketAssociation->getTicket1Id());
        $this->assertEquals($userstoryId, $ticketAssociation->getTicket2Id());
        $this->assertEquals(AssemblaGateway::STORY_RELATION, $ticketAssociation->getRelationship());
    }
}
