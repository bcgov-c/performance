<?php
 
namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\OrganizationStatistics;
use App\Models\JobSchedAudit;
use Carbon\Carbon;

class PopulateOrganizationStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PopulateOrganizationStatistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate Organization Statistics Table';

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
        $this->info(Carbon::now()->format('c')." - Populate Organization Statistics Table, Started: ". $start_time);

        $job_name = 'command:PopulateOrganizationStatistics';
        $audit_id = JobSchedAudit::insertGetId(
            [
                'job_name' => $job_name,
                'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                'status' => 'Initiated'
            ]
        );

        try {
            OrganizationStatistics::truncate();

            // user_demo_jr_view group count
            $total = 0;
            $level = 0;
            do {
                $this->info(Carbon::now()->format('c')." - Processing Level {$level}...");
                switch ($level) {
                    case 0:
                        $field = "organization_key";
                        break;
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                        $field = "level{$level}_key";
                        break;
                    default:
                        break;
                }
                \DB::statement("
                    INSERT INTO organization_statistics (orgid, userdemojrview_groupcount, created_at, updated_at) 
                    SELECT edt.{$field} AS orgid, COUNT(1) AS groupcount, NOW(), NOW() 
                    FROM user_demo_jr_view udjv, employee_demo_tree edt
                    WHERE udjv.orgid = edt.id AND udjv.date_deleted IS NULL AND edt.{$field} IS NOT NULL
                    GROUP BY edt.{$field}
                ");
                $level++;
            } while ($level < 7);

            $result = OrganizationStatistics::select(\DB::raw('count(1) AS totalcount'))->first();
            $total = $result->totalcount;

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
                    'details' => 'Processed '.$total.' rows.',
                    ]
            );
    
        } catch (Exception $e) {
            echo 'Unable to update Organization Statistics.'; echo "\r\n";
            \DB::rollback();
            $end_time = Carbon::now()->format('c');
            DB::table('job_sched_audit')->updateOrInsert(
                [
                    'id' => $audit_id
                ],
                [
                    'job_name' => $job_name,
                    'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                    'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
                    'status' => 'Failed',
                    'details' => 'Unable to update organization_statistics.',
                ]
            );
        }

        $this->info(Carbon::now()->format('c')." - Populate Organization Statistics Table, Completed: {$end_time}");
    }

}
