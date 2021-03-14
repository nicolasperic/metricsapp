<?php

namespace App\Importer;


use App\Dto\Mapper\SprintMapper;
use App\Exceptions\MilestoneNotCreatedException;
use App\Integration\AssemblaGateway;
use App\Sprint;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/** 
 * This class is in charge of creating New Current milestone with carry over from ex current milestone
 * Since Assembla doesn't allow subtasks to be on a different milestone from the parent US, closed subtasks 
 * from OPEN US will be moved to the new milestone as well.
 * To prevent this extra hours and closed tickets mess the new sprint "fresh" metrics we will also 
 * keep track of the carry over (objective: substract carry over stats and get the clean metrics)
 *
 *  Carry Over: (if the sprint has carry over new stats will appear | Carry Over vs Stats without carry over | since current stats consider all tickets
 *      - worked hours: sum of worked hours (both open/closed subtasks and open US that are moved to new milestone)
 *      - closed tickets: count of tickets closed (this should only affect subtasks, since we only move open user stories)
 *      - List of closed tickets (or maybe just a CO tag on all tickets (just US visible for now)
 *
 * //TODO don't forget to cover EPIC tickets (validate if closed user stories are moved, what happens..)
 *
 * 0. Sync Space and Sync Current Milestone (getting local information up to date)
 *
 * 1. Create new Milestone and set it as current via
 *      POST /v1/spaces/:space_id/milestones //space_id is actually wikiname
 *
 * 3. Set previous current milestone as closed//PUT is_completed true (completed_date auto set)
 *      PUT /v1/spaces/:space_id/milestones/:id //space_id is actually wikiname
 *
 * 4. All OPEN tickets should be moved to the new milestone
 *      PUT /v1/spaces/[space_id]/tickets/[number] //space_id is actually wikiname
 *
 *      Can a subtask be open with a closed story? This scenario will be considered as invalid (no open subtasks could be moved without moving parent US)
 *
 *
 *
 *
 * De Assembla en las docs del Planner
 * "close and rename the Current milestone. Give this old milestone a name that describes your sprint or release.
 * It will create a new Current milestone, and move any open tickets to that new Current milestone."
 * Además > condiciones para enviar un ticket a backlog
 * - por estado ej: Paused/New
 * - por cantidad de días inactivo (?)
 *
 * Una vez sincronicé el milestone:
 * - tode ticket open que no sea SUBTASK la muevo al nuevo milestone (hierarchy_type != 1)
 */

class SprintIteration {
    /**
     * @var User
     */
    private $user;
    /** @var AssemblaGateway  */
    private $assemblaGateway;

    const ERROR_MILESTONE_NOT_CREATED = 'There was a problem when creating a new Milestone in Assembla. Iteration stopped.';

    /**
     * @param User $user
     */
    function __construct(User $user)
    {
        $this->user = $user;
        //$this->assemblaGateway = new AssemblaGateway($this->user);
        $this->assemblaGateway = resolve(AssemblaGateway::class);
        $this->assemblaGateway->setUser($this->user);

        //dd(get_class($this->assemblaGateway));
    }

    function closeCurrentSprintAndCreateNewOneWithCarryOver(Sprint $oldSprint, $startDate, $endDate)
    {
        //0.  Sync Milestone Tickets
        //$ticketImporter = new TicketImporter($this->user);
        //$ticketImporter->importMilestoneTickets($oldSprint);
        
        $carryOverData = [
            'worked_hours'           => 0,
            'total_tickets_count'    => 0,
            'closed_subtasks_count'  => 0,
            'user_stories_count'     => 0,
            'subtasks_count'         => 0,
            'closed_subtasks'        => []
        ];
        

        Log::info('[Sprint Iteration] starting');

        //1. Create new Milestone (current) and retrieve new Milestone ID
        $project = $oldSprint->getProject();

        $newSprint = $this->_createNewCurrentMilestone($project, $startDate, $endDate);

        //getting open tickets != subtask
        $carryOverTickets = $oldSprint->getOpenTicketsForCarryOver();


        Log::info("[Sprint Iteration] about to update old sprint ".count($carryOverTickets)." tickets");

        //PUT to assembla will be the same for all tickets, just changing milestone_id
        $putParams = ['ticket' => [
            'milestone_id' => $newSprint->sprint_assembla_id,
        ]];

        foreach ($carryOverTickets as $ticket) {
            Log::info("[Sprint Iteration] updated ticket ".$ticket->number);
            $ticketUpdated = $this->assemblaGateway->updateTicket($putParams, $ticket->number, $project->wikiname);
            if ($ticketUpdated) {
                $oldSprint->tickets()->detach($ticket->id);//detach ticket from old sprint
                $newSprint->tickets()->save($ticket);//attach tiket to new sprint

                $carryOverData['worked_hours'] += $ticket->worked_hours;
                $carryOverData['total_tickets_count'] += 1;

                if ($ticket->is_story) {
                    $carryOverData['user_stories_count'] += 1;

                    $subtasks = $ticket->subtasks;
                    foreach ($subtasks as $subtask) {
                        $oldSprint->tickets()->detach($subtask->id);
                        $newSprint->tickets()->save($subtask);

                        //tracking carry over data
                        $carryOverData['worked_hours'] += $subtask->worked_hours;
                        $carryOverData['subtasks_count'] += 1;
                        $carryOverData['total_tickets_count'] += 1;
                        if ($subtask->state == Ticket::CLOSED_STATE) {
                            $carryOverData['closed_subtasks_count'] += 1;
                            $carryOverData['closed_subtasks'][] = $subtask->number.' '.$subtask->name;
                        }
                    }
                }

            } else {
                //ticket was not updated correctly
                Log::info("[Sprint Iteration] ooops ticket not correctly updated ".$ticket->number);
            }

        }


        $newSprint->carry_over = $carryOverData;
        $newSprint->save();

        Log::info("[Sprint Iteration] setting old milestone as complete");
        //finally set oldSprint as complete :h5:
        $putParams = ['milestone' => [
            'is_completed' => true,
        ]];
        //dump($putParams);
        $milestoneUpdated = $this->assemblaGateway->updateMilestone($putParams, $oldSprint->sprint_assembla_id, $project->wikiname);
        if ($milestoneUpdated) {
            $oldSprint->is_active = 0;
            $oldSprint->planner_type = 0;
            $oldSprint->save();
        } else {
            //something went wrong when updating the milestone
        }

        return $newSprint;
    }

    /**
     * @param $project
     *
     * @return bool | \App\Sprint $sprint
     */
    private function _createNewCurrentMilestone($project, $startDate, $endDate)
    {
        $postParams = ['milestone' => [
            'space_id' => $project->project_assembla_id,
            'title' => $project->sprintIteration->getNewMilestoneUniqueTitle(),
            'updated_by' => $this->user->user_assembla_id,
            'created_by' => $this->user->user_assembla_id,
            'user_id' => $this->user->user_assembla_id,
            'planner_type' => Sprint::PLANNER_TYPE_CURRENT,
            'start_date' => $startDate,
            'due_date' => $endDate,
        ]];

        //dump($postParams);

        Log::info('[Sprint Iteration] creating new milestone in Assembla!');
        $sprintDto = $this->assemblaGateway->createMilestone($postParams, $project->wikiname);

        if ($sprintDto === false) {
            Log::info('[Sprint Iteration] oops milestone was not created! Throwing exception');
            throw new MilestoneNotCreatedException(self::ERROR_MILESTONE_NOT_CREATED);
        }
        Log::info('[Sprint Iteration] New milestone was created in Assembla!');

        $newSprint = SprintMapper::createSprintFromDTO($sprintDto);//storing response on database
        $project->sprints()->save($newSprint);//assigning new sprint to project
        $this->user->sprints()->save($newSprint);//assigning new sprint to user

        return $newSprint;
    }
}


