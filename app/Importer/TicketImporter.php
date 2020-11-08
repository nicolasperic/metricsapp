<?php

namespace App\Importer;

use App\Dto\TicketDto;
use App\Dto\Mapper\TicketMapper;
use App\Dto\Mapper\TicketTimeMapper;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Project;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TicketImporter
{
    private $assemblaGateway;
    function __construct()
    {
        $this->assemblaGateway = new AssemblaGateway();
    }

    /**
     *
     */
    public function importMilestoneTickets($sprint)
    {
        Log::info('[Ticket Importer] Started');
        $project = Project::getProjectByAssemblaId($sprint->project_assembla_id);
        $page = 1;
        $queryParams = [
            'page' => $page,
            'ticket_status' => 'all',
            'sort_by' => 'id',
            'sort_order' => 'desc'
        ];
        do {
            $tickets = $this->assemblaGateway->getTicketsForMilestone($project->wikiname, $sprint->sprint_assembla_id, $queryParams);

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

    private function _createTicketFromDTO(TicketDto $ticketDto, $sprint, $project)
    {
        $ticket = TicketMapper::createTicketFromDTO($ticketDto, $project);
        Log::info("Ticket created {$ticketDto->getNumber()} {$ticketDto->getStatus()} {$ticketDto->getCompletedDate()}".Carbon::parse($ticketDto->getCompletedDate()));
        Log::info('[Ticket Importer] adding ticket to sprint '.$ticketDto->getNumber());
        $sprint->tickets()->save($ticket);//adding ticket to sprint

        if ($ticket->is_story) {
            Log::info('[Ticket Importer] ticket associations '.$ticketDto->getNumber());
            $this->_validateTicketAssociations($ticket, $project);
        }
    }


    private function _validateTicketAssociations($userStory, $project)
    {
        $response = $this->assemblaGateway->getTicketAssociationsBySpaceAndNumber($project->wikiname, $userStory->number);
        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), 1);//TODO move this to assembla gateway
            foreach ($result as $association) {
                if ($association['relationship'] === AssemblaGateway::STORY_RELATION) {
                    Log::info('[Ticket Importer] retrieving ticket by association '.$association['ticket1_id']);
                    $subtask = Ticket::getTicketByAssemblaId($association['ticket1_id']);
                    if (!is_null($subtask)) {
                        $userStory->subtasks()->save($subtask,['relationship' => $association['relationship']]);
                    }//TODO subtask was on a different milestone so it was not created and is not found
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
        $tasks = $this->assemblaGateway->getTrackedTimeForTicket($queryParams);
        if ($tasks) {
            foreach ($tasks as $ticketTimeDto) {
                Log::info("[Ticket Importer] tracking time {$ticketTimeDto->getTicketNumber()} {$ticketTimeDto->getHours()}");
                TicketTimeMapper::createTicketTimeFromDTO($ticketTimeDto);
            }
        }
    }
}
