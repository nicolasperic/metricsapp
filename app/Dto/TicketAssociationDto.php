<?php

namespace App\Dto;

class TicketAssociationDto
{
    /**
     * array:5 [
    "id" => 33974038
    "ticket1_id" => 231717985
    "ticket2_id" => 231438936
    "relationship" => 5
    "created_at" => "2020-03-26T01:37:58.000Z"
    ]*/

    private $id;

    private $ticket1Id;

    private $ticket2Id;

    private $relationship;

    private $createdAt;


    private $responseData;

    public function __construct($responseData)
    {
        $this->responseData = $responseData;
        $this->processInfo();
    }

    private function processInfo()
    {
        $data = $this->getResponseData();
        $this->setId($data['id']);
        $this->setTicket1Id($data['ticket1_id']);
        $this->setTicket2Id($data['ticket2_id']);
        $this->setRelationship($data['relationship']);
        $this->setCreatedAt($data['created_at']);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTicket1Id()
    {
        return $this->ticket1Id;
    }

    /**
     * @param mixed $ticket1Id
     */
    public function setTicket1Id($ticket1Id)
    {
        $this->ticket1Id = $ticket1Id;
    }

    /**
     * @return mixed
     */
    public function getTicket2Id()
    {
        return $this->ticket2Id;
    }

    /**
     * @param mixed $ticket2Id
     */
    public function setTicket2Id($ticket2Id)
    {
        $this->ticket2Id = $ticket2Id;
    }

    /**
     * @return mixed
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * @param mixed $relationship
     */
    public function setRelationship($relationship)
    {
        $this->relationship = $relationship;
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
    public function getResponseData()
    {
        return $this->responseData;
    }

    public function toString()
    {
        return 'ID: '.$this->getId().PHP_EOL.
        'Ticket1Id: '.$this->getTicket1Id().PHP_EOL.
        'Ticket2Id: '.$this->getTicket2Id().PHP_EOL.
        'Relationship: '.$this->getRelationship().PHP_EOL.
        'Created At: '.$this->getCreatedAt().PHP_EOL;
    }
}