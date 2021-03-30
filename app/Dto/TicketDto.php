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
    private $milestoneId;
    /** @var  string  user assigned to ticket*/
    private $assignedToId;
    /** @var  string type */
    private $type;
    /** @var  float estimate (it can be hours, story points based on space configuration) */
    private $estimate;

    /** @var  float total estimate (it can be hours, story points based on space configuration) */
    private $totalEstimate;

    /** @var  boolean 1 if open, 0 if closed */
    private $state;
    /** @var  string status name */
    private $status;

    /** @var  float total hours on ticket */
    private $totalInvestedHours;

    /** @var  float worked hours on ticket */
    private $workedHours;

    /** @var  float working hours on ticket (remaining working hours) */
    private $workingHours;

    /** @var  float total working hours on ticket (total remaining working hours) */
    private $totalWorkingHours;

    /** @var  string ticket ID on Assembla */
    private $ticketAssemblaId;

    /** @var  string Custom Fields array serialized */
    private $customFields;

    /** @var  int  0 No plan level, 1 Subtask, 2 Story, 3 Epic */
    private $hierarchyType;

    private $responseData;

    public function __construct($responseData)
    {
        $this->responseData = $responseData;
        $this->processInfo();
    }

    private function processInfo()
    {
        $data = $this->getResponseData();
        $this->setNumber($data['number']);
        $this->setSummary($data['summary']);
        $this->setEstimate($data['estimate']);
        $this->setTotalEstimate($data['total_estimate']);
        $this->setPriority($data['priority']);
        $this->setState($data['state']);
        $this->setStatus($data['status']);
        $this->setMilestoneId($data['milestone_id']);
        $this->setType($this->_validate('Type', $data['custom_fields']));//this is custom but it's a feature!
        $this->setAssignedToId($data['assigned_to_id']);
        $this->setSpaceId($data['space_id']);
        $this->setSpaceName($this->_validate('space_name', $data));
        $this->setCreatedOn($data['created_on']);
        $this->setCompletedDate($data['completed_date']);
        $this->setIsStory($data['is_story']);
        $this->setTicketAssemblaId($data['id']);
        $this->setTotalInvestedHours($data['total_invested_hours']);
        $this->setWorkedHours($data['worked_hours']);
        $this->setWorkingHours($data['working_hours']);
        $this->setTotalWorkingHours($data['total_working_hours']);
        $this->setCustomFields(serialize($data['custom_fields']));
        $this->setHierarchyType($data['hierarchy_type']);
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

    /**
     * @return float
     */
    public function getTotalEstimate()
    {
        return $this->totalEstimate;
    }

    /**
     * @param float $totalEstimate
     */
    public function setTotalEstimate($totalEstimate)
    {
        $this->totalEstimate = $totalEstimate;
    }

    /**
     * @return float
     */
    public function getWorkingHours()
    {
        return $this->workingHours;
    }

    /**
     * @param float $workingHours
     */
    public function setWorkingHours($workingHours)
    {
        $this->workingHours = $workingHours;
    }

    /**
     * @return float
     */
    public function getTotalWorkingHours()
    {
        return $this->totalWorkingHours;
    }

    /**
     * @param float $totalWorkingHours
     */
    public function setTotalWorkingHours($totalWorkingHours)
    {
        $this->totalWorkingHours = $totalWorkingHours;
    }

    /**
     * @return string
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * @param string $customFields
     */
    public function setCustomFields($customFields)
    {
        $this->customFields = $customFields;
    }

    /**
     * @return int
     */
    public function getHierarchyType()
    {
        return $this->hierarchyType;
    }

    /**
     * @param int $hierarchyType
     */
    public function setHierarchyType($hierarchyType)
    {
        $this->hierarchyType = $hierarchyType;
    }

    public function toString()
    {
        return 'Number: '.$this->number.PHP_EOL.
        'Summary: '.$this->summary.PHP_EOL.
        'Completed date: '.$this->completedDate.PHP_EOL.
        'Space id: '.$this->getSpaceId().PHP_EOL.
        'Space name: '.$this->getSpaceName().PHP_EOL.
        'Is story: '.$this->isStory().PHP_EOL.
        'Hierarchy Type: '.$this->getHierarchyType().PHP_EOL.
        'Priority: '.$this->getPriority().PHP_EOL.
        'Milestone ID: '.$this->getMilestoneId().PHP_EOL.
        'Assigned to ID: '.$this->getAssignedToId().PHP_EOL.
        'State: '.$this->getState().PHP_EOL.
        'Status: '.$this->getStatus().PHP_EOL.
        'Ticket Assembla ID: '.$this->getTicketAssemblaId().PHP_EOL;
    }
}