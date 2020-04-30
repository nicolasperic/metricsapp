<?php

namespace App\Integration;

class AssemblaGateway
{
    /** relationship is 5 - ticket2 is story and ticket1 is subtask of the story */
    const STORY_RELATION = 5;

    public function getAuthenticatedUser()
    {
        return AssemblaRequest::get('user');
    }

    public function getSpaces()
    {
        return AssemblaRequest::get('spaces');
    }

    public function getSpaceUsers($space)
    {
        return AssemblaRequest::get("spaces/{$space}/users");
    }

    public function getTicketBySpaceAndNumber($space, $ticketNumber)
    {
        return AssemblaRequest::get("spaces/{$space}/tickets/{$ticketNumber}");

    }

    public function getTicketAssociationsBySpaceAndNumber($space, $ticketNumber)
    {
        return AssemblaRequest::get("spaces/{$space}/tickets/{$ticketNumber}/ticket_associations");
    }

    /**
     * @param $space
     * @param $milestoneId
     * @param $page
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getTicketsForMilestone($space, $milestoneId, $page = 1)
    {
        return AssemblaRequest::get("spaces/{$space}/tickets/milestone/{$milestoneId}", $page);
    }

    public function getMilestonesForSpace($space)
    {
        return AssemblaRequest::get("spaces/{$space}/milestones");
    }
}
