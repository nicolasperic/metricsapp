<?php

namespace App\Charts;

/**
 * Class AbstractChart
 * This class models a Chart using Chart JS library https://www.chartjs.org/
 * Created for the ease of charts generation
 *
 * @package App\Charts
 */
abstract class AbstractChart
{
    /** @var  string different Chart JS types i.e line, horizontalBar, doughnut */
    private $chartType;
    /** @var  string title added on container */
    private $chartTitle;
    /** @var  string canvas ID used to insert chart on DOM */
    private $elementId;
    /** @var bool flag to validate chart source wasn't null */
    private $hasInformation = false;
    /** @var bool used to toggle footer with totals (used only for multiple project hours) */
    private $hasFooter = false;

    /** @var array labels used for X axis */
    private $labels = [];
    /** @var array datasets with required properties for each chart */
    private $datasets = [];

    private $colorIndex = 0;

    private $lastUpdated;

    /**
     * @return mixed
     */
    public function getChartType()
    {
        return $this->chartType;
    }

    /**
     * @param mixed $chartType
     */
    public function setChartType($chartType)
    {
        $this->chartType = $chartType;
    }

    /**
     * @return mixed
     */
    public function getChartTitle()
    {
        return $this->chartTitle;
    }

    /**
     * @param mixed $chartTitle
     */
    public function setChartTitle($chartTitle)
    {
        $this->chartTitle = $chartTitle;
    }

    /**
     * @return mixed
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @param mixed $elementId
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
    }

    /**
     * @return mixed
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param mixed $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    /**
     * @return mixed
     */
    public function getDatasets()
    {
        return $this->datasets;
    }

    /**
     * NO SE USA, ESTÄ EXTENDIDO
     * @param $dataset
     *
     */
    protected function addDataset($dataset)
    {
        $this->datasets[] = $dataset;
    }

    public function setHasInformation($hasInformation)
    {
         $this->hasInformation = $hasInformation;
    }

    public function hasInformation()
    {
        return $this->hasInformation;
    }

    public function getNextColor()
    {
        //18 colors
        $colors = [
            '#4e73df',//blue primary
            '#ff6384',//rosa tasks
            '#1cc88a',//verde success
            '#f6c23e',//naranja alert
            '#e74a3b',//rojo danger
            '#858796',//gris oscuro
            '#045FB4',//azul oscuro
            '#31B404',//verde más oscuro
            '#AC58FA',//violeta
            '#FE9A2E',//naranja
            '#01DFD7',//celeste verdoso
            '#FFFF00',//amarillo,
            '#FF4000',//rojoaanarajao
            '#585858',//gris oscuro
            '#088A68',//verde petroleo
            '#DBA901',//mostaza
            '#40FF00',//verde fluor
            '#FE2EF7',//fucsia
            '#A5DF00',//verde manzana
        ];

        if (count($colors) >= $this->colorIndex) {
            $nextColor = $colors[$this->colorIndex];
        } else {
            $nextColor =  $this->randomColor();
        }

        $this->colorIndex++;

        return $nextColor;
    }

    private function randomColor()
    {
        return  '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);;
    }

    /**
     * @param boolean $hasFooter
     */
    public function setHasFooter($hasFooter)
    {
        $this->hasFooter = $hasFooter;
    }


    /**
     * @return bool
     */
    public function hasFooter()
    {
        return $this->hasFooter;
    }

    /**
     * @return mixed
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * @param mixed $lastUpdated
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }





}