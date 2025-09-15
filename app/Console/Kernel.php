<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    // protected $commands = [
    //    'App\Console\Commands\DatabaseBackUp'
    // ];
    protected $commands = [
        // Commands\DownloadReport::class,
        Commands\DownloadExcel::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('database:backup')->dailyAt('03:00');//to download database
        $schedule->command('download:excel')->everyMinute();//to download excel file.
        // $schedule->command('download:geojson')->everyMinute();//to download geojson file.
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
