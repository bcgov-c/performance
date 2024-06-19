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
                OrganizationHierarchy::truncate();

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
                    null AS level6,
                    null AS level6_label,
                    null AS level6_deptid,
                    null AS level6_key,
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
                        'level6' => null,
                        'level6_label' => null,
                        'level6_deptid' => null,
                        'level6_key' => null,
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
                    null AS level6,
                    null AS level6_label,
                    null AS level6_deptid,
                    null AS level6_key,
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
                    if (str_contains(strtolower($dept->name), "unallocated") or str_contains(strtolower($dept->name), "inactive") or str_contains(strtolower($dept->name), "inactivate")) {
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
                        $this->AssignToBlankLevel($dept, $parent);
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
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
                        'level6' => null,
                        'level6_label' => null,
                        'level6_deptid' => null,
                        'level6_key' => null,
                        'org_path' => $org_path,
                        'date_deleted' => $dept->date_deleted,
                        'date_updated' => $dept->date_updated,
                        'exception' => $dept->exception,
                        'exception_reason' => $dept->exception_reason,
                        'unallocated' => $dept->unallocated,
                        'duplicate' => $dept->duplicate,
                        'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|"
                    ]);
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
                    null AS level6,
                    null AS level6_label,
                    null AS level6_deptid,
                    null AS level6_key,
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
                    if (str_contains(strtolower($dept->name), "unallocated") or str_contains(strtolower($dept->name), "inactive") or str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        $this->AssignToBlankLevel($dept, $parent);
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
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
                        'level6' => null,
                        'level6_label' => null,
                        'level6_deptid' => null,
                        'level6_key' => null,
                        'org_path' => $org_path,
                        'date_deleted' => $dept->date_deleted,
                        'date_updated' => $dept->date_updated,
                        'exception' => $dept->exception,
                        'exception_reason' => $dept->exception_reason,
                        'unallocated' => $dept->unallocated,
                        'duplicate' => $dept->duplicate,
                        'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|"
                    ]);
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
                    null AS level6,
                    null AS level6_label,
                    null AS level6_deptid,
                    null AS level6_key,
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
                    if (str_contains(strtolower($dept->name), "unallocated") or str_contains(strtolower($dept->name), "inactive") or str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        $this->AssignToBlankLevel($dept, $parent);
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
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
                        'level6' => null,
                        'level6_label' => null,
                        'level6_deptid' => null,
                        'level6_key' => null,
                        'org_path' => $org_path,
                        'date_deleted' => $dept->date_deleted,
                        'date_updated' => $dept->date_updated,
                        'exception' => $dept->exception,
                        'exception_reason' => $dept->exception_reason,
                        'unallocated' => $dept->unallocated,
                        'duplicate' => $dept->duplicate,
                        'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|"
                    ]);
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
                    null AS level6,
                    null AS level6_label,
                    null AS level6_deptid,
                    null AS level6_key,
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
                    if (str_contains(strtolower($dept->name), "unallocated") or str_contains(strtolower($dept->name), "inactive") or str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        $this->AssignToBlankLevel($dept, $parent);
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
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
                        'level6' => null,
                        'level6_label' => null,
                        'level6_deptid' => null,
                        'level6_key' => null,
                        'org_path' => $org_path,
                        'date_deleted' => $dept->date_deleted,
                        'date_updated' => $dept->date_updated,
                        'exception' => $dept->exception,
                        'exception_reason' => $dept->exception_reason,
                        'unallocated' => $dept->unallocated,
                        'duplicate' => $dept->duplicate,
                        'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|{$dept->level4_key}|"
                    ]);
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
                    null AS level6,
                    null AS level6_label,
                    null AS level6_deptid,
                    null AS level6_key,
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
                    if (str_contains(strtolower($dept->name), "unallocated") or str_contains(strtolower($dept->name), "inactive") or str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        $this->AssignToBlankLevel($dept, $parent);
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
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
                        'level6' => null,
                        'level6_label' => null,
                        'level6_deptid' => null,
                        'level6_key' => null,
                        'org_path' => $org_path,
                        'date_deleted' => $dept->date_deleted,
                        'date_updated' => $dept->date_updated,
                        'exception' => $dept->exception,
                        'exception_reason' => $dept->exception_reason,
                        'unallocated' => $dept->unallocated,
                        'duplicate' => $dept->duplicate,
                        'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|{$dept->level4_key}|{$dept->level5_key}|"
                    ]);
                };

                // Level 6
                $this->info(now().' Processing Level 6...');
                $org_level = 8;
                $actual_level = 6;
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
                    null AS level6,
                    null AS level6_label,
                    null AS level6_deptid,
                    null AS level6_key,
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
                        $level5 = $parent->level5;
                        $level5_label = $parent->level5_label;
                        $level5_deptid = $parent->level5_deptid;
                        $level5_key = $parent->level5_key;
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
                        $level5 = NULL;
                        $level5_label = NULL;
                        $level5_deptid = NULL;
                        $level5_key = NULL;
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
                    $dept->level5 = $level5;
                    $dept->level5_label = $level5_label;
                    $dept->level5_deptid = $level5_deptid;
                    $dept->level5_key = $level5_key;
                    if (str_contains(strtolower($dept->name), "unallocated") or str_contains(strtolower($dept->name), "inactive") or str_contains(strtolower($dept->name), "inactivate")) {
                        $dept->unallocated = 1;
                        if ($parent) {
                            $org_path = $parent->org_path;
                        } else {
                            $org_path = null;
                        }
                    } else {
                        $dept->unallocated = 0;
                        $this->AssignToBlankLevel($dept, $parent);
                        if ($parent) {
                            $org_path = $parent->org_path." > ".$dept->name;
                        } else {
                            $org_path = $dept->name;
                        }
                    }
                    $dept->org_path = $org_path;
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
                        'level6' => $dept->level6,
                        'level6_label' => $dept->level6_label,
                        'level6_deptid' => $dept->level6_deptid,
                        'level6_key' => $dept->level6_key,
                        'org_path' => $org_path,
                        'date_deleted' => $dept->date_deleted,
                        'date_updated' => $dept->date_updated,
                        'exception' => $dept->exception,
                        'exception_reason' => $dept->exception_reason,
                        'unallocated' => $dept->unallocated,
                        'duplicate' => $dept->duplicate,
                        'search_key' => "|{$dept->organization_key}|{$dept->level1_key}|{$dept->level2_key}|{$dept->level3_key}|{$dept->level4_key}|{$dept->level5_key}|{$dept->level6_key}|"
                    ]);
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
                'details' => "Processed {$total} rows.",
                ]
            );

            $this->info( "Department Org Hierarchy pull from ODS, Completed: {$end_time}");
        } else {
            $this->info( "Process is currently disabled; or 'PRCS_PULL_ODS_DEPARTMENTS=on' is currently missing in the .env file.");
        }
    }

    private function AssignToBlankLevel (&$dept, $parent) {
        if ($dept->level1) {
            if ($dept->level2) {
                if ($dept->level3) {
                    if ($dept->level4) {
                        if ($dept->level5) {
                            if ($dept->level6) {
                                $dept->exception = 4;
                                $dept->exception_reason = 'Exceeded 6 levels';
                                $dept->ulevel = 7;
                            } else {
                                $dept->level6 = $dept->orgid;
                                $dept->level6_label = $dept->name;
                                $dept->level6_deptid = $dept->deptid;
                                $dept->level6_key = $dept->okey;
                                $dept->ulevel = 6;
                            }
                        } else {
                            $dept->level5 = $dept->orgid;
                            $dept->level5_label = $dept->name;
                            $dept->level5_deptid = $dept->deptid;
                            $dept->level5_key = $dept->okey;
                            $dept->ulevel = 5;
                        }
                    } else {
                        $dept->level4 = $dept->orgid;
                        $dept->level4_label = $dept->name;
                        $dept->level4_deptid = $dept->deptid;
                        $dept->level4_key = $dept->okey;
                        $dept->ulevel = 4;
                    }
                } else {
                    $dept->level3 = $dept->orgid;
                    $dept->level3_label = $dept->name;
                    $dept->level3_deptid = $dept->deptid;
                    $dept->level3_key = $dept->okey;
                    $dept->ulevel = 3;
                }
            } else {
                $dept->level2 = $dept->orgid;
                $dept->level2_label = $dept->name;
                $dept->level2_deptid = $dept->deptid;
                $dept->level2_key = $dept->okey;
                $dept->ulevel = 2;
            }
        } else {
            $dept->level1 = $dept->orgid;
            $dept->level1_label = $dept->name;
            $dept->level1_deptid = $dept->deptid;
            $dept->level1_key = $dept->okey;
            $dept->ulevel = 1;
        }
    }
}
