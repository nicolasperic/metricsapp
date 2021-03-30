<?php

namespace App\Notifications;

use App\Dto\NotificationDto;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssemblaSynced extends Notification
{
    use Queueable;
    /**
     * @var SyncNotificationDto
     */
    private $notificationDto;

    /**
     * Create a new notification instance.
     *
     * @param NotificationDto $notificationDto
     */
    public function __construct(NotificationDto $notificationDto)
    {
        $this->notificationDto = $notificationDto;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'entity_id' => $this->notificationDto->getEntityId(),
            'url' => $this->notificationDto->getUrl(),
            'message' => $this->notificationDto->getMessage(),
            'date' => $this->notificationDto->getDate(),
            'bg_class' => $this->notificationDto->getBackgroundClass(),
            'icon_class' => $this->notificationDto->getIconClass()
        ];
    }

    public function toArray($notifiable)
    {
        //TODO validate how to remove the data enclosing array
        return ['data' => [
            'entity_id' => $this->notificationDto->getEntityId(),
            'url' => $this->notificationDto->getUrl(),
            'message' => $this->notificationDto->getMessage(),
            'date' => $this->notificationDto->getDate(),
            'bg_class' => $this->notificationDto->getBackgroundClass(),
            'icon_class' => $this->notificationDto->getIconClass()
        ]];
    }
}
