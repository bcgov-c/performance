<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JobSchedAudit;

class PopulateAuthUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MaintainLogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Maintain logs stored in job_sched_audit and job_data_audit.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start_time = Carbon::now()->format('c');
        $this->info( 'Maintain Logs, Started:   '. $start_time);
  
        $job_name = 'command:MaintainLogs';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );

        $extraMessage = '';
        $countSchedDeleted = 0;
        $countDataDeleted = 0;

        $retentionSched = strtolower(env('PRCS_SCHED_LOG_RETENTION'));
        if(!$retentionSched) { $retentionSched = 30; }
        $retentionData = strtolower(env('PRCS_DATA_LOG_RETENTION'));
        if(!$retentionData) { $retentionData = 7; }

        if($retentionData > $retentionSched) {
            $extraMessage = "PRCS_SCHED_LOG_RETENTION ({$retentionSched}) must be greater than or equal to PRCS_DATA_LOG_RETENTION ({$retentionData}).  ";
        } else {
            $extraMessage = "Retaining {$retentionSched} days of job_data_audit, and {$retentionData} days of job_data_audit.  ";

            $dateSched = date('Y-m-d H:i:s', strtotime(Carbon::now()->subDays($retentionSched)->format('c')));
            $dateData = date('Y-m-d H:i:s', strtotime(Carbon::now()->subDays($retentionData)->format('c')));

            $this->info(Carbon::now()->format('c')." - Retaining past {$retentionData} days in job_data_audit.");
            $countDataBefore = \DB::table('job_data_audit')->count();
            $recCheck = \DB::table('job_data_audit')->where('created_at', '<', \DB::raw("'{$dateData}'"))->first();
            while ($recCheck) {
                \DB::statement("
                    DELETE 
                    FROM job_data_audit
                    WHERE created_at < '{$dateData}'
                    LIMIT 100000
                ");
                $recCheck = \DB::table('job_data_audit')->where('created_at', '<', \DB::raw("'{$dateData}'"))->first();
            }
            $countDataAfter = \DB::table('job_data_audit')->count();
            $countDataDeleted = $countDataBefore - $countDataAfter;
            $this->info(Carbon::now()->format('c')." - Deleted {$countDataDeleted} rows from job_data_audit.");

            $this->info(Carbon::now()->format('c')." - Retaining past {$retentionSched} days in job_sched_audit.");
            $countSchedBefore = \DB::table('job_sched_audit')->count();
            \DB::statement("
                DELETE 
                FROM job_sched_audit
                WHERE start_time < '{$dateSched}'
            ");
            $countSchedAfter = \DB::table('job_sched_audit')->count();
            $countSchedDeleted = $countSchedBefore - $countSchedAfter;
            $this->info(Carbon::now()->format('c')." - Deleted {$countSchedDeleted} rows from job_sched_audit.");
        }

        $end_time = Carbon::now()->format('c');
        DB::table('job_sched_audit')->updateOrInsert(
          [
            'id' => $audit_id
          ],
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
            'status' => 'Completed',
            'details' => "{$extraMessage}Deleted {$countSchedDeleted} rows from job_sched_audit.  Deleted {$countDataDeleted} rows from job_data_audit."
          ]
        );
  
        $this->info( 'Maintain Logs, Completed: ' . $end_time);
  
    }
}
