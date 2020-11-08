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
        $sprint = factory(Sprint::class)->create();
        $ticketA = factory(Ticket::class)->create();
        $ticketB = factory(Ticket::class)->create();
        $ticketC = factory(Ticket::class)->create();


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC]);

        $this->assertEquals(3, $sprint->getTotalTickets());
    }

    /** @test */
    function a_sprint_can_have_multiple_projects()
    {
        $sprint = factory(Sprint::class)->create();

        $projectA = factory(Project::class)->create([
            'name' => 'Project A'
        ]);
        $projectB = factory(Project::class)->create([
            'name' => 'Project B'
        ]);
        $projectC = factory(Project::class)->create([
            'name' => 'Project C'
        ]);

        $sprint->projects()->saveMany([$projectA, $projectB, $projectC]);

        $this->assertEquals(3, $sprint->projects()->count());
    }
}
