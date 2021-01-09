<?php

namespace App\Notifications;

use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyReportNotification extends Notification
{
    use Queueable;
    /**
     * @var Report
     */
    private $report;
    /**
     * @var
     */
    private $subject;

    /**
     * Create a new notification instance.
     *
     * @param Report $report
     */
    public function __construct(Report $report, $subject)
    {
        $this->report = $report;
        $this->subject = $subject;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $reportContent = explode("\n", $this->report->body);
        $mailMessage = new MailMessage;
        foreach($reportContent as $line) {
            $mailMessage->line($line);
        }
        $mailMessage->subject($this->subject)
            ->greeting("Hello, your weekly report is ready!")
            ->action('View Report', url("/reports/{$this->report->id}"))
            ->line('Thank you for using our application!');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
