<?php

namespace Tests\Unit;

use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    function ticket_status_can_be_updated()
    {
        $ticket = factory(Ticket::class)->create([
            'status' => 'in progress',
        ]);

        $this->assertEquals('in progress', $ticket->status);

    }

    /** @test */
    function can_calculate_lead_time_for_a_completed_ticket()
    {
        $ticket = factory(Ticket::class)->states('completed')->create([
            'created_at' => Carbon::parse('7 days ago'),
            'completed_at' => Carbon::parse('+1 week'),
        ]);

        $this->assertEquals(14, $ticket->getLeadTime());
    }

    /** @test */
    function cannot_calculate_lead_time_for_a_not_completed_ticket()
    {
        $ticket = factory(Ticket::class)->create([
            'created_at' => Carbon::parse('7 days ago'),
        ]);

        $this->assertEquals(null, $ticket->getLeadTime());
    }

    /** @test */
    function can_calculate_cycle_time_for_a_completed_ticket()
    {
        $ticket = factory(Ticket::class)->states('completed')->create([
            'started_at' => Carbon::parse('5 days ago'),
            'completed_at' => Carbon::parse('+1 week'),
        ]);

        $this->assertEquals(12, $ticket->getCycleTime());
    }

    /** @test */
    function cannot_calculate_cycle_time_for_a_not_completed_ticket()
    {
        $ticket = factory(Ticket::class)->create([
            'created_at' => Carbon::parse('7 days ago'),
            'started_at' => Carbon::parse('5 days ago'),
        ]);

        $this->assertEquals(null, $ticket->getCycleTime());
    }
}

/**
 * What should we build first?
 * - Purchasing tickets
 * - Inviting promoters
 * - Creating accounts
 * - Logging in as a promoter
 * - Adding concerts
 * - Editing concerts
 * - Publishing concerts
 * - Integration with Stripe Connect to do direct payouts
 * What should we test first?
 * - Purchasing tickets
 *  - View the concert listing
 *      + Allowing people to view published concerts
 *      + Not allowing people to view unpublished concerts
 *  - Pay for the tickets
 *  - View their purchased tickets in the browser
 *  - Send an email confirmation w/a link back to the tickets
 */