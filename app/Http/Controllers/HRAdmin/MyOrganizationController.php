<?php

namespace App\Http\Controllers\HRAdmin;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SharedProfile;
use App\Models\HRUserDemoJrView;
use App\Models\Position;
use App\Models\UserDemoJrView;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class MyOrganizationController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
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
        return view('hradmin.myorg.myorganization', compact ('request', 'criteriaList'));
    }

    public function getList(Request $request) {
        if ($request->ajax()) {
            $authId = Auth::id();
            $query = HRUserDemoJrView::from('hr_user_demo_jr_view AS u')
                ->where('auth_id', \DB::raw($authId))
                ->whereNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text && $request->criteria == 'u.employee_name', function($q) use ($request) { return $q->whereRaw("(u.employee_name LIKE '%{$request->search_text}%' OR u.user_name LIKE '%{$request->search_text}%')"); })
                ->when($request->search_text && $request->criteria != 'u.employee_name', function($q) use ($request) { return $q->whereRaw("{$request->criteria} LIKE '%{$request->search_text}%'"); })
                ->selectRaw ("
                    user_id,
                    u.user_name,
                    guid,
                    excused_flag,
                    employee_id,
                    u.employee_name, 
                    u.employee_email,
                    u.position_number,
                    u.empl_record,
                    u.reporting_to_employee_id,
                    u.reporting_to_name,
                    u.reporting_to_email,
                    u.reporting_to_position_number,
                    u.supervisor_position_number,
                    jobcode_desc,
                    u.orgid AS orgid,
                    u.organization AS organization,
                    u.level1_program AS level1_program,
                    u.level2_division AS level2_division,
                    u.level3_branch AS level3_branch,
                    u.level4 AS level4,
                    u.deptid AS deptid,
                    u.employee_status,
                    due_date_paused,
                    u.next_conversation_date,
                    excusedtype,
                    CASE WHEN (u.excused_flag != 0 OR u.due_date_paused = 'Y') THEN 'Paused' ELSE u.next_conversation_date END AS nextConversationDue,
                    CASE WHEN (SELECT 1 FROM shared_profiles AS sp WHERE sp.shared_id = u.user_id LIMIT 1) THEN 'Yes' ELSE 'No' END AS shared,
                    CONCAT (u.reportees, ' / ', (SELECT COUNT(1) FROM shared_profiles AS sp USE INDEX (SHARED_PROFILES_SHARED_WITH_FOREIGN), user_demo_jr_view AS u2 WHERE sp.shared_with IS NOT NULL AND sp.shared_with = u.user_id AND sp.shared_id = u2.user_id AND u2.date_deleted IS NULL)) AS reportees,
                    (SELECT COUNT(1) FROM goals as g USE INDEX (GOALS_USER_ID_INDEX) WHERE g.user_id = u.user_id AND g.status = 'active' AND g.is_library = 0 AND g.deleted_at IS NULL) AS activeGoals
                ");
            return Datatables::of($query)->addIndexColumn()
                ->addIndexColumn()
                ->editColumn('reportees', function($row) {
                    $text = $row->reportees;
                    return view('hradmin.myorg.partials.link', compact(["row", 'text']));
                })
                ->rawColumns(['reportees'])
                ->make(true);
        }
    }

    public function reporteesList(Request $request, $id) {
        $user = UserDemoJrView::where('user_id', $id)->first();
        if ($request->ajax()) {
            $direct = Position::from('positions AS posn')
                ->join(\DB::raw('employee_demo AS dmo USE INDEX (IDX_EMPLOYEE_DEMO_POSITION_NUMBER_EMPLOYEE_ID)'), 'posn.position_nbr', 'dmo.position_number')
                ->join(\DB::raw('users AS u USE INDEX (IDX_USERS_EMPLOYEEID_EMPLRECORD)'), function ($join) {
                    $join->on('dmo.employee_id', 'u.employee_id');
                    $join->on('dmo.empl_record', 'u.empl_record');
                })
                ->whereNull('dmo.date_deleted')
                ->where('posn.reports_to', $user->position_number)
                ->selectRaw("
                    dmo.employee_id AS employee_id, 
                    dmo.employee_name AS employee_name, 
                    dmo.employee_email AS employee_email, 
                    'Direct' AS reporteetype
                ");
            $elevated = Position::from('positions AS sspn')
                ->join(\DB::raw('positions AS spn USE INDEX (POSITIONS_REPORTS_TO_POSITION_NBR_INDEX)'), 'sspn.position_nbr', 'spn.reports_to')
                ->join(\DB::raw('employee_demo AS dmo USE INDEX (IDX_EMPLOYEE_DEMO_POSITION_NUMBER_EMPLOYEE_ID)'), 'spn.position_nbr', 'dmo.position_number')
                ->join(\DB::raw('users AS uu USE INDEX (IDX_USERS_EMPLOYEEID_EMPLRECORD)'), function ($join) {
                    $join->on('dmo.employee_id', 'uu.employee_id');
                    $join->on('dmo.empl_record', 'uu.empl_record');
                })
                ->whereNull('dmo.date_deleted')
                ->where('sspn.reports_to', $user->position_number)
                ->whereRaw("NOT EXISTS (SELECT 1 FROM employee_demo AS non USE INDEX (IDX_EMPLOYEE_DEMO_POSITION_NUMBER_EMPLOYEE_ID) WHERE non.position_number = sspn.position_nbr AND non.date_deleted IS NULL LIMIT 1)")
                ->selectRaw("
                    dmo.employee_id AS employee_id, 
                    dmo.employee_name AS employee_name, 
                    dmo.employee_email AS employee_email, 
                    'Delegated' AS reporteetype
                ");
            $shared = \DB::table('shared_profiles AS sp')
                ->whereRaw("sp.shared_with = ".$user->user_id)
                ->join(\DB::raw('users AS u USE INDEX (IDX_USERS_ID)'), 'sp.shared_id', 'u.id')
                ->join(\DB::raw('employee_demo AS dmo USE INDEX (IDX_EMPLOYEE_DEMO_EMPLOYEEID_RECORD)'), function ($join) {
                    $join->on('u.employee_id', 'dmo.employee_id');
                    $join->on('u.empl_record', 'dmo.empl_record');
                })
                ->whereNull('dmo.date_deleted')
                ->selectRaw("
                    dmo.employee_id AS employee_id, 
                    dmo.employee_name AS employee_name, 
                    dmo.employee_email AS employee_email, 
                    'Shared' AS reporteetype
                ");
            $query = $direct->union($elevated);
            $query = $query->union($shared);
            return Datatables::of($query)
                ->addIndexColumn()
                ->make(true);
        }
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
