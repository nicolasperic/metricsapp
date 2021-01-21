<?php

namespace App\Console\Commands;

use App\Jobs\ProcessHoursByUsersReport;
use App\Report;
use App\Reports\HoursByUserReport;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates weekly report based on user settings';

    protected $weeklyReportFromDate;
    protected $weeklyReportToDate;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->calculateLastWeekFromToDates();
    }

    private function calculateLastWeekFromToDates()
    {
        $currentDateMonday = Carbon::now();
        $currentDateSunday = Carbon::now();

        $this->weeklyReportFromDate = $currentDateMonday->subDays($currentDateMonday->dayOfWeek-1)->subWeek()->format('Y/m/d');
        $this->weeklyReportToDate = $currentDateSunday->subDays($currentDateSunday->dayOfWeek)->format('Y/m/d');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::whereNotNull('weekly_report_projects')->get();
        foreach ($users as $user) {
            $weeklyProjects = unserialize($user->weekly_report_projects);
            $weeklyUsers = unserialize($user->weekly_report_users);
            $requestData = [
                'projects' => $weeklyProjects,
                'users' => $weeklyUsers,
                'from_date' => $this->weeklyReportFromDate.' 00:00',
                'to_date' => $this->weeklyReportToDate.' 23:59',
            ];


            $reportModel = HoursByUserReport::forUser($user, $requestData, 'Weekly Report');

            $user->reports()->save($reportModel);
            ProcessHoursByUsersReport::dispatch($requestData, $reportModel, $sendEmail = true);
        }

        $this->info('Weekly Report sent to All Users');

    }
}
