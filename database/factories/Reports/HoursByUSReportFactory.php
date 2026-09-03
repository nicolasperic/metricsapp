<?php

namespace Database\Factories\Reports;

use App\Report;
use App\Reports\HoursByUSReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoursByUSReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HoursByUSReport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'request_data' => ['wikiname' => 'Grassi', 'space_id' => 'test_space', 'from_date' => '2020/11/30 00:00', 'to_date' => '2020/12/13 23:59'],
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
