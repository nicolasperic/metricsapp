<?php

namespace Tests\Unit\Metrics;

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
    function can_calculate_us_types_percentages()
    {
        /** @var \App\Sprint $sprint */
        $sprint = factory(Sprint::class)->create();
        $ticketA = factory(Ticket::class)->create([
            'type' => 'Requirement',
            'worked_hours' => 4.5,
        ]);
        $ticketB = factory(Ticket::class)->create([
            'type' => 'Requirement',
            'worked_hours' => 1,
        ]);
        $ticketC = factory(Ticket::class)->create([
            'type' => 'Bug',
            'worked_hours' => 7,
        ]);
        $ticketD = factory(Ticket::class)->create([
            'type' => 'Support',
            'worked_hours' => 1,
        ]);
        $ticketE = factory(Ticket::class)->create([
            'type' => 'Support',
            'worked_hours' => 0.5,
        ]);
        $ticketF = factory(Ticket::class)->create([
            'type' => 'Support',
            'worked_hours' => 1.75,
        ]);

        /** @var \App\Sprint $sprint */
        $sprintB = factory(Sprint::class)->create();
        $ticketG = factory(Ticket::class)->create([
            'type' => 'Requirement',
            'total_invested_hours' => 4.5,
        ]);
        $sprintB->tickets()->saveMany([$ticketG]);
        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC, $ticketD, $ticketE, $ticketF]);

        $typesPercentages = $sprint->getUserStoriesTypePercentages();

        $this->assertEquals(33.33, $typesPercentages['Requirement']['count_percentage']);
        $this->assertEquals(16.67, $typesPercentages['Bug']['count_percentage']);
        $this->assertEquals(50.00, $typesPercentages['Support']['count_percentage']);

        $this->assertEquals(5.5, $typesPercentages['Requirement']['total_invested_hours']);
        $this->assertEquals(7, $typesPercentages['Bug']['total_invested_hours']);
        $this->assertEquals(3.25, $typesPercentages['Support']['total_invested_hours']);
    }

    /**
     * @test
     */
    function can_calculate_empty_types_percentage()
    {
        /** @var \App\Sprint $sprint */
        $sprint = factory(Sprint::class)->create();
        $ticketA = factory(Ticket::class)->create([
            'type' => 'Requirement',
            'worked_hours' => 2.5,
        ]);
        $ticketB = factory(Ticket::class)->create([
            'type' => 'Requirement',
            'worked_hours' => 1,
        ]);
        $ticketC = factory(Ticket::class)->create([
            'worked_hours' => 2,
        ]);
        $ticketD = factory(Ticket::class)->create([
            'worked_hours' => 3,
        ]);
        $ticketF = factory(Ticket::class)->create([
            'worked_hours' => 4,
        ]);

        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC, $ticketD, $ticketF]);

        $typesPercentages = $sprint->getUserStoriesTypePercentages();

        $this->assertEquals(40.00, $typesPercentages['Requirement']['count_percentage']);
        $this->assertEquals(60.00, $typesPercentages['Empty']['count_percentage']);

        $this->assertEquals(3.5, $typesPercentages['Requirement']['total_invested_hours']);
        $this->assertEquals(9, $typesPercentages['Empty']['total_invested_hours']);
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

}
