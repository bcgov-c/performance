<?php

namespace App\Http\Controllers\HRAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conversation;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;
use App\Models\ExcusedClassification;
use App\Models\OrganizationTree;
use App\Models\SharedProfile;
use App\Models\UserDemoJrView;
use App\Models\Goal;
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




class MyOrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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

        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;

        $request->session()->flash('level0', $level0);
        $request->session()->flash('level1', $level1);
        $request->session()->flash('level2', $level2);
        $request->session()->flash('level3', $level3);
        $request->session()->flash('level4', $level4);

        $criteriaList = $this->search_criteria_list();

        return view('hradmin.myorg.myorganization', compact ('request', 'criteriaList'));
    }

    public function getList(Request $request)
    {
        if ($request->ajax()) 
        {
            $authId = Auth::id();
            $level0 = $request->dd_level0 ? OrganizationTree::where('organization_trees.id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('organization_trees.id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('organization_trees.id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('organization_trees.id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('organization_trees.id', $request->dd_level4)->first() : null;
            $query = UserDemoJrView::from('user_demo_jr_view as u')
            ->whereIn('u.user_id', function ($org) use ($authId) {
                $org->select('o.user_id')
                ->from('auth_users as o')
                ->whereRaw("o.type = 'HR' AND o.auth_id = ".$authId);
            })
            ->when($level0, function($q) use($level0) {$q->where('u.organization', $level0->name);})
            ->when($level1, function($q) use($level1) {$q->where('u.level1_program', $level1->name);})
            ->when($level2, function($q) use($level2) {$q->where('u.level2_division', $level2->name);})
            ->when($level3, function($q) use($level3) {$q->where('u.level3_branch', $level3->name);})
            ->when($level4, function($q) use($level4) {$q->where('u.level4', $level4->name);})
            ->when($request->criteria == 'id' && $request->search_text, function($q) use($request){return $q->whereRaw("u.employee_id like '%".$request->search_text."%'");})
            ->when($request->criteria == 'name' && $request->search_text, function($q) use($request){return $q->whereRaw("u.employee_name like '%".$request->search_text."%'");})
            ->when($request->criteria == 'job' && $request->search_text, function($q) use($request){return $q->whereRaw("u.jobcode_desc like '%".$request->search_text."%'");})
            ->when($request->criteria == 'dpt' && $request->search_text, function($q) use($request){return $q->whereRaw("u.deptid like '%".$request->search_text."%'");})
            ->when($request->criteria == 'all' && $request->search_text, function($q) use ($request) {$q->whereRaw("(u.employee_id like '%".$request->search_text."%' or u.employee_name like '%".$request->search_text."%' or u.jobcode_desc like '%".$request->search_text."%' or u.deptid like '%".$request->search_text."%')");})

            ->selectRaw ("
                u.user_id,
                u.guid,
                u.excused_flag,
                u.employee_id,
                u.employee_name, 
                u.jobcode_desc,
                u.organization,
                u.level1_program,
                u.level2_division,
                u.level3_branch,
                u.level4,
                u.deptid,
                u.employee_status,
                u.due_date_paused,
                u.next_conversation_date,
                u.excusedtype,
                '' AS nextConversationDue,
                '' AS shared,
                '' AS reportees,
                '' AS activeGoals
            ");
            return Datatables::of($query)->addIndexColumn()
            ->editColumn('activeGoals', function($row) {
                $countActiveGoals = Goal::with('goals_shared_with')
                ->where('id', 'goal_id')
                ->where('user_id', $row->user_id)
                ->where('status', 'active')
                ->get()
                ->count();
                return $countActiveGoals.' Goals';
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
                $yesOrNo = SharedProfile::where('shared_id', $row->user_id)->count() > 0 ? "Yes" : "No";
                return $yesOrNo;
            })
            ->editColumn('reportees', function($row) {
                // $users = new User;
                // $users->find('id', $row->user_id);
                // $countReportees = User::whereRaw('id = '.$row->user_id)->hasMany('App\Models\User', 'reporting_to')->get()->count() ?? '0';
                $tmp = $row->id;
                Log::info(User::where('id', '=', $tmp)->reportees());
                // $countReportees = User::with('users')->whereRaw('id = '.$row->user_id)->reporteesCount()->get() ?? '0';
                return '$countReportees';
                // return 0;
            })
            ->make(true);
        }
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'emp' => 'Employee ID', 
            'name'=> 'Employee Name',
            'job' => 'Classification', 
            'dpt' => 'Department ID'
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
