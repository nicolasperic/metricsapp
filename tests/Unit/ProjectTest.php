<?php

namespace Tests\Unit;

use App\Project;
use App\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_create_a_project()
    {
        $project = factory(Project::class)->create([
            'name' => 'Test Project'
        ]);

        $this->assertEquals('Test Project', $project->name);
    }

    /** @test */
    function can_assign_tickets_to_a_project()
    {
        $project = factory(Project::class)->create();

        $ticketA = factory(Ticket::class)->create([
            'project_id' => $project
        ]);
        $ticketB = factory(Ticket::class)->create([
            'project_id' => $project
        ]);
        $ticketC = factory(Ticket::class)->create([
            'project_id' => $project
        ]);


        $this->assertEquals(3, $project->tickets->count());
    }

    //can_add_members_to_a_space

    /**
     * el equipo, lo armo multi space?
     *
     * las personas en Assembla no dependen de espacios
     */

}
