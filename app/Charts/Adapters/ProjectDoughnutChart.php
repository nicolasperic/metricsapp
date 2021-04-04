<?php

namespace App\Charts\Adapters;

use App\Charts\DoughnutChart;
use App\Helper\Helper;
use App\Models\ProjectStat;
use App\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectDoughnutChart
{


    /** @var  DoughnutChart */
    private $doughnutChart;
    
    private $statsAvailable = false;


    public function generateStarredProjectsMonthlyHoursChart($chartTitle = false)
    {
        if ($chartTitle === false) {
            $monthLabel = Carbon::now()->subMonth()->monthName;
            $year = Carbon::now()->subMonth()->year;
            $chartTitle = "Starred Projects Hours for last month ($monthLabel $year)";
        }
        $elementId = 'starredMonthlyHoursDoughnutChart';
        $this->doughnutChart = new DoughnutChart($chartTitle, $elementId);



        $starredProjects = Auth::user()->starredProjects;
        $hoursValues = [];
        $totalHours = 0;
        $labels = [];
        $i = 0;
        foreach ($starredProjects as $project) {
            $labels[] = $project->wikiname;

            $projectHours = $this->getProjectStats($project, ProjectStat::MONTH_RANGE_TYPE);

            $hoursValues[] = $projectHours;
            $totalHours += $projectHours;

            //$i++;
        }


        $this->doughnutChart->setLabels($labels);



        $colors = [];
        $data = [];
        foreach ($hoursValues as $hours) {

            $hoursPercentage = ($totalHours != 0) ? number_format($hours*100/$totalHours, 2)  : null;


            $data[] = $hoursPercentage;
            $colors[] = $this->doughnutChart->getNextColor();
        }

        $this->doughnutChart->addDataset($this->createDataset('Horas', $data,$hoursValues, $colors));



        return $this->doughnutChart;
    }

    private function getProjectStats($project, $rangeType)
    {
        $hours = null;

        $projectStat = $project->stats()
            ->where('range_type', $rangeType)
            ->orderBy('from_date')
            ->where('month',Carbon::now()->subMonth()->month)
            ->where('year', Carbon::now()->subMonth()->year)
            ->first();

        if ($projectStat !== null) {
            $this->doughnutChart->setHasInformation(true);
            $hours = $projectStat->worked_hours;
        }

        return $hours;
    }

    private function createDataset($label, $data, $hourValues, $color)
    {
        $dataset = [
            'label' => $label,
            'data' => $data,//percentages
            'backgroundColor' => $color,
            'hoverBackgroundColor' => $color,//how to handle this? similar color
            'hoverBorderColor' => "rgba(234, 236, 244, 1)",
            'realValues' => $hourValues,
        ];

        return $dataset;
    }

    /**
     * @return boolean
     */
    public function statsAvailable()
    {
        return $this->statsAvailable;
    }

}