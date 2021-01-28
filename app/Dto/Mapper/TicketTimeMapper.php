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

    public static function updateTicketTimeFromDto(TicketTime $ticketTime, TicketTimeDto $ticketTimeDto)
    {
        $changed = false;

        if ($ticketTimeDto->getHours() !== $ticketTime->hours) {
            $ticketTime->hours = $ticketTimeDto->getHours();
            $changed = true;
        }

        if ($ticketTimeDto->getTicketAssemblaId() !== $ticketTime->ticket_assembla_id) {
            $ticketTime->ticket_assembla_id = $ticketTimeDto->getTicketAssemblaId();
            $changed = true;
        }

        if ($ticketTimeDto->getTicketNumber() !== $ticketTime->ticket_number) {
            $ticketTime->ticket_number = $ticketTimeDto->getTicketNumber();
            $changed = true;
        }

        if ($ticketTimeDto->getUserAssemblaId() !== $ticketTime->user_assembla_id) {
            $ticketTime->user_assembla_id = $ticketTimeDto->getUserAssemblaId();
            $changed = true;
        }

        if ($ticketTimeDto->getProjectAssemblaId() !== $ticketTime->project_assembla_id) {
            $ticketTime->project_assembla_id = $ticketTimeDto->getProjectAssemblaId();
            $changed = true;
        }

        if ($changed) {
            $ticketTime->save();
        }
    }

}