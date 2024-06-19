<?php

namespace App\Classes;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PDPDataClass {

    public function UserDemoAnnexSQL_Base (Request $request) {
        return User::join(\DB::raw('employee_demo USE INDEX (EMPLOYEE_DEMO_EMPLOYEE_ID_EMPL_RECORD_UNIQUE)'), 'users.employee_id', 'employee_demo.employee_id')
        ->where('employee_demo.pdp_excluded', \DB::raw(0))
        ->leftjoin(\DB::raw('users_annex USE INDEX (USERS_ANNEX_EMPLOYEE_ID_RECORD_INDEX)'), function($join1) {
            return $join1->on(function($on1) {
                return $on1->whereRaw('(employee_demo.employee_id = users_annex.employee_id AND employee_demo.empl_record = users_annex.empl_record)');
            });
        })->clone();
    }

    public function UserDemoAnnexSQL (Request $request) {
        return $this->UserDemoAnnexSQL_Base($request)
        ->selectRaw("
            users.id AS user_id,
            users.name AS user_name,
            users.employee_id,
            users.guid,
            users.excused_flag,
            users.excused_reason_id,
            users.excused_updated_by,
            users.excused_updated_at,
            users.joining_date,
            users.acctlock,
            users.reporting_to,
            employee_demo.empl_record,
            employee_demo.employee_name,
            employee_demo.employee_email,
            employee_demo.jobcode,
            employee_demo.jobcode_desc,
            employee_demo.job_indicator,
            employee_demo.orgid,
            users_annex.level,
            users_annex.organization,
            users_annex.level1_program,
            users_annex.level2_division,
            users_annex.level3_branch,
            users_annex.level4,
            users_annex.level5,
            users_annex.level6,
            users_annex.organization_key,
            users_annex.level1_key,
            users_annex.level2_key,
            users_annex.level3_key,
            users_annex.level4_key,
            users_annex.level5_key,
            users_annex.level6_key,
            users_annex.organization_deptid,
            users_annex.level1_deptid,
            users_annex.level2_deptid,
            users_annex.level3_deptid,
            users_annex.level4_deptid,
            users_annex.level5_deptid,
            users_annex.level6_deptid,
            employee_demo.deptid,
            employee_demo.employee_status,
            employee_demo.position_number,
            employee_demo.manager_id,
            employee_demo.supervisor_position_number,
            employee_demo.supervisor_emplid,
            employee_demo.supervisor_name,
            employee_demo.supervisor_email,
            users_annex.reporting_to_employee_id,
            users_annex.reporting_to_name,
            users_annex.reporting_to_email,
            users_annex.reporting_to_position_number,
            employee_demo.date_updated,
            employee_demo.date_deleted,
            users_annex.jr_id,
            users_annex.jr_due_date_paused AS due_date_paused,
            users_annex.jr_next_conversation_date AS next_conversation_date,
            users_annex.jr_excused_type AS excused_type,
            users_annex.jr_current_manual_excuse AS current_manual_excuse,
            users_annex.jr_created_by_id AS created_by_id,
            users_annex.jr_created_at AS created_at,
            users_annex.jr_updated_by_id AS updated_by_id,
            users_annex.jr_updated_at AS updated_at,
            users_annex.jr_excused_reason_id AS edj_excused_reason_id,
            users_annex.jr_excused_reason_desc AS edj_excused_reason_desc,
            users_annex.jr_updated_by_name AS updated_by_name,
            users_annex.excused_updated_by_name,
            users_annex.r_name,
            users_annex.reason_id,
            users_annex.reason_name,
            CASE when users_annex.jr_excused_type = 'A' THEN 'Auto' ELSE CASE when users.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
            CASE when users_annex.jr_excused_type = 'A' THEN 'Auto' ELSE CASE when users.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
            users_annex.excused_by_name,
            users_annex.created_at_string,
            users.employee_id AS employee_id_search,
            employee_demo.employee_name AS employee_name_search,
            users_annex.reportees
        ")->clone();
    }

    public function HRUserDemoAnnexSQL_Base (Request $request) {
        $authId = Auth::id();
        return User::join(\DB::raw('employee_demo USE INDEX (EMPLOYEE_DEMO_EMPLOYEE_ID_EMPL_RECORD_UNIQUE)'), 'users.employee_id', 'employee_demo.employee_id')
        ->join('auth_users', 'users.id', 'auth_users.user_id')
        ->where('auth_users.auth_id', \DB::raw($authId))
        ->whereNull('employee_demo.date_deleted')
        ->where('employee_demo.pdp_excluded', \DB::raw(0))
        ->join(\DB::raw('users_annex USE INDEX (USERS_ANNEX_EMPLOYEE_ID_RECORD_INDEX)'), function($join1) {
            return $join1->on(function($on1) {
                return $on1->whereRaw('(employee_demo.employee_id = users_annex.employee_id AND employee_demo.empl_record = users_annex.empl_record)');
            });
        })
        ->join('auth_orgs', function($join2) {
            return $join2->on(function($on2) {
                return $on2->whereRaw("(auth_orgs.type = 'HR' AND auth_orgs.auth_id = auth_users.auth_id AND auth_orgs.orgid = employee_demo.orgid)");
            });
        })->clone();
    }

    public function HRUserDemoAnnexSQL (Request $request) {
        return $this->HRUserDemoAnnexSQL_Base($request)
        ->selectRaw("
            auth_users.auth_id,
            users.id AS user_id,
            users.name AS user_name,
            users.employee_id,
            users.guid,
            users.excused_flag,
            users.excused_reason_id,
            users.excused_updated_by,
            users.excused_updated_at,
            users.joining_date,
            users.acctlock,
            users.reporting_to,
            employee_demo.empl_record,
            employee_demo.employee_name,
            employee_demo.employee_email,
            employee_demo.jobcode,
            employee_demo.jobcode_desc,
            employee_demo.job_indicator,
            employee_demo.orgid,
            users_annex.level,
            users_annex.organization,
            users_annex.level1_program,
            users_annex.level2_division,
            users_annex.level3_branch,
            users_annex.level4,
            users_annex.level5,
            users_annex.level6,
            users_annex.organization_key,
            users_annex.level1_key,
            users_annex.level2_key,
            users_annex.level3_key,
            users_annex.level4_key,
            users_annex.level5_key,
            users_annex.level6_key,
            users_annex.organization_deptid,
            users_annex.level1_deptid,
            users_annex.level2_deptid,
            users_annex.level3_deptid,
            users_annex.level4_deptid,
            users_annex.level5_deptid,
            users_annex.level6_deptid,
            employee_demo.deptid,
            employee_demo.employee_status,
            employee_demo.position_number,
            employee_demo.manager_id,
            employee_demo.supervisor_position_number,
            employee_demo.supervisor_emplid,
            employee_demo.supervisor_name,
            employee_demo.supervisor_email,
            users_annex.reporting_to_employee_id,
            users_annex.reporting_to_name,
            users_annex.reporting_to_email,
            users_annex.reporting_to_position_number,
            employee_demo.date_updated,
            employee_demo.date_deleted,
            users_annex.jr_id,
            users_annex.jr_due_date_paused AS due_date_paused,
            users_annex.jr_next_conversation_date AS next_conversation_date,
            users_annex.jr_excused_type AS excused_type,
            users_annex.jr_current_manual_excuse AS current_manual_excuse,
            users_annex.jr_created_by_id AS created_by_id,
            users_annex.jr_created_at AS created_at,
            users_annex.jr_updated_by_id AS updated_by_id,
            users_annex.jr_updated_at AS updated_at,
            users_annex.jr_excused_reason_id AS edj_excused_reason_id,
            users_annex.jr_excused_reason_desc AS edj_excused_reason_desc,
            users_annex.jr_updated_by_name AS updated_by_name,
            users_annex.excused_updated_by_name,
            users_annex.r_name,
            users_annex.reason_id,
            users_annex.reason_name,
            CASE when users_annex.jr_excused_type = 'A' THEN 'Auto' ELSE CASE when users.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
            CASE when users_annex.jr_excused_type = 'A' THEN 'Auto' ELSE CASE when users.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
            users_annex.excused_by_name,
            users_annex.created_at_string,
            users.employee_id AS employee_id_search,
            employee_demo.employee_name AS employee_name_search,
            users_annex.reportees
        ")->clone();
    }


}