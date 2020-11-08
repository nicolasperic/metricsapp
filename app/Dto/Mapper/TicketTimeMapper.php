<?php
/**
 * The only responsibility of this class is to generate an Entity from a DTO
 */
namespace App\Dto\Mapper;

use App\Dto\TicketTimeDto;
use App\TicketTime;

class TicketTimeMapper extends AbstractMapper
{
    public static function createTicketTimeFromDTO(TicketTimeDto $ticketTimeDto)
    {
        return  TicketTime::create([
            'description' => $ticketTimeDto->getDescription(),
            'hours' => $ticketTimeDto->getHours(),
            'begin_at' => self::getParsedDate($ticketTimeDto->getBeginAt()),
            'end_at' => self::getParsedDate($ticketTimeDto->getEndAt()),
            'ticket_time_assembla_id' => $ticketTimeDto->getTicketTimeAssemblaId(),
            'ticket_number' => $ticketTimeDto->getTicketNumber(),
            'ticket_assembla_id' => $ticketTimeDto->getTicketAssemblaId(),
            'project_assembla_id' => $ticketTimeDto->getProjectAssemblaId(),
            'user_assembla_id' => $ticketTimeDto->getUserAssemblaId(),
            'created_at' => self::getParsedDate($ticketTimeDto->getCreatedAt()),
            'updated_at' => self::getParsedDate($ticketTimeDto->getUpdatedAt()),
        ]);
    }

}