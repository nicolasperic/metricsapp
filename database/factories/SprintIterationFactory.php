<?php

namespace Database\Factories;

use App\Project;
use App\SprintIteration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SprintIterationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SprintIteration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'project_id' => function () {
                return Project::factory()->create()->id;
            },
            'sprint_duration' => SprintIteration::TWO_WEEKS,
            'sprint_start_weekday' => 1,
            'sprint_prefix' => 'SE - ',
            'iteration_status' => SprintIteration::ITERATION_STATUS_RUNNING,
            'next_iteration_start_date' => Carbon::parse('+13 days'),
            'iterations_count' => 0,
        ];
    }
}
