<?php

namespace App\Importer;

use App\Dto\Mapper\TicketTimeMapper;
use App\Dto\TicketTimeDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Ticket;
use App\TicketTime;

use Illuminate\Support\Facades\Log;

class TasksImporter
{
    private $assemblaGateway;
    private $apiCalls;

    function __construct(AssemblaGateway $assemblaGateway)
    {
        $this->assemblaGateway = $assemblaGateway;
    }

    /**
     * @param \App\Ticket $ticket
     *
     * @return int amount of api calls made
     */
    public function importTicketTasks(Ticket $ticket)
    {
        $this->apiCalls = 0;
        Log::info('[TicketTime Importer] about to retrieve tracked time for ticket '.$ticket->id);

        $page = 1;
        $queryParams = ['ticket_ids' => $ticket->ticket_assembla_id, 'page' => $page];
        $allTicketTimesFromAPI = array();
        do {

            $tasks = $this->assemblaGateway->getTrackedTimeForTicket($queryParams);
            $this->apiCalls++;
            if ($tasks) {
                Log::info('[TicketTime Importer] Response 200 for page '.$page);
                $queryParams['page'] = ++$page;

                /** @var TicketTimeDto $ticketTimeDto */
                foreach ($tasks as $ticketTimeDto) {
                    $allTicketTimesFromAPI[$ticketTimeDto->getTicketTimeAssemblaId()] = true;

                    $ticketTime = TicketTime::getTicketTimeByAssemblaId($ticketTimeDto->getTicketTimeAssemblaId());
                    if ($ticketTime === null) {
                        TicketTimeMapper::createTicketTimeFromDTO($ticketTimeDto);
                    } else {
                        TicketTimeMapper::updateTicketTimeFromDto($ticketTime, $ticketTimeDto);
                    }
                }

            } else {
                break;
            }
        } while(count($tasks) === AssemblaRequest::PER_PAGE);

        //sync $ticketsTimes received from API with ticket->ticketsTimes
        foreach ($ticket->ticketTimes as $ticketTime) {
            if (!array_key_exists($ticketTime->ticket_time_assembla_id, $allTicketTimesFromAPI)) {
                $ticketTime->delete();
            }
        }
        Log::info('[TicketTime Importer] Ended');

        return $this->apiCalls;
    }


    public function importTicketsTasks($tickets) {
        $this->apiCalls = 0;
        $page = 0;
        $queryParams = [
            'ticket_ids[]' => $tickets,
            'page' => $page,
        ];
        Log::info('[Ticket Importer] getting tasks for '.count($tickets).' tickets');

        if (count($tickets) === 0) {
            Log::info('[Ticket Importer] No tickets so, no tasks!');
            return $this->apiCalls;
        }

        do {
            $queryParams['page'] = ++$page;
            $tasks = $this->assemblaGateway->getTrackedTimeForTickets($queryParams);
            $this->apiCalls++;

            if ($tasks === false) {
                break;
            }

            foreach ($tasks as $ticketTimeDto) {
                $ticketTime = TicketTime::getTicketTimeByAssemblaId($ticketTimeDto->getTicketTimeAssemblaId());
                if ($ticketTime === null) {
                    TicketTimeMapper::createTicketTimeFromDTO($ticketTimeDto);
                } else {
                    TicketTimeMapper::updateTicketTimeFromDto($ticketTime, $ticketTimeDto);
                }
            }


        } while(count($tasks) === AssemblaRequest::PER_PAGE);
        Log::info('[Ticket Importer] Tasks for all tickets were retrieved in '.$page.' pages');

        return $this->apiCalls;
    }
}
