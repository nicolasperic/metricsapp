<?php

namespace App\Integration;

use GuzzleHttp\Exception\ClientException;

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

    public function validateTicketExistsBySpaceAndNumber($space, $ticketNumber, $validateData = null)
    {
        try {
            $response = self::getTicketBySpaceAndNumber($space, $ticketNumber);
            if ($response->getStatusCode() === 200) {
                $bodyContents = json_decode($response->getBody()->getContents(), 1);
                if ($validateData !== null) {
                    foreach ($validateData as $input => $value) {
                        if ($bodyContents[$input] != $value) {
                            return false;
                        }
                    }
                    return $bodyContents;
                } else {
                    return $bodyContents;
                }
            }
        } catch (ClientException $exception) {
            return false;
        }
    }

    public function getTicketAssociationsBySpaceAndNumber($space, $ticketNumber)
    {
        return AssemblaRequest::get("spaces/{$space}/tickets/{$ticketNumber}/ticket_associations");
    }

    /**
     * @param $space
     * @param $milestoneId
     * @param $queryParams array
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getTicketsForMilestone($space, $milestoneId, $queryParams = [])
    {
        return AssemblaRequest::get("spaces/{$space}/tickets/milestone/{$milestoneId}", $queryParams);
    }

    /**
     * @param $queryParams
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getTrackedTimeForTicket($queryParams)
    {
        return AssemblaRequest::get("tasks", $queryParams);
    }

    public function getMilestonesForSpace($space)
    {
        return AssemblaRequest::get("spaces/{$space}/milestones");
    }
}
