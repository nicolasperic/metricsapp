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
        $response = $this->get('/projects');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_view_a_project_page()
    {
        $project =  factory(Project::class)->create([
            'name' => 'ProjectTest 1',
        ]);
        $response = $this->get('/projects/'.$project->id);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    function user_can_view_a_project_he_owns_page()
    {
        $this->withoutExceptionHandling();

        $project =  factory(Project::class)->create([
            'name' => 'Project Test 1',
        ]);

        $user = factory(User::class)->create();
        $user->projects()->save($project);

        $response = $this->actingAs($user)->get('/projects/'.$project->id);
        $response->assertStatus(200);
        $response->assertSee('Project Test 1');
    }

    /** @test */
    function user_can_view_a_list_of_their_own_projects()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $projectA = factory(Project::class)->create([
            'name' => 'Test Project A',
        ]);
        $projectB = factory(Project::class)->create([
            'name' => 'Test Project B',
        ]);
        $projectC = factory(Project::class)->create([
            'name' => 'Test Project C',
        ]);

        $user->projects()->saveMany([$projectA, $projectB, $projectC]);


        $response = $this->actingAs($user)->get('/projects');
        $response->assertStatus(200);
        $response->data('projects')->assertContains($projectA);
        $response->data('projects')->assertContains($projectB);
        $response->data('projects')->assertContains($projectC);
    }

    /** @test */
    function user_can_only_view_a_list_of_their_own_projects()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $projectA =  factory(Project::class)->create([
            'name' => 'Project Test A',
        ]);
        $projectB =  factory(Project::class)->create([
            'name' => 'Project Test B',
        ]);
        $projectC =  factory(Project::class)->create([
            'name' => 'Project Test C',
        ]);
        $projectD =  factory(Project::class)->create([
            'name' => 'Project Test C',
        ]);

        $user->projects()->saveMany([$projectA, $projectB, $projectC]);
        $otherUser->projects()->save($projectD);

        $response = $this->actingAs($user)->get('/projects');
        $response->assertStatus(200);
        $response->data('projects')->assertContains($projectA);
        $response->data('projects')->assertContains($projectB);
        $response->data('projects')->assertContains($projectC);
        $response->data('projects')->assertNotContains($projectD);
    }
}
