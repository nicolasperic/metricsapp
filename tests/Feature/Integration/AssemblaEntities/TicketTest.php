<?php

namespace Tests\Feature\Integration;

use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class TicketTest
    extends TestCase
{
    /** @test */
    function can_retrieve_a_ticket_by_space_and_number()
    {
        $assemblaGateway = new AssemblaGateway();

        $response = $assemblaGateway->getTicketBySpaceAndNumber('sommiercenter', '1022');
        //sommiercenter canaldeautopartes cemaco AD-Barbieri pinturerias-rex Grupo-Grassi
        //$response = $assemblaGateway->getTicketBySpaceAndNumber('Grupo-Grassi', '7');
        $bodyContents = json_decode($response->getBody()->getContents(), 1);
        //dd($bodyContents);

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

        $response = $assemblaGateway->getTicketBySpaceAndNumber('sommiercenter', '1578');
        $responseData = json_decode($response->getBody()->getContents(), 1);

        $ticketDto = new TicketDto($responseData);

        $this->assertEquals(200, $response->getStatusCode());
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
}
