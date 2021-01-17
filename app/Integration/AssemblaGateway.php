<?php

namespace App\Integration;

use App\Dto\AssemblaUserDto;
use App\Dto\ProjectDto;
use App\Dto\SprintDto;
use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Dto\TicketTimeDto;
use App\User;
use GuzzleHttp\Exception\ClientException;

class AssemblaGateway
{
    /** relationship is 5 - ticket2 is story and ticket1 is subtask of the story */
    const STORY_RELATION = 5;
    /**
     * @var User
     */
    private $user;

    function __construct(User $user)
    {

        $this->user = $user;
    }


    /**
     * Returns currently authenticated user.
     * https://api-docs.assembla.cc/content/ref/user_show.html
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getAuthenticatedUser()
    {
        $user = false;
        $response =  AssemblaRequest::get('user', $this->user->assembla_key, $this->user->assembla_secret);
        if ($response->getStatusCode() == 200) {
            $userData = json_decode($response->getBody()->getContents(), 1);
            $user = new AssemblaUserDto($userData);

        }

        return $user;
    }

    /**
     * Returns user image
     * https://api-docs.assembla.cc/content/ref/users_picture.html
     *
     * @param $userId
     *
     * @return string
     */
    public function getUserImage($userId)
    {
        $response = AssemblaRequest::get('users/'.$userId.'/picture', $this->user->assembla_key, $this->user->assembla_secret);

        return $response->getHeaderLine('X-Guzzle-Redirect-History');
    }

    /**
     * Returns user profile.
     * https://api-docs.assembla.cc/content/ref/user_show.html
     *
     * @param $userId
     *
     * @return AssemblaUserDto|bool //TODO update return documentation on all functions (no longer using ResponseInterface, using DTO)
     */
    public function getUser($userId)
    {
        $user = false;
        $response = AssemblaRequest::get("users/{$userId}", $this->user->assembla_key, $this->user->assembla_secret);
        if ($response->getStatusCode() == 200) {
            $userData = json_decode($response->getBody()->getContents(), 1);
            $user = new AssemblaUserDto($userData);
        }
        
        return $user;
    }

    /**
     * Get list of spaces user is participating to
     * https://api-docs.assembla.cc/content/ref/spaces_index.html
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getSpaces()
    {   //TODO spaces could be more than just one page, we need to update this function (and others) to continue asking for more pages
        $spaces = false;
        $response = AssemblaRequest::get("spaces", $this->user->assembla_key, $this->user->assembla_secret);
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
    {
        $spaceUsers = false;
        $response = AssemblaRequest::get("spaces/{$space}/users", $this->user->assembla_key, $this->user->assembla_secret);
        if ($response->getStatusCode() == 200) {
            $spaceUsers = [];
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $spaceUserData) {
                $spaceUsers[] = new AssemblaUserDto($spaceUserData);
            }
        }
        return $spaceUsers;
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
        $ticket = false;

        $response = AssemblaRequest::get("spaces/{$space}/tickets/{$ticketNumber}", $this->user->assembla_key, $this->user->assembla_secret);
        if ($response->getStatusCode() == 200) {
            $ticketData = json_decode($response->getBody()->getContents(), 1);
            $ticket = new TicketDto($ticketData);
        }

        //return AssemblaRequest::get("spaces/{$space}/tickets/{$ticketNumber}");
        return $ticket;
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
        $ticketAssociations = false;
        $response = AssemblaRequest::get("spaces/{$space}/tickets/{$ticketNumber}/ticket_associations", $this->user->assembla_key, $this->user->assembla_secret);
        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $associationData) {
                $ticketAssociations[] = new TicketAssociationDto($associationData);
            }
        }
        //print count($ticketAssociations).PHP_EOL;
        return $ticketAssociations;
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
        $response = AssemblaRequest::get("spaces/{$space}/tickets/milestone/{$milestoneId}", $this->user->assembla_key, $this->user->assembla_secret, $queryParams);
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
        //TODO generar nuevo getTrackedTime() para sobregarcargar en esta función

        $tasks = false;
        $response = AssemblaRequest::get("tasks", $this->user->assembla_key, $this->user->assembla_secret, $queryParams);
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
        $milestones = false;
        $response = AssemblaRequest::get("spaces/{$space}/milestones/all", $this->user->assembla_key, $this->user->assembla_secret);
        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), 1);

            foreach ($result as $milestoneData) {
                $milestones[] = new SprintDto($milestoneData);
            }
        }
        return $milestones;
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
            /** @var TicketDto $ticketDto */
            $ticketDto = self::getTicketBySpaceAndNumber($space, $ticketNumber);

            if ($validateData !== null) {
                foreach ($validateData as $input => $value) {
                    if ($ticketDto->getResponseData()[$input] != $value) {
                        return false;
                    }
                }
                return $ticketDto;
            } else {
                return $ticketDto;
            }

        } catch (ClientException $exception) {
            return false;
        }
    }
}
