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
            ->sendOutputTo(storage_path('logs/ExportDatabaseToBI.log'))
            ->daily();

        $schedule->command('command:GetODSEmployeeDemographics')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/GetODSEmployeeDemographics_daily.log'))
            ->dailyAt('00:05')
            ->days([1, 2, 3, 4, 5, 6]);

        $schedule->command('command:GetODSEmployeeDemographics --alldata')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/GetODSEmployeeDemographics_weekly.log'))
            ->dailyAt('00:05')
            ->sundays();

        $schedule->command('command:GetODSDeptHierarchy')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/GetODSDeptHierarchy.log'))
            ->dailyAt('00:15');
  
        $schedule->command('command:BuildOrgTree')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/BuildOrgTree.log'))
            ->dailyAt('00:20');
  
        $schedule->command('command:SyncUserProfile')
            ->timezone('America/Vancouver')
            ->dailyAt('00:25')
            ->appendOutputTo(storage_path('logs/SyncUserProfile.log'));

        $schedule->command('command:GetODSPositions')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/GetODSPositions.log'))
            ->dailyAt('00:30');

        $schedule->command('command:UpdateGUIDByEmployeeId')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/UpdateGUIDByEmployeeId.log'))
            ->dailyAt('00:45');

        $schedule->command('command:PopulateAuthUsers')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/PopulateAuthUsers.log'))
            ->dailyAt('01:00');
  
        $schedule->command('command:PopulateAuthOrgs')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/PopulateAuthOrgs.log'))
            ->dailyAt('01:05');
  
        $schedule->command('command:SetNextLevelManager')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/SetNextLevelManager.log'))
            ->dailyAt('01:15');

        $schedule->command('command:BuildAdminOrgUsers')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/BuildAdminOrgUsers.log'))
            ->dailyAt('01:30');

        $schedule->command('command:CalcNextConversationDate')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/CalcNextConversationDate.log'))
            ->dailyAt('02:00');

        $schedule->command('command:NotifyConversationDue')
            ->timezone('America/Vancouver')    
            ->dailyAt('02:30')
            ->appendOutputTo(storage_path('logs/NotifyConversationDue.log'));
        
        $schedule->command('command:BuildEmployeeDemoTree')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/BuildEmployeeDemoTree.log'))
            ->dailyAt('03:00');
  
        $schedule->command('command:PopulateEmployeeManagersTable')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/PopulateEmployeeManagersTable.log'))
            ->dailyAt('03:30');
  
        $schedule->command('command:PopulateUsersAnnexTable')
            ->timezone('America/Vancouver')
            ->sendOutputTo(storage_path('logs/PopulateUsersAnnexTable.log'))
            ->dailyAt('04:00');
  
        $schedule->command('command:CleanShareProfile')
            ->timezone('America/Vancouver')    
            ->sendOutputTo(storage_path('logs/CleanShareProfile.log'))
            ->dailyAt('05:00');
        
        $schedule->command('command:MaintainLogs')
            ->timezone('America/Vancouver')    
            ->sendOutputTo(storage_path('logs/MaintainLogs.log'))
            ->dailyAt('06:00');
        
        $schedule->command('notify:daily')
            ->timezone('America/Vancouver')    
            ->dailyAt('08:00')
            ->appendOutputTo(storage_path('logs/daily.log'));

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
