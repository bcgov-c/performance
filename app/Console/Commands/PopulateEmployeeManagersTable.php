<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JobSchedAudit;

class PopulateEmployeeManagersTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PopulateEmployeeManagersTable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build employee manager details in employee_managers table';

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
        $this->info( 'Populate Employee Managers Table, Started:   '. $start_time);
  
        $job_name = 'command:PopulateEmployeeManagersTable';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );

        \DB::beginTransaction();
        try {
            \DB::statement("
                DELETE 
                FROM employee_managers 
            ");

            \DB::statement("
                INSERT INTO employee_managers (employee_id, position_number, orgid, supervisor_emplid, supervisor_name, supervisor_name2, supervisor_position_number, supervisor_email, supervisor_userid, priority, source)
                SELECT emv_ed3.employee_id, 
                    emv_ed3.position_number, 
                    emv_ed3.orgid, 
                    emv_sed3.employee_id supervisor_emplid, 
                    emv_sed3.employee_name supervisor_name, 
                    emv_u3.name supervisor_name2, 
                    emv_p3.reports_to supervisor_position_number, 
                    emv_sed3.employee_email, 
                    emv_u3.id supervisor_userid,
                    1 priority, 
                    'Posn' source 
                FROM employee_demo emv_ed3, 
                    positions emv_p3, 
                    employee_demo emv_sed3 USE INDEX (idx_employee_demo_position_number_employee_id),
                    users emv_u3
                WHERE emv_ed3.position_number = emv_p3.position_nbr 
                    AND emv_p3.reports_to = emv_sed3.position_number
                    AND emv_sed3.employee_id IS NOT NULL 
                    AND emv_sed3.employee_id <> '' 
                    AND emv_ed3.date_deleted IS NULL
                    AND emv_sed3.date_deleted IS NULL
                    AND emv_sed3.employee_id = emv_u3.employee_id
            ");
            
            \DB::statement("
                INSERT INTO employee_managers (employee_id, position_number, orgid, supervisor_emplid, supervisor_name, supervisor_name2, supervisor_position_number, supervisor_email, supervisor_userid, priority, source)
                SELECT emv_ed4.employee_id, 
                    emv_ed4.position_number, 
                    emv_ed4.orgid, 
                    emv_sed4.employee_id supervisor_emplid, 
                    emv_sed4.employee_name supervisor_name, 
                    emv_u4.name supervisor_name2, 
                    emv_sp4.reports_to supervisor_position_number, 
                    emv_sed4.employee_email, 
                    emv_u4.id supervisor_userid,
                    2 priority, 
                    'Posn Next' source 
                FROM employee_demo emv_ed4, 
                    positions emv_p4, 
                    positions emv_sp4, 
                    employee_demo emv_sed4 USE INDEX (idx_employee_demo_position_number_employee_id),
                    users emv_u4
                WHERE emv_ed4.position_number = emv_p4.position_nbr 
                    AND emv_p4.reports_to = emv_sp4.position_nbr 
                    AND emv_sp4.reports_to = emv_sed4.position_number
                    AND emv_sed4.employee_id IS NOT NULL 
                    AND emv_ed4.date_deleted IS NULL
                    AND emv_sed4.date_deleted IS NULL
                    AND emv_sed4.employee_id = emv_u4.employee_id
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM employee_demo emv_ed3, 
                            positions emv_p3, 
                            employee_demo emv_sed3 USE INDEX (idx_employee_demo_position_number_employee_id),
                            users emv_u3
                        WHERE emv_ed3.position_number = emv_p3.position_nbr 
                            AND emv_p3.reports_to = emv_sed3.position_number
                            AND emv_sed3.employee_id IS NOT NULL 
                            AND emv_sed3.employee_id <> '' 
                            AND emv_ed3.date_deleted IS NULL
                            AND emv_sed3.date_deleted IS NULL
                            AND emv_sed3.employee_id = emv_u3.employee_id
                            AND emv_ed3.employee_id = emv_ed4.employee_id
                    )
            ");
            
            \DB::statement("
                INSERT INTO employee_managers (employee_id, position_number, orgid, supervisor_emplid, supervisor_name, supervisor_name2, supervisor_position_number, supervisor_email, supervisor_userid, priority, source)
                SELECT emv_ed1.employee_id, 
                    emv_ed1.position_number, 
                    emv_ed1.orgid, 
                    emv_ed1.supervisor_emplid, 
                    emv_ed1.supervisor_name, 
                    emv_u1.name supervisor_name2, 
                    emv_ed1.supervisor_position_number, 
                    emv_ed1.supervisor_email, 
                    emv_u1.id supervisor_userid,
                    3 priority, 
                    'ODS' source
                FROM employee_demo emv_ed1, 
                    employee_demo emv_sed1 USE INDEX (idx_employee_demo_employeeid_record),
                    users emv_u1
                WHERE emv_ed1.supervisor_emplid = emv_sed1.employee_id
                    AND emv_ed1.supervisor_emplid IS NOT NULL 
                    AND emv_ed1.supervisor_emplid <> ''
                    AND emv_sed1.date_deleted IS NULL
                    AND emv_ed1.date_deleted IS NULL 
                    AND emv_sed1.employee_id = emv_u1.employee_id
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM employee_demo emv_ed3, 
                            positions emv_p3, 
                            employee_demo emv_sed3 USE INDEX (idx_employee_demo_position_number_employee_id),
                            users emv_u3
                        WHERE emv_ed3.position_number = emv_p3.position_nbr 
                            AND emv_p3.reports_to = emv_sed3.position_number
                            AND emv_sed3.employee_id IS NOT NULL 
                            AND emv_sed3.employee_id <> '' 
                            AND emv_ed3.date_deleted IS NULL
                            AND emv_sed3.date_deleted IS NULL
                            AND emv_sed3.employee_id = emv_u3.employee_id
                            AND emv_ed3.employee_id = emv_ed1.employee_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM employee_demo emv_ed4, 
                            positions emv_p4, 
                            positions emv_sp4, 
                            employee_demo emv_sed4 USE INDEX (idx_employee_demo_position_number_employee_id),
                            users emv_u4
                        WHERE emv_ed4.position_number = emv_p4.position_nbr 
                            AND emv_p4.reports_to = emv_sp4.position_nbr 
                            AND emv_sp4.reports_to = emv_sed4.position_number
                            AND emv_sed4.employee_id IS NOT NULL 
                            AND emv_ed4.date_deleted IS NULL
                            AND emv_sed4.date_deleted IS NULL
                            AND emv_sed4.employee_id = emv_u4.employee_id
                            AND emv_ed4.employee_id = emv_ed1.employee_id
                    )
            ");
            
            \DB::statement("
                INSERT INTO employee_managers (employee_id, position_number, orgid, supervisor_emplid, supervisor_name, supervisor_name2, supervisor_position_number, supervisor_email, supervisor_userid, priority, source)
                SELECT emv_ed2.employee_id, 
                    emv_ed2.position_number, 
                    emv_ed2.orgid, 
                    emv_sed2.employee_id supervisor_emplid, 
                    emv_sed2.employee_name supervisor_name, 
                    emv_u2.name supervisor_name2, 
                    emv_ed2.supervisor_position_number, 
                    emv_sed2.employee_email, 
                    emv_u2.id supervisor_userid,
                    4 priority, 
                    'ODS Next' source
                FROM employee_demo emv_ed2, 
                    employee_demo emv_sed2 USE INDEX (idx_employee_demo_position_number_employee_id),
                    users emv_u2
                WHERE emv_ed2.supervisor_position_number = emv_sed2.position_number
                    AND emv_ed2.date_deleted IS NULL
                    AND emv_sed2.date_deleted IS NULL
                    AND emv_sed2.employee_id = emv_u2.employee_id
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM employee_demo emv_ed3, 
                            positions emv_p3, 
                            employee_demo emv_sed3 USE INDEX (idx_employee_demo_position_number_employee_id),
                            users emv_u3
                        WHERE emv_ed3.position_number = emv_p3.position_nbr 
                            AND emv_p3.reports_to = emv_sed3.position_number
                            AND emv_sed3.employee_id IS NOT NULL 
                            AND emv_sed3.employee_id <> '' 
                            AND emv_ed3.date_deleted IS NULL
                            AND emv_sed3.date_deleted IS NULL
                            AND emv_sed3.employee_id = emv_u3.employee_id
                            AND emv_ed3.employee_id = emv_ed2.employee_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM employee_demo emv_ed4, 
                            positions emv_p4, 
                            positions emv_sp4, 
                            employee_demo emv_sed4 USE INDEX (idx_employee_demo_position_number_employee_id),
                            users emv_u4
                        WHERE emv_ed4.position_number = emv_p4.position_nbr 
                            AND emv_p4.reports_to = emv_sp4.position_nbr 
                            AND emv_sp4.reports_to = emv_sed4.position_number
                            AND emv_sed4.employee_id IS NOT NULL 
                            AND emv_ed4.date_deleted IS NULL
                            AND emv_sed4.date_deleted IS NULL
                            AND emv_sed4.employee_id = emv_u4.employee_id
                            AND emv_ed4.employee_id = emv_ed2.employee_id
                    )
                    AND NOT EXISTS (
                        SELECT 1
                        FROM employee_demo emv_ed1, 
                            employee_demo emv_sed1 USE INDEX (idx_employee_demo_employeeid_record),
                            users emv_u1
                        WHERE emv_ed1.supervisor_emplid = emv_sed1.employee_id
                            AND emv_ed1.supervisor_emplid IS NOT NULL 
                            AND emv_ed1.supervisor_emplid <> ''
                            AND emv_sed1.date_deleted IS NULL
                            AND emv_ed1.date_deleted IS NULL 
                            AND emv_sed1.employee_id = emv_u1.employee_id
                            AND emv_ed1.employee_id = emv_ed2.employee_id
                    )   
            ");
            
            \DB::commit();
        } catch (Exception $e) {
            echo 'Unable to populate employee_managers table.'; echo "\r\n";
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
                'details' => 'Unable to populate employee_managers table.',
              ]
            );
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
          ]
        );
  
        $this->info( 'Populate Employee Managers Table, Completed: ' . $end_time);
  
    }
}
