<?php

namespace Database\Factories;

use App\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Test Project',
            'code' => 'TPJ',
            'wikiname' => 'test-project',
            'project_assembla_id' => 'TESTID1234',
            'status' => 1,
        ];
    }
}
