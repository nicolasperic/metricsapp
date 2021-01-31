<?php

namespace App\Importer;

use App\Dto\Mapper\TicketMapper;
use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Dto\TicketTimeDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Project;
use App\Ticket;
use App\TicketTime;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketImporterByTrackedTime
{
    private $assemblaGateway;

    /**
     * @var array information required for the import
     * KEYS: wikiname, space_id, from_date and to_date
     */
    private $requestData;

    function __construct($requestData, User $user)
    {
        $this->assemblaGateway = new AssemblaGateway($user);
        $this->requestData= $requestData;
    }


/*
TASK DATA
99 => array:12 [
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


    /**
     *
     *
     */
    public function importTrackedTicketsOnPeriod()
    {
        Log::info('[Ticket Importer By Tracked Time] Started');


        $this->apicalls = 0;
        $startTime = time();

        //setting request parameters
        $wikiname = $this->requestData['wikiname'];
        $spaceId = $this->requestData['space_id'];
        $from = $this->requestData['from_date'];
        $to = $this->requestData['to_date'];

        $page = 1;
        do {
            $queryParams = [
                'spaces' => $spaceId,
                'from' => $from,
                'to' => $to,
                'page' => $page,
            ];



            Log::info('query params '.print_r($queryParams, 1));
            $user = Auth::user();//TODO if this is used on a JOB AUth will need to be replaced with the user instance
            $applicationKey = $user->assembla_key;
            $applicationSecret = $user->assembla_secret;
            $response = AssemblaRequest::get("tasks", $applicationKey, $applicationSecret, $queryParams);//TODO para probar esto más rápido, guardar esta data con los DTO's local y usar eso para el importer
            $this->apicalls++;
            $result = json_decode($response->getBody()->getContents(), 1);
            if (!is_array($result)) {
                break;
            }



            if ($tickets) {
                Log::info('[Ticket Importer] Response 200 for page '.$page);
                $queryParams['page'] = ++$page;

                foreach ($tickets as $ticketDto) {
                    if (!Ticket::ticketExists($ticketDto->getTicketAssemblaId())) {
                        Log::info('[Ticket Importer] about to create ticket '.$ticketDto->getNumber());
                        $this->_createTicketFromDTO($ticketDto, $sprint, $project);
                        $this->_createTrackedTimeFor($ticketDto->getTicketAssemblaId());
                    }
                }
            } else {
                break;
            }
        } while(count($tickets) === AssemblaRequest::PER_PAGE);
        Log::info('[Ticket Importer] Ended');
    }

    /**TODO this function si repeated with TicketMapper class, the whole file needs to be checked!
     * @param TicketDto $ticketDto
     * @param           $sprint
     * @param           $project
     */
    private function _createTicketFromDTO(TicketDto $ticketDto, $sprint, $project)
    {
        $ticket = TicketMapper::createTicketFromDTO($ticketDto, $project);
        Log::info("Ticket created {$ticketDto->getNumber()} {$ticketDto->getStatus()} {$ticketDto->getCompletedDate()}".Carbon::parse($ticketDto->getCompletedDate()));
        Log::info('[Ticket Importer] adding ticket to sprint '.$ticketDto->getNumber());
        $this->_addTicketToSprint($ticket, $sprint);

        if ($ticket->is_story) {
            Log::info('[Ticket Importer] ticket associations '.$ticketDto->getNumber());
            $this->_validateTicketAssociations($ticket, $project);
        }
    }

    private function _getParsedDate($date)
    {
        if (strlen($date)) {
            $date = Carbon::parse($date);
        }

        return $date;
    }

    private function _addTicketToSprint($ticket, $sprint)
    {
        $sprint->tickets()->save($ticket);
    }

    private function _validateTicketAssociations($userStory, $project)
    {
        $ticketAssociations = $this->assemblaGateway->getTicketAssociationsBySpaceAndNumber($project->wikiname, $userStory->number);
        if ($ticketAssociations !== false) {

            /** @var TicketAssociationDto $association */
            foreach ($ticketAssociations  as $association) {
                if ($association->getRelationship() === AssemblaGateway::STORY_RELATION) {
                    $subtask = Ticket::getTicketByAssemblaId($association->getTicket1Id());
                    $userStory->subtasks()->save($subtask,['relationship' => $association->getRelationship()]);
                }
            }
        }
        /** ticket1_id" => 231717985 subtask
        "ticket2_id" => 231438936 story
        "relationship" => 5 */
    }

    private function _createTrackedTimeFor($ticketId)
    {
        Log::info('[Ticket Importer] about to retrieve tracked time for ticket '.$ticketId);
        $queryParams = ['ticket_ids' => $ticketId];
        $response = $this->assemblaGateway->getTrackedTimeForTicket($queryParams);
        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $trackedTime) {
                $ticketTimeDto = new TicketTimeDto($trackedTime);
                Log::info("[Ticket Importer] tracking time {$ticketTimeDto->getTicketNumber()} {$ticketTimeDto->getHours()}");
                TicketTime::create([
                    'description' => $ticketTimeDto->getDescription(),
                    'hours' => $ticketTimeDto->getHours(),
                    'begin_at' => $this->_getParsedDate($ticketTimeDto->getBeginAt()),
                    'end_at' => $this->_getParsedDate($ticketTimeDto->getEndAt()),
                    'ticket_time_assembla_id' => $ticketTimeDto->getTicketTimeAssemblaId(),
                    'ticket_number' => $ticketTimeDto->getTicketNumber(),
                    'ticket_assembla_id' => $ticketTimeDto->getTicketAssemblaId(),
                    'project_assembla_id' => $ticketTimeDto->getProjectAssemblaId(),
                    'user_assembla_id' => $ticketTimeDto->getUserAssemblaId(),
                    'created_at' => $this->_getParsedDate($ticketTimeDto->getCreatedAt()),
                    'updated_at' => $this->_getParsedDate($ticketTimeDto->getUpdatedAt()),
                ]);
            }
        }
    }
}
