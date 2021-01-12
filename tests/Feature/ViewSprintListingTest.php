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
        $response = $this->get('/sprints');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_view_a_sprint_page()
    {

        $sprint =  Sprint::factory()->create([
            'name' => 'Sprint Test 1',
        ]);
        $response = $this->get('/sprints/'.$sprint->id);

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
        ]);
        $sprintB =  Sprint::factory()->create([
            'name' => 'Sprint Test B',
        ]);
        $sprintC =  Sprint::factory()->create([
            'name' => 'Sprint Test C',
        ]);
        $user->sprints()->saveMany([$sprintA, $sprintB, $sprintC]);


        $response = $this->actingAs($user)->get('/sprints');
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
        ]);
        $sprintB =  Sprint::factory()->create([
            'name' => 'Sprint Test B',
        ]);
        $sprintC =  Sprint::factory()->create([
            'name' => 'Sprint Test C',
        ]);
        $sprintD =  Sprint::factory()->create([
            'name' => 'Sprint Test C',
        ]);

        $user->sprints()->saveMany([$sprintA, $sprintB, $sprintC]);
        $otherUser->sprints()->save($sprintD);

        $response = $this->actingAs($user)->get('/sprints');
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
        ]);

        $sprint =  Sprint::factory()->create([
            'name' => 'Sprint Test 1',
        ]);

        $project->sprints()->save($sprint);

        $user = User::factory()->create();
        $user->sprints()->save($sprint);

        $response = $this->actingAs($user)->get('/sprints/'.$sprint->id);
        $response->assertStatus(200);
        $response->assertSee('Sprint Test 1');
    }


    /** @test */
    function user_cannot_view_a_sprint_he_does_not_own()
    {
        $sprint =  Sprint::factory()->create([
            'name' => 'Sprint Test 1',
        ]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/sprints/'.$sprint->id);


        $response->assertStatus(404);
    }
}
