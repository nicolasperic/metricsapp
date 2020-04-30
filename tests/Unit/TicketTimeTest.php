<?php

namespace Tests\Unit;

use App\Ticket;
use App\TicketTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_track_time_for_a_ticket()
    {
        $ticket = factory(Ticket::class)->create([
            'ticket_assembla_id' => '1234abcd',
            'number' => 1122,
        ]);

        /** @var TicketTime $ticketTime */
        $ticketTime = factory(TicketTime::class)->create([
            'description' => 'Tracking time test',
            'hours' => 1.5,
            'begin_at' => Carbon::parse('-1 hour'),
            'end_at' => Carbon::parse('+1 hour'),
            'ticket_number' => 1122,
            'ticket_assembla_id' => '1234abcd',
            'project_assembla_id' => '12345abcde',
            'user_assembla_id' => '001abcf'
        ]);

        $ticketForTime = $ticketTime->ticket();

        $this->assertEquals($ticketForTime->number, $ticket->number);
        $this->assertEquals($ticketForTime->ticket_assembla_id, $ticket->ticket_assembla_id);

    }
}
