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

        /** @var \Carbon\Carbon $now */
        $now = new Carbon();
        $schedule->command('weekly:report')->everyMinute()->when(function () use($now) {
            //workaround for Heroku scheduler
            //$decimMin = (strlen($today->minute) > 1)? substr($today->minute, 0, 1): 0;
            return $now->dayOfWeek == Carbon::MONDAY
                && $now->hour == 8; //&& $decimMin == 1;
        });

        $schedule->command('assembla:sync')->everyMinute()->when(function () use($now) {
            //workaround for Heroku scheduler
            return $now->hour % 6 == 0;//every six hours
        });

        $schedule->command("projectsstats:sync --year=$now->year --month=$now->month")
            ->everyMinute()->when(function () use($now) {
            //workaround for Heroku scheduler
            return $now->hour % 3 == 0;//every 3 hours
        });

        //tracking last month hours only on the first 5 days of the month
        if ($now->day <= 5) {
            $schedule->command("projectsstats:sync --year=$now->year --month=".$now->subMonth()->month)
                ->everyMinute()->when(function () use($now) {
                    //workaround for Heroku scheduler
                    return $now->hour % 3 == 0;//every 3 hours
                });
        }

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
