<?php

namespace App\Dto;

class ProjectDto
{
    private $projectAssemblaId;
    private $name;
    private $wikiName;
    private $status;

    private $responseData;

    public function __construct($responseData)
    {
        $this->setResponseData($responseData);
        $this->processInfo();
    }

    private function setResponseData($responseData)
    {
        $this->responseData = $responseData;
    }

    private function getResponseData()
    {
        return $this->responseData;
    }

    private function processInfo()
    {
        $data = $this->getResponseData();
        $this->setName($data['name']);
        $this->setWikiName($data['wiki_name']);
        $this->setStatus($data['status']);
        $this->setProjectAssemblaId($data['id']);
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getProjectAssemblaId()
    {
        return $this->projectAssemblaId;
    }

    /**
     * @param mixed $projectAssemblaId
     */
    public function setProjectAssemblaId($projectAssemblaId)
    {
        $this->projectAssemblaId = $projectAssemblaId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getWikiName()
    {
        return $this->wikiName;
    }

    /**
     * @param mixed $wikiName
     */
    public function setWikiName($wikiName)
    {
        $this->wikiName = $wikiName;
    }

    public function toString()
    {
        return "Name: ".$this->getName().PHP_EOL.
        "Wiki name: ".$this->getWikiName().PHP_EOL.
        "Status: ".$this->getStatus().PHP_EOL.
        "Project ID: ".$this->getProjectAssemblaId();
    }
    



}
