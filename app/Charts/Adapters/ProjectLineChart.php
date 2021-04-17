<?php

namespace App\Charts\Adapters;

use App\Charts\LineChart;
use App\Helper\Helper;
use App\Models\ProjectStat;
use App\Project;
use Illuminate\Support\Facades\Auth;

class ProjectLineChart
{


    /** @var  LineChart */
    private $lineChart;

    /**
     * @param $chartTitle string
     *
     * @return LineChart
     */
    public function generateMonthlyHoursChart($project, $chartTitle = 'Hours per month')
    {
        $elementId = $project->wikiname.'MonthlyHoursChart';
        $this->lineChart = new LineChart($chartTitle, $elementId);


        $values = $this->getProjectStats($project, ProjectStat::MONTH_RANGE_TYPE);
        $this->lineChart->setLabels($values['labels']);
        $this->lineChart->addDataset($this->createDataset('Hours', array_values($values['hours'])));
        $this->lineChart->addDataset($this->createDataset('Tasks',array_values($values['tasks']), '#ff6384'));

        //following lines will create a dataset for the average hours value
        $this->lineChart->addDataset($this->createDataset(
            'Average',
            array_fill(0, count($values['hours']),$values['average']),
            'rgb(46, 46, 31, 0.6)'
        ));

        return $this->lineChart;
    }

    public function generateStarredProjectsMonthlyHoursChart($chartTitle = 'Starred Projects Hours per Month')
    {
        $elementId = 'starredMonthlyHoursChart';
        $this->lineChart = new LineChart($chartTitle, $elementId);
        $this->lineChart->setHasFooter(true);

        //1, 8, 9 10

        $allMonths = [];


        $starredProjects = Auth::user()->starredProjects;
        $chartDatasets = [];
        $i = 0;
        foreach ($starredProjects as $project) {
            $projectValues = $this->getProjectStats($project, ProjectStat::MONTH_RANGE_TYPE);
            $chartDatasets[$project->wikiname] = ['hours' => $projectValues['hours']];
            $allMonths = array_unique(array_merge($allMonths, array_keys($projectValues['hours'])));
            //$i++;
        }



        $allMonths = array_flip($allMonths);
        foreach ($chartDatasets as $wikiname => $dataset) {
            $projectHours = $this->fillMissingMonths($allMonths, $dataset['hours']);

            $this->lineChart->addDataset($this->createDataset($wikiname, $projectHours));
            $i++;
        }



        ksort($allMonths);
        $this->lineChart->setLabels($this->getLabels($allMonths));


        //$this->lineChart->addDataset($this->createDataset('Tasks',$values['tasks'], '#ff6384'));

        ;


/*


        $sommierValues = $this->getProjectStats($project, ProjectStat::MONTH_RANGE_TYPE);
        $allMonths = array_unique(array_merge($allMonths, array_keys($sommierValues['hours'])));

        $peltex = \App\Project::find(8);
        $peltexValues = $this->getProjectStats($peltex, ProjectStat::MONTH_RANGE_TYPE);

        $rex = \App\Project::find(9);
        $rexValues = $this->getProjectStats($rex, ProjectStat::MONTH_RANGE_TYPE);
        $allMonths = array_unique(array_merge($allMonths, array_keys($rexValues['hours'])));

        $barbieri = \App\Project::find(1);
        $barbieriValues = $this->getProjectStats($barbieri, ProjectStat::MONTH_RANGE_TYPE);
        $allMonths = array_unique(array_merge($allMonths, array_keys($barbieriValues['hours'])));



        $allMonths = array_flip($allMonths);
        //foreach project hours we should "normalize" the months, adding the required null values



        $projectHours = $this->fillMissingMonths($allMonths, $sommierValues['hours']);

        $this->lineChart->addDataset($this->createDataset('Sommier Hours', $projectHours));

        $projectHours = $this->fillMissingMonths($allMonths, $rexValues['hours']);
        $this->lineChart->addDataset($this->createDataset('Rex Hours', $projectHours, 'rgba(255, 99, 132)'));

        $projectHours = $this->fillMissingMonths($allMonths, $barbieriValues['hours']);
        $this->lineChart->addDataset($this->createDataset('Barbieri Hours', $projectHours, 'rgba(255, 159, 64)'));

        $projectHours = $this->fillMissingMonths($allMonths, $peltexValues['hours']);
        $this->lineChart->addDataset($this->createDataset('Peltex Hours', $projectHours,'rgba(80, 130, 50)'));

        ksort($allMonths);

        $this->lineChart->setLabels($this->getLabels($allMonths));


        //dd($this->lineChart);
        /*
         *  "rgba(255, 99, 132, 0.2)",
        "rgba(255, 159, 64, 0.2)",
        "rgba(255, 205, 86, 0.2)",
        "rgba(75, 192, 192, 0.2)",
         */
        //following lines will create a dataset for the average hours value
        /*$this->lineChart->addDataset($this->createDataset(
            'Average',
            array_fill(0, count($values['hours']),$values['average']),
            'rgb(46, 46, 31, 0.6)'

        ));*/


        return $this->lineChart;
    }

    private function getProjectStats($project, $rangeType)
    {
        $labels = [];
        $hours = [];
        $tasks = [];

        $projectStats = $project->stats()
            ->where('range_type', $rangeType)
            ->orderBy('from_date', 'desc')
            ->limit(12)
            ->get();

        if ($projectStats->count()) {
            $this->lineChart->setHasInformation(true);
        }

        $totalHours = 0;
        $totalItems = 0;
        foreach ($projectStats as $stat) {
            $monthIndex = $stat->year.str_pad($stat->month,2,'0',STR_PAD_LEFT);
            $labels[intval($monthIndex)] = Helper::getMonthLabelFromNumber($stat->month).' '.$stat->year;
            $hours[intval($monthIndex)] = $stat->worked_hours;
            $tasks[intval($monthIndex)] = $stat->total_tasks;

            $totalHours += $stat->worked_hours;
            if ($stat->worked_hours !== null) {
                $totalItems++;
            }
        }


        $average = ($totalItems)? number_format($totalHours/$totalItems, 2):0;

        ksort($labels);
        ksort($hours);
        ksort($tasks);


        return [
            'labels'        => array_values($labels),
            'hours'         => $hours,
            'tasks'         => array_values($tasks),
            'total_hours'   => $totalHours,
            'average'       => $average
        ];
    }

    private function createDataset($label, $data, $color = false)
    {
        $dataset = [
            'label' => $label,
            'data' => $data,
            'color' => $color
        ];

        return $dataset;
    }

    /**
     * This function prepares the project month hours dataset values by:
     * 1. Filling missing months with null values (to correctly display months on chart)
     * 2. Sorts values based on the month key (has the year reference)
     * 3. Returns the array_values only to clean keys (since the chart needs it)
     * @param $allMonths
     * @param $projectMonths
     *
     * @return array
     */
    private function fillMissingMonths($allMonths, $projectMonths)
    {
        foreach ($allMonths as $generalMonth => $value) {
            if (!array_key_exists($generalMonth, $projectMonths)) {
                $projectMonths[$generalMonth] = null;
            }
        }

        ksort($projectMonths);
        $projectMonths = array_values($projectMonths);
        return $projectMonths;
    }

    private function getLabels($allMonths)
    {
        $monthsLabels = [];
        foreach ($allMonths as $monthKey => $month) {
            $year = substr($monthKey, 0, 4);
            $month = substr($monthKey, 4, 2);
            $monthsLabels[] = Helper::getMonthLabelFromNumber(intval($month)).' '.$year;
        }

        return $monthsLabels;
    }

}