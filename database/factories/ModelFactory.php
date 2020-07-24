<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Project;
use App\Sprint;
use App\Ticket;
use App\AssemblaUser;
use App\TicketTime;
use App\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});

$factory->define(AssemblaUser::class, function (Faker $faker) {
    return [
        'user_assembla_id' => 'TESTID1234',
        'login' => 'johndoe',
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
    ];
});

$factory->define(Project::class, function (Faker $faker) {
    return [
        'name' => 'Test Project',
        'code' => 'TPJ',
        'wikiname' => 'test-project',
        'project_assembla_id' => 'TESTID1234',
        'status' => 1,
    ];
});


$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'project_id' => function () {
            return factory(App\Project::class)->create()->id;
        },
        'name' => 'TIC-1234: ticket name',
        'number' => 1234,
        'status' => 'Accepted',
        'is_story' => true,
        'created_at' => Carbon::now(),
    ];
});

$factory->define(TicketTime::class, function(Faker $faker) {
    return [
        'ticket_time_assembla_id' => 1234,
        'description' => 'Tracking time test',
        'hours' => 1.5,
        'begin_at' => Carbon::parse('-1 hour'),
        'end_at' => Carbon::parse('+1 hour'),
        'ticket_number' => 1122,
        'ticket_assembla_id' => '1234abcd',
        'project_assembla_id' => '12345abcde',
        'user_assembla_id' => '001abcf',
    ];
});

$factory->define(Sprint::class, function (Faker $faker) {
    return [
        'name' => 'Sprint 1',
        'start_date' => Carbon::parse('4 days ago'),
        'end_date' => Carbon::parse('+5 days'),
    ];
});

$factory->state(Ticket::class, 'completed', function () {
    return [
        'completed_at' => Carbon::parse('+1 week'),
        'state' => Ticket::CLOSED_STATE
    ];
});