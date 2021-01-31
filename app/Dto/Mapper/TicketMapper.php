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
            'sprint_assembla_id' => $ticketDto->getMilestoneId(),
            'assigned_to_user_assembla_id' => $ticketDto->getAssignedToId(),
            'is_story' => $ticketDto->isStory(),
            'estimate' => $ticketDto->getEstimate(),
            'total_estimate' => $ticketDto->getTotalEstimate(),
            'total_invested_hours' => $ticketDto->getTotalInvestedHours(),
            'worked_hours' => $ticketDto->getWorkedHours(),
            'working_hours' => $ticketDto->getWorkingHours(),
            'total_working_hours' => $ticketDto->getTotalWorkingHours(),
            'type' => $ticketDto->getType(),
            'custom_fields' => $ticketDto->getCustomFields(),
            'started_at' => self::getParsedDate($ticketDto->getCreatedOn()),//todo started on is not real
            'created_at' => self::getParsedDate($ticketDto->getCreatedOn()),
            'completed_at' => self::getParsedDate($ticketDto->getCompletedDate()),
        ]);
    }

    /**
     * @param \App\Ticket $ticket
     * @param TicketDto $ticketDto
     */
    public static function updateTicketFromDTO($ticket, TicketDto $ticketDto)
    {
        $changed = false;
        if ($ticketDto->getSummary() !== $ticket->name) {
            $ticket->name = $ticketDto->getSummary();
            $changed = true;
        }

        if ($ticketDto->getNumber() !== $ticket->number) {
            $ticket->number = $ticketDto->getNumber();
            $changed = true;
        }

        if ($ticketDto->getStatus() !== $ticket->status) {
            $ticket->status = $ticketDto->getStatus();
            $changed = true;
        }

        if ($ticketDto->getState() !== $ticket->state) {
            $ticket->state = $ticketDto->getState();
            $changed = true;
        }

        if ($ticketDto->isStory() !== $ticket->is_story) {
            $ticket->is_story = $ticketDto->isStory();
            $changed = true;
        }

        if ($ticketDto->getTotalInvestedHours() !== $ticket->total_invested_hours) {
            $ticket->total_invested_hours = $ticketDto->getTotalInvestedHours();
            $changed = true;
        }

        if ($ticketDto->getWorkedHours() !== $ticket->worked_hours) {
            $ticket->worked_hours = $ticketDto->getWorkedHours();
            $changed = true;
        }

        if ($ticketDto->getType() !== $ticket->type) {
            $ticket->type = $ticketDto->getType();
            $changed = true;
        }

        if ($ticketDto->getMilestoneId() !== $ticket->sprint_assembla_id) {
            $ticket->sprint_assembla_id = $ticketDto->getMilestoneId();
            $changed = true;
        }

        if ($ticketDto->getAssignedToId() !== $ticket->assigned_to_user_assembla_id) {
            $ticket->assigned_to_user_assembla_id = $ticketDto->getAssignedToId();
            $changed = true;
        }

        if ($ticketDto->getEstimate() !== $ticket->estimate) {
            $ticket->estimate = $ticketDto->getEstimate();
            $changed = true;
        }

        if ($ticketDto->getTotalEstimate() !== $ticket->total_estimate) {
            $ticket->total_estimate = $ticketDto->getTotalEstimate();
            $changed = true;
        }

        if ($ticketDto->getWorkingHours() !== $ticket->working_hours) {
            $ticket->working_hours = $ticketDto->getWorkingHours();
            $changed = true;
        }

        if ($ticketDto->getCustomFields() !== $ticket->custom_fields) {
            $ticket->custom_fields = $ticketDto->getCustomFields();
            $changed = true;
        }

        if ($changed) {
            $ticket->save();
        }
    }
    

}