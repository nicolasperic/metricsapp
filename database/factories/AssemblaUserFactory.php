<?php

namespace Database\Factories;

use App\AssemblaUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssemblaUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssemblaUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_assembla_id' => 'TESTID1234',
            'login' => 'johndoe',
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
