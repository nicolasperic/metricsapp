<?php

namespace Database\Factories;

use App\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'request_data' => serialize(['wikiname' => 'Grassi', 'from_date' => '2020/11/30', 'to_date' => '2020/12/13']),
            'title' => 'Hours by User Story',
        ];
    }

    public function processed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Report::PROCESSED_STATUS,
                'body' => '160 horas',
                'finished_at' => Carbon::parse('-1 hour'),
            ];
        });
    }
}
