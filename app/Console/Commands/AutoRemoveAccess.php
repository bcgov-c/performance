<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JobSchedAudit;
use App\Models\ModelHasRoleAudit;

class AutoRemoveAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AutoRemoveAccess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove access for Service Representatives when change in Dept ID or Position Nbr is detected.';

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
        $this->info( 'Auto Remove Access, Started:   '. $start_time);

        $switch = strtolower(env('PRCS_AUTO_REMOVE_ACCESS'));
  
        $job_name = 'command:AutoRemoveAccess';
        $status = (($switch == 'on') ? 'Initiated' : 'Disabled');
        $audit_id = JobSchedAudit::insertGetId(
            [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
            ]
        );

        $count_success = 0;
        $count_failed = 0;

        if ($switch == 'on') {

            $candidates = \DB::table('model_has_roles AS mhr')
                ->selectRaw('mhr.model_id, mhr.role_id')
                ->whereRaw('
                    mhr.role_id = 5
                    AND NOT (EXISTS (SELECT 1 FROM model_has_role_audits AS mhra, users AS u, employee_demo AS ed WHERE mhra.model_id = mhr.model_id AND mhra.role_id = mhr.role_id AND mhra.deleted_at IS NULL AND mhra.model_id = u.id AND u.employee_id = ed.employee_id AND ed.date_deleted IS NULL AND ed.deptid = mhra.deptid)
                    AND EXISTS (SELECT 1 FROM model_has_role_audits AS mhra, users AS u, employee_demo AS ed WHERE mhra.model_id = mhr.model_id AND mhra.role_id = mhr.role_id AND mhra.deleted_at IS NULL AND mhra.model_id = u.id AND u.employee_id = ed.employee_id AND ed.date_deleted IS NULL AND ed.position_number = mhra.position_number))
                ')
                ->get();
    
            foreach($candidates as $oneid){
                \DB::beginTransaction();
                try {
                    ModelHasRoleAudit::updateOrCreate([
                        'model_id' => $oneid->model_id,
                        'role_id' => $oneid->role_id,
                    ], [
                        'deleted_at' => Carbon::now(),
                        'deleted_by' => 'AutoRemoveAccess',
                    ]);
                    \DB::table('model_has_roles')->whereRaw("model_id = {$oneid->model_id} AND role_id = {$oneid->role_id}")->delete();
                    DB::commit();
                    echo 'Removed access for '.$oneid->model_id.'.'; echo "\r\n";
                    $count_success += 1;
                } catch (Exception $e) {
                    echo 'Unable to remove access for user '.$oneid->model_id.'.'; echo "\r\n";
                    \DB::rollback();
                    $count_failed += 1;
                }
            }

        } else {
            $this->info( 'Process is currently disabled; or "PRCS_AUTO_REMOVE_ACCESS=on" is currently missing in the config.');
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
            'details' => "Deleted {$count_success} rows.  Failed {$count_failed} rows."
          ]
        );
  
        $this->info( 'Auto Remove Access, Completed: ' . $end_time);
  
    }
}
