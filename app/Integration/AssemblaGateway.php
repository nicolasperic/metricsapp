<?php

namespace App\Integration;

use App\Dto\ProjectDto;
use App\Dto\TicketDto;
use App\Dto\TicketTimeDto;
use GuzzleHttp\Exception\ClientException;

class AssemblaGateway
{
    /** relationship is 5 - ticket2 is story and ticket1 is subtask of the story */
    const STORY_RELATION = 5;

    /**
     * Returns currently authenticated user.
     * https://api-docs.assembla.cc/content/ref/user_show.html
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getAuthenticatedUser()
    {
        return AssemblaRequest::get('user');
    }

    /**
     * Returns user profile.
     * https://api-docs.assembla.cc/content/ref/user_show.html
     *
     * @param $userId
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUser($userId)
    {
        return AssemblaRequest::get("users/{$userId}");
    }

    /**
     * Get list of spaces user is participating to
     * https://api-docs.assembla.cc/content/ref/spaces_index.html
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getSpaces()
    {
        $spaces = false;
        $response = AssemblaRequest::get("spaces");
        if ($response->getStatusCode() == 200) {
            $spaces = [];
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $spaceData) {
                $spaces[] = new ProjectDto($spaceData);
            }
        }

        return $spaces;
    }

    /**
     * Returns users for a specified space.
     * https://api-docs.assembla.cc/content/ref/space_users.html
     *
     * @param $space
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getSpaceUsers($space)
    {//TODO update this an all requests so they return DTO's instead of a response
        return AssemblaRequest::get("spaces/{$space}/users");
    }

    /**
     * Returns a ticket by a ticket number.
     * https://api-docs.assembla.cc/content/ref/tickets_show.html
     *
     * @param $space
     * @param $ticketNumber
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getTicketBySpaceAndNumber($space, $ticketNumber)
    {
        return AssemblaRequest::get("spaces/{$space}/tickets/{$ticketNumber}");

    }

    /**
     * Returns a list of ticket associations
     * https://api-docs.assembla.cc/content/ref/ticket_associations_index.html
     *
     * @param $space
     * @param $ticketNumber
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getTicketAssociationsBySpaceAndNumber($space, $ticketNumber)
    {
        return AssemblaRequest::get("spaces/{$space}/tickets/{$ticketNumber}/ticket_associations");
    }

    /**
     * Get the list of tickets for a milestone
     * https://api-docs.assembla.cc/content/ref/milestone_tickets.html
     *
     * @param $space
     * @param $milestoneId
     * @param $queryParams array
     *
     * @return array|bool array of tickets or false if request to API is not 200
     */
    public function getTicketsForMilestone($space, $milestoneId, $queryParams = [])
    {
        $tickets = false;
        $response = AssemblaRequest::get("spaces/{$space}/tickets/milestone/{$milestoneId}", $queryParams);
        if ($response->getStatusCode() == 200) {
            $tickets = [];
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $ticketData) {
                $tickets[] = new TicketDto($ticketData);
            }
        }


        //return AssemblaRequest::get("spaces/{$space}/tickets/milestone/{$milestoneId}", $queryParams);
        return $tickets;
    }

    /**
     * Returns a paginated list of tasks. Pages are default to 25 tasks.
     * https://api-docs.assembla.cc/content/ref/tasks_index.html
     *
     * @param $queryParams
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getTrackedTimeForTicket($queryParams)
    {

        $tasks = false;
        $response = AssemblaRequest::get("tasks", $queryParams);
        if ($response->getStatusCode() == 200) {
            $tasks = [];
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $trackedTimeData) {
                $tasks[] = new TicketTimeDto($trackedTimeData);
            }
        }

        return $tasks;
    }

    /**
     * Returns a list of paginated upcoming milestones. Pages are defaulted to 10 milestones.
     * https://api-docs.assembla.cc/content/ref/milestones_index.html
     *
     * @param $space
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getMilestonesForSpace($space)
    {
        return AssemblaRequest::get("spaces/{$space}/milestones");
    }

    /**
     * This function is used to validate if a ticket exists on a given space. If set $validateData array will allow to
     * us to also validate any ticket field i.e if is a subtask ['is_story' => false] or a story  ['is_story' => true]
     *
     * @param      $space
     * @param      $ticketNumber
     * @param null $validateData
     *
     * @return bool|mixed
     */
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
}
