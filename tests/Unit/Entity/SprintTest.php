<?php

namespace Tests\Unit;

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
    function can_calculate_sprint_total_story_points()
    {
        $sprint = factory(Sprint::class)->create();
        $ticketA = factory(Ticket::class)->create([
            'story_points' => 5,
        ]);
        $ticketB = factory(Ticket::class)->create([
            'story_points' => 8,
        ]);
        $ticketC = factory(Ticket::class)->create([
            'story_points' => 13,
        ]);


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC]);

        $this->assertEquals(3, $sprint->getTotalTickets());
        $this->assertEquals(26, $sprint->getTotalStoryPoints());
    }

    /** @test */
    function can_calculate_sprint_completed_story_points()
    {
        $sprint = factory(Sprint::class)->create();
        $ticketA = factory(Ticket::class)->states('completed')->create([
            'story_points' => 5,
        ]);
        $ticketB = factory(Ticket::class)->states('completed')->create([
            'story_points' => 8,
        ]);
        $ticketC = factory(Ticket::class)->create([
            'story_points' => 13,
        ]);


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC]);

        $this->assertEquals(3, $sprint->getTotalTickets());
        $this->assertEquals(26, $sprint->getTotalStoryPoints());
        $this->assertEquals(13, $sprint->getCompletedStoryPoints());
    }

    /** @test */
    function can_calculate_sprint_completed_stories()
    {
        $sprint = factory(Sprint::class)->create();
        $ticketA = factory(Ticket::class)->states('completed')->create([
            'is_story' => true,
        ]);
        $ticketB = factory(Ticket::class)->states('completed')->create([
            'is_story' => true,
        ]);
        $ticketC = factory(Ticket::class)->states('completed')->create([
            'is_story' => false,
        ]);
        $ticketD = factory(Ticket::class)->create([
            'is_story' => false,
        ]);
        $ticketE = factory(Ticket::class)->create([
            'is_story' => true,
        ]);


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC, $ticketD, $ticketE]);

        $this->assertEquals(5, $sprint->getTotalTickets());
        $this->assertEquals(2, $sprint->getCompletedStories());
    }

    /** @test */
    function can_calculate_sprint_completed_subtasks()
    {
        $sprint = factory(Sprint::class)->create();
        $ticketA = factory(Ticket::class)->states('completed')->create([
            'is_story' => true,
        ]);
        $ticketB = factory(Ticket::class)->states('completed')->create([
            'is_story' => true,
        ]);
        $ticketC = factory(Ticket::class)->states('completed')->create([
            'is_story' => false,
        ]);
        $ticketD = factory(Ticket::class)->create([
            'is_story' => false,
        ]);
        $ticketE = factory(Ticket::class)->create([
            'is_story' => true,
        ]);


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC, $ticketD, $ticketE]);

        $this->assertEquals(5, $sprint->getTotalTickets());
        $this->assertEquals(1, $sprint->getCompletedSubtasks());
    }

    /** @test */
    function can_calculate_sprint_percent_completed_story_points()
    {
        $sprint = factory(Sprint::class)->create();
        $ticketA = factory(Ticket::class)->states('completed')->create([
            'story_points' => 5,
        ]);
        $ticketB = factory(Ticket::class)->states('completed')->create([
            'story_points' => 8,
        ]);
        $ticketC = factory(Ticket::class)->create([
            'story_points' => 8,
        ]);


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC]);

        $this->assertEquals(3, $sprint->getTotalTickets());
        $this->assertEquals(21, $sprint->getTotalStoryPoints());
        $this->assertEquals(61.9, $sprint->getPercentCompletedStoryPoints());
    }

    /** @test */
    function can_calculate_sprint_average_lead_time()
    {
        $sprint = factory(Sprint::class)->create();

        $ticketA = factory(Ticket::class)->states('completed')->create([
            'created_at' => Carbon::parse('6 days ago'),
            'completed_at' => Carbon::parse('+1 week'),
        ]);//13 days of lead time for ticket A
        $ticketB = factory(Ticket::class)->states('completed')->create([
            'created_at' => Carbon::parse('10 days ago'),
            'completed_at' => Carbon::parse('+5 days'),
        ]);//15 days of lead time for ticket B
        $ticketC = factory(Ticket::class)->states('completed')->create([
            'created_at' => Carbon::parse('5 days ago'),
            'completed_at' => Carbon::parse('+2 days'),
        ]);//7 days of lead time for ticket C
        $ticketD = factory(Ticket::class)->create([
            'created_at' => Carbon::parse('7 days ago'),
        ]);//Lead time can't be calculated since ticket D is not completed


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC, $ticketD]);

        $this->assertEquals(4, $sprint->getTotalTickets());
        $this->assertEquals(11.67, $sprint->getAverageLeadTime());
    }

    /** @test */
    function can_calculate_sprint_average_cycle_time()
    {
        $sprint = factory(Sprint::class)->create();

        $ticketA = factory(Ticket::class)->states('completed')->create([
            'started_at' => Carbon::parse('3 days ago'),
            'completed_at' => Carbon::parse('+1 week'),
        ]);//10 days of cycle time for ticket A
        $ticketB = factory(Ticket::class)->states('completed')->create([
            'started_at' => Carbon::parse('15 days ago'),
            'completed_at' => Carbon::parse('+5 days'),
        ]);//20 days of cycle time for ticket B
        $ticketC = factory(Ticket::class)->states('completed')->create([
            'started_at' => Carbon::parse('6 days ago'),
            'completed_at' => Carbon::parse('+1 days'),
        ]);//7 days of cycle time for ticket C
        $ticketD = factory(Ticket::class)->create([
            'started_at' => Carbon::parse('7 days ago'),
        ]);//Cycle time can't be calculated since ticket D is not completed


        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC, $ticketD]);

        $this->assertEquals(4, $sprint->getTotalTickets());
        $this->assertEquals(12.33, $sprint->getAverageCycleTime());
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
