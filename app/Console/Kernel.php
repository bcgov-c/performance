<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\SendDailyNotification::class,
        Commands\BuildAdminOrgUsers::class,
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
        $schedule->command('command:ExportDatabaseToBI')
        ->timezone('America/Vancouver')
        ->dailyAt('00:00');

        $schedule->command('command:GetODSEmployeeDemographics')
        ->timezone('America/Vancouver')
        ->dailyAt('00:10');

        $schedule->command('command:BuildOrgTree')
        ->timezone('America/Vancouver')
        ->dailyAt('00:20');
  
        $schedule->command('command:SyncUserProfile')
        ->timezone('America/Vancouver')
        ->dailyAt('00:25');

        $schedule->command('command:BuildAdminOrgUsers')
        ->timezone('America/Vancouver')
        ->dailyAt('01:30');

        $schedule->command('command:CalcNextConversationDate')
        ->timezone('America/Vancouver')
        ->dailyAt('02:00');

        $schedule->command('command:CalcNextConversationDate')
        ->timezone('America/Vancouver')
        ->dailyAt('10:45');

        $schedule->command('command:NotifyConversationDue')
        ->timezone('America/Vancouver')    
        ->dailyAt('02:30')
        ->appendOutputTo(storage_path('logs/NotifyConversationDue.log'));
        
        $schedule->command('command:NotifyConversationDue')    
        ->timezone('America/Vancouver')
        // ->dailyAt('02:30')
        ->hourlyAt(30)
        ->between('08:00', '22:00')
        ->appendOutputTo(storage_path('logs/NotifyConversationDue.log'));

        $schedule->command('notify:daily')
        ->dailyAt('08:00');

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
