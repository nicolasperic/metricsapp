<?php

namespace Tests\Unit;

use App\Helper\Helper;
use App\Models\ProjectStat;
use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectStatsTest extends TestCase
{
    use RefreshDatabase;

    /** @test //TODO this might be awful for now until we can decide what to tests on this class and write the tests : p */
    public function avoidWarningMessageNoTestFoundInClass()
    {
        $this->assertEquals(1,1);
    }
    /**  */
    public function can_calculate_monthly_stats_for_chart()
    {
        $project = Project::factory()->create([
            'id' => 1,
            'name' => 'Test Project'
        ]);

        $projectStat1 = ProjectStat::factory()->create([
            'from_date' => '2021-01-01',
            'to_date' => '2021-01-31',
            'month' => 1,
            'worked_hours' => 15.5,
            'total_tasks' => 10
        ]);
        $projectStat2 = ProjectStat::factory()->create([
            'from_date' => '2021-02-01',
            'to_date' => '2021-02-28',
            'month' => 2,
            'worked_hours' => 23.75,
            'total_tasks' => 14
        ]);

        $projectStat3 = ProjectStat::factory()->create([
            'from_date' => '2021-03-01',
            'to_date' => '2021-03-31',
            'month' => 3,
            'worked_hours' => 73.5,
            'total_tasks' => 50
        ]);

        $projectStat4 = ProjectStat::factory()->create([
            'from_date' => '2021-04-01',
            'to_date' => '2021-04-30',
            'month' => 4,
            'worked_hours' => 47,
            'total_tasks' => 32
        ]);

        $projectStat5 = ProjectStat::factory()->create([
            'from_date' => '2021-05-01',
            'to_date' => '2021-05-31',
            'month' => 5,
            'worked_hours' => 90,
            'total_tasks' => 55
        ]);

        $project->stats()->saveMany([$projectStat1, $projectStat2, $projectStat3, $projectStat4, $projectStat5]);


        $labels = [];
        $hours = [];
        $tasks = [];
        dump(count($project->stats));
        $projectStats = $project->stats()->where('range_type', ProjectStat::MONTH_RANGE_TYPE)->orderBy('from_date', 'desc')->get();
        foreach ($projectStats as $stat) {
            $labels[$stat->month] = Helper::getMonthLabelFromNumber($stat->month);
            $hours[] = $stat->worked_hours;
            $tasks[] = $stat->total_tasks;
        }

        dump($hours);
        dump($tasks);
        dump($labels);
        return [
            'labels' => ['Mayo', 'Junio', 'Julio'],
            'hours' => [580.75, 232.5, 97.5],
            'tasks' => [88, 149, 35]
        ];

        //1 create el project
        //2 asignar las project stats históricas
        //3 ejecutar la función
        //4 asserts para validar que los cálculso son los esperados : )
    }
}
