<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AdminOrg;
use App\Models\AdminOrgUser;
use App\Models\EmployeeDemo;
use App\Models\JobSchedAudit;
use App\Models\SharedProfile;
use Illuminate\Console\Command;

class BuildAdminOrgUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:buildAdminOrgUsers { --manual }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build assigned users based on admin_orgs and shared_profiles tables';

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



        $switch = strtolower(env('PRCS_BUILD_ADMIN_ORG_USERS'));
        $manualoverride = (strtolower($this->option('manual')) ? true : false);
  
        if ($switch == 'on' || $manualoverride) {

            $start_time = Carbon::now();
            $audit_id = JobSchedAudit::insertGetId(
                [
                    'job_name' => $this->signature,
                    'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                    'status' => 'Initiated'
                ]
            );

            $this->info( now() );
            $this->info('Build Assigned Admin Org Users');

            $granted_to_ids = AdminOrg::distinct('user_id')->orderBy('user_id')->pluck('user_id');

            $this->info( 'Step 1: Truncate Table' );
            AdminOrgUser::truncate();
            
            $this->info( now() );

            // Step 2: Insert record into the table based on admin_orgs
            $this->info( 'Step 2: Insert record into the table based on admin_orgs' );
            AdminOrgUser::insertUsing([
                    'granted_to_id', 'allowed_user_id',
                    'admin_org_id'
                ], User::withoutGlobalScopes()->select('admin_orgs.user_id', 'users.id',
                            'admin_orgs.id')
                        ->join('employee_demo', 'users.guid', '=', 'employee_demo.guid')
                        ->join('admin_orgs', function ($j1) {
                            $j1->on(function ($j1a) {
                                $j1a->whereRAW('(admin_orgs.organization = employee_demo.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (employee_demo.organization = "" OR employee_demo.organization IS NULL)))');
                            } )
                            ->on(function ($j2a) {
                                $j2a->whereRAW('(admin_orgs.level1_program = employee_demo.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (employee_demo.level1_program = "" OR employee_demo.level1_program IS NULL)))');
                            } )
                            ->on(function ($j3a) {
                                $j3a->whereRAW('(admin_orgs.level2_division = employee_demo.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (employee_demo.level2_division = "" OR employee_demo.level2_division IS NULL)))');
                            } )
                            ->on(function ($j4a) {
                                $j4a->whereRAW('(admin_orgs.level3_branch = employee_demo.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (employee_demo.level3_branch = "" OR employee_demo.level3_branch IS NULL)))');
                            } )
                            ->on(function ($j5a) {
                                $j5a->whereRAW('(admin_orgs.level4 = employee_demo.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (employee_demo.level4 = "" OR employee_demo.level4 IS NULL)))');
                            } );
                        })
                );


            $this->info( now() );

            //                 
            $this->info( 'Step 3: Insert record into the table based on shared_profile' );
            AdminOrgUser::insertUsing([
                    'granted_to_id', 'allowed_user_id', 'access_type',
                    'shared_profile_id'
                ],
                    SharedProfile::selectRaw('shared_with, shared_id,
                        case when (shared_item = \'["1"]\') then \'1\' 
                                when (shared_item = \'["2"]\') then \'2\' 	 
                                when (shared_item = \'["1","2"]\') then \'0\' 
                                else null end, id')
                );
                    
            
            $this->info( now() );

            $end_time = Carbon::now();
            JobSchedAudit::updateOrInsert(
                [
                    'id' => $audit_id
                ],
                [
                    'job_name' => $this->signature,
                    'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                    'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
                    'status' => 'Completed'
                ]
            );

        } else {
            $start_time = Carbon::now()->format('c');
            $audit_id = JobSchedAudit::insertGetId(
                [
                    'job_name' => 'command:buildAdminOrgUsers',
                    'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                    'status' => 'Disabled',
                    'detail' => 'Process is currently disabled; or "PRCS_BUILD_ADMIN_ORG_USERS=on" is currently missing in the .env file.',
                ]
            );
            $this->info( 'Process is currently disabled; or "PRCS_BUILD_ADMIN_ORG_USERS=on" is currently missing in the .env file.');
        }

        return 0;
    }
}
