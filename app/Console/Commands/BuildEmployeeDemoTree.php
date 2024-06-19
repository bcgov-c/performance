<?php
 
namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\EmployeeDemoTree;
use App\Models\EmployeeDemoTreeTemp;
use App\Models\JobSchedAudit;
use Carbon\Carbon;

class BuildEmployeeDemoTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:BuildEmployeeDemoTree {--manual}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build Employee Demographics Tree';

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
        $this->info(Carbon::now()->format('c')." - Build Employee Demographics Tree, Started: ". $start_time);

        $job_name = 'command:BuildEmployeeDemoTree';
        $switch = strtolower(env('PRCS_BUILD_ORG_TREE'));
        $manualoverride = (strtolower($this->option('manual')) ? true : false);
        $status = (($switch == 'on' || $manualoverride) ? 'Initiated' : 'Disabled');
        $audit_id = JobSchedAudit::insertGetId(
            [
                'job_name' => $job_name,
                'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                'status' => $status
            ]
        );

        $total = 0;

        if ($switch == 'on' || $manualoverride) {
            EmployeeDemoTreeTemp::truncate();
            $level = 0;
            do {
                $this->info(Carbon::now()->format('c')." - Processing Level {$level}...");
                switch ($level) {
                    case 0:
                        $field = "organization_key";
                        $parent_id = "NULL";
                        break;
                    case 1:
                        $field = "level{$level}_key";
                        $parent_id = "a.organization_key";
                        break;
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                        $field = "level{$level}_key";
                        $level2 = $level - 1;
                        $parent_id = "a.level{$level2}_key";
                        break;
                    default:
                        break;
                }
                // Insert by Org Level/Group
                \DB::statement("
                    INSERT INTO employee_demo_tree_temp (id, name, deptid, level, organization, level1_program, level2_division, level3_branch, level4, level5, level6, organization_key, level1_key, level2_key, level3_key, level4_key, level5_key, level6_key, organization_deptid, level1_deptid, level2_deptid, level3_deptid, level4_deptid, level5_deptid, level6_deptid, headcount, groupcount, parent_id) 
                    SELECT DISTINCT CONVERT(a.okey, UNSIGNED) AS okey, a.name, a.deptid, a.ulevel, a.organization_label, a.level1_label, a.level2_label, a.level3_label, a.level4_label, a.level5_label, a.level6_label, a.organization_key, a.level1_key, a.level2_key, a.level3_key, a.level4_key, a.level5_key, a.level6_key, a.organization_deptid, a.level1_deptid, a.level2_deptid, a.level3_deptid, a.level4_deptid, a.level5_deptid, a.level6_deptid,
                        (SELECT COUNT(1) FROM employee_demo AS e USE INDEX (idx_employee_demo_deptid) WHERE e.deptid = a.deptid AND e.date_deleted IS NULL) AS headcount,
                        0 AS groupcount,
                        {$parent_id}
                    FROM ods_dept_org_hierarchy AS a USE INDEX (idx_byHierarchyokey),
                        ods_dept_org_hierarchy AS b USE INDEX (ods_dept_org_hierarchy_deptid_{$field}_index, idx_ods_dept_org_hierarchy_{$field}),
                        employee_demo AS c USE INDEX (idx_employee_demo_deptid) 
                    WHERE a.okey = CONVERT(b.{$field}, UNSIGNED)
                        AND b.deptid = c.deptid AND c.date_deleted IS NULL
                ");
                // Update Group Count
                $group = DB::select("
                    SELECT g.{$field} AS orgid, COUNT(1) AS groupcount FROM employee_demo AS f USE INDEX (idx_employee_demo_deptid), ods_dept_org_hierarchy AS g USE INDEX (ods_dept_org_hierarchy_deptid_{$field}_index, idx_ods_dept_org_hierarchy_{$field})  
                    WHERE g.deptid = f.deptid AND  f.date_deleted IS NULL AND g.{$field} IS NOT NULL
                    GROUP BY g.{$field}
                ");
                foreach($group as $dept){
                    EmployeeDemoTreeTemp::where('id', $dept->orgid)
                        ->update(
                            [
                                'groupcount' => $dept->groupcount,
                            ]
                        );
                    // $this->info(Carbon::now()->format('c')." - Org:{$dept->orgid} Count:{$dept->groupcount}");
                }
                $level++;
            } while ($level < 7);

            $result = EmployeeDemoTreeTemp::select(\DB::raw('count(1) AS totalcount'))->first();
            $total = $result->totalcount;

            // Move new tree
            if($total > 0) {
                \DB::beginTransaction();
                try {
                    $this->info(Carbon::now()->format('c').' - Copying tree from temp...');
                    EmployeeDemoTree::query()->delete();
                    \DB::statement("INSERT INTO employee_demo_tree SELECT * FROM employee_demo_tree_temp");
                    \DB::commit();
                    $this->info(Carbon::now()->format('c').' - Copy completed.');
                } catch (Exception $e) {
                    echo 'Unable to copy new tree from temp.'; echo "\r\n";
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
                            'details' => 'Unable to copy new tree from temp.',
                        ]
                    );
                        }
            } else {
                $this->info(Carbon::now()->format('c').' - No new tree generated. Keeping previous tree.');
            }

            // Update OrgId in employee_demo table
            $this->info(Carbon::now()->format('c').' - Updating Org Ids in employee_demo...');
            \DB::statement("UPDATE employee_demo SET employee_demo.orgid = (SELECT employee_demo_tree.id FROM employee_demo_tree WHERE employee_demo_tree.deptid = employee_demo.deptid LIMIT 1)");
            $this->info(Carbon::now()->format('c').' - Org Ids updated in employee_demo.');

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

            $this->info(Carbon::now()->format('c')." - Build Employee Demographics Tree, Completed: {$end_time}");
        } else {
            $this->info(Carbon::now()->format('c')." - Process is currently disabled; or 'PRCS_BUILD_ORG_TREE=on' is currently missing in the .env file.");
        }
    }

}
