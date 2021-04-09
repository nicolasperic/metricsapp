<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\WeeklyReport::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        //$schedule->command('weekly:report')->weeklyOn(1, '8:00');
        $schedule->command('weekly:report')->everyMinute()->when(function (){
            //workaround for Heroku scheduler
            $today = new Carbon();

            //$decimMin = (strlen($today->minute) > 1)? substr($today->minute, 0, 1): 0;
            return $today->dayOfWeek == Carbon::MONDAY
                && $today->hour == 8; //&& $decimMin == 1;
        });

        $schedule->command('assembla:sync')->everyMinute()->when(function (){
            //workaround for Heroku scheduler
            $now = new Carbon();
            return $now->hour % 6 == 0;//every six hours
        });

        $now = new Carbon();
        $schedule->command("projectsstats:sync --year=$now->year --month=$now->month")->everyMinute()->when(function (){
            //workaround for Heroku scheduler
            $now = new Carbon();
            return $now->hour % 3 == 0;//every 3 hours
        });

        /*$schedule->command('sprintiteration:iterate')->everyMinute()->when(function (){
            //workaround for Heroku scheduler
            $today = new Carbon();
            return $today->hour == 8;//every day at 8AM
        });*/


    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
