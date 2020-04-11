<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Project;
use App\Sprint;
use App\Ticket;
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

$factory->define(Project::class, function (Faker $faker) {
    return [
        'name' => 'Test Project',
        'code' => 'TPJ',
    ];
});


$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'project_id' => function () {
            return factory(App\Project::class)->create()->id;
        },
        'name' => 'TIC-1234',
        'status' => 'Accepted',
        'created_at' => Carbon::now(),
    ];
});

$factory->define(Sprint::class, function (Faker $faker) {
    return [
        'name' => 'Sprint 1',
    ];
});

$factory->state(Ticket::class, 'completed', function () {
    return [
        'completed_at' => Carbon::parse('+1 week')
    ];
});