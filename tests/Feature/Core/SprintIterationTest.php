<?php

namespace Tests\Feature\Core;

use App\Dto\SprintDto;
use App\Exceptions\MilestoneNotCreatedException;
use App\Exceptions\SprintIterationException;
use App\Integration\AssemblaGateway;
use App\Project;
use App\Sprint;
use App\SprintIteration;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class SprintIterationTest
    extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_iterate_a_sprint()
    {
        $user = User::factory()->create([
            'user_assembla_id' => 'TEST1234'
        ]);

        $projectA = Project::factory()->create([
            'name'                => 'Project A',
            'wikiname'            => 'canaldeautoparte',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQY'
        ]);
        $sprintA = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQY',
            'name'                => 'Sprint A',
            'sprint_assembla_id'  => '13040067',
            'is_active'           => 1,
            'planner_type'        => Sprint::PLANNER_TYPE_CURRENT,
        ]);

        $projectA->sprints()->save($sprintA);
        $user->projects()->save($projectA);


        $userstory = Ticket::factory()->create([
            'status'         => 'Accepted',
            'state'          => Ticket::OPEN_STATE,
            'created_at'     => Carbon::parse('6 days ago'),
            'worked_hours'   => 0,
            'hierarchy_type' => Ticket::HIERARCHY_STORY,
            'number' => 22
        ]);
        $subtaskA = Ticket::factory()->create([
            'name'           => 'TIC-1: subtask name A',
            'is_story'       => false,
            'status'         => 'In Progress',
            'worked_hours'   => 2.5,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $subtaskB = Ticket::factory()->completed()->create([
            'name'           => 'TIC-2: subtask name B',
            'is_story'       => false,
            'status'         => 'Done',
            'created_at'     => Carbon::parse('4 days ago'),
            'worked_hours'   => 3,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);

        $userstoryB = Ticket::factory()->completed()->create([
            'status'         => 'Done',
            'created_at'     => Carbon::parse('6 days ago'),
            'worked_hours'   => 0,
            'hierarchy_type' => Ticket::HIERARCHY_STORY,
            'number' => 25
        ]);
        $subtaskC = Ticket::factory()->completed()->create([
            'name'           => 'TIC-3: subtask name C',
            'is_story'       => false,
            'status'         => 'Invalid',
            'worked_hours'   => 0,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $subtaskD = Ticket::factory()->completed()->create([
            'name'           => 'TIC-4: subtask name D',
            'is_story'       => false,
            'status'         => 'New',
            'worked_hours'   => 0,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $ticketA = Ticket::factory()->create([
            'name'           => 'TIC-5: no plan level ticket',
            'is_story'       => false,
            'status'         => 'In Progress',
            'worked_hours'   => 4.5,
            'hierarchy_type' => Ticket::HIERARCHY_NO_PLAN_LEVEL,
            'number' => 23
        ]);
        $ticketB = Ticket::factory()->create([
            'name'           => 'TIC-5: no plan level ticket',
            'is_story'       => false,
            'status'         => 'Accepted',
            'worked_hours'   => 4.5,
            'hierarchy_type' => Ticket::HIERARCHY_NO_PLAN_LEVEL,
            'number' => 24
        ]);
        $epic = Ticket::factory()->create([
            'name'           => 'EPIC-5: epic',
            'is_story'       => false,
            'status'         => 'Accepted',
            'worked_hours'   => 5,
            'hierarchy_type' => Ticket::HIERARCHY_EPIC,
            'number' => 27
        ]);

        $userstory->subtasks()->save($subtaskA, ['relationship' => 5]);
        $userstory->subtasks()->save($subtaskB, ['relationship' => 5]);
        $userstoryB->subtasks()->save($subtaskC, ['relationship' => 5]);
        $userstoryB->subtasks()->save($subtaskD, ['relationship' => 5]);


        /** @var \App\SprintIteration $twoWeeksIteration */
        $twoWeeksIteration = SprintIteration::factory()->create([
            'sprint_duration' => 2,
            'sprint_start_weekday' => 1,//Lunes
            'sprint_prefix' => 'SE - ',
            'iteration_status' => SprintIteration::ITERATION_STATUS_RUNNING,
            'next_iteration_start_date' => '2021-03-22',
            'iterations_count' => 2,
            'iteration_user_assembla_id' => $user->user_assembla_id
        ]);

        $projectA->sprintIteration()->save($twoWeeksIteration);


        $sprintA->tickets()->saveMany([$userstory, $userstoryB, $subtaskA, $subtaskB, $subtaskC, $subtaskD, $ticketA, $ticketB, $epic]);

        $this->instance(
            AssemblaGateway::class,
            Mockery::mock(AssemblaGateway::class, function (MockInterface $mock) {
                $mock->shouldReceive('setUser')->once();
                $milestoneData = [
                    'title' => 'SE - 22MAR21',
                    'is_completed' => false,
                    'start_date' => '22/03/2021',
                    'due_date' => '04/04/2021',
                    'id' => 'TESTID1234',
                    'space_id' => 'dpT43eCVCr54kBacwqjQYw',
                    'planner_type' => \App\Sprint::PLANNER_TYPE_CURRENT
                ];
                $milestone = new SprintDto($milestoneData);
                $mock->shouldReceive('updateMilestone')->once()->andReturn(true);
                $mock->shouldReceive('createMilestone')->once()->andReturn($milestone);
                $mock->shouldReceive('updateTicket')->times(4)->andReturn(true);
            })
        );

        Carbon::setTestNow(Carbon::parse('2021-03-22'));
        $sprint = $twoWeeksIteration->iterate();



        $this->assertEquals('SE - 22MAR21', $sprint->name);
        $this->assertEquals(3, $twoWeeksIteration->fresh()->iterations_count);
        $this->assertEquals('2021/04/05', $twoWeeksIteration->fresh()->next_iteration_start_date);
        $this->assertEquals(SprintIteration::ITERATION_STATUS_RUNNING, $twoWeeksIteration->fresh()->iteration_status);
    }

    /** @test */
    function can_handle_tickets_on_sprint_iteration()
    {
        $userA = User::factory()->create([
            'user_assembla_id' => 'TEST1234'
        ]);

        $projectA = Project::factory()->create([
            'name'                => 'Project A',
            'wikiname'            => 'canaldeautopartes',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $sprintA = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                => 'Sprint A',
            'sprint_assembla_id'  => '13040067',
            'is_active'           => 1,
            'planner_type'        => Sprint::PLANNER_TYPE_CURRENT,
        ]);

        $projectA->sprints()->save($sprintA);
        $userA->projects()->save($projectA);


        $userstory = Ticket::factory()->create([
            'status'         => 'Accepted',
            'state'          => Ticket::OPEN_STATE,
            'created_at'     => Carbon::parse('6 days ago'),
            'worked_hours'   => 0,
            'hierarchy_type' => Ticket::HIERARCHY_STORY,
            'number' => 22
        ]);
        $subtaskA = Ticket::factory()->create([
            'name'           => 'TIC-1: subtask name A',
            'is_story'       => false,
            'status'         => 'In Progress',
            'worked_hours'   => 2.5,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $subtaskB = Ticket::factory()->completed()->create([
            'name'           => 'TIC-2: subtask name B',
            'is_story'       => false,
            'status'         => 'Done',
            'created_at'     => Carbon::parse('4 days ago'),
            'worked_hours'   => 3,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);

        $userstoryB = Ticket::factory()->completed()->create([
            'status'         => 'Done',
            'created_at'     => Carbon::parse('6 days ago'),
            'worked_hours'   => 0,
            'hierarchy_type' => Ticket::HIERARCHY_STORY,
            'number' => 25
        ]);
        $subtaskC = Ticket::factory()->completed()->create([
            'name'           => 'TIC-3: subtask name C',
            'is_story'       => false,
            'status'         => 'Invalid',
            'worked_hours'   => 0,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $subtaskD = Ticket::factory()->completed()->create([
            'name'           => 'TIC-4: subtask name D',
            'is_story'       => false,
            'status'         => 'New',
            'worked_hours'   => 0,
            'hierarchy_type' => Ticket::HIERARCHY_SUBTASK
        ]);
        $ticketA = Ticket::factory()->create([
            'name'           => 'TIC-5: no plan level ticket',
            'is_story'       => false,
            'status'         => 'In Progress',
            'worked_hours'   => 4.5,
            'hierarchy_type' => Ticket::HIERARCHY_NO_PLAN_LEVEL,
            'number' => 23
        ]);
        $ticketB = Ticket::factory()->create([
            'name'           => 'TIC-5: no plan level ticket',
            'is_story'       => false,
            'status'         => 'Accepted',
            'worked_hours'   => 4.5,
            'hierarchy_type' => Ticket::HIERARCHY_NO_PLAN_LEVEL,
            'number' => 24
        ]);
        $epic = Ticket::factory()->create([
            'name'           => 'EPIC-5: epic',
            'is_story'       => false,
            'status'         => 'Accepted',
            'worked_hours'   => 5,
            'hierarchy_type' => Ticket::HIERARCHY_EPIC,
            'number' => 27
        ]);

        $userstory->subtasks()->save($subtaskA, ['relationship' => 5]);
        $userstory->subtasks()->save($subtaskB, ['relationship' => 5]);
        $userstoryB->subtasks()->save($subtaskC, ['relationship' => 5]);
        $userstoryB->subtasks()->save($subtaskD, ['relationship' => 5]);

        /** @var \App\SprintIteration $twoWeeksIteration */
        $twoWeeksIteration = SprintIteration::factory()->create([
            'sprint_duration' => 2,
            'sprint_start_weekday' => 1,//Lunes
            'sprint_prefix' => 'SE - ',
            'iteration_status' => SprintIteration::ITERATION_STATUS_RUNNING,
            'next_iteration_start_date' => '2021-03-22',
            'iterations_count' => 2,
            'iteration_user_assembla_id' => 'TEST1234'
        ]);
        $projectA->sprintIteration()->save($twoWeeksIteration);


        $sprintA->tickets()->saveMany([$userstory, $userstoryB, $subtaskA, $subtaskB, $subtaskC, $subtaskD, $ticketA, $ticketB, $epic]);



        $this->instance(
            AssemblaGateway::class,
            Mockery::mock(AssemblaGateway::class, function (MockInterface $mock) {
                $mock->shouldReceive('setUser')->once();
                $milestoneData = [
                    'title' => 'SE - 22MAR21',
                    'is_completed' => false,
                    'start_date' => '22/03/2021',
                    'due_date' => '04/04/2021',
                    'id' => 'TESTID1234',
                    'space_id' => 'dpT43eCVCr54kBacwqjQYw',
                    'planner_type' => \App\Sprint::PLANNER_TYPE_CURRENT
                ];
                $milestone = new SprintDto($milestoneData);
                $mock->shouldReceive('updateMilestone')->once()->andReturn(true);
                $mock->shouldReceive('createMilestone')->once()->andReturn($milestone);
                $mock->shouldReceive('updateTicket')->times(4)->andReturn(true);
            })
        );


        Carbon::setTestNow(Carbon::parse('2021-03-22'));
        $newSprint = $twoWeeksIteration->iterate();

        //carry over> 'hierarchy_type' '!=' Ticket::HIERARCHY_SUBTASK n 'state' '=' Ticket::OPEN_STATE
        //userstory 22 , ticketA 23  ticketB 24 n epic 27
        $newSprint->tickets->assertContains($epic);
        $newSprint->tickets->assertContains($userstory);
        $newSprint->tickets->assertContains($subtaskA);
        $newSprint->tickets->assertContains($subtaskB);
        $newSprint->tickets->assertContains($ticketA);
        $newSprint->tickets->assertContains($ticketB);

        $newSprint->tickets->assertNotContains($userstoryB);
        $newSprint->tickets->assertNotContains($subtaskC);
        $newSprint->tickets->assertNotContains($subtaskD);

        $sprintA->tickets->assertNotContains($epic);
        $sprintA->tickets->assertNotContains($userstory);
        $sprintA->tickets->assertNotContains($subtaskA);
        $sprintA->tickets->assertNotContains($subtaskB);
        $sprintA->tickets->assertNotContains($ticketA);
        $sprintA->tickets->assertNotContains($ticketB);

        $sprintA->tickets->assertContains($userstoryB);
        $sprintA->tickets->assertContains($subtaskC);
        $sprintA->tickets->assertContains($subtaskD);
    }

    /** @test */
    function will_halt_iteration_when_milestone_not_created()
    {
        $this->instance(
            AssemblaGateway::class,
            Mockery::mock(AssemblaGateway::class, function (MockInterface $mock) {
                $mock->shouldReceive('setUser')->once();
                $mock->shouldReceive('createMilestone')->once()->andReturn(false);
                //milestone not created
            })
        );

        $userA = User::factory()->create([
            'user_assembla_id' => 'TEST1234'
        ]);

        $projectA = Project::factory()->create([
            'name'                => 'Project A',
            'wikiname'            => 'canaldeautopartes',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $sprintA = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                => 'Sprint A',
            'sprint_assembla_id'  => '13040067',
            'is_active'           => 1,
            'planner_type'        => Sprint::PLANNER_TYPE_CURRENT,
        ]);

        $projectA->sprints()->save($sprintA);
        $userA->projects()->save($projectA);

        $twoWeeksIteration = SprintIteration::factory()->create([
            'sprint_duration' => 2,
            'sprint_start_weekday' => 1,//Lunes
            'sprint_prefix' => 'SE - ',
            'iteration_status' => SprintIteration::ITERATION_STATUS_RUNNING,
            'next_iteration_start_date' => '2021-03-22',
            'iterations_count' => 2,
            'iteration_user_assembla_id' => 'TEST1234'
        ]);
        $projectA->sprintIteration()->save($twoWeeksIteration);


        $expectedException = false;
        try {
            /** @var \App\SprintIteration $twoWeeksIteration */
            $twoWeeksIteration->iterate();
        } catch(MilestoneNotCreatedException $e) {
            $expectedException = true;
        }

        $this->assertTrue($expectedException);
        $this->assertEquals(2, $twoWeeksIteration->fresh()->iterations_count);
        $this->assertEquals(null, $twoWeeksIteration->fresh()->next_iteration_start_date);
        $errorMessage = \App\Importer\SprintIteration::ERROR_MILESTONE_NOT_CREATED;
        $this->assertEquals($errorMessage, $twoWeeksIteration->fresh()->error_message);
        $this->assertEquals(SprintIteration::ITERATION_STATUS_STOPPED, $twoWeeksIteration->fresh()->iteration_status);
    }

    /** @test */
    function can_display_error_message_when_iteration_fails_without_an_user()
    {
        $this->instance(
            AssemblaGateway::class,
            Mockery::mock(AssemblaGateway::class, function (MockInterface $mock) {
                //shouldn't be called at all, but we are replacing it still just in case : p
            })
        );

        $projectA = Project::factory()->create([
            'name'                => 'Project A',
            'wikiname'            => 'canaldeautopartes',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $sprintA = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                => 'Sprint A',
            'sprint_assembla_id'  => '13040067',
            'is_active'           => 1,
            'planner_type'        => Sprint::PLANNER_TYPE_CURRENT,
        ]);

        $projectA->sprints()->save($sprintA);

        $twoWeeksIteration = SprintIteration::factory()->create([
            'sprint_duration' => 2,
            'sprint_start_weekday' => 1,//Lunes
            'sprint_prefix' => 'SE - ',
            'iteration_status' => SprintIteration::ITERATION_STATUS_RUNNING,
            'next_iteration_start_date' => '2021-03-22',
            'iterations_count' => 2,
            'iteration_user_assembla_id' => 'NOTEXISTINGUSER'
        ]);
        $projectA->sprintIteration()->save($twoWeeksIteration);


        $expectedException = false;
        try {
            /** @var \App\SprintIteration $twoWeeksIteration */
            $twoWeeksIteration->iterate();
        } catch(SprintIterationException $e) {
            $expectedException = true;
        }

        $this->assertTrue($expectedException);
        $this->assertEquals(2, $twoWeeksIteration->fresh()->iterations_count);
        $this->assertEquals(null, $twoWeeksIteration->fresh()->next_iteration_start_date);
        $errorMessage = SprintIteration::ERROR_NO_USER;
        $this->assertEquals($errorMessage, $twoWeeksIteration->fresh()->error_message);
        $this->assertEquals(SprintIteration::ITERATION_STATUS_STOPPED, $twoWeeksIteration->fresh()->iteration_status);
    }

    /** @test */
    function can_display_error_message_when_iteration_fails_without_a_current_sprint()
    {

        $this->instance(
            AssemblaGateway::class,
            Mockery::mock(AssemblaGateway::class, function (MockInterface $mock) {
                //shouldn't be called at all, but we are replacing it still just in case : p
            })
        );

        $userA = User::factory()->create([
            'user_assembla_id' => 'TEST1234'
        ]);

        $projectA = Project::factory()->create([
            'name'                => 'Project A',
            'wikiname'            => 'canaldeautopartes',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $sprintA = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                => 'Sprint A',
            'sprint_assembla_id'  => '13040067',
            'is_active'           => 1,
            'planner_type'        => Sprint::PLANNER_TYPE_BACKLOG,//NOT CURRENT! This is the key of this test
        ]);

        $projectA->sprints()->save($sprintA);

        $twoWeeksIteration = SprintIteration::factory()->create([
            'sprint_duration' => 2,
            'sprint_start_weekday' => 1,//Lunes
            'sprint_prefix' => 'SE - ',
            'iteration_status' => SprintIteration::ITERATION_STATUS_RUNNING,
            'next_iteration_start_date' => '2021-03-22',
            'iterations_count' => 2,
            'iteration_user_assembla_id' => $userA->user_assembla_id
        ]);
        $projectA->sprintIteration()->save($twoWeeksIteration);


        $expectedException = false;
        try {
            /** @var \App\SprintIteration $twoWeeksIteration */
            $twoWeeksIteration->iterate();
        } catch(SprintIterationException $e) {
            $expectedException = true;
        }

        $this->assertTrue($expectedException);
        $this->assertEquals(2, $twoWeeksIteration->fresh()->iterations_count);
        $this->assertEquals(null, $twoWeeksIteration->fresh()->next_iteration_start_date);

        $sprintErrorMessage = str_replace('@wikiname',$projectA->wikiname, SprintIteration::ERROR_NO_CURRENT_SPRINT);
        $this->assertEquals($sprintErrorMessage, $twoWeeksIteration->fresh()->error_message);
        $this->assertEquals(SprintIteration::ITERATION_STATUS_STOPPED, $twoWeeksIteration->fresh()->iteration_status);
    }
}
