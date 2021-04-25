<?php

namespace App\Charts;

class BarChart extends AbstractChart
{
    const CHART_TYPE = 'horizontalBar';//bar > vertical

    private $barsCount;

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
            'label' => "",
            'data' => [],
            'fill' => false,
            'backgroundColor' => '',
            'borderColor' => '',
            'borderWidth' => 1

        ];
        $dataset = array_merge($dataset, $datasetValues);


        parent::addDataset($dataset);
    }

    public function maintainAspectRatio()
    {
        return $this->barsCount >= 12;
    }

    /**
     * @param mixed $barsCount
     */
    public function setBarsCount($barsCount)
    {
        $this->barsCount = $barsCount;
    }


}