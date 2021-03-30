<?php

namespace Tests\Feature;


use App\Project;
use App\Sprint;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestResult;
use Tests\TestCase;

class ViewSprintListingTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function guest_cannot_view_a_sprint_list_page()
    {
        $response = $this->get('/milestones');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_view_a_sprint_page()
    {

        $project =  Project::factory()->create([
            'name' => 'Project Test 1',
            'wikiname' => 'test'
        ]);
        $sprint =  Sprint::factory()->create([
            'name' => 'Sprint Test 1',
            'sprint_assembla_id' => '123456'
        ]);
        $project->sprints()->save($sprint);
        $response = $this->get('/spaces/'.$project->wikiname.'/milestones/'.$sprint->sprint_assembla_id);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    function user_can_view_a_list_of_their_own_sprints()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $sprintA =  Sprint::factory()->create([
            'name' => 'Sprint Test A',
            'sprint_assembla_id' => '1',
        ]);
        $sprintB =  Sprint::factory()->create([
            'name' => 'Sprint Test B',
            'sprint_assembla_id' => '2',
        ]);
        $sprintC =  Sprint::factory()->create([
            'name' => 'Sprint Test C',
            'sprint_assembla_id' => '3',
        ]);
        $user->sprints()->saveMany([$sprintA, $sprintB, $sprintC]);

        $project =  Project::factory()->create([
            'name' => 'Project Test 1',
            'wikiname' => 'test'
        ]);
        $project->sprints()->saveMany([$sprintA, $sprintB, $sprintC]);

        $response = $this->actingAs($user)->get('/milestones');
        $response->assertStatus(200);
        $response->data('openSprints')->assertContains($sprintA);
        $response->data('openSprints')->assertContains($sprintB);
        $response->data('openSprints')->assertContains($sprintC);
    }

    /** @test */
    function user_can_only_view_a_list_of_their_own_sprints()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $sprintA =  Sprint::factory()->create([
            'name' => 'Sprint Test A',
            'sprint_assembla_id' => '1',
        ]);
        $sprintB =  Sprint::factory()->create([
            'name' => 'Sprint Test B',
            'sprint_assembla_id' => '2',
        ]);
        $sprintC =  Sprint::factory()->create([
            'name' => 'Sprint Test C',
            'sprint_assembla_id' => '3',
        ]);
        $sprintD =  Sprint::factory()->create([
            'name' => 'Sprint Test D',
            'sprint_assembla_id' => '4',
        ]);

        $user->sprints()->saveMany([$sprintA, $sprintB, $sprintC]);
        $otherUser->sprints()->save($sprintD);

        $project =  Project::factory()->create([
            'name' => 'Project Test 1',
            'wikiname' => 'test'
        ]);
        $project->sprints()->saveMany([$sprintA, $sprintB, $sprintC, $sprintD]);

        $response = $this->actingAs($user)->get('/milestones');
        $response->assertStatus(200);
        $response->data('openSprints')->assertContains($sprintA);
        $response->data('openSprints')->assertContains($sprintB);
        $response->data('openSprints')->assertContains($sprintC);
        $response->data('openSprints')->assertNotContains($sprintD);
    }


    /** @test */
    function user_can_view_a_sprint_he_owns_page()
    {
        $this->withoutExceptionHandling();

        $project =  Project::factory()->create([
            'name' => 'Project Test 1',
            'wikiname' => 'test'
        ]);

        $sprint =  Sprint::factory()->create([
            'name' => 'Sprint Test 1',
            'sprint_assembla_id' => '12341234',
        ]);

        $project->sprints()->save($sprint);

        $user = User::factory()->create();
        $user->sprints()->save($sprint);

        $response = $this->actingAs($user)->get('/spaces/'.$project->wikiname.'/milestones/' .$sprint->sprint_assembla_id);
        $response->assertStatus(200);
        $response->assertSee('Sprint Test 1');
    }


    /** @test */
    function user_cannot_view_a_sprint_he_does_not_own()
    {
        $project =  Project::factory()->create([
            'name' => 'Project Test 1',
            'wikiname' => 'test'
        ]);
        $sprint =  Sprint::factory()->create([
            'name' => 'Sprint Test 1',
            'sprint_assembla_id' => '123456'
        ]);
        $project->sprints()->save($sprint);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get("spaces/test/milestones/123456");


        $response->assertStatus(404);
    }
}
