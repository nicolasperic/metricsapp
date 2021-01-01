<?php

namespace App\Dto;
/**
 * 
 * Array
(
[id] => 12335704
[start_date] =>
[due_date] => 2023-07-31
[budget] =>
[title] => Backlog
[user_id] =>
[created_at] => 2018-07-31T12:07:07.000Z
[created_by] => awkfUI9wer46vDacwqEsg8
[space_id] => dxD3_KI5ur6ky6dmr6QqzO
[description] =>
[is_completed] =>
[completed_date] =>
[updated_at] => 2019-04-04T16:59:27.000Z
[updated_by] => awkfUI9wer46vDacwqEsg8
[release_level] =>
[release_notes] =>
[planner_type] => 0
[pretty_release_level] => None
)
 * Class SprintDto
 *
 * @package App\Dto
 */
class SprintDto
{
    private $sprintAssemblaId;
    private $projectAssemblaId;
    private $title;
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
        $this->setTitle($data['title']);
        $this->setStatus(!boolval($data['is_completed']));
        $this->setSprintAssemblaId($data['id']);
        $this->setProjectAssemblaId($data['space_id']);
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
    public function getSprintAssemblaId()
    {
        return $this->sprintAssemblaId;
    }

    /**
     * @param mixed $sprintAssemblaId
     */
    public function setSprintAssemblaId($sprintAssemblaId)
    {
        $this->sprintAssemblaId = $sprintAssemblaId;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function toString()
    {
        return "Name: ".$this->getTitle().PHP_EOL.
        "Status: ".$this->getStatus().PHP_EOL.
        "Sprint ID: ".$this->getSprintAssemblaId();
        "Project ID: ".$this->getProjectAssemblaId();
    }
    



}
