<?php

namespace Tests\Feature;


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

        $sprint =  factory(Sprint::class)->create([
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

        $user = factory(User::class)->create();

        $sprintA =  factory(Sprint::class)->create([
            'name' => 'Sprint Test A',
        ]);
        $sprintB =  factory(Sprint::class)->create([
            'name' => 'Sprint Test B',
        ]);
        $sprintC =  factory(Sprint::class)->create([
            'name' => 'Sprint Test C',
        ]);
        $user->sprints()->saveMany([$sprintA, $sprintB, $sprintC]);


        $response = $this->actingAs($user)->get('/sprints');
        $response->assertStatus(200);
        $response->data('sprints')->assertContains($sprintA);
        $response->data('sprints')->assertContains($sprintB);
        $response->data('sprints')->assertContains($sprintC);
    }

    /** @test */
    function user_can_only_view_a_list_of_their_own_sprints()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $sprintA =  factory(Sprint::class)->create([
            'name' => 'Sprint Test A',
        ]);
        $sprintB =  factory(Sprint::class)->create([
            'name' => 'Sprint Test B',
        ]);
        $sprintC =  factory(Sprint::class)->create([
            'name' => 'Sprint Test C',
        ]);
        $sprintD =  factory(Sprint::class)->create([
            'name' => 'Sprint Test C',
        ]);

        $user->sprints()->saveMany([$sprintA, $sprintB, $sprintC]);
        $otherUser->sprints()->save($sprintD);

        $response = $this->actingAs($user)->get('/sprints');
        $response->assertStatus(200);
        $response->data('sprints')->assertContains($sprintA);
        $response->data('sprints')->assertContains($sprintB);
        $response->data('sprints')->assertContains($sprintC);
        $response->data('sprints')->assertNotContains($sprintD);
    }


    /** @test */
    function user_can_view_a_sprint_he_owns_page()
    {
        $this->withoutExceptionHandling();

        $sprint =  factory(Sprint::class)->create([
            'name' => 'Sprint Test 1',
        ]);

        $user = factory(User::class)->create();
        $user->sprints()->save($sprint);

        $response = $this->actingAs($user)->get('/sprints/'.$sprint->id);
        $response->assertStatus(200);
        $response->assertSee('Sprint Test 1');
    }


    /** @test */
    function user_cannot_view_a_sprint_he_does_not_own()
    {
        $sprint =  factory(Sprint::class)->create([
            'name' => 'Sprint Test 1',
        ]);

        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->get('/sprints/'.$sprint->id);


        $response->assertStatus(404);
    }
}
