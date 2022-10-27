<?php

namespace App\Http\Controllers\HRAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conversation;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;
use App\Models\ExcusedClassification;
use App\Models\OrganizationTree;
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
            $query = User::withoutGlobalScopes()
            ->from('users as u')
            ->leftjoin('employee_demo as d', 'u.guid', 'd.guid')
            ->leftjoin('employee_demo_jr as j', 'u.guid', 'j.guid')
            ->whereRAW("trim(u.guid) <> ''")
            ->whereNotNull('u.guid')
            ->whereRAW("j.id = (select max(j1.id) from employee_demo_jr as j1 where j1.guid = j.guid) and d.date_deleted is null")
            ->whereExists(function ($orgs) use ($authId) {
                $orgs->select('o.user_id')
                ->from('admin_orgs as o')
                ->whereRAW('o.user_id = '.$authId.' and (o.organization = d.organization or ((o.organization = "" or o.organization IS null) and (d.organization = "" or d.organization is null)))'
                .' and (o.level1_program = d.level1_program or ((o.level1_program = "" or o.level1_program IS null) and (d.level1_program = "" or d.level1_program is null)))'
                .' and (o.level2_division = d.level2_division or ((o.level2_division = "" or o.level2_division IS null) and (d.level2_division = "" or d.level2_division is null)))'
                .' and (o.level3_branch = d.level3_branch or ((o.level3_branch = "" or o.level3_branch IS null) and (d.level3_branch = "" or d.level3_branch is null)))'
                .' and (o.level4 = d.level4 or ((o.level4 = "" or o.level4 IS null) and (d.level4 = "" or d.level4 is null)))');
            })
            ->when($level0, function($q) use($level0) {return $q->where('d.organization', $level0->name);})
            ->when($level1, function($q) use($level1) {return $q->where('d.level1_program', $level1->name);})
            ->when($level2, function($q) use($level2) {return $q->where('d.level2_division', $level2->name);})
            ->when($level3, function($q) use($level3) {return $q->where('d.level3_branch', $level3->name);})
            ->when($level4, function($q) use($level4) {return $q->where('d.level4', $level4->name);})
            ->when($request->criteria == 'id' && $request->search_text, function($q) use($request){return $q->whereRAW("d.employee_id like '%".$request->search_text."%'");})
            ->when($request->criteria == 'name' && $request->search_text, function($q) use($request){return $q->whereRAW("d.employee_name like '%".$request->search_text."%'");})
            ->when($request->criteria == 'job' && $request->search_text, function($q) use($request){return $q->whereRAW("d.jobcode_desc like '%".$request->search_text."%'");})
            ->when($request->criteria == 'dpt' && $request->search_text, function($q) use($request){return $q->whereRAW("d.deptid like '%".$request->search_text."%'");})
            ->when($request->criteria == 'all' && $request->search_text, function($q) use ($request) {$q->whereRAW("(d.employee_id like '%".$request->search_text."%' or d.employee_name like '%".$request->search_text."%' or d.jobcode_desc like '%".$request->search_text."%' or d.deptid like '%".$request->search_text."%')");})
            ->select
            (
                'u.id',
                'u.guid',
                'u.excused_flag',
                'd.employee_id',
                'd.employee_name', 
                'd.jobcode_desc',
                'd.organization',
                'd.level1_program',
                'd.level2_division',
                'd.level3_branch',
                'd.level4',
                'd.deptid',
                'd.employee_status',
                'j.due_date_paused',
                'j.next_conversation_date',
            );
            return Datatables::of($query)->addIndexColumn()
            ->addColumn('activeGoals', function($row) {
                $countActiveGoals = $row->activeGoals()->count() . ' Goals';
                return $countActiveGoals;
            })
            ->addColumn('nextConversationDue', function ($row) {
                if ($row->excused_flag) {
                    return 'Paused';
                } 
                $jr = EmployeeDemoJunior::where('guid', $row->guid)->getQuery()->orderBy('id', 'desc')->first();
                if ($jr) {
                    if  ($jr->due_date_paused != 'Y') {
                        $text = Carbon::parse($jr->next_conversation_date)->format('M d, Y');
                        return $text;
                    } else {
                        return 'Paused';
                    }
                }
                return '';
            })
            ->addColumn('excused', function ($row) {
                $jr = EmployeeDemoJunior::where('guid', $row->guid)->getQuery()->orderBy('id', 'desc')->first();
                if ($jr) {
                    if ($jr->excused_type) {
                        if ($jr->excused_type == 'A') {
                            return 'Auto';
                        }
                        if ($jr->excused_type == 'M' ) {
                            return 'Manual';
                        }
                    }
                }
                if ($row->excused_flag) {
                    return 'Manual';
                }
                return 'No';
            })
            ->addColumn('shared', function ($row) {
                $yesOrNo = $row->is_shared ? "Yes" : "No";
                return $yesOrNo;
            })
            ->addColumn('reportees', function($row) {
                $countReportees = $row->reportees()->count() ?? '0';
                return $countReportees;
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
