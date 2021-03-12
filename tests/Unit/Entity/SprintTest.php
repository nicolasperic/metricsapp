<?php

namespace Tests\Unit\Entity;

use App\Project;
use App\Sprint;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_create_a_sprint_holding_tickets()
    {
        $sprint = Sprint::factory()->create();
        $ticketA = Ticket::factory()->create();
        $ticketB = Ticket::factory()->create();
        $ticketC = Ticket::factory()->create();


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC]);

        $this->assertEquals(3, $sprint->getTotalTickets());
    }

    /** @test */
    function a_sprint_can_have_multiple_projects()
    {
        $sprint = Sprint::factory()->create();

        $projectA = Project::factory()->create([
            'name' => 'Project A'
        ]);
        $projectB = Project::factory()->create([
            'name' => 'Project B'
        ]);
        $projectC = Project::factory()->create([
            'name' => 'Project C'
        ]);

        $sprint->projects()->saveMany([$projectA, $projectB, $projectC]);

        $this->assertEquals(3, $sprint->projects()->count());
    }

    /** @test */
    function can_calculate_sprint_carry_over()
    {
        $completedUS= Ticket::factory()->completed()->create([
            'status' => 'Done',
            'state' => Ticket::CLOSED_STATE,
            'created_at' => Carbon::parse('6 days ago'),
            'worked_hours' => 0,
            'hierarchy_type' => Ticket::HIERARCHY_STORY
        ]);
        $subtaskCA = Ticket::factory()->create([
            'name' => 'TIC-C1: subtask name CA',
            'is_story' => false,
            'status' => 'In Progress',
            'worked_hours' => 100,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $completedUS->subtasks()->save($subtaskCA,['relationship' => 5]);

        $userstory = Ticket::factory()->create([
            'status' => 'Accepted',
            'state' => Ticket::OPEN_STATE,
            'created_at' => Carbon::parse('6 days ago'),
            'worked_hours' => 0,
            'hierarchy_type' => Ticket::HIERARCHY_STORY
        ]);
        $subtaskA = Ticket::factory()->create([
            'name' => 'TIC-1: subtask name A',
            'is_story' => false,
            'status' => 'In Progress',
            'worked_hours' => 2.5,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $subtaskB = Ticket::factory()->completed()->create([
            'name' => 'TIC-2: subtask name B',
            'is_story' => false,
            'status' => 'Done',
            'created_at' => Carbon::parse('4 days ago'),
            'worked_hours' => 3,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $subtaskC = Ticket::factory()->completed()->create([
            'name' => 'TIC-3: subtask name C',
            'is_story' => false,
            'status' => 'Invalid',
            'worked_hours' => 0,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $subtaskD = Ticket::factory()->completed()->create([
            'name' => 'TIC-4: subtask name D',
            'is_story' => false,
            'status' => 'New',
            'worked_hours' => 0,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $ticket = Ticket::factory()->create([
            'name' => 'TIC-5: no plan level ticket',
            'is_story' => false,
            'status' => 'In Progress',
            'worked_hours' => 4.5,
            'hierarchy_type' => Ticket::HIERARCHY_NO_PLAN_LEVEL
        ]);


        $userstory->subtasks()->save($subtaskA,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskB,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskC,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskD,['relationship' => 5]);

        $sprint = Sprint::factory()->create();
        $sprint->tickets()->saveMany([$userstory, $subtaskA, $subtaskB, $subtaskC, $subtaskD, $ticket, $completedUS, $subtaskCA]);

        $carryOver = $this->getCarryOver($sprint);

        $sprint->carry_over = $carryOver;
        $sprint->save();

       // dd($sprint);

        $this->assertEquals(10, $carryOver['worked_hours']);
        $this->assertEquals(3, $carryOver['closed_subtasks_count']);
        $this->assertEquals(1, $carryOver['user_stories_count']);
        $this->assertEquals(4, $carryOver['subtasks_count']);
        $this->assertEquals(6, $carryOver['total_tickets_count']);
    }

    private function getCarryOver($sprint)
    {
        $carryOverData = [
            'worked_hours'           => 0,
            'total_tickets_count'    => 0,
            'closed_subtasks_count'  => 0,
            'user_stories_count'     => 0,
            'subtasks_count'         => 0,
            'closed_subtasks'        => []
        ];

        $carryOverTickets = $sprint->getOpenTicketsForCarryOver();

        foreach ($carryOverTickets as $ticket) {

            $carryOverData['worked_hours'] += $ticket->worked_hours;
            //$carryOverData['closed_tickets'][] = $ticket->number.' '.$ticket->name;
            $carryOverData['total_tickets_count'] += 1;
            if ($ticket->is_story) {
                $carryOverData['user_stories_count'] += 1;
                $subtasks = $ticket->subtasks;
                foreach ($subtasks as $subtask) {
                    $carryOverData['subtasks_count'] += 1;
                    $carryOverData['worked_hours'] += $subtask->worked_hours;
                    $carryOverData['total_tickets_count'] += 1;
                    if ($subtask->state == Ticket::CLOSED_STATE) {
                        $carryOverData['closed_subtasks_count'] += 1;
                        $carryOverData['closed_subtasks'][] = $subtask->number.' '.$subtask->name;
                    }

                }
            }
        }

        return $carryOverData;
    }

}
