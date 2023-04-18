<?php

namespace App\Http\Controllers\HRAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SharedProfile;
use App\Models\HRUserDemoJrView;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            // $query = HRUserDemoJrView::from('hr_user_demo_jr_view AS u')
            $query = HRUserDemoJrView::from('user_demo_jr_view AS u')
                // ->where('auth_id', \DB::raw($authId))
                ->whereNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text && $request->criteria != 'all', function($q) use($request) { return $q->whereRaw("u.{$request->criteria} like '%{$request->search_text}%'"); })
                ->when($request->search_text && $request->criteria == 'all', function($q) use($request) { return $q->whereRaw("(u.employee_id LIKE '%{$request->search_text}%' OR u.employee_name LIKE '%{$request->search_text}%' OR u.jobcode_desc LIKE '%{$request->search_text}%' OR u.deptid LIKE '%{$request->search_text}%')"); })
                ->selectRaw ("
                    u.user_id,
                    guid,
                    excused_flag,
                    employee_id,
                    employee_name, 
                    jobcode_desc,
                    u.orgid AS orgid,
                    u.organization AS organization,
                    u.level1_program AS level1_program,
                    u.level2_division AS level2_division,
                    u.level3_branch AS level3_branch,
                    u.level4 AS level4,
                    u.deptid AS deptid,
                    employee_status,
                    due_date_paused,
                    next_conversation_date,
                    excusedtype,
                    '' AS nextConversationDue,
                    '' AS shared,
                    '' AS reportees,
                    '' AS activeGoals
                ");
            return Datatables::of($query)->addIndexColumn()
                ->editColumn('activeGoals', function($row) {
                    return (User::where('id', $row->user_id)->first()->activeGoals()->count() ?? '0').' Goals';
                })
                ->editColumn('nextConversationDue', function ($row) {
                    if ($row->excused_flag) {
                        return 'Paused';
                    } 
                    if ($row->due_date_paused != 'Y') {
                        $text = Carbon::parse($row->next_conversation_date)->format('M d, Y');
                        return $text;
                    } else {
                        return 'Paused';
                    }
                    return '';
                })
                ->editColumn('shared', function ($row) {
                    return SharedProfile::where('shared_id', $row->user_id)->count() > 0 ? "Yes" : "No";
                })
                ->editColumn('reportees', function($row) {
                    return User::where('id', $row->user_id)->first()->reporteesCount() ?? '0';
                })
                ->make(true);
        }
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'employee_id' => 'Employee ID', 
            'employee_name'=> 'Employee Name',
            'jobcode_desc' => 'Classification', 
            'deptid' => 'Department ID'
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
