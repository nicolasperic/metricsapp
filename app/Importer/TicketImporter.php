<?php

namespace App\Importer;

use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
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
     * @param $sprint
     */
    public function __importMilestoneTickets($sprint)
    {

        Log::info('[Ticket Importer] Started');

        $project = Project::getProjectByAssemblaId($sprint->project_assembla_id);
        $response = $this->assemblaGateway->getTicketsForMilestone($project->wikiname, $sprint->sprint_assembla_id);

        if ($response->getStatusCode() == 200) {
            Log::info('[Ticket Importer] Response 200');
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $ticketData) {
                $ticketDto = new TicketDto($ticketData);

                if (!Ticket::ticketExists($ticketDto->getTicketAssemblaId())) {
                    Log::info('[Ticket Importer] about to create ticket '.$ticketDto->getNumber());
                    $this->_createTicketFromDTO($ticketDto, $sprint, $project);
                }
            }
        }
    }

    /**
     *
     */
    public function importMilestoneTickets($sprint)
    {
        Log::info('[Ticket Importer] Started');
        $project = Project::getProjectByAssemblaId($sprint->project_assembla_id);
        $page = 1;
        do {
            $response = $this->assemblaGateway->getTicketsForMilestone($project->wikiname, $sprint->sprint_assembla_id, $page);

            if ($response->getStatusCode() == 200) {
                Log::info('[Ticket Importer] Response 200 for page '.$page);
                $page++;
                $result = json_decode($response->getBody()->getContents(), 1);
                foreach ($result as $ticketData) {
                    $ticketDto = new TicketDto($ticketData);
                    if (!Ticket::ticketExists($ticketDto->getTicketAssemblaId())) {
                        Log::info('[Ticket Importer] about to create ticket '.$ticketDto->getNumber());
                        $this->_createTicketFromDTO($ticketDto, $sprint, $project);
                    }
                }
            }
        } while(count($result) === 100);//todo avoid hardcoding the page size here
        Log::info('[Ticket Importer] Ended');
    }

    private function _createTicketFromDTO(TicketDto $ticketDto, $sprint, $project)
    {

        $ticket = Ticket::create([
            'project_id' => $project->id,
            'name' => $ticketDto->getSummary(),
            'number' => $ticketDto->getNumber(),
            'status' => $ticketDto->getStatus(),
            'state' => $ticketDto->getState(),
            'ticket_assembla_id' => $ticketDto->getTicketAssemblaId(),
            'is_story' => $ticketDto->isStory(),
            'story_points' => $ticketDto->getComplexity(),
            'total_invested_hours' => $ticketDto->getTotalInvestedHours(),
            'worked_hours' => $ticketDto->getWorkedHours(),
            'started_at' => $this->_getParsedDate($ticketDto->getCreatedOn()),//todo started on is not real
            'created_at' => $this->_getParsedDate($ticketDto->getCreatedOn()),
            'completed_at' => $this->_getParsedDate($ticketDto->getCompletedDate()),
        ]);
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
        $response = $this->assemblaGateway->getTicketAssociationsBySpaceAndNumber($project->wikiname, $userStory->number);
        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $association) {
                if ($association['relationship'] === AssemblaGateway::STORY_RELATION) {
                    $subtask = Ticket::getTicketByAssemblaId($association['ticket1_id']);
                    $userStory->subtasks()->save($subtask,['relationship' => $association['relationship']]);
                }
            }
        }
        /** ticket1_id" => 231717985 subtask
        "ticket2_id" => 231438936 story
        "relationship" => 5 */
    }
}
