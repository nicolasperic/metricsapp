<?php

namespace App\Charts;

class DoughnutChart extends AbstractChart
{
    const CHART_TYPE = 'doughnut';

    function __construct($chartTitle, $elementId)
    {
        $this->setChartType(self::CHART_TYPE);
        $this->setChartTitle($chartTitle);
        $this->setElementId($elementId);
    }

    /**
     * This function is used to ease the dataset generation
     * Since some properties will always use default values we generate the complete array
     * and allow to replace the required properties by merging the received array
     *
     *
     * @param $datasetValues array properties will be merged with default dataset
     */
    public function addDataset($datasetValues)
    {
        $dataset = [
            'label' => "",//replaced on merge
            'fill' => false,
            'lineTension' => 0.3,
            'pointRadius' => 3,
            'pointHoverRadius' => 3,
            'pointHitRadius' => 10,
            'pointBorderWidth' => 2,
            'data' => []//replaced on merge

        ];
        //blue line for hours rgba(78, 115, 223, 1)
        $dataset = array_merge($dataset, $datasetValues);


        parent::addDataset($dataset);
    }
}