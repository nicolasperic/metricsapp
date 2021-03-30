<?php

namespace Tests\Feature;

use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewProjectListingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_cannot_view_a_project_list_page()
    {
        $response = $this->get('/spaces');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_view_a_project_page()
    {
        $project =  Project::factory()->create([
            'name' => 'ProjectTest 1',
        ]);
        $response = $this->get('/spaces/'.$project->wikiname);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    function user_can_view_a_project_he_owns_page()
    {
        $this->withoutExceptionHandling();

        $project =  Project::factory()->create([
            'name' => 'Project Test 1',
        ]);

        $user = User::factory()->create();
        $user->projects()->save($project);

        $response = $this->actingAs($user)->get('/spaces/'.$project->wikiname);
        $response->assertStatus(200);
        $response->assertSee('Project Test 1');
    }

    /** @test */
    function user_can_view_a_list_of_their_own_projects()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $projectA = Project::factory()->create([
            'name' => 'Test Project A',
        ]);
        $projectB = Project::factory()->create([
            'name' => 'Test Project B',
        ]);
        $projectC = Project::factory()->create([
            'name' => 'Test Project C',
        ]);

        $user->projects()->saveMany([$projectA, $projectB, $projectC]);


        $response = $this->actingAs($user)->get('/spaces');
        $response->assertStatus(200);
        $response->data('projects')->assertContains($projectA);
        $response->data('projects')->assertContains($projectB);
        $response->data('projects')->assertContains($projectC);
    }

    /** @test */
    function user_can_only_view_a_list_of_their_own_projects()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $projectA =  Project::factory()->create([
            'name' => 'Project Test A',
        ]);
        $projectB =  Project::factory()->create([
            'name' => 'Project Test B',
        ]);
        $projectC =  Project::factory()->create([
            'name' => 'Project Test C',
        ]);
        $projectD =  Project::factory()->create([
            'name' => 'Project Test C',
        ]);

        $user->projects()->saveMany([$projectA, $projectB, $projectC]);
        $otherUser->projects()->save($projectD);

        $response = $this->actingAs($user)->get('/spaces');
        $response->assertStatus(200);
        $response->data('projects')->assertContains($projectA);
        $response->data('projects')->assertContains($projectB);
        $response->data('projects')->assertContains($projectC);
        $response->data('projects')->assertNotContains($projectD);
    }
}
