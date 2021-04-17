<?php

namespace App\Charts\Adapters;

use App\Charts\DoughnutChart;
use App\Ticket;
use Illuminate\Support\Facades\DB;

class SprintDoughnutChart
{

    private $sprint = null;

    /** @var  DoughnutChart */
    private $doughnutChart;

    public function generateStoriesTypesChartFor($sprint)
    {
        $this->sprint = $sprint;
        $elementId = 'storiesTypesDoughnutChart';
        $this->doughnutChart = new DoughnutChart('User Stories Types', $elementId);

        $storiesTypes = $this->getUserStoriesTypeStats();

        $hoursValues = [];
        $hoursPercentage = [];
        $countValues = [];
        $countPercentage = [];

        $backgroundColors = [];
        $hoverColors = [];

        foreach ($storiesTypes as $label => $storyType) {
            $hoursValues[] = $storyType['total_invested_hours'];
            $hoursPercentage[] = $storyType['hours_percentage'];
            $countValues[] = $storyType['total'];
            $countPercentage[] = $storyType['count_percentage'];
            $backgroundColors[] = $storyType['color']['main'];
            $hoverColors[] = $storyType['color']['hover'];
            $this->doughnutChart->setHasInformation(true);
        }

        $labels = array_keys($storiesTypes);
        $this->doughnutChart->addDataset($this->createDataset('Percentage','user stories', $countPercentage, $countValues, $backgroundColors, $hoverColors));
        $this->doughnutChart->addDataset($this->createDataset('Hours', 'hours', $hoursPercentage, $hoursValues, $backgroundColors, $hoverColors));

        $this->doughnutChart->setLastUpdated($sprint->updated_at);
        $this->doughnutChart->setLabels($labels);
        $this->doughnutChart->setWidth(6);



        return $this->doughnutChart;
    }

    /**
     * This function will return information grouped by User Story TYPE
     * Support, Bug, Requirement, Spike, Recurrent, Empty (when not assigned)
     * @return array
     */
    private function getUserStoriesTypeStats()
    {

        $colors = [
            'Requirement' => ['main' => '#1cc88a', 'hover' => '#17a673'],//verde
            'Support' => ['main' => '#4e73df', 'hover' => '#3b5399'],//azul
            'Bug' => ['main' => '#e74a3b', 'hover' => '#c22819'],//rojo
            'Spike' => ['main' => '#f6c23e', 'hover' => '#cea334'],//amarillo
            'Recurrent' => ['main' => '#dbd8ce', 'hover' => '#bdbab1'],//gris
            'Empty' => ['main' => '#a947c4', 'hover' => '#8d3ba3'],//violeta
            'BA' => ['main' => '#66e0ff', 'hover' => '#008fb3'],//celeste
            'QA' => ['main' => '#ff9933', 'hover' => '#cc6600'],//naranja
            'Design-UX' => ['main' => '#ff99ff', 'hover' => '#ff4dff']//violeta más claro
        ];
        $typesUsCount= $this->sprint->belongsToMany(Ticket::class)//DB::table('tickets')
        ->select('type', DB::raw('count(*) as total'))
            ->where('is_story', true)
            ->groupBy('type')
            ->get();


        $typesHours = $this->sprint->belongsToMany(Ticket::class)//DB::table('tickets')
        ->select('type', DB::raw('sum(worked_hours) as total_invested_hours'))
            //->where('is_story', false)
            ->groupBy('type')
            ->get();

        $ticketsMetrics = $this->sprint->getTicketsMetricsInstance();
        $storiesCount = $ticketsMetrics->getStoriesCount();
        $totalWorkedHours = $ticketsMetrics->getTotalWorkedHours();

        //dd($types);
        $result = array();
        foreach ($typesUsCount as $type) {
            $label = ($type->type)? $type->type: 'Empty';

            $countPercentage = (floatval($storiesCount) !== 0.0 )?number_format(($type->total / $storiesCount) * 100, 2):0;

            $color = null;
            if (array_key_exists($label, $colors)) {
                $color = $colors[$label];
            } else {
                $main = $this->doughnutChart->getNextColor();
                $hover = $this->doughnutChart->adjustBrightness($main, -0.15);
                $color = ['main' => $main, 'hover' => $hover];
            }


            $result[$label] = [
                'label' => $label,
                'total' => $type->total,
                'count_percentage' => $countPercentage,
                'total_invested_hours' => 0,
                'hours_percentage' => 0,
                'color' => $color,
            ];
        }



        foreach ($typesHours as $type) {
            $label = ($type->type)? $type->type: 'Empty';

            $hoursPercentage = (floatval($totalWorkedHours) !== 0.0 )? number_format(($type->total_invested_hours / $totalWorkedHours) * 100, 2) : 0;

            if (array_key_exists($label, $result)) {
                $result[$label] = array_merge($result[$label],[
                    'total_invested_hours' => $type->total_invested_hours,
                    'hours_percentage' => $hoursPercentage,
                ]);
            }

        }


        return $result;
    }

    private function createDataset($label, $dataLabel, $data, $realValues, $backgroundColor, $hoverColor)
    {
        $dataset = [
            'label' => $label,
            'data' => $data,//percentages
            'backgroundColor' => $backgroundColor,
            'hoverBackgroundColor' => $hoverColor,//how to handle this? similar color
            'hoverBorderColor' => "rgba(234, 236, 244, 1)",
            'realValues' => $realValues,
            'dataLabel' => $dataLabel
        ];

        return $dataset;
    }
}