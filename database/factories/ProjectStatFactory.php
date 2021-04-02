<?php

namespace Database\Factories;

use App\Models\ProjectStat;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectStatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectStat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'project_id'    => 1,
            'from_date'     => '2021-03-01',
            'to_date'       => '2021-02-31',
            'range_type'    => ProjectStat::MONTH_RANGE_TYPE,
            'year'          => 2021,
            'month'         => 3,
            'week'          => 9,
            'worked_hours'  => 73.5,
            'total_tasks'   => 50,
        ];
    }
}
