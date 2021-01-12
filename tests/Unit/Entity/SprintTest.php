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
}
