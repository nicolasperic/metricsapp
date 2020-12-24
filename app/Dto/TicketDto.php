<?php

namespace App\Dto;

class TicketDto
{
    /** @var string ticket number */
    private $number;
    /** @var  string ticket summary */
    private $summary;

    /** @var   */
    private $createdOn;
    /** @var string ticket completed date*/
    private $completedDate;
    /** @var  string space unique id */
    private $spaceId;
    /** @var  string space name */
    private $spaceName;
    /** @var  boolean true if story, false if subtask */
    private $isStory;
    /** @var  int priority */
    private $priority;
    /** @var  string milestone ID */
    private $milestoneId;//not really sure if we need to know this
    /** @var  string  user assigned to ticket*/
    private $assignedToId;
    /** @var  int story points */
    private $complexity;
    /** @var  string type */
    private $type;
    /** @var  int estimate (it can be hours, story points based on space configuration) */
    private $estimate;
    /** @var  boolean 1 if open, 0 if closed */
    private $state;
    /** @var  string status name */
    private $status;

    /** @var  float total hours on ticket */
    private $totalInvestedHours;

    /** @var  float worked hours on ticket */
    private $workedHours;

    /** @var  string ticket ID on Assembla */
    private $ticketAssemblaId;

    /*
       "working_hours" => 0.0
        "estimate" => 19.0
        "total_estimate" => 19.0
        "total_invested_hours" => 49.5
        "total_working_hours" => 0.0
     */

    private $responseData;

    public function __construct($responseData)
    {
        $this->responseData = $responseData;
        $this->processInfo();
    }

    /**
     *
     * array:30 [
    "id" => 231587796
    X"number" => 1022
    X"summary" => "[US] MSI Estrategia de Rollback"
    "description" => ""
    X"priority" => 3
    X"completed_date" => null
    "component_id" => null
    "created_on" => "2020-02-18T19:28:33.000Z"
    "permission_type" => 1
    "importance" => -7.0
    X"is_story" => true
    X"milestone_id" => 12975241
    "notification_list" => "cvixt811Gr4PBcacwqjQYw,ajLyFEiVir6A3ccK-zJOy8,d8r95QiVer6zj-aH8tHBnc,aAbtrS7fKr6y_dcP_HzTya"
    X"space_id" => "dxD3_KI5ur6ky6dmr6QqzO"
    X"state" => 1
    X"status" => "Stage Test"
    "story_importance" => 0
    "updated_at" => "2020-04-06T13:58:14.000Z"
    "working_hours" => 0.0
    "estimate" => 19.0
    "total_estimate" => 19.0
    "total_invested_hours" => 49.5
    "total_working_hours" => 0.0
    "assigned_to_id" => "aAbtrS7fKr6y_dcP_HzTya"
    "reporter_id" => "cvixt811Gr4PBcacwqjQYw"
    "custom_fields" => array:1 [
    "Complexity" => ""
    ]
    "hierarchy_type" => 2
    "due_date" => null
    "number_with_prefix" => 1022
    "space_name" => "Sommier Center"
    ]
     */
    private function processInfo()
    {
        $data = $this->getResponseData();
        $this->setNumber($data['number']);
        $this->setSummary($data['summary']);
        $this->setEstimate($data['estimate']);
        $this->setPriority($data['priority']);
        $this->setState($data['state']);
        $this->setStatus($data['status']);
        $this->setMilestoneId($data['milestone_id']);
        $this->setComplexity($this->_validate('Complexity', $data['custom_fields'], 0));
        $this->setType($this->_validate('Type', $data['custom_fields']));
        $this->setAssignedToId($data['assigned_to_id']);
        $this->setSpaceId($data['space_id']);
        $this->setSpaceName($this->_validate('space_name', $data));
        $this->setCreatedOn($data['created_on']);
        $this->setCompletedDate($data['completed_date']);
        $this->setIsStory($data['is_story']);
        $this->setTicketAssemblaId($data['id']);
        $this->setTotalInvestedHours($data['total_invested_hours']);
        $this->setWorkedHours($data['worked_hours']);
    }

    private function _validate($key, $data, $default = null)
    {
        $value = $default;
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
        }
        return $value;
    }

    /**
     * @return mixed
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @return int
     */
    public function getEstimate()
    {
        return $this->estimate;
    }

    /**
     * @param int $estimate
     */
    public function setEstimate($estimate)
    {
        $this->estimate = $estimate;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getNumber().' '.$this->getSummary();
    }

    /**
     * @param mixed $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return mixed
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param mixed $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return mixed
     */
    public function getCompletedDate()
    {
        return $this->completedDate;
    }

    /**
     * @param mixed $completedDate
     */
    public function setCompletedDate($completedDate)
    {
        $this->completedDate = $completedDate;
    }

    /**
     * @return mixed
     */
    public function getSpaceId()
    {
        return $this->spaceId;
    }

    /**
     * @param mixed $spaceId
     */
    public function setSpaceId($spaceId)
    {
        $this->spaceId = $spaceId;
    }

    /**
     * @return mixed
     */
    public function getSpaceName()
    {
        return $this->spaceName;
    }

    /**
     * @param mixed $spaceName
     */
    public function setSpaceName($spaceName)
    {
        $this->spaceName = $spaceName;
    }

    /**
     * @return mixed
     */
    public function isStory()
    {
        return $this->isStory;
    }

    /**
     * @param mixed $isStory
     */
    public function setIsStory($isStory)
    {
        $this->isStory = $isStory;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getMilestoneId()
    {
        return $this->milestoneId;
    }

    /**
     * @param mixed $milestoneId
     */
    public function setMilestoneId($milestoneId)
    {
        $this->milestoneId = $milestoneId;
    }

    /**
     * @return mixed
     */
    public function getAssignedToId()
    {
        return $this->assignedToId;
    }

    /**
     * @param mixed $assignedToId
     */
    public function setAssignedToId($assignedToId)
    {
        $this->assignedToId = $assignedToId;
    }

    /**
     * @return mixed
     */
    public function getComplexity()
    {
        if (empty($this->complexity)) {
            $this->complexity = 0;
        }
        return $this->complexity;
    }

    /**
     * @param mixed $complexity
     */
    public function setComplexity($complexity)
    {
        $this->complexity = $complexity;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
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
     * @return string
     */
    public function getTicketAssemblaId()
    {
        return $this->ticketAssemblaId;
    }

    /**
     * @param string $ticketAssemblaId
     */
    public function setTicketAssemblaId($ticketAssemblaId)
    {
        $this->ticketAssemblaId = $ticketAssemblaId;
    }

    /**
     * @return float
     */
    public function getTotalInvestedHours()
    {
        return $this->totalInvestedHours;
    }

    /**
     * @param float $totalInvestedHours
     */
    public function setTotalInvestedHours($totalInvestedHours)
    {
        $this->totalInvestedHours = $totalInvestedHours;
    }

    /**
     * @return float
     */
    public function getWorkedHours()
    {
        return $this->workedHours;
    }

    /**
     * @param float $workedHours
     */
    public function setWorkedHours($workedHours)
    {
        $this->workedHours = $workedHours;
    }

    public function toString()
    {
        return 'Number: '.$this->number.PHP_EOL.
        'Summary: '.$this->summary.PHP_EOL.
        'Completed date: '.$this->completedDate.PHP_EOL.
        'Space id: '.$this->getSpaceId().PHP_EOL.
        'Space name: '.$this->getSpaceName().PHP_EOL.
        'Is story: '.$this->isStory().PHP_EOL.
        'Priority: '.$this->getPriority().PHP_EOL.
        'Milestone ID: '.$this->getMilestoneId().PHP_EOL.
        'Assigned to ID: '.$this->getAssignedToId().PHP_EOL.
        'Complexity: '.$this->getComplexity().PHP_EOL.
        'State: '.$this->getState().PHP_EOL.
        'Status: '.$this->getStatus().PHP_EOL.
        'Ticket Assembla ID: '.$this->getTicketAssemblaId().PHP_EOL;
    }
}