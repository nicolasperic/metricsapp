<?php

namespace App\Dto;

/**
 * id" => "cvixt811Gr4PBcacwqjQYw"
"login" => "nicoperic"
"name" => "Nicolás Peric"
"picture" => "https://www.assembla.com/v1/users/cvixt811Gr4PBcacwqjQYw/picture"
"email" => "nperic@summasolutions.net"
"organization" => ""
"phone" => ""*/
class AssemblaUserDto
{
    private $userAssemblaId;
    private $login;
    private $name;
    private $picture;
    private $email;

    private $responseData;

    public function __construct($responseData)
    {
        $this->setResponseData($responseData);
        $this->processInfo();
    }

    private function processInfo()
    {
        $data = $this->getResponseData();
        $this->setUserAssemblaId($data['id']);
        $this->setLogin($data['login']);
        $this->setName($data['name']);
        $this->setEmail($data['email']);
    }

    private function setResponseData($responseData)
    {
        $this->responseData = $responseData;
    }

    private function getResponseData()
    {
        return $this->responseData;
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
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
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
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


}
