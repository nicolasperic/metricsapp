<?php

namespace App\Notifications;

use App\Helper\Helper;
use App\Report;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Used notification an all reports to let the User know the report was processed
 * //TODO generate a notification for failing scenarios? hmmm
 * Class ReportProcessed
 *
 * @package App\Notifications
 */
class ReportProcessed extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var Report
     */
    private $report;

    /**
     * Create a new notification instance.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
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
            'entity_id' => $this->report->id,
            'url' => url('reports', $this->report->id),
            'message' => $this->report->getNotificationMessage(),
            'date' => Carbon::now()->format('F d, Y g:i a'),
            'bg_class' => Helper::getReportNotificationBackground($this->report->type),
            'icon_class' => 'fa-file-alt',
        ];
    }

    public function toArray($notifiable)
    {
        //TODO validate how to remove the data enclosing array
        return ['data' => [
            'entity_id' => $this->report->id,
            'url' => url('reports', $this->report->id),
            'message' => $this->report->getNotificationMessage(),
            'date' => Carbon::now()->format('F d, Y g:i a'),
            'bg_class' => Helper::getReportNotificationBackground($this->report->type),
            'icon_class' => 'fa-file-alt',
        ]];
    }
}
