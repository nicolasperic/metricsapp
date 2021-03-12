<?php

namespace Database\Factories;

use App\Project;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

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
            'name' => 'TIC-1234: ticket name',
            'number' => 1234,
            'status' => 'Accepted',
            'state' => Ticket::OPEN_STATE,
            'is_story' => true,
            'created_at' => Carbon::now(),
        ];
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'ticket_assembla_id' => 'ticket123',
                'completed_at' => Carbon::parse('+1 week'),
                'state' => Ticket::CLOSED_STATE
            ];
        });
    }
}
