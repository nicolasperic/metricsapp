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
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }




    /**
     * Returns currently authenticated user.
     * https://api-docs.assembla.cc/content/ref/user_show.html
     *
     * @return AssemblaUserDto | false
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
     * @return AssemblaUserDto|bool
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
     * @return array ProjectDto | false
     */
    public function getSpaces($queryParams = [])
    {//dowhile
        $spaces = false;
        $response = AssemblaRequest::get("spaces", $this->user->assembla_key, $this->user->assembla_secret, $queryParams);
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
     * @return array AssemblaUserDto | false
     */
    public function getSpaceUsers($space)
    {//dowhile
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
     * This function returns the role a user has on a given space
     * Since Assembla doesn't provide and endpoint for this we are filtering the user
     * from the list of user roles in space
     *
     * Assembla Endpoint used:
     * Returns list of user roles (space members)
     * https://api-docs.assembla.cc/content/ref/user_roles_index.html
     *
     * @param $userAssemblaId string user id in assembla
     * @param $space string wikiname or space id in assembla
     *
     * @return array|bool
     */
    public function getUserRoleInSpace($userAssemblaId, $space)
    {
        $userRole = false;

        $page = 0;
        do {
            $page++;
            $queryParams = ['page' => $page];
            $response = AssemblaRequest::get("spaces/{$space}/user_roles", $this->user->assembla_key, $this->user->assembla_secret, $queryParams);
            $result = json_decode($response->getBody()->getContents(), 1);

            $key = array_search($userAssemblaId, array_column($result, 'user_id'));
            if ($key !== false) {
                $userRole = [
                    'user_role' => $result[$key]['role'],
                    'user_status' => $result[$key]['status']
                ];
                break;
            }
        } while(count($result) === AssemblaRequest::PER_PAGE);

        return $userRole;
    }

    /**
     * Returns a ticket by a ticket number.
     * https://api-docs.assembla.cc/content/ref/tickets_show.html
     *
     * @param $space
     * @param $ticketNumber
     *
     * @return TicketDto | false
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
     * @return array TicketAssociationDto | false
     */
    public function getTicketAssociationsBySpaceAndNumber($space, $ticketNumber)
    {//dowhile
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
     * @return array TicketDto |bool array of tickets or false if request to API is not 200
     */
    public function getTicketsForMilestone($space, $milestoneId, $queryParams = [])
    {//dowhile
        $tickets = false;
        $response = AssemblaRequest::get("spaces/{$space}/tickets/milestone/{$milestoneId}", $this->user->assembla_key, $this->user->assembla_secret, $queryParams);
        if ($response->getStatusCode() == 200) {
            $tickets = [];
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $ticketData) {
                $tickets[] = new TicketDto($ticketData);
            }
        }

        return $tickets;
    }

    /**
     * Returns a paginated list of tasks. Pages are default to 25 tasks.
     * https://api-docs.assembla.cc/content/ref/tasks_index.html
     *
     * @param $queryParams
     *
     * @return array TicketTimeDto | false
     */
    public function getTrackedTimeForTicket($queryParams)
    {
        return $this->getTasks($queryParams);
    }

    /**
     * This function is used to retrieve tasks for multiple tickets in few API calls
     * To be able to have multiple Ids with the same param name we use the $multiple = true trigger
     * i.e: ?ticket_ids[]=ticket_id&ticket_ids[]=another_ticket_id...
     *
     * Currently used only on TasksImporter
     * @param $queryParams
     *
     * @return array
     */
    public function getTrackedTimeForTickets($queryParams)
    {
        return $this->getTasks($queryParams, $multiple = true);
    }

    /**
     * Returns a paginated list of tasks. Pages are default to 25 tasks.
     * https://api-docs.assembla.cc/content/ref/tasks_index.html
     *
     * @param      $queryParams
     *
     * @param bool $multiple flag used to trigger URL query strings that allow multiple parameters with same name
     *
     * @return array TicketTimeDto | false
     */
    public function getTasks($queryParams, $multiple = false)
    {//dowhile
        $tasks = false;
        $response = AssemblaRequest::get("tasks", $this->user->assembla_key, $this->user->assembla_secret, $queryParams, $multiple);
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
     * There's no need to be an owner to get the Space Milestones. If no content is retrieved
     * it could be due to a disabled space
     *
     * Returns a list of paginated upcoming milestones. Pages are defaulted to 10 milestones.
     * https://api-docs.assembla.cc/content/ref/milestones_index.html
     *
     * @param $space
     *
     * @return array SprintDto | false
     */
    public function getMilestonesForSpace($space)
    {//dowhile
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
     * This function is used for creating a new milestone on the given space
     *
     * @param $postParams
     * @param $space
     *
     * @return SprintDto|bool
     */
    public function createMilestone($postParams, $space)
    {
        $milestone = false;
        $response = AssemblaRequest::post("spaces/{$space}/milestones", $this->user->assembla_key, $this->user->assembla_secret, $postParams);
        if ($response->getStatusCode() == 201) {//201 result of post new resources were created OK
            $milestoneData = json_decode($response->getBody()->getContents(), 1);
            $milestone = new SprintDto($milestoneData);
        } else {
            //TODO since the new milestone was not correctly created the user needs to be notified and the SprintIteration process should be halted
        }
        return $milestone;
    }

    /**
     * @param $putParams
     * @param $ticketNumber
     * @param $space
     *
     * @return bool
     */
    public function updateTicket($putParams, $ticketNumber, $space) {
        $result = false;
        $response = AssemblaRequest::put("spaces/{$space}/tickets/{$ticketNumber}", $this->user->assembla_key, $this->user->assembla_secret, $putParams);
        if ($response->getStatusCode() == 204) {
            $result = true;
        }

        return $result;
    }

    public function updateMilestone($putParams, $milestoneId, $space)
    {
        $result = false;
        $response = AssemblaRequest::put("spaces/{$space}/milestones/{$milestoneId}", $this->user->assembla_key, $this->user->assembla_secret, $putParams);
        if ($response->getStatusCode() == 204) {
            $result = true;
        }

        return $result;
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
