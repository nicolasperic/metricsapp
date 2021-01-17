<?php

namespace App\Dto;


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
        //print print_r($responseData,1).PHP_EOL;
        $this->setResponseData($responseData);
        $this->processInfo();
    }

    private function processInfo()
    {
        $data = $this->getResponseData();
        $this->setUserAssemblaId($data['id']);
        $this->setLogin($data['login']);
        $this->setName($data['name']);
        $this->setEmail($this->_validate('email', $data, ''));
        $this->setPicture($this->_validate('picture', $data, ''));
    }

    private function _validate($key, $data, $default = null)
    {
        $value = $default;
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
        }
        return $value;
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

    public function toString()
    {
        return 'ID: '.$this->getUserAssemblaId().PHP_EOL.
        'Login: '.$this->getLogin().PHP_EOL.
        'Name: '.$this->getName().PHP_EOL.
        'Email: '.$this->getEmail().PHP_EOL.
        'Picture: '.$this->getPicture().PHP_EOL;

    }


}
