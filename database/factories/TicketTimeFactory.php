<?php

namespace Database\Factories;

use App\TicketTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TicketTime::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
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
    }
}
