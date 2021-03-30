<?php

namespace Tests\Unit;

use App\Integration\AssemblaGateway;
use App\Ticket;
use App\TicketTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_retrieve_ticket_by_assembla_id()
    {
        $ticket = Ticket::factory()->completed()->create([
            'name' => '[US] Test Ticket',
            'number' => 1234,
            'ticket_assembla_id' => 'assembla_id_1234'
        ]);

        $ticketWithAssemblaId = Ticket::getTicketByAssemblaId('assembla_id_1234');
        $this->assertEquals($ticketWithAssemblaId->name, $ticket->name);
        $this->assertEquals($ticketWithAssemblaId->number, $ticket->number);
        $this->assertEquals($ticketWithAssemblaId->ticket_assembla_id, $ticket->ticket_assembla_id);
    }
    /** @test */
    function can_calculate_lead_time_for_a_completed_ticket()
    {
        $ticket = Ticket::factory()->completed()->create([
            'created_at' => Carbon::parse('7 days ago'),
            'completed_at' => Carbon::parse('+1 week'),
        ]);

        $this->assertEquals(14, $ticket->getLeadTime());
    }

    /** @test */
    function cannot_calculate_lead_time_for_a_not_completed_ticket()
    {
        $ticket = Ticket::factory()->create([
            'created_at' => Carbon::parse('7 days ago'),
        ]);

        $this->assertEquals(null, $ticket->getLeadTime());
    }

    /** @test */
    function can_calculate_cycle_time_for_a_completed_ticket()
    {
        $ticket = Ticket::factory()->completed()->create([
            'started_at' => Carbon::parse('5 days ago'),
            'completed_at' => Carbon::parse('+1 week'),
        ]);

        $this->assertEquals(12, $ticket->getCycleTime());
    }

    /** @test */
    function cannot_calculate_cycle_time_for_a_not_completed_ticket()
    {
        $ticket = Ticket::factory()->create([
            'created_at' => Carbon::parse('7 days ago'),
            'started_at' => Carbon::parse('5 days ago'),
        ]);

        $this->assertEquals(null, $ticket->getCycleTime());
    }

    /** @test */
    function can_associate_a_subtask_to_a_user_story()
    {
        $userstory = Ticket::factory()->create();
        $subtask = Ticket::factory()->create([
            'name' => 'TIC-1: subtask name',
            'is_story' => false
        ]);


        $userstory->subtasks()->save($subtask,['relationship' => 5]);


        foreach ($userstory->subtasks as $subtask) {
            $this->assertEquals('TIC-1: subtask name',$subtask->name);
            $this->assertEquals(0,$subtask->is_story);
            $this->assertEquals(5,$subtask->pivot->relationship);
        }
    }

    /** @test */
    function can_determine_parent_story()
    {
        $userstory = Ticket::factory()->create([
            'number' => 10,
            'name' =>'IC-1: userstory name',
        ]);
        $subtask = Ticket::factory()->create([
            'name' => 'TIC-2: subtask name',
            'is_story' => false,
        ]);


        $userstory->subtasks()->save($subtask,['relationship' => 5]);


        $parentStory = $subtask->parent();


        $this->assertEquals('IC-1: userstory name',$parentStory->name);
        $this->assertEquals(1,$parentStory->is_story);

    }


    /** @test */
    function can_calculate_story_total_hours_based_on_subtasks()
    {
        $userstory = Ticket::factory()->create();
        $subtaskA = Ticket::factory()->create([
            'name' => 'TIC-1: subtask name A',
            'is_story' => false,
            'worked_hours' => 5
        ]);
        $subtaskB = Ticket::factory()->create([
            'name' => 'TIC-2: subtask name B',
            'is_story' => false,
            'worked_hours' => 3,
        ]);
        $subtaskC = Ticket::factory()->create([
            'name' => 'TIC-3: subtask name C',
            'is_story' => false,
            'worked_hours' => 1,
        ]);

        $relatedD = Ticket::factory()->create([
            'name' => 'TIC-4: realted Story D',
            'is_story' => true,
            'worked_hours' => 10,
        ]);

        $userstory->subtasks()->save($subtaskA,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskB,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskC,['relationship' => 5]);
        $userstory->subtasks()->save($relatedD,['relationship' => 2]);

        $this->assertEquals(9, $userstory->getSubtasksTotalWorkedHours());
    }

    /** @test */
    function can_validate_userstory_invalid_subtasks_statuses()
    {
        $userstory = Ticket::factory()->completed()->create([
            'status' => 'Done',
            'created_at' => Carbon::parse('6 days ago'),
        ]);
        $subtaskA = Ticket::factory()->create([
            'name' => 'TIC-1: subtask name A',
            'is_story' => false,
            'status' => 'In Progress',
        ]);
        $subtaskB = Ticket::factory()->completed()->create([
            'name' => 'TIC-2: subtask name B',
            'is_story' => false,
            'status' => 'Done',
            'created_at' => Carbon::parse('4 days ago'),
        ]);
        $subtaskC = Ticket::factory()->create([
            'name' => 'TIC-3: subtask name C',
            'is_story' => false,
            'status' => 'Invalid',
        ]);
        $subtaskD = Ticket::factory()->create([
            'name' => 'TIC-4: subtask name D',
            'is_story' => false,
            'status' => 'Accepted',
        ]);

        $userstory->subtasks()->save($subtaskA,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskB,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskC,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskD,['relationship' => 5]);

        $subtasks = $userstory->getInvalidStatusSubtasks();

        $subtasks->assertContains($subtaskA);
        $subtasks->assertNotContains($subtaskB);
        $subtasks->assertNotContains($subtaskC);
        $subtasks->assertContains($subtaskD);
        $this->assertEquals(2, $subtasks->count());
    }

    /** @test */
    function can_validate_userstory_valid_subtasks_statuses()
    {
        $userstory = Ticket::factory()->completed()->create([
            'status' => 'Done',
            'created_at' => Carbon::parse('6 days ago'),
        ]);
        $subtaskA = Ticket::factory()->completed()->create([
            'name' => 'TIC-2: subtask name B',
            'is_story' => false,
            'status' => 'Done',
            'created_at' => Carbon::parse('4 days ago'),
        ]);
        $subtaskB = Ticket::factory()->create([
            'name' => 'TIC-3: subtask name C',
            'is_story' => false,
            'status' => 'Invalid',
        ]);

        $userstory->subtasks()->save($subtaskA,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskB,['relationship' => 5]);

        $subtasks = $userstory->getInvalidStatusSubtasks();


        $subtasks->assertNotContains($subtaskA);
        $subtasks->assertNotContains($subtaskB);
        $this->assertEquals(0, $subtasks->count());
    }

    /** @test */
    function can_calculate_total_tracked_time_for_a_ticket()
    {
        /** @var Ticket $ticket */
        $ticket = Ticket::factory()->create([
            'ticket_assembla_id' => '1234abcd',
            'number' => 1122,
        ]);

        /** @var TicketTime $ticketTime */
        TicketTime::factory()->create([
            'hours' => 1.5,
            'ticket_assembla_id' => '1234abcd',
        ]);

        TicketTime::factory()->create([
            'hours' => 2,
            'ticket_assembla_id' => '1234abcd',
        ]);

        TicketTime::factory()->create([
            'hours' => 5,
            'ticket_assembla_id' => '1234abcd',
        ]);

        //tracked on a different ticket
        TicketTime::factory()->create([
            'hours' => 15,
            'ticket_assembla_id' => '9876zxy',
        ]);

        $this->assertEquals(8.5, $ticket->getTotalTrackedTime());
    }

    /** @test */
    function can_calculate_total_tracked_time_for_a_user_story()
    {
        /** @var Ticket $userstory */
        $userstory = Ticket::factory()->create();
        $subtaskA = Ticket::factory()->create([
            'ticket_assembla_id' => 'subtaska',
            'name' => 'TIC-1: subtask name A',
            'is_story' => false,
            'worked_hours' => 5
        ]);
        TicketTime::factory()->create([
            'hours' => 1.5,
            'ticket_assembla_id' => 'subtaska',
        ]);
        TicketTime::factory()->create([
            'hours' => 1.5,
            'ticket_assembla_id' => 'subtaska',
        ]);
        TicketTime::factory()->create([
            'hours' => 2,
            'ticket_assembla_id' => 'subtaska',
        ]);
        $subtaskB = Ticket::factory()->create([
            'ticket_assembla_id' => 'subtaskb',
            'name' => 'TIC-2: subtask name B',
            'is_story' => false,
            'worked_hours' => 3,
        ]);
        TicketTime::factory()->create([
            'hours' => 1,
            'ticket_assembla_id' => 'subtaskb',
        ]);
        TicketTime::factory()->create([
            'hours' => 2,
            'ticket_assembla_id' => 'subtaskb',
        ]);
        $subtaskC = Ticket::factory()->create([
            'ticket_assembla_id' => 'subtaskc',
            'name' => 'TIC-3: subtask name C',
            'is_story' => false,
            'worked_hours' => 1,
        ]);
        TicketTime::factory()->create([
            'hours' => 1,
            'ticket_assembla_id' => 'subtaskc',
        ]);

        $relatedD = Ticket::factory()->create([
            'ticket_assembla_id' => 'relatedd',
            'name' => 'TIC-4: realted Story D',
            'is_story' => true,
            'worked_hours' => 10,
        ]);
        TicketTime::factory()->create([
            'hours' => 10,
            'ticket_assembla_id' => 'relatedd',
        ]);

        $userstory->subtasks()->save($subtaskA,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskB,['relationship' => 5]);
        $userstory->subtasks()->save($subtaskC,['relationship' => 5]);
        $userstory->subtasks()->save($relatedD,['relationship' => 2]);

        $this->assertEquals(9, $userstory->getSubtasksTotalWorkedHours());
        $this->assertEquals(9, $userstory->getTotalTrackedTime());
    }
}