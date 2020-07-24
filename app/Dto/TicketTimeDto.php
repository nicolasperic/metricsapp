<?php

namespace App\Dto;

class TicketTimeDto
{

    private $ticketTimeAssemblaId;
    private $description;
    private $hours;
    private $beginAt;
    private $endAt;
    private $ticketNumber;
    private $ticketAssemblaId;
    private $projectAssemblaId;
    private $userAssemblaId;
    private $createdAt;
    private $updatedAt;

    /*
     * id" => 23605573
    "description" => "Category page: left-nav filters"
    "url" => "/spaces/cemaco/tickets/77"
    "hours" => "8.0"
    "begin_at" => "2013-11-07T20:29:29.000Z"
    "end_at" => "2013-11-07T20:29:29.000Z"
    "space_id" => "dKs4GwzB8r4Pz7acwqjQYw"
    "ticket_number" => 77
    "ticket_id" => 69478723
    "user_id" => "dmax02RC4r4OkUacwqjQWU"
    "created_at" => "2013-11-07T20:29:29.000Z"
    "updated_at" => "2013-11-07T20:29:29.000Z"
     */

    private $responseData;

    public function __construct($responseData)
    {
        $this->responseData = $responseData;
        $this->processInfo();
    }

    private function processInfo()
    {
        $data = $this->getResponseData();
        $this->setTicketTimeAssemblaId($data['id']);
        $this->setDescription($data['description']);
        $this->setHours($data['hours']);
        $this->setBeginAt($data['begin_at']);
        $this->setEndAt($data['end_at']);
        $this->setTicketAssemblaId($data['ticket_id']);
        $this->setTicketNumber($data['ticket_number']);
        $this->setProjectAssemblaId($data['space_id']);
        $this->setUserAssemblaId($data['user_id']);
        $this->setCreatedAt($data['created_at']);
        $this->setUpdatedAt($data['updated_at']);

    }
    /**
     * @return mixed
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @return mixed
     */
    public function getTicketTimeAssemblaId()
    {
        return $this->ticketTimeAssemblaId;
    }

    /**
     * @param mixed $ticketTimeAssemblaId
     */
    public function setTicketTimeAssemblaId($ticketTimeAssemblaId)
    {
        $this->ticketTimeAssemblaId = $ticketTimeAssemblaId;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * @param mixed $hours
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    }

    /**
     * @return mixed
     */
    public function getBeginAt()
    {
        return $this->beginAt;
    }

    /**
     * @param mixed $beginAt
     */
    public function setBeginAt($beginAt)
    {
        $this->beginAt = $beginAt;
    }

    /**
     * @return mixed
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @param mixed $endAt
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;
    }

    /**
     * @return mixed
     */
    public function getTicketNumber()
    {
        return $this->ticketNumber;
    }

    /**
     * @param mixed $ticketNumber
     */
    public function setTicketNumber($ticketNumber)
    {
        $this->ticketNumber = $ticketNumber;
    }

    /**
     * @return mixed
     */
    public function getTicketAssemblaId()
    {
        return $this->ticketAssemblaId;
    }

    /**
     * @param mixed $ticketAssemblaId
     */
    public function setTicketAssemblaId($ticketAssemblaId)
    {
        $this->ticketAssemblaId = $ticketAssemblaId;
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
    public function getUserAssemblaId()
    {
        return $this->userAssemblaId;
    }

    /**
     * @param mixed $userAssemblaId
     */
    public function setUserAssemblaId($userAssemblaId)
    {
        $this->userAssemblaId = $userAssemblaId;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }


}