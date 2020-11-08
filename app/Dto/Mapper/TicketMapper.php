<?php
/**
 * The only responsibility of this class is to generate an Entity from a DTO
 */
namespace App\Dto\Mapper;

use App\Dto\TicketDto;
use App\Ticket;

class TicketMapper extends AbstractMapper
{
    public static function createTicketFromDTO(TicketDto $ticketDto, $project)
    {
        return $ticket = Ticket::create([
            'project_id' => $project->id,
            'name' => $ticketDto->getSummary(),
            'number' => $ticketDto->getNumber(),
            'status' => $ticketDto->getStatus(),
            'state' => $ticketDto->getState(),
            'ticket_assembla_id' => $ticketDto->getTicketAssemblaId(),
            'is_story' => $ticketDto->isStory(),
            'story_points' => $ticketDto->getEstimate(),//TODO this mapping needs to be configurable (story_points)
            'total_invested_hours' => $ticketDto->getTotalInvestedHours(),
            'worked_hours' => $ticketDto->getWorkedHours(),
            'type' => $ticketDto->getType(),
            'started_at' => self::getParsedDate($ticketDto->getCreatedOn()),//todo started on is not real
            'created_at' => self::getParsedDate($ticketDto->getCreatedOn()),
            'completed_at' => self::getParsedDate($ticketDto->getCompletedDate()),
        ]);


    }

}