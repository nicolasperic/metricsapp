<?php

namespace App\Importer;

use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Dto\Mapper\TicketMapper;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Project;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TicketImporter
{
    private $assemblaGateway;
    private $ticketTimeImporter;
    private $apiCalls;
    /**
     * @var User
     */
    private $user;

    function __construct(User $user)
    {
        $this->assemblaGateway = new AssemblaGateway($user);
        $this->ticketTimeImporter = new TasksImporter($this->assemblaGateway);
        $this->user = $user;
    }

    /**
     * @param \App\Sprint $sprint
     */
    public function importMilestoneTickets($sprint)
    {
        $startTime = time();
        $this->apiCalls = 0;
        Log::info('[Ticket Importer] Started');
        $project = Project::getProjectByAssemblaId($sprint->project_assembla_id);

        $this->importProjectUsersIfNone($project);

        $page = 1;
        $queryParams = [
            'page' => $page,
            'ticket_status' => 'all',
            'sort_by' => 'id',
            'sort_order' => 'desc'
        ];
        $allSprintTicketsFromAPI = array();
        do {
            $tickets = $this->assemblaGateway->getTicketsForMilestone($project->wikiname, $sprint->sprint_assembla_id, $queryParams);
            $this->apiCalls++;

            if ($tickets) {
                Log::info('[Ticket Importer] Response 200 for page '.$page);
                $queryParams['page'] = ++$page;

                /** @var TicketDto $ticketDto */
                foreach ($tickets as $ticketDto) {
                    $allSprintTicketsFromAPI[$ticketDto->getTicketAssemblaId()] = true;

                    $ticket = Ticket::getTicketByAssemblaId($ticketDto->getTicketAssemblaId());
                    if ($ticket === null) {
                        Log::info('[Ticket Importer] about to create ticket '.$ticketDto->getNumber());
                        $this->_createTicketFromDTO($ticketDto, $sprint, $project);
                    } else {
                        //we need to update ticket fields and milestone assignation!
                        //some tickets could be present on the milestone in our DB and not in Assembla...
                        if (!$sprint->tickets->contains('number', $ticket->number)) {
                            $sprint->tickets()->save($ticket);
                        }

                        if ($ticket->is_story) {
                            Log::info('[Ticket Importer] ticket associations '.$ticketDto->getNumber());
                            $this->_retrieveTicketAssociations($ticket, $project, true);
                        }
                        TicketMapper::updateTicketFromDTO($ticket, $ticketDto);//Ticket Data synced

                        //Milestone; ticket associations and ticket tracked time//TODO test what happens if I track i.e 1h and then I change it to 30m
                    }
                }
            } else {
                break;
            }
        } while(count($tickets) === AssemblaRequest::PER_PAGE);


        $this->apiCalls += $this->ticketTimeImporter->importTicketsTasks(array_keys($allSprintTicketsFromAPI));//TODO validate query string limit, batch import if many tickets (it worked with 147 tickets)
        //[Ticket Importer] Ended in 1.95 minutes with 57 api calls vs [Ticket Importer] Ended in 3.82 minutes with 203 api calls

        //sync $tickets received from API with sprints->tickets
        foreach ($sprint->tickets as $ticket) {
            if (!array_key_exists($ticket->ticket_assembla_id, $allSprintTicketsFromAPI)) {
                $sprint->tickets()->detach($ticket->id);
            }
        }
        $sprint->touch();//just to trigger that the sprint was updated

        $endTime = time();
        $minutes = round(($endTime - $startTime)/60, 2);
        Log::info('[Ticket Importer] Ended in '.$minutes.' minutes with '.$this->apiCalls.' api calls');
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
            $this->_retrieveTicketAssociations($ticket, $project);
        }
    }


    /**
     *
     * relationship is 5 - ticket2 is story and ticket1 is subtask of the story
     *
     * @param \App\Ticket $userStory
     * @param \App\Project $project
     */
    private function _retrieveTicketAssociations($userStory, $project, $ticketAlreadyExisted = false)
    {
        $this->apiCalls++;
        $ticketAssociations = $this->assemblaGateway->getTicketAssociationsBySpaceAndNumber($project->wikiname, $userStory->number);
        if ($ticketAssociations !== false) {
            /** @var TicketAssociationDto $association */
            foreach ($ticketAssociations  as $association) {
                if ($association->getRelationship() === AssemblaGateway::STORY_RELATION) {
                    Log::info('[Ticket Importer] retrieving ticket by association '.$association->getTicket1Id());
                    $subtask = Ticket::getTicketByAssemblaId($association->getTicket1Id());
                    if (!is_null($subtask)) {
                        if ($ticketAlreadyExisted) {
                            if ($userStory->subtasks()->where('ticket2_id', $subtask->id)->count() === 0) {
                                $userStory->subtasks()->save($subtask,['relationship' => $association->getRelationship()]);
                            }
                        } else {
                            $userStory->subtasks()->save($subtask,['relationship' => $association->getRelationship()]);
                        }
                    }//subtask was on a different milestone so it was not created
                }
            }
        }
    }

    private function importProjectUsersIfNone($project)
    {
        if (count($project->assemblaUsers) === 0) {
            Log::info('[Ticket Importer] no assembla users found > triggering UserImporter');
            $userImporter = new UserImporter($this->user);
            $userImporter->importSpaceUsers($project);
        }
    }
}
