<?php

namespace Tests\Unit;

use App\Ticket;
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