<?php
 
namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\OrgTree;
use App\Models\OrganizationHierarchyStaging;
use App\Models\OrganizationHierarchy;
use App\Models\JobSchedAudit;
use App\Models\JobDataAudit;
use App\Models\EmployeeDemo;
use Carbon\Carbon;

class GetODSDeptHierarchy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getODSDeptHierarchy {--manual}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve data from CData:Datamart_ePerform_meta_dept_org_hierarchy';

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
        $this->info('Department Org Hierarchy pull from ODS, Started: '. $start_time);

        $job_name = 'command:getODSDeptHierarchy';
        $switch = strtolower(env('PRCS_PULL_ODS_DEPARTMENTS'));
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

            $top = 10000;
            $skip = 0;

            $deptdata = Http::acceptJson()
            ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->withBasicAuth(env('ODS_DEMO_CLIENT_ID'),env('ODS_DEMO_CLIENT_SECRET'))
            ->withOptions(['query' => [
                '$top' => $top, 
                '$skip' => $skip, 
                // Filter below to shorten test run
                // '$filter' => "OrgHierarchyKey eq 1629",
                '$orderby' => 'HierarchyLevel, ParentOrgHierarchyKey, OrgHierarchyKey',
            ] ])
            ->get( env('ODS_DEPARTMENTS_URI') . '?$top=' . $top . '&$skip=' . $skip );
            $data = $deptdata['value'];

            OrganizationHierarchyStaging::truncate();

            do {

                $total += count($data);
                $this->info( now()." Staging => top = {$top} : skip = {$skip} : data = ".count($data)." : Count = {$total}");

                foreach($data as $item){
                    $customKey = "{$item['HierarchyLevel']}-{$item['ParentOrgHierarchyKey']}-{$item['OrgHierarchyKey']}";
                    // $old_values = [ 
                    //     'table' => 'ods_dept_org_hierarchy_stg',                        
                    // ];
                    OrganizationHierarchyStaging::create([
                        'OrgID' => $customKey,
                        'HierarchyLevel' => $item['HierarchyLevel'],
                        'ParentOrgHierarchyKey' => $item['ParentOrgHierarchyKey'],
                        'OrgHierarchyKey' => $item['OrgHierarchyKey'],
                        'DepartmentID' => $item['DepartmentID'],
                        'BusinessName' => $item['BusinessName'],
                        'date_deleted' => $item['date_deleted'],
                        'date_updated' => $item['date_updated'],
                    ]);
                    // $new_values = [ 
                    //     'table' => 'ods_dept_org_hierarchy_stg',                        
                    //     'OrgID' => $customKey, 
                    //     'HierarchyLevel' => $item['HierarchyLevel'],
                    //     'ParentOrgHierarchyKey' => $item['ParentOrgHierarchyKey'],
                    //     'OrgHierarchyKey' => $item['OrgHierarchyKey'],
                    //     'DepartmentID' => $item['DepartmentID'],
                    //     'BusinessName' => $item['BusinessName'],
                    //     'date_deleted' => $item['date_deleted'],
                    //     'date_updated' => $item['date_updated'],
                    // ];
                    // $audit = new JobDataAudit;
                    // $audit->job_sched_id = $audit_id;
                    // $audit->old_values = json_encode($old_values);
                    // $audit->new_values = json_encode($new_values);
                    // $audit->save();
                };

                $skip += $top;

                $deptdata = Http::acceptJson() 
                ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
                ->withBasicAuth(env('ODS_DEMO_CLIENT_ID'),env('ODS_DEMO_CLIENT_SECRET'))
                ->withOptions(['query' => [
                    '$top' => $top, 
                    '$skip' => $skip, 
                    // Filter below to shorten test run
                    // '$filter' => "OrgHierarchyKey eq '1629'",
                    '$orderby' => 'HierarchyLevel, ParentOrgHierarchyKey, OrgHierarchyKey',
                    ] ])
              ->get( env('ODS_DEPARTMENTS_URI') . '?$top=' . $top . '&$skip=' . $skip );
              $data = $deptdata['value'];
      
            } while(count($data)!=0);

            $stagingCount = OrganizationHierarchyStaging::count();

            if ($stagingCount > 0) {

                // Organizations
                $this->info(now().' Processing Organizations...');
                $level_organization = 2;
                $organizations = OrganizationHierarchyStaging::whereRaw("HierarchyLevel = {$level_organization}")
                ->orderBy("BusinessName")
                ->orderBy("OrgHierarchyKey")
                ->selectRaw("
                    OrgID AS orgid,
                    HierarchyLevel AS hlevel,
                    ParentOrgHierarchyKey AS pkey,
                    OrgHierarchyKey AS okey,
                    DepartmentID AS deptid,
                    BusinessName AS name,
                    0 AS ulevel,
                    OrgID AS organization,
                    BusinessName AS organization_label,
                    DepartmentID AS organization_deptid,
                    OrgHierarchyKey AS organization_key,
                    null AS level1,
                    null AS level1_label,
                    null AS level1_deptid,
                    null AS level1_key,
                    null AS level2,
                    null AS level2_label,
                    null AS level2_deptid,
                    null AS level2_key,
                    null AS level3,
                    null AS level3_label,
                    null AS level3_deptid,
                    null AS level3_key,
                    null AS level4,
                    null AS level4_label,
                    null AS level4_deptid,
                    null AS level4_key,
                    null AS level5,
                    null AS level5_label,
                    null AS level5_deptid,
                    null AS level5_key,
                    BusinessName AS org_path,
                    date_deleted,
                    date_updated,
                    0 AS exception,
                    null AS exception_reason,
                    0 AS unallocated,
                    0 AS duplicate
                ")
                ->get();
                foreach($organizations as $org){
                    $org_old = OrganizationHierarchy::whereRaw("orgid = '".$org->orgid."'")
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        level3,
                        level3_label,
                        level3_deptid,
                        level3_key,
                        level4,
                        level4_label,
                        level4_deptid,
                        level4_key,
                        level5,
                        level5_label,
                        level5_deptid,
                        level5_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    if ($org_old) {
                        if (trim($org) != trim($org_old)) {
                            // $old_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $org_old->orgid, 
                            //     'hlevel' => $org_old->hlevel, 
                            //     'pkey' => $org_old->pkey,
                            //     'okey' => $org_old->okey,
                            //     'deptid' => $org_old->deptid,
                            //     'name' => $org_old->name,
                            //     'ulevel' => $org_old->ulevel,
                            //     'organization' => $org_old->organization,
                            //     'organization_label' => $org_old->organization_label,
                            //     'organization_deptid' => $org_old->organization_deptid,
                            //     'organization_key' => $org_old->organization_key,
                            //     'level1' => $org_old->level1,
                            //     'level1_label' => $org_old->level1_label,
                            //     'level1_deptid' => $org_old->level1_deptid,
                            //     'level1_key' => $org_old->level1_key,
                            //     'level2' => $org_old->level2,
                            //     'level2_label' => $org_old->level2_label,
                            //     'level2_deptid' => $org_old->level2_deptid,
                            //     'level2_key' => $org_old->level2_key,
                            //     'level3' => $org_old->level3,
                            //     'level3_label' => $org_old->level3_label,
                            //     'level3_deptid' => $org_old->level3_deptid,
                            //     'level3_key' => $org_old->level3_key,
                            //     'level4' => $org_old->level4,
                            //     'level4_label' => $org_old->level4_label,
                            //     'level4_deptid' => $org_old->level4_deptid,
                            //     'level4_key' => $org_old->level4_key,
                            //     'level5' => $org_old->level5,
                            //     'level5_label' => $org_old->level5_label,
                            //     'level5_deptid' => $org_old->level5_deptid,
                            //     'level5_key' => $org_old->level5_key,
                            //     'org_path' => $org_old->org_path,
                            //     'date_deleted' => $org_old->date_deleted,
                            //     'date_updated' => $org_old->date_updated,
                            //     'exception' => $org_old->exception,
                            //     'exception_reason' => $org_old->exception_reason,
                            //     'unallocated' => $org_old->unallocated,
                            //     'duplicate' => $org_old->duplicate
                            // ];
                            OrganizationHierarchy::whereRaw("orgid = '".$org->orgid."'")
                            ->update([
                                'hlevel' => $org->hlevel,
                                'pkey' => $org->pkey,
                                'okey' => $org->okey,
                                'deptid' => $org->deptid,
                                'name' => $org->name,
                                'ulevel' => $org->ulevel,
                                'organization' => $org->organization,
                                'organization_label' => $org->organization_label,
                                'organization_deptid' => $org->organization_deptid,
                                'organization_key' => $org->organization_key,
                                'level1' => null,
                                'level1_label' => null,
                                'level1_deptid' => null,
                                'level1_key' => null,
                                'level2' => null,
                                'level2_label' => null,
                                'level2_deptid' => null,
                                'level2_key' => null,
                                'level3' => null,
                                'level3_label' => null,
                                'level3_deptid' => null,
                                'level3_key' => null,
                                'level4' => null,
                                'level4_label' => null,
                                'level4_deptid' => null,
                                'level4_key' => null,
                                'level5' => null,
                                'level5_label' => null,
                                'level5_deptid' => null,
                                'level5_key' => null,
                                'org_path' => $org->org_path,
                                'date_deleted' => $org->date_deleted,
                                'date_updated' => $org->date_updated,
                                'exception' => 0,
                                'exception_reason' => null,
                                'unallocated' => 0,
                                'duplicate' => 0,
                                'search_key' => "|{$org->organization_key}|"
                            ]);
                            // $new_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $org->orgid, 
                            //     'hlevel' => $org->hlevel,
                            //     'pkey' => $org->pkey,
                            //     'okey' => $org->okey,
                            //     'deptid' => $org->deptid,
                            //     'name' => $org->name,
                            //     'ulevel' => $org->ulevel,
                            //     'organization' => $org->organization,
                            //     'organization_label' => $org->organization_label,
                            //     'organization_deptid' => $org->organization_deptid,
                            //     'organization_key' => $org->organization_key,
                            //     'level1' => null,
                            //     'level1_label' => null,
                            //     'level1_deptid' => null,
                            //     'level1_key' => null,
                            //     'level2' => null,
                            //     'level2_label' => null,
                            //     'level2_deptid' => null,
                            //     'level2_key' => null,
                            //     'level3' => null,
                            //     'level3_label' => null,
                            //     'level3_deptid' => null,
                            //     'level3_key' => null,
                            //     'level4' => null,
                            //     'level4_label' => null,
                            //     'level4_deptid' => null,
                            //     'level4_key' => null,
                            //     'level5' => null,
                            //     'level5_label' => null,
                            //     'level5_deptid' => null,
                            //     'level5_key' => null,
                            //     'org_path' => $org->org_path,
                            //     'date_deleted' => $org->date_deleted,
                            //     'date_updated' => $org->date_updated,
                            //     'exception' => 0,
                            //     'exception_reason' => null,
                            //     'unallocated' => 0,
                            //     'duplicate' => 0
                            // ];
                            // $audit = new JobDataAudit;
                            // $audit->job_sched_id = $audit_id;
                            // $audit->old_values = json_encode($old_values);
                            // $audit->new_values = json_encode($new_values);
                            // $audit->save();
                        }
                    } else {
                        // $old_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy'
                        // ];
                        OrganizationHierarchy::create([
                            'orgid' => $org->orgid,
                            'hlevel' => $org->hlevel,
                            'pkey' => $org->pkey,
                            'okey' => $org->okey,
                            'deptid' => $org->deptid,
                            'name' => $org->name,
                            'ulevel' => $org->ulevel,
                            'organization' => $org->organization,
                            'organization_label' => $org->organization_label,
                            'organization_key' => $org->organization_key,
                            'level1' => null,
                            'level1_label' => null,
                            'level1_deptid' => null,
                            'level1_key' => null,
                            'level2' => null,
                            'level2_label' => null,
                            'level2_deptid' => null,
                            'level2_key' => null,
                            'level3' => null,
                            'level3_label' => null,
                            'level3_deptid' => null,
                            'level3_key' => null,
                            'level4' => null,
                            'level4_label' => null,
                            'level4_deptid' => null,
                            'level4_key' => null,
                            'level5' => null,
                            'level5_label' => null,
                            'level5_deptid' => null,
                            'level5_key' => null,
                            'search_key' => "|{$org->organization_key}|",
                            'org_path' => $org->org_path,
                            'date_deleted' => $org->date_deleted,
                            'date_updated' => $org->date_updated,
                            'exception' => 0,
                            'exception_reason' => null,
                            'unallocated' => 0,
                            'duplicate' => 0,
                            'search_key' => "|{$org->organization_key}|"
                        ]);
                        // $new_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy',                        
                        //     'orgid' => $org->orgid, 
                        //     'hlevel' => $org->hlevel,
                        //     'pkey' => $org->pkey,
                        //     'okey' => $org->okey,
                        //     'deptid' => $org->deptid,
                        //     'name' => $org->name,
                        //     'ulevel' => $org->ulevel,
                        //     'organization' => $org->organization,
                        //     'organization_label' => $org->organization_label,
                        //     'organization_key' => $org->organization_key,
                        //     'level1' => null,
                        //     'level1_label' => null,
                        //     'level1_deptid' => null,
                        //     'level1_key' => null,
                        //     'level2' => null,
                        //     'level2_label' => null,
                        //     'level2_deptid' => null,
                        //     'level2_key' => null,
                        //     'level3' => null,
                        //     'level3_label' => null,
                        //     'level3_deptid' => null,
                        //     'level3_key' => null,
                        //     'level4' => null,
                        //     'level4_label' => null,
                        //     'level4_deptid' => null,
                        //     'level4_key' => null,
                        //     'level5' => null,
                        //     'level5_label' => null,
                        //     'level5_deptid' => null,
                        //     'level5_key' => null,
                        //     'search_key' => "|{$org->organization_key}|",
                        //     'org_path' => $org->org_path,
                        //     'date_deleted' => $org->date_deleted,
                        //     'date_updated' => $org->date_updated,
                        //     'exception' => 0,
                        //     'exception_reason' => null,
                        //     'unallocated' => 0,
                        //     'duplicate' => 0
                        // ];
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                    }
                };

                // Level 1
                $this->info(now().' Processing Level 1...');
                $org_level = 3;
                $actual_level = 1;
                $depts = OrganizationHierarchyStaging::whereRaw("HierarchyLevel = {$org_level}")
                ->orderBy("BusinessName")
                ->orderBy("OrgHierarchyKey")
                ->selectRaw("
                    OrgID AS orgid,
                    HierarchyLevel AS hlevel,
                    ParentOrgHierarchyKey AS pkey,
                    OrgHierarchyKey AS okey,
                    DepartmentID AS deptid,
                    BusinessName AS name,
                    0 AS ulevel,
                    null AS organization,
                    null AS organization_label,
                    null AS organization_deptid,
                    null AS organization_key,
                    null AS level1,
                    null AS level1_label,
                    null AS level1_deptid,
                    null AS level1_key,
                    null AS level2,
                    null AS level2_label,
                    null AS level2_deptid,
                    null AS level2_key,
                    null AS level3,
                    null AS level3_label,
                    null AS level3_deptid,
                    null AS level3_key,
                    null AS level4,
                    null AS level4_label,
                    null AS level4_deptid,
                    null AS level4_key,
                    null AS level5,
                    null AS level5_label,
                    null AS level5_deptid,
                    null AS level5_key,
                    null AS org_path,
                    date_deleted,
                    date_updated,
                    0 AS exception,
                    null AS exception_reason,
                    0 AS unallocated,
                    0 AS duplicate
                ")
                ->get();
                foreach($depts as $dept){
                    $dept_old = OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        level3,
                        level3_label,
                        level3_deptid,
                        level3_key,
                        level4,
                        level4_label,
                        level4_deptid,
                        level4_key,
                        level5,
                        level5_label,
                        level5_deptid,
                        level5_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    $parent = OrganizationHierarchy::whereRaw("okey = ".$dept->pkey)
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    if ($parent) {
                        if ($parent->exception) {
                            $exception = 2;
                            $exception_reason = "Parent has exception";
                        } else {
                            if ($parent->date_deleted) {
                                $exception = 3;
                                $exception_reason = "Parent was deleted";
                            } else {
                                $exception = 0;
                                $exception_reason = NULL;
                            }
                        }
                        $organization = $parent->organization;
                        $organization_label = $parent->organization_label;
                        $organization_deptid = $parent->organization_deptid;
                        $organization_key = $parent->organization_key;
                    } else {
                        $exception = 1;
                        $exception_reason = "Parent Node missing";
                        $organization = NULL;
                        $organization_label = NULL;
                        $organization_deptid = NULL;
                        $organization_key = NULL;
                    }
                    $dept->exception = $exception;
                    $dept->exception_reason = $exception_reason;
                    $dept->organization = $organization;
                    $dept->organization_label = $organization_label;
                    $dept->organization_deptid = $organization_deptid;
                    $dept->organization_key = $organization_key;
                    if (str_contains(strtolower($dept->name), "unallocated") || str_contains(strtolower($dept->name), "inactive") || str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        $dept->level1 = null;
                        $dept->level1_label = null;
                        $dept->level1_deptid = null;
                        $dept->level1_key = null;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        // $this->AssignToBlankLevel($dept, $parent);
                        $dept->level1 = $dept->orgid;
                        $dept->level1_label = $dept->name;
                        $dept->level1_deptid = $dept->deptid;
                        $dept->level1_key = $dept->okey;
                        $dept->duplicate = 0;
                        $dept->ulevel = 1;
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
                    if ($dept_old) {
                        if (trim($dept) != trim($dept_old)) {
                            // $old_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept_old->orgid, 
                            //     'hlevel' => $dept_old->hlevel, 
                            //     'pkey' => $dept_old->pkey,
                            //     'okey' => $dept_old->okey,
                            //     'deptid' => $dept_old->deptid,
                            //     'name' => $dept_old->name,
                            //     'ulevel' => $dept_old->ulevel,
                            //     'organization' => $dept_old->organization,
                            //     'organization_label' => $dept_old->organization_label,
                            //     'organization_deptid' => $dept_old->organization_deptid,
                            //     'organization_key' => $dept_old->organization_key,
                            //     'level1' => $dept_old->level1,
                            //     'level1_label' => $dept_old->level1_label,
                            //     'level1_deptid' => $dept_old->level1_deptid,
                            //     'level1_key' => $dept_old->level1_key,
                            //     'level2' => $dept_old->level2,
                            //     'level2_label' => $dept_old->level2_label,
                            //     'level2_deptid' => $dept_old->level2_deptid,
                            //     'level2_key' => $dept_old->level2_key,
                            //     'level3' => $dept_old->level3,
                            //     'level3_label' => $dept_old->level3_label,
                            //     'level3_deptid' => $dept_old->level3_deptid,
                            //     'level3_key' => $dept_old->level3_key,
                            //     'level4' => $dept_old->level4,
                            //     'level4_label' => $dept_old->level4_label,
                            //     'level4_deptid' => $dept_old->level4_deptid,
                            //     'level4_key' => $dept_old->level4_key,
                            //     'level5' => $dept_old->level5,
                            //     'level5_label' => $dept_old->level5_label,
                            //     'level5_deptid' => $dept_old->level5_deptid,
                            //     'level5_key' => $dept_old->level5_key,
                            //     'org_path' => $dept_old->org_path,
                            //     'date_deleted' => $dept_old->date_deleted,
                            //     'date_updated' => $dept_old->date_updated,
                            //     'exception' => $dept_old->exception,
                            //     'exception_reason' => $dept_old->exception_reason,
                            //     'unallocated' => $dept_old->unallocated,
                            //     'duplicate' => $dept_old->duplicate
                            // ];
                            OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                            ->update([
                                'hlevel' => $dept->hlevel,
                                'pkey' => $dept->pkey,
                                'okey' => $dept->okey,
                                'deptid' => $dept->deptid,
                                'name' => $dept->name,
                                'ulevel' => $dept->ulevel,
                                'organization' => $dept->organization,
                                'organization_label' => $dept->organization_label,
                                'organization_deptid' => $dept->organization_deptid,
                                'organization_key' => $dept->organization_key,
                                'level1' => $dept->level1,
                                'level1_label' => $dept->level1_label,
                                'level1_deptid' => $dept->level1_deptid,
                                'level1_key' => $dept->level1_key,
                                'level2' => null,
                                'level2_label' => null,
                                'level2_deptid' => null,
                                'level2_key' => null,
                                'level3' => null,
                                'level3_label' => null,
                                'level3_deptid' => null,
                                'level3_key' => null,
                                'level4' => null,
                                'level4_label' => null,
                                'level4_deptid' => null,
                                'level4_key' => null,
                                'level5' => null,
                                'level5_label' => null,
                                'level5_deptid' => null,
                                'level5_key' => null,
                                'org_path' => $org_path,
                                'date_deleted' => $dept->date_deleted,
                                'date_updated' => $dept->date_updated,
                                'exception' => $dept->exception,
                                'exception_reason' => $dept->exception_reason,
                                'unallocated' => $dept->unallocated,
                                'duplicate' => $dept->duplicate,
                                'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|"
                            ]);
                            // $new_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept->orgid, 
                            //     'hlevel' => $dept->hlevel,
                            //     'pkey' => $dept->pkey,
                            //     'okey' => $dept->okey,
                            //     'deptid' => $dept->deptid,
                            //     'name' => $dept->name,
                            //     'ulevel' => $dept->ulevel,
                            //     'organization' => $dept->organization,
                            //     'organization_label' => $dept->organization_label,
                            //     'organization_deptid' => $dept->organization_deptid,
                            //     'organization_key' => $dept->organization_key,
                            //     'level1' => $dept->level1,
                            //     'level1_label' => $dept->level1_label,
                            //     'level1_deptid' => $dept->level1_deptid,
                            //     'level1_key' => $dept->level1_key,
                            //     'level2' => null,
                            //     'level2_label' => null,
                            //     'level2_deptid' => null,
                            //     'level2_key' => null,
                            //     'level3' => null,
                            //     'level3_label' => null,
                            //     'level3_deptid' => null,
                            //     'level3_key' => null,
                            //     'level4' => null,
                            //     'level4_label' => null,
                            //     'level4_deptid' => null,
                            //     'level4_key' => null,
                            //     'level5' => null,
                            //     'level5_label' => null,
                            //     'level5_deptid' => null,
                            //     'level5_key' => null,
                            //     'org_path' => $org_path,
                            //     'date_deleted' => $dept->date_deleted,
                            //     'date_updated' => $dept->date_updated,
                            //     'exception' => $dept->exception,
                            //     'exception_reason' => $dept->exception_reason,
                            //     'unallocated' => $dept->unallocated,
                            //     'duplicate' => $dept->duplicate
                            // ];
                            // $audit = new JobDataAudit;
                            // $audit->job_sched_id = $audit_id;
                            // $audit->old_values = json_encode($old_values);
                            // $audit->new_values = json_encode($new_values);
                            // $audit->save();
                        }
                    } else {
                        // $old_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy'
                        // ];
                        OrganizationHierarchy::create([
                            'orgid' => $dept->orgid,
                            'hlevel' => $dept->hlevel,
                            'pkey' => $dept->pkey,
                            'okey' => $dept->okey,
                            'deptid' => $dept->deptid,
                            'name' => $dept->name,
                            'ulevel' => $dept->ulevel,
                            'organization' => $organization,
                            'organization_label' => $organization_label,
                            'organization_deptid' => $organization_deptid,
                            'organization_key' => $dept->organization_key,
                            'level1' => $dept->level1,
                            'level1_label' => $dept->level1_label,
                            'level1_deptid' => $dept->level1_deptid,
                            'level1_key' => $dept->level1_key,
                            'level2' => null,
                            'level2_label' => null,
                            'level2_deptid' => null,
                            'level2_key' => null,
                            'level3' => null,
                            'level3_label' => null,
                            'level3_deptid' => null,
                            'level3_key' => null,
                            'level4' => null,
                            'level4_label' => null,
                            'level4_deptid' => null,
                            'level4_key' => null,
                            'level5' => null,
                            'level5_label' => null,
                            'level5_deptid' => null,
                            'level5_key' => null,
                            'org_path' => $org_path,
                            'date_deleted' => $dept->date_deleted,
                            'date_updated' => $dept->date_updated,
                            'exception' => $dept->exception,
                            'exception_reason' => $dept->exception_reason,
                            'unallocated' => $dept->unallocated,
                            'duplicate' => $dept->duplicate,
                            'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|"
                        ]);
                        // $new_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy',                        
                        //     'orgid' => $dept->orgid, 
                        //     'hlevel' => $dept->hlevel,
                        //     'pkey' => $dept->pkey,
                        //     'okey' => $dept->okey,
                        //     'deptid' => $dept->deptid,
                        //     'name' => $dept->name,
                        //     'ulevel' => $dept->ulevel,
                        //     'organization' => $organization,
                        //     'organization_label' => $organization_label,
                        //     'organization_deptid' => $organization_deptid,
                        //     'organization_key' => $dept->organization_key,
                        //     'level1' => $dept->level1,
                        //     'level1_label' => $dept->level1_label,
                        //     'level1_deptid' => $dept->level1_deptid,
                        //     'level1_key' => $dept->level1_key,
                        //     'level2' => null,
                        //     'level2_label' => null,
                        //     'level2_deptid' => null,
                        //     'level2_key' => null,
                        //     'level3' => null,
                        //     'level3_label' => null,
                        //     'level3_deptid' => null,
                        //     'level3_key' => null,
                        //     'level4' => null,
                        //     'level4_label' => null,
                        //     'level4_deptid' => null,
                        //     'level4_key' => null,
                        //     'level5' => null,
                        //     'level5_label' => null,
                        //     'level5_deptid' => null,
                        //     'level5_key' => null,
                        //     'org_path' => $org_path,
                        //     'date_deleted' => $dept->date_deleted,
                        //     'date_updated' => $dept->date_updated,
                        //     'exception' => $dept->exception,
                        //     'exception_reason' => $dept->exception_reason,
                        //     'unallocated' => $dept->unallocated,
                        //     'duplicate' => $dept->duplicate
                        // ];
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                    }
                };

                // Level 2
                $this->info(now().' Processing Level 2...');
                $org_level = 4;
                $actual_level = 2;
                $depts = OrganizationHierarchyStaging::whereRaw("HierarchyLevel = {$org_level}")
                ->orderBy("BusinessName")
                ->orderBy("OrgHierarchyKey")
                ->selectRaw("
                    OrgID AS orgid,
                    HierarchyLevel AS hlevel,
                    ParentOrgHierarchyKey AS pkey,
                    OrgHierarchyKey AS okey,
                    DepartmentID AS deptid,
                    BusinessName AS name,
                    0 AS ulevel,
                    null AS organization,
                    null AS organization_label,
                    null AS organization_deptid,
                    null AS organization_key,
                    null AS level1,
                    null AS level1_label,
                    null AS level1_deptid,
                    null AS level1_key,
                    null AS level2,
                    null AS level2_label,
                    null AS level2_deptid,
                    null AS level2_key,
                    null AS level3,
                    null AS level3_label,
                    null AS level3_deptid,
                    null AS level3_key,
                    null AS level4,
                    null AS level4_label,
                    null AS level4_deptid,
                    null AS level4_key,
                    null AS level5,
                    null AS level5_label,
                    null AS level5_deptid,
                    null AS level5_key,
                     null AS org_path,
                    date_deleted,
                    date_updated,
                    0 AS exception,
                    null AS exception_reason,
                    0 AS unallocated,
                    0 AS duplicate
                ")
                ->get();
                foreach($depts as $dept){
                    $dept_old = OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        level3,
                        level3_label,
                        level3_deptid,
                        level3_key,
                        level4,
                        level4_label,
                        level4_deptid,
                        level4_key,
                        level5,
                        level5_label,
                        level5_deptid,
                        level5_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    $parent = OrganizationHierarchy::whereRaw("okey = ".$dept->pkey)
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    if ($parent) {
                        if ($parent->exception) {
                            $exception = 2;
                            $exception_reason = "Parent has exception";
                        } else {
                            if ($parent->date_deleted) {
                                $exception = 3;
                                $exception_reason = "Parent was deleted";
                            } else {
                                $exception = 0;
                                $exception_reason = NULL;
                            }
                        }
                        $organization = $parent->organization;
                        $organization_label = $parent->organization_label;
                        $organization_deptid = $parent->organization_deptid;
                        $organization_key = $parent->organization_key;
                        $level1 = $parent->level1;
                        $level1_label = $parent->level1_label;
                        $level1_deptid = $parent->level1_deptid;
                        $level1_key = $parent->level1_key;
                    } else {
                        $exception = 1;
                        $exception_reason = "Parent Node missing";
                        $organization = NULL;
                        $organization_label = NULL;
                        $organization_deptid = NULL;
                        $organization_key = NULL;
                        $level1 = NULL;
                        $level1_label = NULL;
                        $level1_deptid = NULL;
                        $level1_key = NULL;
                    }
                    $dept->exception = $exception;
                    $dept->exception_reason = $exception_reason;
                    $dept->organization = $organization;
                    $dept->organization_label = $organization_label;
                    $dept->organization_deptid = $organization_deptid;
                    $dept->organization_key = $organization_key;
                    $dept->level1 = $level1;
                    $dept->level1_label = $level1_label;
                    $dept->level1_deptid = $level1_deptid;
                    $dept->level1_key = $level1_key;
                    if (str_contains(strtolower($dept->name), "unallocated") || str_contains(strtolower($dept->name), "inactive") || str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        // $this->AssignToBlankLevel($dept, $parent);
                        $dept->level2 = $dept->orgid;
                        $dept->level2_label = $dept->name;
                        $dept->level2_deptid = $dept->deptid;
                        $dept->level2_key = $dept->okey;
                        $dept->duplicate = $parent->duplicate ?? 0;
                        $dept->ulevel = 2;
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
                    if ($dept_old) {
                        if (trim($dept) != trim($dept_old)) {
                            // $old_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept_old->orgid, 
                            //     'hlevel' => $dept_old->hlevel, 
                            //     'pkey' => $dept_old->pkey,
                            //     'okey' => $dept_old->okey,
                            //     'deptid' => $dept_old->deptid,
                            //     'name' => $dept_old->name,
                            //     'ulevel' => $dept_old->ulevel,
                            //     'organization' => $dept_old->organization,
                            //     'organization_label' => $dept_old->organization_label,
                            //     'organization_deptid' => $dept_old->organization_deptid,
                            //     'organization_key' => $dept_old->organization_key,
                            //     'level1' => $dept_old->level1,
                            //     'level1_label' => $dept_old->level1_label,
                            //     'level1_deptid' => $dept_old->level1_deptid,
                            //     'level1_key' => $dept_old->level1_key,
                            //     'level2' => $dept_old->level2,
                            //     'level2_label' => $dept_old->level2_label,
                            //     'level2_deptid' => $dept_old->level2_deptid,
                            //     'level2_key' => $dept_old->level2_key,
                            //     'level3' => $dept_old->level3,
                            //     'level3_label' => $dept_old->level3_label,
                            //     'level3_deptid' => $dept_old->level3_deptid,
                            //     'level3_key' => $dept_old->level3_key,
                            //     'level4' => $dept_old->level4,
                            //     'level4_label' => $dept_old->level4_label,
                            //     'level4_deptid' => $dept_old->level4_deptid,
                            //     'level4_key' => $dept_old->level4_key,
                            //     'level5' => $dept_old->level5,
                            //     'level5_label' => $dept_old->level5_label,
                            //     'level5_deptid' => $dept_old->level5_deptid,
                            //     'level5_key' => $dept_old->level5_key,
                            //     'org_path' => $dept_old->org_path,
                            //     'date_deleted' => $dept_old->date_deleted,
                            //     'date_updated' => $dept_old->date_updated,
                            //     'exception' => $dept_old->exception,
                            //     'exception_reason' => $dept_old->exception_reason,
                            //     'unallocated' => $dept_old->unallocated,
                            //     'duplicate' => $dept_old->duplicate
                            // ];
                            OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                            ->update([
                                'hlevel' => $dept->hlevel,
                                'pkey' => $dept->pkey,
                                'okey' => $dept->okey,
                                'deptid' => $dept->deptid,
                                'name' => $dept->name,
                                'ulevel' => $dept->ulevel,
                                'organization' => $dept->organization,
                                'organization_label' => $dept->organization_label,
                                'organization_deptid' => $dept->organization_deptid,
                                'organization_key' => $dept->organization_key,
                                'level1' => $dept->level1,
                                'level1_label' => $dept->level1_label,
                                'level1_deptid' => $dept->level1_deptid,
                                'level1_key' => $dept->level1_key,
                                'level2' => $dept->level2,
                                'level2_label' => $dept->level2_label,
                                'level2_deptid' => $dept->level2_deptid,
                                'level2_key' => $dept->level2_key,
                                'level3' => null,
                                'level3_label' => null,
                                'level3_deptid' => null,
                                'level3_key' => null,
                                'level4' => null,
                                'level4_label' => null,
                                'level4_deptid' => null,
                                'level4_key' => null,
                                'level5' => null,
                                'level5_label' => null,
                                'level5_deptid' => null,
                                'level5_key' => null,
                                'org_path' => $org_path,
                                'date_deleted' => $dept->date_deleted,
                                'date_updated' => $dept->date_updated,
                                'exception' => $dept->exception,
                                'exception_reason' => $dept->exception_reason,
                                'unallocated' => $dept->unallocated,
                                'duplicate' => $dept->duplicate,
                                'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|"
                            ]);
                            // $new_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept->orgid, 
                            //     'hlevel' => $dept->hlevel,
                            //     'pkey' => $dept->pkey,
                            //     'okey' => $dept->okey,
                            //     'deptid' => $dept->deptid,
                            //     'name' => $dept->name,
                            //     'ulevel' => $dept->ulevel,
                            //     'organization' => $dept->organization,
                            //     'organization_label' => $dept->organization_label,
                            //     'organization_deptid' => $dept->organization_deptid,
                            //     'organization_key' => $dept->organization_key,
                            //     'level1' => $dept->level1,
                            //     'level1_label' => $dept->level1_label,
                            //     'level1_deptid' => $dept->level1_deptid,
                            //     'level1_key' => $dept->level1_key,
                            //     'level2' => $dept->level2,
                            //     'level2_label' => $dept->level2_label,
                            //     'level2_deptid' => $dept->level2_deptid,
                            //     'level2_key' => $dept->level2_key,
                            //     'level3' => null,
                            //     'level3_label' => null,
                            //     'level3_deptid' => null,
                            //     'level3_key' => null,
                            //     'level4' => null,
                            //     'level4_label' => null,
                            //     'level4_deptid' => null,
                            //     'level4_key' => null,
                            //     'level5' => null,
                            //     'level5_label' => null,
                            //     'level5_deptid' => null,
                            //     'level5_key' => null,
                            //     'org_path' => $org_path,
                            //     'date_deleted' => $dept->date_deleted,
                            //     'date_updated' => $dept->date_updated,
                            //     'exception' => $dept->exception,
                            //     'exception_reason' => $dept->exception_reason,
                            //     'unallocated' => $dept->unallocated,
                            //     'duplicate' => $dept->duplicate
                            // ];
                            // $audit = new JobDataAudit;
                            // $audit->job_sched_id = $audit_id;
                            // $audit->old_values = json_encode($old_values);
                            // $audit->new_values = json_encode($new_values);
                            // $audit->save();
                        }
                    } else {
                        // $old_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy'
                        // ];
                        OrganizationHierarchy::create([
                            'orgid' => $dept->orgid,
                            'hlevel' => $dept->hlevel,
                            'pkey' => $dept->pkey,
                            'okey' => $dept->okey,
                            'deptid' => $dept->deptid,
                            'name' => $dept->name,
                            'ulevel' => $dept->ulevel,
                            'organization' => $dept->organization,
                            'organization_label' => $dept->organization_label,
                            'organization_deptid' => $dept->organization_deptid,
                            'organization_key' => $dept->organization_key,
                            'level1' => $dept->level1,
                            'level1_label' => $dept->level1_label,
                            'level1_deptid' => $dept->level1_deptid,
                            'level1_key' => $dept->level1_key,
                            'level2' => $dept->level2,
                            'level2_label' => $dept->level2_label,
                            'level2_deptid' => $dept->level2_deptid,
                            'level2_key' => $dept->level2_key,
                            'level3' => null,
                            'level3_label' => null,
                            'level3_deptid' => null,
                            'level3_key' => null,
                            'level4' => null,
                            'level4_label' => null,
                            'level4_deptid' => null,
                            'level4_key' => null,
                            'level5' => null,
                            'level5_label' => null,
                            'level5_deptid' => null,
                            'level5_key' => null,
                            'org_path' => $org_path,
                            'date_deleted' => $dept->date_deleted,
                            'date_updated' => $dept->date_updated,
                            'exception' => $dept->exception,
                            'exception_reason' => $dept->exception_reason,
                            'unallocated' => $dept->unallocated,
                            'duplicate' => $dept->duplicate,
                            'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|"
                        ]);
                        // $new_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy',                        
                        //     'orgid' => $dept->orgid, 
                        //     'hlevel' => $dept->hlevel,
                        //     'pkey' => $dept->pkey,
                        //     'okey' => $dept->okey,
                        //     'deptid' => $dept->deptid,
                        //     'name' => $dept->name,
                        //     'ulevel' => $dept->ulevel,
                        //     'organization' => $dept->organization,
                        //     'organization_label' => $dept->organization_label,
                        //     'organization_deptid' => $dept->organization_deptid,
                        //     'organization_key' => $dept->organization_key,
                        //     'level1' => $dept->level1,
                        //     'level1_label' => $dept->level1_label,
                        //     'level1_deptid' => $dept->level1_deptid,
                        //     'level1_key' => $dept->level1_key,
                        //     'level2' => $dept->level2,
                        //     'level2_label' => $dept->level2_label,
                        //     'level2_deptid' => $dept->level2_deptid,
                        //     'level2_key' => $dept->level2_key,
                        //     'level3' => null,
                        //     'level3_label' => null,
                        //     'level3_deptid' => null,
                        //     'level3_key' => null,
                        //     'level4' => null,
                        //     'level4_label' => null,
                        //     'level4_deptid' => null,
                        //     'level4_key' => null,
                        //     'level5' => null,
                        //     'level5_label' => null,
                        //     'level5_deptid' => null,
                        //     'level5_key' => null,
                        //     'org_path' => $org_path,
                        //     'date_deleted' => $dept->date_deleted,
                        //     'date_updated' => $dept->date_updated,
                        //     'exception' => $dept->exception,
                        //     'exception_reason' => $dept->exception_reason,
                        //     'unallocated' => $dept->unallocated,
                        //     'duplicate' => $dept->duplicate
                        // ];
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                    }
                };

                // Level 3
                $this->info(now().' Processing Level 3...');
                $org_level = 5;
                $actual_level = 3;
                $depts = OrganizationHierarchyStaging::whereRaw("HierarchyLevel = {$org_level}")
                ->orderBy("BusinessName")
                ->orderBy("OrgHierarchyKey")
                ->selectRaw("
                    OrgID AS orgid,
                    HierarchyLevel AS hlevel,
                    ParentOrgHierarchyKey AS pkey,
                    OrgHierarchyKey AS okey,
                    DepartmentID AS deptid,
                    BusinessName AS name,
                    0 AS ulevel,
                    null AS organization,
                    null AS organization_label,
                    null AS organization_deptid,
                    null AS organization_key,
                    null AS level1,
                    null AS level1_label,
                    null AS level1_deptid,
                    null AS level1_key,
                    null AS level2,
                    null AS level2_label,
                    null AS level2_deptid,
                    null AS level2_key,
                    null AS level3,
                    null AS level3_label,
                    null AS level3_deptid,
                    null AS level3_key,
                    null AS level4,
                    null AS level4_label,
                    null AS level4_deptid,
                    null AS level4_key,
                    null AS level5,
                    null AS level5_label,
                    null AS level5_deptid,
                    null AS level5_key,
                    null AS org_path,
                    date_deleted,
                    date_updated,
                    0 AS exception,
                    null AS exception_reason,
                    0 AS unallocated,
                    0 AS duplicate
                ")
                ->get();
                foreach($depts as $dept){
                    $dept_old = OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        level3,
                        level3_label,
                        level3_deptid,
                        level3_key,
                        level4,
                        level4_label,
                        level4_deptid,
                        level4_key,
                        level5,
                        level5_label,
                        level5_deptid,
                        level5_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    $parent = OrganizationHierarchy::whereRaw("okey = ".$dept->pkey)
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    if ($parent) {
                        if ($parent->exception) {
                            $exception = 2;
                            $exception_reason = "Parent has exception";
                        } else {
                            if ($parent->date_deleted) {
                                $exception = 3;
                                $exception_reason = "Parent was deleted";
                            } else {
                                $exception = 0;
                                $exception_reason = NULL;
                            }
                        }
                        $organization = $parent->organization;
                        $organization_label = $parent->organization_label;
                        $organization_deptid = $parent->organization_deptid;
                        $organization_key = $parent->organization_key;
                        $level1 = $parent->level1;
                        $level1_label = $parent->level1_label;
                        $level1_deptid = $parent->level1_deptid;
                        $level1_key = $parent->level1_key;
                        $level2 = $parent->level2;
                        $level2_label = $parent->level2_label;
                        $level2_deptid = $parent->level2_deptid;
                        $level2_key = $parent->level2_key;
                    } else {
                        $exception = 1;
                        $exception_reason = "Parent Node missing";
                        $organization = NULL;
                        $organization_label = NULL;
                        $organization_deptid = NULL;
                        $organization_key = NULL;
                        $level1 = NULL;
                        $level1_label = NULL;
                        $level1_deptid = NULL;
                        $level1_key = NULL;
                        $level2 = NULL;
                        $level2_label = NULL;
                        $level2_deptid = NULL;
                        $level2_key = NULL;
                    }
                    $dept->exception = $exception;
                    $dept->exception_reason = $exception_reason;
                    $dept->organization = $organization;
                    $dept->organization_label = $organization_label;
                    $dept->organization_deptid = $organization_deptid;
                    $dept->organization_key = $organization_key;
                    $dept->level1 = $level1;
                    $dept->level1_label = $level1_label;
                    $dept->level1_deptid = $level1_deptid;
                    $dept->level1_key = $level1_key;
                    $dept->level2 = $level2;
                    $dept->level2_label = $level2_label;
                    $dept->level2_deptid = $level2_deptid;
                    $dept->level2_key = $level2_key;
                    if (str_contains(strtolower($dept->name), "unallocated") || str_contains(strtolower($dept->name), "inactive") || str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        // $this->AssignToBlankLevel($dept, $parent);
                        $dept->level3 = $dept->orgid;
                        $dept->level3_label = $dept->name;
                        $dept->level3_deptid = $dept->deptid;
                        $dept->level3_key = $dept->okey;
                        $dept->duplicate = $parent->duplicate ?? 0;
                        $dept->ulevel = 3;
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
                    if ($dept_old) {
                        if (trim($dept) != trim($dept_old)) {
                            // $old_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept_old->orgid, 
                            //     'hlevel' => $dept_old->hlevel, 
                            //     'pkey' => $dept_old->pkey,
                            //     'okey' => $dept_old->okey,
                            //     'deptid' => $dept_old->deptid,
                            //     'name' => $dept_old->name,
                            //     'ulevel' => $dept_old->ulevel,
                            //     'organization' => $dept_old->organization,
                            //     'organization_label' => $dept_old->organization_label,
                            //     'organization_deptid' => $dept_old->organization_deptid,
                            //     'organization_key' => $dept_old->organization_key,
                            //     'level1' => $dept_old->level1,
                            //     'level1_label' => $dept_old->level1_label,
                            //     'level1_deptid' => $dept_old->level1_deptid,
                            //     'level1_key' => $dept_old->level1_key,
                            //     'level2' => $dept_old->level2,
                            //     'level2_label' => $dept_old->level2_label,
                            //     'level2_deptid' => $dept_old->level2_deptid,
                            //     'level2_key' => $dept_old->level2_key,
                            //     'level3' => $dept_old->level3,
                            //     'level3_label' => $dept_old->level3_label,
                            //     'level3_deptid' => $dept_old->level3_deptid,
                            //     'level3_key' => $dept_old->level3_key,
                            //     'level4' => $dept_old->level4,
                            //     'level4_label' => $dept_old->level4_label,
                            //     'level4_deptid' => $dept_old->level4_deptid,
                            //     'level4_key' => $dept_old->level4_key,
                            //     'level5' => $dept_old->level5,
                            //     'level5_label' => $dept_old->level5_label,
                            //     'level5_deptid' => $dept_old->level5_deptid,
                            //     'level5_key' => $dept_old->level5_key,
                            //     'org_path' => $dept_old->org_path,
                            //     'date_deleted' => $dept_old->date_deleted,
                            //     'date_updated' => $dept_old->date_updated,
                            //     'exception' => $dept_old->exception,
                            //     'exception_reason' => $dept_old->exception_reason,
                            //     'unallocated' => $dept_old->unallocated,
                            //     'duplicate' => $dept_old->duplicate
                            // ];
                            OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                            ->update([
                                'hlevel' => $dept->hlevel,
                                'pkey' => $dept->pkey,
                                'okey' => $dept->okey,
                                'deptid' => $dept->deptid,
                                'name' => $dept->name,
                                'ulevel' => $dept->ulevel,
                                'organization' => $dept->organization,
                                'organization_label' => $dept->organization_label,
                                'organization_deptid' => $dept->organization_deptid,
                                'organization_key' => $dept->organization_key,
                                'level1' => $dept->level1,
                                'level1_label' => $dept->level1_label,
                                'level1_deptid' => $dept->level1_deptid,
                                'level1_key' => $dept->level1_key,
                                'level2' => $dept->level2,
                                'level2_label' => $dept->level2_label,
                                'level2_deptid' => $dept->level2_deptid,
                                'level2_key' => $dept->level2_key,
                                'level3' => $dept->level3,
                                'level3_label' => $dept->level3_label,
                                'level3_deptid' => $dept->level3_deptid,
                                'level3_key' => $dept->level3_key,
                                'level4' => null,
                                'level4_label' => null,
                                'level4_deptid' => null,
                                'level4_key' => null,
                                'level5' => null,
                                'level5_label' => null,
                                'level5_deptid' => null,
                                'level5_key' => null,
                                'org_path' => $org_path,
                                'date_deleted' => $dept->date_deleted,
                                'date_updated' => $dept->date_updated,
                                'exception' => $dept->exception,
                                'exception_reason' => $dept->exception_reason,
                                'unallocated' => $dept->unallocated,
                                'duplicate' => $dept->duplicate,
                                'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|"
                            ]);
                            // $new_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept->orgid, 
                            //     'hlevel' => $dept->hlevel,
                            //     'pkey' => $dept->pkey,
                            //     'okey' => $dept->okey,
                            //     'deptid' => $dept->deptid,
                            //     'name' => $dept->name,
                            //     'ulevel' => $dept->ulevel,
                            //     'organization' => $dept->organization,
                            //     'organization_label' => $dept->organization_label,
                            //     'organization_deptid' => $dept->organization_deptid,
                            //     'organization_key' => $dept->organization_key,
                            //     'level1' => $dept->level1,
                            //     'level1_label' => $dept->level1_label,
                            //     'level1_deptid' => $dept->level1_deptid,
                            //     'level1_key' => $dept->level1_key,
                            //     'level2' => $dept->level2,
                            //     'level2_label' => $dept->level2_label,
                            //     'level2_deptid' => $dept->level2_deptid,
                            //     'level2_key' => $dept->level2_key,
                            //     'level3' => $dept->level3,
                            //     'level3_label' => $dept->level3_label,
                            //     'level3_deptid' => $dept->level3_deptid,
                            //     'level3_key' => $dept->level3_key,
                            //     'level4' => null,
                            //     'level4_label' => null,
                            //     'level4_deptid' => null,
                            //     'level4_key' => null,
                            //     'level5' => null,
                            //     'level5_label' => null,
                            //     'level5_deptid' => null,
                            //     'level5_key' => null,
                            //     'org_path' => $org_path,
                            //     'date_deleted' => $dept->date_deleted,
                            //     'date_updated' => $dept->date_updated,
                            //     'exception' => $dept->exception,
                            //     'exception_reason' => $dept->exception_reason,
                            //     'unallocated' => $dept->unallocated,
                            //     'duplicate' => $dept->duplicate
                            // ];
                            // $audit = new JobDataAudit;
                            // $audit->job_sched_id = $audit_id;
                            // $audit->old_values = json_encode($old_values);
                            // $audit->new_values = json_encode($new_values);
                            // $audit->save();
                        }
                    } else {
                        // $old_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy'
                        // ];
                        OrganizationHierarchy::create([
                            'orgid' => $dept->orgid,
                            'hlevel' => $dept->hlevel,
                            'pkey' => $dept->pkey,
                            'okey' => $dept->okey,
                            'deptid' => $dept->deptid,
                            'name' => $dept->name,
                            'ulevel' => $dept->ulevel,
                            'organization' => $dept->organization,
                            'organization_label' => $dept->organization_label,
                            'organization_deptid' => $dept->organization_deptid,
                            'organization_key' => $dept->organization_key,
                            'level1' => $dept->level1,
                            'level1_label' => $dept->level1_label,
                            'level1_deptid' => $dept->level1_deptid,
                            'level1_key' => $dept->level1_key,
                            'level2' => $dept->level2,
                            'level2_label' => $dept->level2_label,
                            'level2_deptid' => $dept->level2_deptid,
                            'level2_key' => $dept->level2_key,
                            'level3' => $dept->level3,
                            'level3_label' => $dept->level3_label,
                            'level3_deptid' => $dept->level3_deptid,
                            'level3_key' => $dept->level3_key,
                            'level4' => null,
                            'level4_label' => null,
                            'level4_deptid' => null,
                            'level4_key' => null,
                            'level5' => null,
                            'level5_label' => null,
                            'level5_deptid' => null,
                            'level5_key' => null,
                            'org_path' => $org_path,
                            'date_deleted' => $dept->date_deleted,
                            'date_updated' => $dept->date_updated,
                            'exception' => $dept->exception,
                            'exception_reason' => $dept->exception_reason,
                            'unallocated' => $dept->unallocated,
                            'duplicate' => $dept->duplicate,
                            'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|"
                        ]);
                        // $new_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy',                        
                        //     'orgid' => $dept->orgid, 
                        //     'hlevel' => $dept->hlevel,
                        //     'pkey' => $dept->pkey,
                        //     'okey' => $dept->okey,
                        //     'deptid' => $dept->deptid,
                        //     'name' => $dept->name,
                        //     'ulevel' => $dept->ulevel,
                        //     'organization' => $dept->organization,
                        //     'organization_label' => $dept->organization_label,
                        //     'organization_deptid' => $dept->organization_deptid,
                        //     'organization_key' => $dept->organization_key,
                        //     'level1' => $dept->level1,
                        //     'level1_label' => $dept->level1_label,
                        //     'level1_deptid' => $dept->level1_deptid,
                        //     'level1_key' => $dept->level1_key,
                        //     'level2' => $dept->level2,
                        //     'level2_label' => $dept->level2_label,
                        //     'level2_deptid' => $dept->level2_deptid,
                        //     'level2_key' => $dept->level2_key,
                        //     'level3' => $dept->level3,
                        //     'level3_label' => $dept->level3_label,
                        //     'level3_deptid' => $dept->level3_deptid,
                        //     'level3_key' => $dept->level3_key,
                        //     'level4' => null,
                        //     'level4_label' => null,
                        //     'level4_deptid' => null,
                        //     'level4_key' => null,
                        //     'level5' => null,
                        //     'level5_label' => null,
                        //     'level5_deptid' => null,
                        //     'level5_key' => null,
                        //     'org_path' => $org_path,
                        //     'date_deleted' => $dept->date_deleted,
                        //     'date_updated' => $dept->date_updated,
                        //     'exception' => $dept->exception,
                        //     'exception_reason' => $dept->exception_reason,
                        //     'unallocated' => $dept->unallocated,
                        //     'duplicate' => $dept->duplicate
                        // ];
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                    }
                };

                // Level 4
                $this->info(now().' Processing Level 4...');
                $org_level = 6;
                $actual_level = 4;
                $depts = OrganizationHierarchyStaging::whereRaw("HierarchyLevel = {$org_level}")
                ->orderBy("BusinessName")
                ->orderBy("OrgHierarchyKey")
                ->selectRaw("
                    OrgID AS orgid,
                    HierarchyLevel AS hlevel,
                    ParentOrgHierarchyKey AS pkey,
                    OrgHierarchyKey AS okey,
                    DepartmentID AS deptid,
                    BusinessName AS name,
                    0 AS ulevel,
                    null AS organization,
                    null AS organization_label,
                    null AS organization_deptid,
                    null AS organization_key,
                    null AS level1,
                    null AS level1_label,
                    null AS level1_deptid,
                    null AS level1_key,
                    null AS level2,
                    null AS level2_label,
                    null AS level2_deptid,
                    null AS level2_key,
                    null AS level3,
                    null AS level3_label,
                    null AS level3_deptid,
                    null AS level3_key,
                    null AS level4,
                    null AS level4_label,
                    null AS level4_deptid,
                    null AS level4_key,
                    null AS level5,
                    null AS level5_label,
                    null AS level5_deptid,
                    null AS level5_key,
                    null AS org_path,
                    date_deleted,
                    date_updated,
                    0 AS exception,
                    null AS exception_reason,
                    0 AS unallocated,
                    0 AS duplicate
                ")
                ->get();
                foreach($depts as $dept){
                    $dept_old = OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        level3,
                        level3_label,
                        level3_deptid,
                        level3_key,
                        level4,
                        level4_label,
                        level4_deptid,
                        level4_key,
                        level5,
                        level5_label,
                        level5_deptid,
                        level5_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    $parent = OrganizationHierarchy::whereRaw("okey = ".$dept->pkey)
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        level3,
                        level3_label,
                        level3_deptid,
                        level3_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    if ($parent) {
                        if ($parent->exception) {
                            $exception = 2;
                            $exception_reason = "Parent has exception";
                        } else {
                            if ($parent->date_deleted) {
                                $exception = 3;
                                $exception_reason = "Parent was deleted";
                            } else {
                                $exception = 0;
                                $exception_reason = NULL;
                            }
                        }
                        $organization = $parent->organization;
                        $organization_label = $parent->organization_label;
                        $organization_deptid = $parent->organization_deptid;
                        $organization_key = $parent->organization_key;
                        $level1 = $parent->level1;
                        $level1_label = $parent->level1_label;
                        $level1_deptid = $parent->level1_deptid;
                        $level1_key = $parent->level1_key;
                        $level2 = $parent->level2;
                        $level2_label = $parent->level2_label;
                        $level2_deptid = $parent->level2_deptid;
                        $level2_key = $parent->level2_key;
                        $level3 = $parent->level3;
                        $level3_label = $parent->level3_label;
                        $level3_deptid = $parent->level3_deptid;
                        $level3_key = $parent->level3_key;
                    } else {
                        $exception = 1;
                        $exception_reason = "Parent Node missing";
                        $organization = NULL;
                        $organization_label = NULL;
                        $organization_deptid = NULL;
                        $organization_key = NULL;
                        $level1 = NULL;
                        $level1_label = NULL;
                        $level1_deptid = NULL;
                        $level1_key = NULL;
                        $level2 = NULL;
                        $level2_label = NULL;
                        $level2_deptid = NULL;
                        $level2_key = NULL;
                        $level3 = NULL;
                        $level3_label = NULL;
                        $level3_deptid = NULL;
                        $level3_key = NULL;
                    }
                    $dept->exception = $exception;
                    $dept->exception_reason = $exception_reason;
                    $dept->organization = $organization;
                    $dept->organization_label = $organization_label;
                    $dept->organization_deptid = $organization_deptid;
                    $dept->organization_key = $organization_key;
                    $dept->level1 = $level1;
                    $dept->level1_label = $level1_label;
                    $dept->level1_deptid = $level1_deptid;
                    $dept->level1_key = $level1_key;
                    $dept->level2 = $level2;
                    $dept->level2_label = $level2_label;
                    $dept->level2_deptid = $level2_deptid;
                    $dept->level2_key = $level2_key;
                    $dept->level3 = $level3;
                    $dept->level3_label = $level3_label;
                    $dept->level3_deptid = $level3_deptid;
                    $dept->level3_key = $level3_key;
                    if (str_contains(strtolower($dept->name), "unallocated") || str_contains(strtolower($dept->name), "inactive") || str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        // $this->AssignToBlankLevel($dept, $parent);
                        $dept->level4 = $dept->orgid;
                        $dept->level4_label = $dept->name;
                        $dept->level4_deptid = $dept->deptid;
                        $dept->level4_key = $dept->okey;
                        $dept->duplicate = $parent->duplicate ?? 0;
                        $dept->ulevel = 4;
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
                    if ($dept_old) {
                        if (trim($dept) != trim($dept_old)) {
                            // $old_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept_old->orgid, 
                            //     'hlevel' => $dept_old->hlevel, 
                            //     'pkey' => $dept_old->pkey,
                            //     'okey' => $dept_old->okey,
                            //     'deptid' => $dept_old->deptid,
                            //     'name' => $dept_old->name,
                            //     'ulevel' => $dept_old->ulevel,
                            //     'organization' => $dept_old->organization,
                            //     'organization_label' => $dept_old->organization_label,
                            //     'organization_deptid' => $dept_old->organization_deptid,
                            //     'organization_key' => $dept_old->organization_key,
                            //     'level1' => $dept_old->level1,
                            //     'level1_label' => $dept_old->level1_label,
                            //     'level1_deptid' => $dept_old->level1_deptid,
                            //     'level1_key' => $dept_old->level1_key,
                            //     'level2' => $dept_old->level2,
                            //     'level2_label' => $dept_old->level2_label,
                            //     'level2_deptid' => $dept_old->level2_deptid,
                            //     'level2_key' => $dept_old->level2_key,
                            //     'level3' => $dept_old->level3,
                            //     'level3_label' => $dept_old->level3_label,
                            //     'level3_deptid' => $dept_old->level3_deptid,
                            //     'level3_key' => $dept_old->level3_key,
                            //     'level4' => $dept_old->level4,
                            //     'level4_label' => $dept_old->level4_label,
                            //     'level4_deptid' => $dept_old->level4_deptid,
                            //     'level4_key' => $dept_old->level4_key,
                            //     'level5' => $dept_old->level5,
                            //     'level5_label' => $dept_old->level5_label,
                            //     'level5_deptid' => $dept_old->level5_deptid,
                            //     'level5_key' => $dept_old->level5_key,
                            //     'org_path' => $dept_old->org_path,
                            //     'date_deleted' => $dept_old->date_deleted,
                            //     'date_updated' => $dept_old->date_updated,
                            //     'exception' => $dept_old->exception,
                            //     'exception_reason' => $dept_old->exception_reason,
                            //     'unallocated' => $dept_old->unallocated,
                            //     'duplicate' => $dept_old->duplicate
                            // ];
                            OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                            ->update([
                                'hlevel' => $dept->hlevel,
                                'pkey' => $dept->pkey,
                                'okey' => $dept->okey,
                                'deptid' => $dept->deptid,
                                'name' => $dept->name,
                                'ulevel' => $dept->ulevel,
                                'organization' => $dept->organization,
                                'organization_label' => $dept->organization_label,
                                'organization_deptid' => $dept->organization_deptid,
                                'organization_key' => $dept->organization_key,
                                'level1' => $dept->level1,
                                'level1_label' => $dept->level1_label,
                                'level1_deptid' => $dept->level1_deptid,
                                'level1_key' => $dept->level1_key,
                                'level2' => $dept->level2,
                                'level2_label' => $dept->level2_label,
                                'level2_deptid' => $dept->level2_deptid,
                                'level2_key' => $dept->level2_key,
                                'level3' => $dept->level3,
                                'level3_label' => $dept->level3_label,
                                'level3_deptid' => $dept->level3_deptid,
                                'level3_key' => $dept->level3_key,
                                'level4' => $dept->level4,
                                'level4_label' => $dept->level4_label,
                                'level4_deptid' => $dept->level4_deptid,
                                'level4_key' => $dept->level4_key,
                                'level5' => null,
                                'level5_label' => null,
                                'level5_deptid' => null,
                                'level5_key' => null,
                                'org_path' => $org_path,
                                'date_deleted' => $dept->date_deleted,
                                'date_updated' => $dept->date_updated,
                                'exception' => $dept->exception,
                                'exception_reason' => $dept->exception_reason,
                                'unallocated' => $dept->unallocated,
                                'duplicate' => $dept->duplicate,
                                'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|{$dept->level4_key}|"
                            ]);
                            // $new_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept->orgid, 
                            //     'hlevel' => $dept->hlevel,
                            //     'pkey' => $dept->pkey,
                            //     'okey' => $dept->okey,
                            //     'deptid' => $dept->deptid,
                            //     'name' => $dept->name,
                            //     'ulevel' => $dept->ulevel,
                            //     'organization' => $dept->organization,
                            //     'organization_label' => $dept->organization_label,
                            //     'organization_deptid' => $dept->organization_deptid,
                            //     'organization_key' => $dept->organization_key,
                            //     'level1' => $dept->level1,
                            //     'level1_label' => $dept->level1_label,
                            //     'level1_deptid' => $dept->level1_deptid,
                            //     'level1_key' => $dept->level1_key,
                            //     'level2' => $dept->level2,
                            //     'level2_label' => $dept->level2_label,
                            //     'level2_deptid' => $dept->level2_deptid,
                            //     'level2_key' => $dept->level2_key,
                            //     'level3' => $dept->level3,
                            //     'level3_label' => $dept->level3_label,
                            //     'level3_deptid' => $dept->level3_deptid,
                            //     'level3_key' => $dept->level3_key,
                            //     'level4' => $dept->level4,
                            //     'level4_label' => $dept->level4_label,
                            //     'level4_deptid' => $dept->level4_deptid,
                            //     'level4_key' => $dept->level4_key,
                            //     'level5' => null,
                            //     'level5_label' => null,
                            //     'level5_deptid' => null,
                            //     'level5_key' => null,
                            //     'org_path' => $org_path,
                            //     'date_deleted' => $dept->date_deleted,
                            //     'date_updated' => $dept->date_updated,
                            //     'exception' => $dept->exception,
                            //     'exception_reason' => $dept->exception_reason,
                            //     'unallocated' => $dept->unallocated,
                            //     'duplicate' => $dept->duplicate
                            // ];
                            // $audit = new JobDataAudit;
                            // $audit->job_sched_id = $audit_id;
                            // $audit->old_values = json_encode($old_values);
                            // $audit->new_values = json_encode($new_values);
                            // $audit->save();
                        }
                    } else {
                        // $old_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy'
                        // ];
                        OrganizationHierarchy::create([
                            'orgid' => $dept->orgid,
                            'hlevel' => $dept->hlevel,
                            'pkey' => $dept->pkey,
                            'okey' => $dept->okey,
                            'deptid' => $dept->deptid,
                            'name' => $dept->name,
                            'ulevel' => $dept->ulevel,
                            'organization' => $dept->organization,
                            'organization_label' => $dept->organization_label,
                            'organization_deptid' => $dept->organization_deptid,
                            'organization_key' => $dept->organization_key,
                            'level1' => $dept->level1,
                            'level1_label' => $dept->level1_label,
                            'level1_deptid' => $dept->level1_deptid,
                            'level1_key' => $dept->level1_key,
                            'level2' => $dept->level2,
                            'level2_label' => $dept->level2_label,
                            'level2_deptid' => $dept->level2_deptid,
                            'level2_key' => $dept->level2_key,
                            'level3' => $dept->level3,
                            'level3_label' => $dept->level3_label,
                            'level3_deptid' => $dept->level3_deptid,
                            'level3_key' => $dept->level3_key,
                            'level4' => $dept->level4,
                            'level4_label' => $dept->level4_label,
                            'level4_deptid' => $dept->level4_deptid,
                            'level4_key' => $dept->level4_key,
                            'level5' => null,
                            'level5_label' => null,
                            'level5_deptid' => null,
                            'level5_key' => null,
                            'org_path' => $org_path,
                            'date_deleted' => $dept->date_deleted,
                            'date_updated' => $dept->date_updated,
                            'exception' => $dept->exception,
                            'exception_reason' => $dept->exception_reason,
                            'unallocated' => $dept->unallocated,
                            'duplicate' => $dept->duplicate,
                            'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|{$dept->level4_key}|"
                        ]);
                        // $new_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy',                        
                        //     'orgid' => $dept->orgid, 
                        //     'hlevel' => $dept->hlevel,
                        //     'pkey' => $dept->pkey,
                        //     'okey' => $dept->okey,
                        //     'deptid' => $dept->deptid,
                        //     'name' => $dept->name,
                        //     'ulevel' => $dept->ulevel,
                        //     'organization' => $dept->organization,
                        //     'organization_label' => $dept->organization_label,
                        //     'organization_deptid' => $dept->organization_deptid,
                        //     'organization_key' => $dept->organization_key,
                        //     'level1' => $dept->level1,
                        //     'level1_label' => $dept->level1_label,
                        //     'level1_deptid' => $dept->level1_deptid,
                        //     'level1_key' => $dept->level1_key,
                        //     'level2' => $dept->level2,
                        //     'level2_label' => $dept->level2_label,
                        //     'level2_deptid' => $dept->level2_deptid,
                        //     'level2_key' => $dept->level2_key,
                        //     'level3' => $dept->level3,
                        //     'level3_label' => $dept->level3_label,
                        //     'level3_deptid' => $dept->level3_deptid,
                        //     'level3_key' => $dept->level3_key,
                        //     'level4' => $dept->level4,
                        //     'level4_label' => $dept->level4_label,
                        //     'level4_deptid' => $dept->level4_deptid,
                        //     'level4_key' => $dept->level4_key,
                        //     'level5' => null,
                        //     'level5_label' => null,
                        //     'level5_deptid' => null,
                        //     'level5_key' => null,
                        //     'org_path' => $org_path,
                        //     'date_deleted' => $dept->date_deleted,
                        //     'date_updated' => $dept->date_updated,
                        //     'exception' => $dept->exception,
                        //     'exception_reason' => $dept->exception_reason,
                        //     'unallocated' => $dept->unallocated,
                        //     'duplicate' => $dept->duplicate
                        // ];
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                    }
                };

                // Level 5
                $this->info(now().' Processing Level 5...');
                $org_level = 7;
                $actual_level = 5;
                $depts = OrganizationHierarchyStaging::whereRaw("HierarchyLevel = {$org_level}")
                ->orderBy("BusinessName")
                ->orderBy("OrgHierarchyKey")
                ->selectRaw("
                    OrgID AS orgid,
                    HierarchyLevel AS hlevel,
                    ParentOrgHierarchyKey AS pkey,
                    OrgHierarchyKey AS okey,
                    DepartmentID AS deptid,
                    BusinessName AS name,
                    0 AS ulevel,
                    null AS organization,
                    null AS organization_label,
                    null AS organization_deptid,
                    null AS organization_key,
                    null AS level1,
                    null AS level1_label,
                    null AS level1_deptid,
                    null AS level1_key,
                    null AS level2,
                    null AS level2_label,
                    null AS level2_deptid,
                    null AS level2_key,
                    null AS level3,
                    null AS level3_label,
                    null AS level3_deptid,
                    null AS level3_key,
                    null AS level4,
                    null AS level4_label,
                    null AS level4_deptid,
                    null AS level4_key,
                    null AS level5,
                    null AS level5_label,
                    null AS level5_deptid,
                    null AS level5_key,
                    null AS org_path,
                    date_deleted,
                    date_updated,
                    0 AS exception,
                    null AS exception_reason,
                    0 AS unallocated,
                    0 AS duplicate
                ")
                ->get();
                foreach($depts as $dept){
                    $dept_old = OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        level3,
                        level3_label,
                        level3_deptid,
                        level3_key,
                        level4,
                        level4_label,
                        level4_deptid,
                        level4_key,
                        level5,
                        level5_label,
                        level5_deptid,
                        level5_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    $parent = OrganizationHierarchy::whereRaw("okey = ".$dept->pkey)
                    ->selectRaw("
                        orgid,
                        hlevel,
                        pkey,
                        okey,
                        deptid,
                        name,
                        ulevel,
                        organization,
                        organization_label,
                        organization_deptid,
                        organization_key,
                        level1,
                        level1_label,
                        level1_deptid,
                        level1_key,
                        level2,
                        level2_label,
                        level2_deptid,
                        level2_key,
                        level3,
                        level3_label,
                        level3_deptid,
                        level3_key,
                        level4,
                        level4_label,
                        level4_deptid,
                        level4_key,
                        org_path,
                        date_deleted,
                        date_updated,
                        exception,
                        exception_reason,
                        unallocated,
                        duplicate
                    ")
                    ->first();
                    if ($parent) {
                        if ($parent->exception) {
                            $exception = 2;
                            $exception_reason = "Parent has exception";
                        } else {
                            if ($parent->date_deleted) {
                                $exception = 3;
                                $exception_reason = "Parent was deleted";
                            } else {
                                $exception = 0;
                                $exception_reason = NULL;
                            }
                        }
                        $organization = $parent->organization;
                        $organization_label = $parent->organization_label;
                        $organization_deptid = $parent->organization_deptid;
                        $organization_key = $parent->organization_key;
                        $level1 = $parent->level1;
                        $level1_label = $parent->level1_label;
                        $level1_deptid = $parent->level1_deptid;
                        $level1_key = $parent->level1_key;
                        $level2 = $parent->level2;
                        $level2_label = $parent->level2_label;
                        $level2_deptid = $parent->level2_deptid;
                        $level2_key = $parent->level2_key;
                        $level3 = $parent->level3;
                        $level3_label = $parent->level3_label;
                        $level3_deptid = $parent->level3_deptid;
                        $level3_key = $parent->level3_key;
                        $level4 = $parent->level4;
                        $level4_label = $parent->level4_label;
                        $level4_deptid = $parent->level4_deptid;
                        $level4_key = $parent->level4_key;
                    } else {
                        $exception = 1;
                        $exception_reason = "Parent Node missing";
                        $organization = NULL;
                        $organization_label = NULL;
                        $organization_deptid = NULL;
                        $organization_key = NULL;
                        $level1 = NULL;
                        $level1_label = NULL;
                        $level1_deptid = NULL;
                        $level1_key = NULL;
                        $level2 = NULL;
                        $level2_label = NULL;
                        $level2_deptid = NULL;
                        $level2_key = NULL;
                        $level3 = NULL;
                        $level3_label = NULL;
                        $level3_deptid = NULL;
                        $level3_key = NULL;
                        $level4 = NULL;
                        $level4_label = NULL;
                        $level4_deptid = NULL;
                        $level4_key = NULL;
                    }
                    $dept->exception = $exception;
                    $dept->exception_reason = $exception_reason;
                    $dept->organization = $organization;
                    $dept->organization_label = $organization_label;
                    $dept->organization_deptid = $organization_deptid;
                    $dept->organization_key = $organization_key;
                    $dept->level1 = $level1;
                    $dept->level1_label = $level1_label;
                    $dept->level1_deptid = $level1_deptid;
                    $dept->level1_key = $level1_key;
                    $dept->level2 = $level2;
                    $dept->level2_label = $level2_label;
                    $dept->level2_deptid = $level2_deptid;
                    $dept->level2_key = $level2_key;
                    $dept->level3 = $level3;
                    $dept->level3_label = $level3_label;
                    $dept->level3_deptid = $level3_deptid;
                    $dept->level3_key = $level3_key;
                    $dept->level4 = $level4;
                    $dept->level4_label = $level4_label;
                    $dept->level4_deptid = $level4_deptid;
                    $dept->level4_key = $level4_key;
                    if (str_contains(strtolower($dept->name), "unallocated") || str_contains(strtolower($dept->name), "inactive") || str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        // $this->AssignToBlankLevel($dept, $parent);
                        $dept->level5 = $dept->orgid;
                        $dept->level5_label = $dept->name;
                        $dept->level5_deptid = $dept->deptid;
                        $dept->level5_key = $dept->okey;
                        $dept->duplicate = $parent->duplicate ?? 0;
                        $dept->ulevel = 5;
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
                    if ($dept_old) {
                        if (trim($dept) != trim($dept_old)) {
                            // $old_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept_old->orgid, 
                            //     'hlevel' => $dept_old->hlevel, 
                            //     'pkey' => $dept_old->pkey,
                            //     'okey' => $dept_old->okey,
                            //     'deptid' => $dept_old->deptid,
                            //     'name' => $dept_old->name,
                            //     'ulevel' => $dept_old->ulevel,
                            //     'organization' => $dept_old->organization,
                            //     'organization_label' => $dept_old->organization_label,
                            //     'organization_deptid' => $dept_old->organization_deptid,
                            //     'organization_key' => $dept_old->organization_key,
                            //     'level1' => $dept_old->level1,
                            //     'level1_label' => $dept_old->level1_label,
                            //     'level1_deptid' => $dept_old->level1_deptid,
                            //     'level1_key' => $dept_old->level1_key,
                            //     'level2' => $dept_old->level2,
                            //     'level2_label' => $dept_old->level2_label,
                            //     'level2_deptid' => $dept_old->level2_deptid,
                            //     'level2_key' => $dept_old->level2_key,
                            //     'level3' => $dept_old->level3,
                            //     'level3_label' => $dept_old->level3_label,
                            //     'level3_deptid' => $dept_old->level3_deptid,
                            //     'level3_key' => $dept_old->level3_key,
                            //     'level4' => $dept_old->level4,
                            //     'level4_label' => $dept_old->level4_label,
                            //     'level4_deptid' => $dept_old->level4_deptid,
                            //     'level4_key' => $dept_old->level4_key,
                            //     'level5' => $dept_old->level5,
                            //     'level5_label' => $dept_old->level5_label,
                            //     'level5_deptid' => $dept_old->level5_deptid,
                            //     'level5_key' => $dept_old->level5_key,
                            //     'org_path' => $dept_old->org_path,
                            //     'date_deleted' => $dept_old->date_deleted,
                            //     'date_updated' => $dept_old->date_updated,
                            //     'exception' => $dept_old->exception,
                            //     'exception_reason' => $dept_old->exception_reason,
                            //     'unallocated' => $dept_old->unallocated,
                            //     'duplicate' => $dept_old->duplicate
                            // ];
                            OrganizationHierarchy::whereRaw("orgid = '".$dept->orgid."'")
                            ->update([
                                'hlevel' => $dept->hlevel,
                                'pkey' => $dept->pkey,
                                'okey' => $dept->okey,
                                'deptid' => $dept->deptid,
                                'name' => $dept->name,
                                'ulevel' => $dept->ulevel,
                                'organization' => $dept->organization,
                                'organization_label' => $dept->organization_label,
                                'organization_deptid' => $dept->organization_deptid,
                                'organization_key' => $dept->organization_key,
                                'level1' => $dept->level1,
                                'level1_label' => $dept->level1_label,
                                'level1_deptid' => $dept->level1_deptid,
                                'level1_key' => $dept->level1_key,
                                'level2' => $dept->level2,
                                'level2_label' => $dept->level2_label,
                                'level2_deptid' => $dept->level2_deptid,
                                'level2_key' => $dept->level2_key,
                                'level3' => $dept->level3,
                                'level3_label' => $dept->level3_label,
                                'level3_deptid' => $dept->level3_deptid,
                                'level3_key' => $dept->level3_key,
                                'level4' => $dept->level4,
                                'level4_label' => $dept->level4_label,
                                'level4_deptid' => $dept->level4_deptid,
                                'level4_key' => $dept->level4_key,
                                'level5' => $dept->level5,
                                'level5_label' => $dept->level5_label,
                                'level5_deptid' => $dept->level5_deptid,
                                'level5_key' => $dept->level5_key,
                                'org_path' => $org_path,
                                'date_deleted' => $dept->date_deleted,
                                'date_updated' => $dept->date_updated,
                                'exception' => $dept->exception,
                                'exception_reason' => $dept->exception_reason,
                                'unallocated' => $dept->unallocated,
                                'duplicate' => $dept->duplicate,
                                'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|{$dept->level4_key}|{$dept->level5_key}|"
                            ]);
                            // $new_values = [ 
                            //     'table' => 'ods_dept_org_hierarchy',                        
                            //     'orgid' => $dept->orgid, 
                            //     'hlevel' => $dept->hlevel,
                            //     'pkey' => $dept->pkey,
                            //     'okey' => $dept->okey,
                            //     'deptid' => $dept->deptid,
                            //     'name' => $dept->name,
                            //     'ulevel' => $dept->ulevel,
                            //     'organization' => $dept->organization,
                            //     'organization_label' => $dept->organization_label,
                            //     'organization_deptid' => $dept->organization_deptid,
                            //     'organization_key' => $dept->organization_key,
                            //     'level1' => $dept->level1,
                            //     'level1_label' => $dept->level1_label,
                            //     'level1_deptid' => $dept->level1_deptid,
                            //     'level1_key' => $dept->level1_key,
                            //     'level2' => $dept->level2,
                            //     'level2_label' => $dept->level2_label,
                            //     'level2_deptid' => $dept->level2_deptid,
                            //     'level2_key' => $dept->level2_key,
                            //     'level3' => $dept->level3,
                            //     'level3_label' => $dept->level3_label,
                            //     'level3_deptid' => $dept->level3_deptid,
                            //     'level3_key' => $dept->level3_key,
                            //     'level4' => $dept->level4,
                            //     'level4_label' => $dept->level4_label,
                            //     'level4_deptid' => $dept->level4_deptid,
                            //     'level4_key' => $dept->level4_key,
                            //     'level5' => $dept->level5,
                            //     'level5_label' => $dept->level5_label,
                            //     'level5_deptid' => $dept->level5_deptid,
                            //     'level5_key' => $dept->level5_key,
                            //     'org_path' => $org_path,
                            //     'date_deleted' => $dept->date_deleted,
                            //     'date_updated' => $dept->date_updated,
                            //     'exception' => $dept->exception,
                            //     'exception_reason' => $dept->exception_reason,
                            //     'unallocated' => $dept->unallocated,
                            //     'duplicate' => $dept->duplicate
                            // ];
                            // $audit = new JobDataAudit;
                            // $audit->job_sched_id = $audit_id;
                            // $audit->old_values = json_encode($old_values);
                            // $audit->new_values = json_encode($new_values);
                            // $audit->save();
                        }
                    } else {
                        // $old_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy'
                        // ];
                        OrganizationHierarchy::create([
                            'orgid' => $dept->orgid,
                            'hlevel' => $dept->hlevel,
                            'pkey' => $dept->pkey,
                            'okey' => $dept->okey,
                            'deptid' => $dept->deptid,
                            'name' => $dept->name,
                            'ulevel' => $dept->ulevel,
                            'organization' => $dept->organization,
                            'organization_label' => $dept->organization_label,
                            'organization_deptid' => $dept->organization_deptid,
                            'organization_key' => $dept->organization_key,
                            'level1' => $dept->level1,
                            'level1_label' => $dept->level1_label,
                            'level1_deptid' => $dept->level1_deptid,
                            'level1_key' => $dept->level1_key,
                            'level2' => $dept->level2,
                            'level2_label' => $dept->level2_label,
                            'level2_deptid' => $dept->level2_deptid,
                            'level2_key' => $dept->level2_key,
                            'level3' => $dept->level3,
                            'level3_label' => $dept->level3_label,
                            'level3_deptid' => $dept->level3_deptid,
                            'level3_key' => $dept->level3_key,
                            'level4' => $dept->level4,
                            'level4_label' => $dept->level4_label,
                            'level4_deptid' => $dept->level4_deptid,
                            'level4_key' => $dept->level4_key,
                            'level5' => $dept->level5,
                            'level5_label' => $dept->level5_label,
                            'level5_deptid' => $dept->level5_deptid,
                            'level5_key' => $dept->level5_key,
                            'org_path' => $org_path,
                            'date_deleted' => $dept->date_deleted,
                            'date_updated' => $dept->date_updated,
                            'exception' => $dept->exception,
                            'exception_reason' => $dept->exception_reason,
                            'unallocated' => $dept->unallocated,
                            'duplicate' => $dept->duplicate,
                            'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|{$dept->level4_key}|{$dept->level5_key}|"
                        ]);
                        // $new_values = [ 
                        //     'table' => 'ods_dept_org_hierarchy',                        
                        //     'orgid' => $dept->orgid, 
                        //     'hlevel' => $dept->hlevel,
                        //     'pkey' => $dept->pkey,
                        //     'okey' => $dept->okey,
                        //     'deptid' => $dept->deptid,
                        //     'name' => $dept->name,
                        //     'ulevel' => $dept->ulevel,
                        //     'organization' => $dept->organization,
                        //     'organization_label' => $dept->organization_label,
                        //     'organization_deptid' => $dept->organization_deptid,
                        //     'organization_key' => $dept->organization_key,
                        //     'level1' => $dept->level1,
                        //     'level1_label' => $dept->level1_label,
                        //     'level1_deptid' => $dept->level1_deptid,
                        //     'level1_key' => $dept->level1_key,
                        //     'level2' => $dept->level2,
                        //     'level2_label' => $dept->level2_label,
                        //     'level2_deptid' => $dept->level2_deptid,
                        //     'level2_key' => $dept->level2_key,
                        //     'level3' => $dept->level3,
                        //     'level3_label' => $dept->level3_label,
                        //     'level3_deptid' => $dept->level3_deptid,
                        //     'level3_key' => $dept->level3_key,
                        //     'level4' => $dept->level4,
                        //     'level4_label' => $dept->level4_label,
                        //     'level4_deptid' => $dept->level4_deptid,
                        //     'level4_key' => $dept->level4_key,
                        //     'level5' => $dept->level5,
                        //     'level5_label' => $dept->level5_label,
                        //     'level5_deptid' => $dept->level5_deptid,
                        //     'level5_key' => $dept->level5_key,
                        //     'org_path' => $org_path,
                        //     'date_deleted' => $dept->date_deleted,
                        //     'date_updated' => $dept->date_updated,
                        //     'exception' => $dept->exception,
                        //     'exception_reason' => $dept->exception_reason,
                        //     'unallocated' => $dept->unallocated,
                        //     'duplicate' => $dept->duplicate
                        // ];
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                    }
                };
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
                'details' => "Processed {$total} rows. Inserted {$count_insert} rows. Updated {$count_update} rows. Deleted {$count_delete} rows.",
                ]
            );

            $this->info( "Department Org Hierarchy pull from ODS, Completed: {$end_time}");
        } else {
            $this->info( "Process is currently disabled; or 'PRCS_PULL_ODS_DEPARTMENTS=on' is currently missing in the .env file.");
        }
    }

    private function AssignToBlankLevel (&$dept, $parent) {
        if ($dept->level1) {
            if ($dept->level1_label == $dept->name) {
                $dept->duplicate = 1;
                $dept->ulevel = 1;
            } else {
                if ($dept->level2) {
                    if ($dept->level2_label == $dept->name) {
                        $dept->duplicate = 1;
                        $dept->ulevel = 2;
                    } else {
                        if ($dept->level3) {
                            if ($dept->level3_label == $dept->name) {
                                $dept->duplicate = 1;
                                $dept->ulevel = 3;
                            } else {
                                if ($dept->level4) {
                                    if ($dept->level4_label == $dept->name) {
                                        $dept->duplicate = 1;
                                        $dept->ulevel = 4;
                                    } else {
                                        if ($dept->level5) {
                                            if ($dept->level5_label == $dept->name) {
                                                $dept->duplicate = 1;
                                                $dept->ulevel = 5;
                                            } else {
                                                $dept->exception = 4;
                                                $dept->exception_reason = 'Exceeded 5 levels';
                                                $dept->duplicate = $parent->duplicate ?? 0;
                                                $dept->ulevel = 6;
                                            }
                                        } else {
                                            $dept->level5 = $dept->orgid;
                                            $dept->level5_label = $dept->name;
                                            $dept->level5_deptid = $dept->deptid;
                                            $dept->level5_key = $dept->okey;
                                            $dept->duplicate = $parent->duplicate ?? 0;
                                            $dept->ulevel = 5;
                                        }
                                    }        
                                } else {
                                    $dept->level4 = $dept->orgid;
                                    $dept->level4_label = $dept->name;
                                    $dept->level4_deptid = $dept->deptid;
                                    $dept->level4_key = $dept->okey;
                                    $dept->duplicate = $parent->duplicate ?? 0;
                                    $dept->ulevel = 4;
                                }
                            }
                        } else {
                            $dept->level3 = $dept->orgid;
                            $dept->level3_label = $dept->name;
                            $dept->level3_deptid = $dept->deptid;
                            $dept->level3_key = $dept->okey;
                            $dept->duplicate = $parent->duplicate ?? 0;
                            $dept->ulevel = 3;
                        }
                    }
                } else {
                    $dept->level2 = $dept->orgid;
                    $dept->level2_label = $dept->name;
                    $dept->level2_deptid = $dept->deptid;
                    $dept->level2_key = $dept->okey;
                    $dept->duplicate = $parent->duplicate ?? 0;
                    $dept->ulevel = 2;
                }
            }
        } else {
            $dept->level1 = $dept->orgid;
            $dept->level1_label = $dept->name;
            $dept->level1_deptid = $dept->deptid;
            $dept->level1_key = $dept->okey;
            $dept->duplicate = 0;
            $dept->ulevel = 1;
        }
    }
}
