<?php

namespace App\Importer;

use App\Dto\TicketAssociationDto;
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
     * @param \App\Sprint $sprint
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

                /** @var TicketDto $ticketDto */
                foreach ($tickets as $ticketDto) {
                    if (!Ticket::ticketExists($ticketDto->getTicketAssemblaId())) {
                        Log::info('[Ticket Importer] about to create ticket '.$ticketDto->getNumber());
                        $this->_createTicketFromDTO($ticketDto, $sprint, $project);
                        $this->_createTrackedTimeFor($ticketDto->getTicketAssemblaId());
                    } else {
                        //we need to update ticket fields and milestone assignation!
                        //since we are importing for a given milestone, tickets processed here won't need a milestone update!
                        //but some tickets could be present on the milestone in our DB and not on Assembla...

                        $ticket = Ticket::getTicketByAssemblaId($ticketDto->getTicketAssemblaId());
                        TicketMapper::updateTicketFromDTO($ticket, $ticketDto);//Ticket Data synced
                        //Milestone; ticket associations and ticket tracked time//TODO test what happens if I track i.e 1h and then I change it to 30m
                    }
                }
            } else {
                break;
            }
        } while(count($tickets) === AssemblaRequest::PER_PAGE);
        Log::info('[Ticket Importer] Ended');
    }

    /**
     * @param TicketDto    $ticketDto
     * @param \App\Sprint  $sprint
     * @param \App\Project $project
     */
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


    /**
     *
     * relationship is 5 - ticket2 is story and ticket1 is subtask of the story
     *
     * @param \App\Ticket $userStory
     * @param \App\Project $project
     */
    private function _validateTicketAssociations($userStory, $project)
    {
        $ticketAssociations = $this->assemblaGateway->getTicketAssociationsBySpaceAndNumber($project->wikiname, $userStory->number);
        if ($ticketAssociations !== false) {
            /** @var TicketAssociationDto $association */
            foreach ($ticketAssociations  as $association) {
                if ($association->getRelationship() === AssemblaGateway::STORY_RELATION) {
                    Log::info('[Ticket Importer] retrieving ticket by association '.$association->getTicket1Id());
                    $subtask = Ticket::getTicketByAssemblaId($association->getTicket1Id());
                    if (!is_null($subtask)) {
                        $userStory->subtasks()->save($subtask,['relationship' => $association->getRelationship()]);
                    }//subtask was on a different milestone so it was not created
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
