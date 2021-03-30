<?php

namespace App\Dto;


class NotificationDto {

    private $notificationData;

    private $entityId;
    private $url;
    private $message;
    private $date;
    private $iconClass;//fa-file-alt, fa-sync
    private $backgroundClass;//bg-success, bg-warning, bg-info, etc

    function __construct($notificationData)
    {
        $this->notificationData = $notificationData;
        $this->processInfo();
    }

    private function processInfo()
    {
        $notificationData = $this->getNotificationData();
        $this->setEntityId($notificationData['entity_id']);
        $this->setUrl($notificationData['url']);
        $this->setMessage($notificationData['message']);
        $this->setDate($notificationData['date']);
        $this->setIconClass($notificationData['icon_class']);
        $this->setBackgroundClass($notificationData['bg_class']);

    }

    /**
     * @return mixed
     */
    private function getNotificationData()
    {
        return $this->notificationData;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getIconClass()
    {
        return $this->iconClass;
    }

    /**
     * @param mixed $iconClass
     */
    public function setIconClass($iconClass)
    {
        $this->iconClass = $iconClass;
    }

    /**
     * @return mixed
     */
    public function getBackgroundClass()
    {
        return $this->backgroundClass;
    }

    /**
     * @param mixed $backgroundClass
     */
    public function setBackgroundClass($backgroundClass)
    {
        $this->backgroundClass = $backgroundClass;
    }

}