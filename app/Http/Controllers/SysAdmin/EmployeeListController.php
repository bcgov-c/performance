<?php

namespace App\Http\Controllers\SysAdmin;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Goal;
use App\Models\SharedProfile;
use App\Models\UserDemoJrView;
use App\Models\Position;
use App\Models\EmployeeDemo;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class EmployeeListController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function currentList(Request $request) {
        $user = auth()->user();
        $userid = $user->id;
            
        $user_role = DB::table('model_has_roles')                        
            ->where('model_id', $userid)
            ->whereIntegerInRaw('role_id', [4, 5])
            ->where('model_type', 'App\Models\User')
            ->get();
            
        if(count($user_role) == 0) {
            return redirect()->to('/');
            exit;
        }


        $errors = session('errors');
        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->criteria = isset($old['criteria']) ? $old['criteria'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
        } 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
            ]);
        }
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $criteriaList = $this->search_criteria_list();
        return view('shared.employeelists.currentlist', compact ('request', 'criteriaList'));
    }

    public function pastList(Request $request) {
        $user = auth()->user();
        $userid = $user->id;
            
        $user_role = DB::table('model_has_roles')                        
            ->where('model_id', $userid)
            ->whereIntegerInRaw('role_id', [4, 5])
            ->where('model_type', 'App\Models\User')
            ->get();
            
        if(count($user_role) == 0) {
            return redirect()->to('/');
            exit;
        }

        $errors = session('errors');
        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->criteria = isset($old['criteria']) ? $old['criteria'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
        } 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
            ]);
        }
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $criteriaList = $this->search_criteria_list();
        return view('shared.employeelists.pastlist', compact ('request', 'criteriaList'));
    }

    public function getCurrentList(Request $request) {
        $user = auth()->user();
        $userid = $user->id;
            
        $user_role = DB::table('model_has_roles')                        
            ->where('model_id', $userid)
            ->whereIntegerInRaw('role_id', [4, 5])
            ->where('model_type', 'App\Models\User')
            ->get();
            
        if(count($user_role) == 0) {
            return redirect()->to('/');
            exit;
        }

        if ($request->ajax()) {
            $query = UserDemoJrView::from('user_demo_jr_view AS u')
                ->whereNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text && $request->criteria == 'u.employee_name', function($q) use ($request) { return $q->whereRaw("(u.employee_name LIKE '%{$request->search_text}%' OR u.user_name LIKE '%{$request->search_text}%')"); })
                ->when($request->search_text && $request->criteria != 'u.employee_name', function($q) use ($request) { return $q->whereRaw("{$request->criteria} LIKE '%{$request->search_text}%'"); })
                ->selectRaw ("
                    u.user_id AS id,
                    u.guid,
                    u.user_name,
                    u.excused_flag,
                    u.employee_id,
                    u.employee_name, 
                    u.employee_email, 
                    u.position_number,
                    u.empl_record,
                    u.jobcode_desc,
                    u.organization,
                    u.level1_program,
                    u.level2_division,
                    u.level3_branch,
                    u.level4,
                    u.deptid,
                    u.date_deleted,
                    u.employee_status,
                    u.supervisor_name,
                    u.supervisor_position_number,
                    u.reporting_to_employee_id,
                    u.reporting_to_name,
                    u.reporting_to_email,
                    u.reporting_to_position_number,
                    u.due_date_paused,
                    u.next_conversation_date,
                    u.excusedtype AS excused,
                    CASE WHEN (u.excused_flag != 0 OR u.due_date_paused = 'Y') THEN 'Paused' ELSE u.next_conversation_date END AS nextConversationDue,
                    CASE WHEN (SELECT 1 FROM shared_profiles AS sp WHERE sp.shared_id = u.user_id LIMIT 1) THEN 'Yes' ELSE 'No' END AS shared,
                    CONCAT (u.reportees, ' / ', (SELECT COUNT(1) FROM shared_profiles AS sp USE INDEX (SHARED_PROFILES_SHARED_WITH_FOREIGN), users AS u2, employee_demo AS dmo USE INDEX (IDX_EMPLOYEE_DEMO_EMPLOYEEID_RECORD) WHERE sp.shared_with IS NOT NULL AND sp.shared_with = u.user_id AND sp.shared_id = u2.id AND u2.employee_id = dmo.employee_id AND u2.empl_record = dmo.empl_record AND dmo.date_deleted IS NULL)) AS reportees,
                    (SELECT COUNT(1) FROM goals as g USE INDEX (GOALS_USER_ID_INDEX) WHERE g.user_id = u.user_id AND g.status = 'active' AND g.is_library = 0 AND g.deleted_at IS NULL) AS activeGoals
                ");
            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('reportees', function($row) {
                    $text = $row->reportees;
                    return view('shared.employeelists.partials.link', compact(["row", 'text']));
                })
                ->rawColumns(['reportees'])
                ->make(true);
        }
    }

    public function reporteesList(Request $request, $id, $posn) {
        $user = UserDemoJrView::whereRaw("user_id = {$id}")
            ->where("position_number", $posn)
            ->first();
        if ($request->ajax()) {
            $reportees = EmployeeDemo::join(\DB::raw('users_annex AS d_ua USE INDEX (users_annex_employee_id_record_index)'), function($join){
                    return $join->on(function($on){
                        return $on->whereRaw("employee_demo.employee_id = d_ua.employee_id")
                            ->whereRaw("employee_demo.empl_record = d_ua.empl_record")
                            ->whereNull('employee_demo.date_deleted');
                    });
                })
                ->join(\DB::raw('employee_managers AS d_um USE INDEX (idx_employee_managers_supervisor_emplid_employee_id)'), function($join) use($user, $posn) {
                    return $join->on(function($on) use($user, $posn) {
                        return $on->whereRaw("d_um.supervisor_emplid = '{$user->employee_id}'")
                            ->whereRaw("d_um.employee_id = employee_demo.employee_id")
                            ->whereRaw("d_um.supervisor_position_number = '{$posn}'");
                    });
                })
                ->where('d_ua.reporting_to_employee_id', $user->employee_id)
                ->where('d_ua.reporting_to_position_number', $posn)
                ->selectRaw("
                    employee_demo.employee_id AS employee_id, 
                    employee_demo.employee_name AS employee_name, 
                    employee_demo.employee_email AS employee_email, 
                    CASE WHEN d_um.source IN ('Posn', 'ODS') THEN 'Direct' WHEN d_um.source IN ('Posn Next', 'ODS Next') THEN 'Delegated' END AS reporteetype
                ");
            $shared = \DB::table('shared_profiles AS sp')
                ->whereRaw("sp.shared_with = ".$user->user_id)
                ->join(\DB::raw('users AS u USE INDEX (IDX_USERS_ID)'), 'sp.shared_id', 'u.id')
                ->join(\DB::raw('employee_demo AS dmo USE INDEX (IDX_EMPLOYEE_DEMO_EMPLOYEEID_RECORD)'), function ($join) {
                    $join->on('u.employee_id', 'dmo.employee_id');
                    $join->on('u.empl_record', 'dmo.empl_record');
                })
                ->whereNull('dmo.date_deleted')
                ->whereRaw('dmo.pdp_excluded = 0')
                ->selectRaw("
                    dmo.employee_id AS employee_id, 
                    dmo.employee_name AS employee_name, 
                    dmo.employee_email AS employee_email, 
                    'Shared' AS reporteetype
                ");
            $query = $reportees->union($shared);
            return Datatables::of($query)
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function exportCurrent(Request $request, $paramJSON = null) {
        $user = auth()->user();
        $userid = $user->id;
            
        $user_role = DB::table('model_has_roles')                        
            ->where('model_id', $userid)
            ->whereIntegerInRaw('role_id', [4, 5])
            ->where('model_type', 'App\Models\User')
            ->get();
            
        if(count($user_role) == 0) {
            return redirect()->to('/');
            exit;
        }

        $param = json_decode($paramJSON);
        $dd_level0 = $param[0];
        $dd_level1 = $param[1];
        $dd_level2 = $param[2];
        $dd_level3 = $param[3];
        $dd_level4 = $param[4];
        $criteria = $param[5];
        $search_text = $param[6];
        $query = UserDemoJrView::from('user_demo_jr_view AS u')
            ->whereNull('u.date_deleted')
            ->when($dd_level0, function($q) use($dd_level0) { return $q->where('u.organization_key', $dd_level0); })
            ->when($dd_level1, function($q) use($dd_level1) { return $q->where('u.level1_key', $dd_level1); })
            ->when($dd_level2, function($q) use($dd_level2) { return $q->where('u.level2_key', $dd_level2); })
            ->when($dd_level3, function($q) use($dd_level3) { return $q->where('u.level3_key', $dd_level3); })
            ->when($dd_level4, function($q) use($dd_level4) { return $q->where('u.level4_key', $dd_level4); })
            ->when($search_text && $criteria == 'u.employee_name', function($q) use ($search_text) { return $q->whereRaw("(u.employee_name LIKE '%{$search_text}%' OR u.user_name LIKE '%{$search_text}%')"); })
            ->when($search_text && $criteria != 'u.employee_name', function($q) use ($criteria, $search_text) { return $q->whereRaw("{$criteria} LIKE '%{$search_text}%'"); })
            ->selectRaw ("
                u.user_id AS id,
                u.guid,
                u.user_name,
                u.excused_flag,
                u.employee_id,
                u.employee_name, 
                u.employee_email, 
                u.position_number,
                u.empl_record,
                u.jobcode_desc,
                u.organization,
                u.level1_program,
                u.level2_division,
                u.level3_branch,
                u.level4,
                u.deptid,
                u.date_deleted,
                u.employee_status,
                u.supervisor_name,
                u.supervisor_position_number,
                u.reporting_to_employee_id,
                u.reporting_to_name,
                u.reporting_to_email,
                u.reporting_to_position_number,
                u.due_date_paused,
                u.next_conversation_date,
                u.excusedtype AS excused,
                CASE WHEN (u.excused_flag != 0 OR u.due_date_paused = 'Y') THEN 'Paused' ELSE u.next_conversation_date END AS nextConversationDue,
                CASE WHEN (SELECT 1 FROM shared_profiles AS sp WHERE sp.shared_id = u.user_id LIMIT 1) THEN 'Yes' ELSE 'No' END AS shared,
                CONCAT (u.reportees, ' / ', (SELECT COUNT(1) FROM shared_profiles AS sp USE INDEX (SHARED_PROFILES_SHARED_WITH_FOREIGN), users AS u2, employee_demo AS dmo USE INDEX (IDX_EMPLOYEE_DEMO_EMPLOYEEID_RECORD) WHERE sp.shared_with IS NOT NULL AND sp.shared_with = u.user_id AND sp.shared_id = u2.id AND u2.employee_id = dmo.employee_id AND u2.empl_record = dmo.empl_record AND dmo.date_deleted IS NULL)) AS reportees,
                (SELECT COUNT(1) FROM goals as g USE INDEX (GOALS_USER_ID_INDEX) WHERE g.user_id = u.user_id AND g.status = 'active' AND g.is_library = 0 AND g.deleted_at IS NULL) AS activeGoals
            ");
        $records = $query->get();
        // Generating Output file
        $filename = 'Current_Employees_'. Carbon::now()->format('YmdHis') . '.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = [
            'Employee ID',  
            'Name', 
            'Email', 
            'Position #',  
            'Reports To Name', 
            'Reports To Position #',  
            'Status', 
            'Record #', 
            'Classification', 
            'Organization', 
            'Level 1',
            'Level 2',
            'Level 3',
            'Level 4',
            'Dept ID',
            'Active Goals',
            'Next Conversation',
            'Excused',
            'Shared',
            'Reports',
        ];
        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($records as $rec) {
                $row['Employee ID'] = $rec->employee_id;
                $row['Name'] = $rec->employee_name;
                $row['Email'] = $rec->employee_email;
                $row['Position #'] = $rec->position_number;
                $row['Reports To Name'] = $rec->reporting_to_name;
                $row['Reports To Position #'] = $rec->reporting_to_position_number;
                $row['Status'] = $rec->employee_status;
                $row['Record #'] = $rec->empl_record;
                $row['Classification'] = $rec->jobcode_desc;
                $row['Organization'] = $rec->organization;
                $row['Level 1'] = $rec->level1_program;
                $row['Level 2'] = $rec->level2_division;
                $row['Level 3'] = $rec->level3_branch;
                $row['Level 4'] = $rec->level4;
                $row['Dept ID'] = $rec->deptid;
                $row['Active Goals'] = $rec->activeGoals;
                $row['Next Conversation'] = $rec->nextConversationDue;
                $row['Excused'] = $rec->excused;
                $row['Shared'] = $rec->shared;
                $row['Reports'] = $rec->reportees;
                fputcsv($file, array(
                    $row['Employee ID'], 
                    $row['Name'], 
                    $row['Email'], 
                    $row['Position #'],  
                    $row['Reports To Name'], 
                    $row['Reports To Position #'],
                    $row['Status'], 
                    $row['Record #'], 
                    $row['Classification'], 
                    $row['Organization'], 
                    $row['Level 1'], 
                    $row['Level 2'], 
                    $row['Level 3'], 
                    $row['Level 4'], 
                    $row['Dept ID'], 
                    $row['Active Goals'], 
                    $row['Next Conversation'], 
                    $row['Excused'], 
                    $row['Shared'], 
                    $row['Reports'] 
                ));
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function getPastList(Request $request) {
        $user = auth()->user();
        $userid = $user->id;
            
        $user_role = DB::table('model_has_roles')                        
            ->where('model_id', $userid)
            ->whereIntegerInRaw('role_id', [4, 5])
            ->where('model_type', 'App\Models\User')
            ->get();
            
        if(count($user_role) == 0) {
            return redirect()->to('/');
            exit;
        }

        if ($request->ajax()) {
            $query = UserDemoJrView::from('user_demo_jr_view AS u')
                ->whereNotNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text && $request->criteria == 'u.employee_name', function($q) use ($request) { return $q->whereRaw("(u.employee_name LIKE '%{$request->search_text}%' OR u.user_name LIKE '%{$request->search_text}%')"); })
                ->when($request->search_text && $request->criteria != 'u.employee_name', function($q) use ($request) { return $q->whereRaw("{$request->criteria} LIKE '%{$request->search_text}%'"); })
                ->selectRaw ("
                    u.user_id AS id,
                    u.guid,
                    u.user_name,
                    u.excused_flag,
                    u.employee_id,
                    u.employee_name, 
                    u.employee_email, 
                    u.position_number,
                    u.empl_record,
                    u.jobcode_desc,
                    u.organization,
                    u.level1_program,
                    u.level2_division,
                    u.level3_branch,
                    u.level4,
                    u.deptid,
                    u.date_deleted AS u_date_deleted,
                    u.employee_status,
                    u.supervisor_name,
                    u.supervisor_position_number,
                    u.reporting_to_employee_id,
                    u.reporting_to_name,
                    u.reporting_to_email,
                    u.reporting_to_position_number,
                    u.due_date_paused,
                    u.next_conversation_date,
                    u.excusedtype AS excused,
                    CASE WHEN (u.excused_flag != 0 OR u.due_date_paused = 'Y') THEN 'Paused' ELSE u.next_conversation_date END AS nextConversationDue,
                    CASE WHEN (SELECT 1 FROM shared_profiles AS sp WHERE sp.shared_id = u.user_id LIMIT 1) THEN 'Yes' ELSE 'No' END AS shared,
                    u.reportees,
                    (SELECT COUNT(1) FROM goals as g USE INDEX (GOALS_USER_ID_INDEX) WHERE g.user_id = u.user_id AND g.status = 'active' AND g.is_library = 0 AND g.deleted_at IS NULL) AS activeGoals,
                    CASE WHEN u.date_deleted IS NOT NULL THEN u.date_deleted ELSE '' END AS date_deleted
                ");
            return Datatables::of($query)
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function exportPast(Request $request,  $paramJSON = null) {
        $user = auth()->user();
        $userid = $user->id;
            
        $user_role = DB::table('model_has_roles')                        
            ->where('model_id', $userid)
            ->whereIntegerInRaw('role_id', [4, 5])
            ->where('model_type', 'App\Models\User')
            ->get();
            
        if(count($user_role) == 0) {
            return redirect()->to('/');
            exit;
        }

        $param = json_decode($paramJSON);
        $dd_level0 = $param[0];
        $dd_level1 = $param[1];
        $dd_level2 = $param[2];
        $dd_level3 = $param[3];
        $dd_level4 = $param[4];
        $criteria = $param[5];
        $search_text = $param[6];
        $query = UserDemoJrView::from('user_demo_jr_view AS u')
            ->whereNotNull('u.date_deleted')
            ->when($dd_level0, function($q) use($dd_level0) { return $q->where('u.organization_key', $dd_level0); })
            ->when($dd_level1, function($q) use($dd_level1) { return $q->where('u.level1_key', $dd_level1); })
            ->when($dd_level2, function($q) use($dd_level2) { return $q->where('u.level2_key', $dd_level2); })
            ->when($dd_level3, function($q) use($dd_level3) { return $q->where('u.level3_key', $dd_level3); })
            ->when($dd_level4, function($q) use($dd_level4) { return $q->where('u.level4_key', $dd_level4); })
            ->when($search_text && $criteria == 'u.employee_name', function($q) use ($search_text) { return $q->whereRaw("(u.employee_name LIKE '%{$search_text}%' OR u.user_name LIKE '%{$search_text}%')"); })
            ->when($search_text && $criteria != 'u.employee_name', function($q) use ($criteria, $search_text) { return $q->whereRaw("{$criteria} LIKE '%{$search_text}%'"); })
            ->selectRaw ("
                u.user_id AS id,
                u.guid,
                u.user_name,
                u.excused_flag,
                u.employee_id,
                u.employee_name, 
                u.employee_email, 
                u.position_number,
                u.empl_record,
                u.jobcode_desc,
                u.organization,
                u.level1_program,
                u.level2_division,
                u.level3_branch,
                u.level4,
                u.deptid,
                u.date_deleted AS u_date_deleted,
                u.employee_status,
                u.supervisor_name,
                u.supervisor_position_number,
                u.reporting_to_employee_id,
                u.reporting_to_name,
                u.reporting_to_email,
                u.reporting_to_position_number,
                u.due_date_paused,
                u.next_conversation_date,
                u.excusedtype AS excused,
                CASE WHEN (u.excused_flag != 0 OR u.due_date_paused = 'Y') THEN 'Paused' ELSE u.next_conversation_date END AS nextConversationDue,
                CASE WHEN (SELECT 1 FROM shared_profiles AS sp WHERE sp.shared_id = u.user_id LIMIT 1) THEN 'Yes' ELSE 'No' END AS shared,
                u.reportees,
                (SELECT COUNT(1) FROM goals as g USE INDEX (GOALS_USER_ID_INDEX) WHERE g.user_id = u.user_id AND g.status = 'active' AND g.is_library = 0 AND g.deleted_at IS NULL) AS activeGoals,
                CASE WHEN u.date_deleted IS NOT NULL THEN u.date_deleted ELSE '' END AS date_deleted
            ");
        $records = $query->get();
        // Generating Output file
        $filename = 'Past_Employees_'. Carbon::now()->format('YmdHis') . '.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = [
            'Employee ID',  
            'Name', 
            'Email', 
            'Position #',  
            'Reports To Name', 
            'Reports To Position #',  
            'Status', 
            'Record #', 
            'Classification', 
            'Organization', 
            'Level 1',
            'Level 2',
            'Level 3',
            'Level 4',
            'Dept ID',
            'Active Goals',
            'Next Conversation',
            'Excused',
            'Shared',
            'Reports',
            'Date Deleted',
        ];
        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($records as $rec) {
                $row['Employee ID'] = $rec->employee_id;
                $row['Name'] = $rec->employee_name;
                $row['Email'] = $rec->employee_email;
                $row['Position #'] = $rec->position_number;
                $row['Reports To Name'] = $rec->reporting_to_name;
                $row['Reports To Position #'] = $rec->reporting_to_position_number;
                $row['Status'] = $rec->employee_status;
                $row['Record #'] = $rec->empl_record;
                $row['Classification'] = $rec->jobcode_desc;
                $row['Organization'] = $rec->organization;
                $row['Level 1'] = $rec->level1_program;
                $row['Level 2'] = $rec->level2_division;
                $row['Level 3'] = $rec->level3_branch;
                $row['Level 4'] = $rec->level4;
                $row['Dept ID'] = $rec->deptid;
                $row['Active Goals'] = $rec->activeGoals;
                $row['Next Conversation'] = $rec->nextConversationDue;
                $row['Excused'] = $rec->excused;
                $row['Shared'] = $rec->shared;
                $row['Direct Reports'] = $rec->reportees;
                $row['Date Deleted'] = $rec->date_deleted;
                fputcsv($file, array(
                    $row['Employee ID'], 
                    $row['Name'], 
                    $row['Email'], 
                    $row['Position #'],  
                    $row['Reports To Name'], 
                    $row['Reports To Position #'],
                    $row['Status'], 
                    $row['Record #'], 
                    $row['Classification'], 
                    $row['Organization'], 
                    $row['Level 1'], 
                    $row['Level 2'], 
                    $row['Level 3'], 
                    $row['Level 4'], 
                    $row['Dept ID'], 
                    $row['Active Goals'], 
                    $row['Next Conversation'], 
                    $row['Excused'], 
                    $row['Shared'], 
                    $row['Direct Reports'], 
                    $row['Date Deleted'] 
                ));
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    protected function search_criteria_list() {
        return [
            'u.employee_name'=> 'Name',
            'u.employee_id' => 'Employee ID', 
            'u.employee_email' => 'Email', 
            'u.position_number' => 'Position #',
            'u.reporting_to_name' => 'Reports To Name',
            'u.reporting_to_position_number' => 'Reports to Position #',
            'u.jobcode_desc' => 'Classification',
            'u.deptid' => 'Dept ID'
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
