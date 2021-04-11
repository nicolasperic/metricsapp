<?php

namespace App\Notifications;

use App\Helper\Helper;
use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

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
        $reportContent = $this->getReportHtmlContent(json_decode($this->report->body));
        $mailMessage = new MailMessage;

        $mailMessage->line(new HtmlString($reportContent));

        $mailMessage->subject($this->subject)
            //->view('reports.hoursbyuser', ['report' => $this->report])
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

    private function getReportHtmlContent($reportBody)
    {
        $htmlContent = '';


        $header = $reportBody->header;
        $projects = $reportBody->projects;
        $users = $reportBody->users;



        $htmlContent .= '<div class="report-header">';
        $htmlContent .= '<h4>Hours by Users and Spaces Report</h4>';

        $htmlContent .= '<div class="report-dates-totals">
                                <span style="display: block;">From <strong>'.Helper::getDateWithoutHours($header->from).'</strong> to <strong>'.Helper::getDateWithoutHours($header->to).'</strong></span>
                                <span style="display: block;">Total Hours <strong>'.$header->total_hours.'</strong> Total Tasks <strong>'.$header->total_tasks.'</strong></span>
                        </div>
                        </div>';

        $htmlContent .= '<h4>Hours by Projects</h4>';
        $labels = [];
        $hours = [];
    foreach($projects as $project) {
        $labels[] = $project->wikiname;
        $hours[] = $project->total_hours;

        $htmlContent .=  '<div class="report-table-header" style="margin-bottom: 15px;">
                            <h4 style="margin-bottom: 10px;">'.$project->wikiname.'</h4>
                            <span>Total hours: <strong>'.$project->total_hours.'</strong> ('. Helper::getPercentageValue($project->total_hours, $header->total_hours, $decimals = 2) .'%)</span>
                            <span>Total tasks: <strong>'.$project->total_tasks.'</strong> ('. Helper::getPercentageValue($project->total_tasks, $header->total_tasks, $decimals = 2) .'%)</span>
                        </div>
                        <div class="report-user-stories">
                            <table class="table table-striped" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th scope="col" style="text-align: left;">User</th>
                                        <th scope="col" style="text-align: left;">Hours</th>
                                        <th scope="col" style="text-align: left;">Tasks</th>
                                    </tr>
                                    </thead>
                                    <tbody>';

        foreach($project->users as $user) {
            $htmlContent .= '<tr>
                            <td>' . $user->username . '</td>
                            <td>' . $user->total_hours . ' hours (' . Helper::getPercentageValue($user->total_hours, $project->total_hours, $decimals = 2) . '%)</td>
                            <td>' . $user->total_tasks . ' tasks (' . Helper::getPercentageValue($user->total_tasks, $project->total_tasks, $decimals = 2) . '%)</td>
                        </tr>';

        }
        $htmlContent .= '</tbody>
                </table>
            </div>';

    }


        $htmlContent .= '<div class="report-table-header">
    <h4>Hours by User</h4>

</div>
<div class="report-user-stories">
    <table class="table table-striped" style="width: 100%;">
        <thead>
        <tr>
            <th scope="col" style="text-align: left;">User</th>
            <th scope="col" style="text-align: left;">Hours</th>
            <th scope="col" style="text-align: left;">Tasks</th>
        </tr>
        </thead>
        <tbody>';

    foreach($users as $user) {
        $htmlContent .= '<tr>
                <td>'.$user->username.'</td>
                <td>'.$user->total_hours.' hours ('. Helper::getPercentageValue($user->total_hours, $header->total_hours, $decimals = 2) .'%)</td>
                <td>'.$user->total_tasks.' tasks ('. Helper::getPercentageValue($user->total_tasks, $header->total_tasks, $decimals = 2) .'%)</td>
            </tr>';
    }
        $htmlContent .=  '</tbody>
                    </table>
                </div>';


        $htmlContent .= '<img src="https://quickchart.io/chart?c={type:%27doughnut%27,data:{labels:'.str_replace('"', '%27',json_encode($labels)).',datasets:[{label:%27Projects%27,data:'.str_replace('"', '%27',json_encode($hours)).'}]}}" />';


        return $htmlContent;
    }
}
