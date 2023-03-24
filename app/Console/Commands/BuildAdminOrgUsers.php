<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AdminOrg;
use App\Models\AdminOrgUser;
use App\Models\EmployeeDemo;
use App\Models\JobSchedAudit;
use App\Models\SharedProfile;
use App\Models\UserDemoJrView;
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

            $this->info( 'Step 1: Truncate Table' );
            AdminOrgUser::truncate();
            
            $this->info( now() );

            // Step 2: Insert record into the table based on admin_orgs
            $this->info( 'Step 2: Insert record into the table based on admin_orgs' );
            AdminOrgUser::insertUsing([
                    'granted_to_id', 'allowed_user_id',
                    'admin_org_id'
                ],
                    UserDemoJrView::join('admin_orgs', 'admin_orgs.orgid', 'user_demo_jr_view.orgid')
                    ->where('admin_orgs.version', 2)
                    ->whereNull('user_demo_jr_view.date_deleted')
                    ->select('admin_orgs.user_id', 'user_demo_jr_view.user_id', 'admin_orgs.orgid')
                    ->distinct()
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
