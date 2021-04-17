<?php

namespace App\Charts\Adapters;

use App\Charts\BarChart;

class SprintBarChart
{
    /** @var  BarChart */
    private $barChart;

    /**
     * This functions requires an array of data with a specific format
     * One row per user with the following information
     * label, total_hours and hours_percentage
     *
     * @param       $sprint
     * @param array $usersHours
     *
     * @return BarChart
     */
    public function generateHoursPerUserBarChartFor($sprint, array $usersHours)
    {
        $elementId = 'hoursPerUserBarChart';
        $this->barChart = new BarChart('Hours per user', $elementId);


        $labels = [];
        $hoursValues = [];
        $hoursPercentage = [];

        $backgroundColors = [];
        $borderColors = [];

        foreach ($usersHours as $userHours) {
            $labels[] = $userHours['label'];
            $hoursValues[] = $userHours['total_hours'];
            $hoursPercentage[] = $userHours['hours_percentage'];
            $color = $this->barChart->getNextColor();
            $backgroundColors[] = $this->barChart->adjustBrightness($color, 0.8);//$color;
            $borderColors[] = $color;
        }
        if (count($usersHours)) {
            $this->barChart->setHasInformation(true);
        }


        //$this->barChart->addDataset($this->createDataset('Percentage','user stories', $countPercentage, $countValues, $backgroundColors, $hoverColors));
        $this->barChart->addDataset($this->createDataset('hours', $hoursValues, $backgroundColors, $borderColors));

        $this->barChart->setLabels($labels);
        if ($sprint) {
            $this->barChart->setLastUpdated($sprint->updated_at);
        }
        $this->barChart->setWidth(6);



        return $this->barChart;
    }

    private function createDataset($label, $data, $backgroundColors, $borderColors)
    {
        $dataset = [
            'label' => $label,
            'data' => $data,
            'backgroundColor' => $backgroundColors,
            'borderColor' => $borderColors,
        ];

        return $dataset;
    }
}