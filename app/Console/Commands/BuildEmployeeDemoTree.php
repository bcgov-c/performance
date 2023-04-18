<?php
 
namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\EmployeeDemoTree;
use App\Models\OrganizationHierarchy;
use App\Models\JobSchedAudit;
use App\Models\EmployeeDemo;
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

        $count_insert = 0;
        $count_update = 0;
        $count_delete = 0;
        $total = 0;

        if ($switch == 'on' || $manualoverride) {
            $level = 0;
            do {
                $this->info(Carbon::now()->format('c')." - Processing Level {$level}...");
                $allDepts = OrganizationHierarchy::distinct()
                ->select("odoh.*")
                ->selectRaw("(SELECT COUNT(e.employee_id) FROM employee_demo AS e WHERE e.deptid = odoh.deptid AND e.date_deleted IS NULL) AS headcount")
                ->whereRaw("EXISTS (SELECT DISTINCT 1 FROM employee_demo AS d WHERE d.deptid = ods_dept_org_hierarchy.deptid)")
                ->orderBy("odoh.name")
                ->orderBy("odoh.okey");
                switch ($level) {
                    case 0:
                        $allDepts = $allDepts->join("ods_dept_org_hierarchy AS odoh", "odoh.okey", "ods_dept_org_hierarchy.organization_key");
                        $field = "organization_key";
                        break;
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                        $allDepts = $allDepts->join("ods_dept_org_hierarchy AS odoh", "odoh.okey", "ods_dept_org_hierarchy.level{$level}_key");
                        $field = "level{$level}_key";
                        break;
                    default:
                        break;
                }
                $allDepts = $allDepts->get();
                foreach ($allDepts AS $dept) {
                    $node = EmployeeDemoTree::where('id', $dept->okey)->first();
                    $groupcount = EmployeeDemo::join('ods_dept_org_hierarchy', 'employee_demo.deptid', 'ods_dept_org_hierarchy.deptid')
                        ->whereNull('employee_demo.date_deleted')
                        ->whereRaw("ods_dept_org_hierarchy.{$field} = {$dept->okey}")
                        ->count();
                    if (!$node) {
                        $node = new EmployeeDemoTree([
                            'id' => $dept->okey,
                            'name' => $dept->name,
                            'deptid' => $dept->deptid,
                            'status' => 1,
                            'level' => $dept->ulevel,
                            'headcount' => $dept->headcount,
                            'groupcount' => $groupcount,
                            'organization' =>  $dept->organization_label,
                            'level1_program' =>  $dept->level1_label,
                            'level2_division' =>  $dept->level2_label,
                            'level3_branch' =>  $dept->level3_label,
                            'level4' =>  $dept->level4_label,
                            'level5' =>  $dept->level5_label,
                            'organization_key' =>  $dept->organization_key,
                            'level1_key' =>  $dept->level1_key,
                            'level2_key' =>  $dept->level2_key,
                            'level3_key' =>  $dept->level3_key,
                            'level4_key' =>  $dept->level4_key,
                            'level5_key' =>  $dept->level5_key,
                            'organization_deptid' =>  $dept->organization_deptid,
                            'level1_deptid' =>  $dept->level1_deptid,
                            'level2_deptid' =>  $dept->level2_deptid,
                            'level3_deptid' =>  $dept->level3_deptid,
                            'level4_deptid' =>  $dept->level4_deptid,
                            'level5_deptid' =>  $dept->level5_deptid,
                            'organization_orgid' =>  $dept->organization_orgid,
                            'level1_orgid' =>  $dept->level1_orgid,
                            'level2_orgid' =>  $dept->level2_orgid,
                            'level3_orgid' =>  $dept->level3_orgid,
                            'level4_orgid' =>  $dept->level4_orgid,
                            'level5_orgid' =>  $dept->level5_orgid,
                        ]);
                        switch ($level) {
                            case 0:
                                $node->saveAsRoot(); 
                                break;
                            case 1:
                                $field = "organization_key";
                                $node->parent_id = $dept->{$field};     
                                $node->save();
                                break;              
                            case 2:
                            case 3:
                            case 4:
                            case 5:
                                $level2 = $level - 1;
                                $field = "level{$level2}_key";
                                $node->parent_id = $dept->{$field};     
                                $node->save();
                                break;              
                            default:
                                break;
                        }
                        $this->info(Carbon::now()->format('c')." -   created {$level} - {$dept->okey} - {$dept->name}");
                        $count_insert++;
                    } else {
                        $node->name = $dept->name;
                        $node->deptid = $dept->deptid;
                        $node->headcount = $dept->headcount;
                        $node->groupcount = $groupcount;
                        $node->organization = $dept->organization_label;
                        $node->level1_program = $dept->level1_label;
                        $node->level2_division = $dept->level2_label;
                        $node->level3_branch= $dept->level3_label;
                        $node->level4 = $dept->level4_label;
                        $node->level5 = $dept->level5_label;
                        $node->organization_deptid = $dept->organization_deptid;
                        $node->level1_deptid = $dept->level1_deptid;
                        $node->level2_deptid = $dept->level2_deptid;
                        $node->level3_deptid = $dept->level3_deptid;
                        $node->level4_deptid = $dept->level4_deptid;
                        $node->level5_deptid = $dept->level5_deptid;
                        $node->save();
                        $this->info(Carbon::now()->format('c')." -   updated {$level} - {$dept->okey} - {$dept->name}");
                        $count_update++;
                    }
                    $total++;
                }
                $level++;
            } while ($level < 6);

            // Update OrgId in employee_demo table
            $this->info(Carbon::now()->format('c').' - Updating Org Ids in employee_demo...');
            EmployeeDemo::whereRaw("deptid IS NULL OR TRIM(deptid) = ''")
            ->update(['orgid' => null]);
            $demoDepts = EmployeeDemo::distinct()
            ->whereNotNull('deptid')
            ->select('deptid')
            ->orderBy('deptid')
            ->get();
            foreach($demoDepts as $dept){
            $org = EmployeeDemoTree::where('deptid', $dept->deptid)
                ->select('id')
                ->first();
            EmployeeDemo::where('deptid', $dept->deptid)
                ->update(['orgid' => $org ? $org->id : null]);
            }
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
                'details' => 'Processed '.$total.' rows. Inserted '.$count_insert.' rows. Updated '.$count_update.' rows. Deleted '.$count_delete.' rows. ',
                ]
            );

            $this->info(Carbon::now()->format('c')." - Build Employee Demographics Tree, Completed: {$end_time}");
        } else {
            $this->info(Carbon::now()->format('c')." - Process is currently disabled; or 'PRCS_BUILD_ORG_TREE=on' is currently missing in the .env file.");
        }
    }

}
