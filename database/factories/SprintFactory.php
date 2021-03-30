<?php

namespace Database\Factories;

use App\Sprint;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SprintFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sprint::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Sprint 1',
            'start_date' => Carbon::parse('4 days ago'),
            'end_date' => Carbon::parse('+5 days'),
            'is_active' => 1,
            'planner_type' => 0,
        ];
    }
}
