<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GoalController;
use App\Models\Organization;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\Goal;
use App\Models\GoalType;
use App\Models\OrgNode;
use App\Models\ExcusedReason;
use App\Models\OrganizationTree;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;

class HRadminController extends Controller
{
    public function myorg(Request $request)
    {
        $level0 = null;
        if($request->level0) {
            $level0 = $request->level0;
        } else {
            $level0 = $this->getOrgLevel0();
        }
        $this->getSearchCriterias($crit);

        $query = DB::table('employee_demo as emp')
        ->whereNull('emp.date_deleted')
        ->select('emp.employee_id', 'emp.guid', 'emp.employee_name', 'emp.jobcode_desc', 'emp.organization','emp.level1_program', 'emp.level2_division', 'emp.level3_branch', 'emp.level4', 'emp.deptid');
        // ->addselect(function ($aa){});
        // ->addselect(DB::raw("(select count(id) as goal_count from goals where user_id = emp.employee_id and status = 'active' group by user_id) as goal_count"));

        if ($request->has('dd_level0') && $request->dd_level0 && $request->dd_level0 != 'all') {
            $query = $query->where('organization', $request->dd_level0);
        }

        if ($request->has('dd_level1') && $request->dd_level1 && $request->dd_level1 != 'all') {
            $query = $query->where('level1_program', $request->dd_level1);
        }

        if ($request->has('dd_level2') && $request->dd_level2 && $request->dd_level2 != 'all') {
            $query = $query->where('level2_division', $request->dd_level2);
        }

        if ($request->has('dd_level3') && $request->dd_level3 && $request->dd_level3 != 'all') {
            $query = $query->where('level3_branch', $request->dd_level3);
        }

        if ($request->has('dd_level4') && $request->dd_level4 && $request->dd_level4 != 'all') {
            $query = $query->where('level4', $request->dd_level4);
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'emp') {
            $query = $query->where('employee_id', 'like', "%" . $request->searchText . "%");
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'name') {
            $query = $query->where('employee_name', 'like', "%" . $request->searchText . "%");
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'cls') {
            $query = $query->where('classification', 'like', "%" . $request->searchText . "%");
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'dpt') {
            $query = $query->where('deptid', 'like', "%" . $request->searchText . "%");
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'all') {
            $query = $query->where(function ($query2) use ($request) {
                $query2->where('employee_id', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('employee_name', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('classification', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('deptid', 'like', "%" . $request->searchText . "%");
            });
        }

        $iEmpl = $query->orderBy('employee_name')->paginate(10);

        return view('hradmin.myorg', compact('level0', 'crit', 'iEmpl', 'request'));
    }





    
    public function addgoal(Request $request)
    {

        $tree = OrgNode::with('children')
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
            ->from('employee_demo')
            ->where('employee_demo.deptid', 'deptid')
            ->whereRaw('employee_demo.pdp_excluded = 0');
        })->get();

        $level0Value = 'all';
        $level1Value = 'all';
        $level2Value = 'all';
        $level3Value = 'all';
        $level4Value = 'all';

        $this->getDropdownValues($mandatoryOrSuggested, $goalTypes);
        $level0 = $this->getOrgLevel0();

        $authId = Auth::id();
        $goalTypes = GoalType::all();
        $user = User::find($authId);
        $bankgoals = DB::table('goals')
        ->where('is_library', true)
        ->leftjoin('users', 'goals.user_id', '=', 'users.id')
        ->leftjoin('employee_demo', 'goals.user_id', '=', 'employee_demo.employee_id')
        ->leftjoin('goal_types', 'goals.goal_type_id', '=', 'goal_types.id')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->select('goals.*', 'users.name', 'employee_demo.deptid', 'employee_demo.level1_program', 'employee_demo.level2_division', 'employee_demo.level3_branch', 'employee_demo.level4',
        DB::raw('(CASE
        WHEN is_mandatory = 0 THEN "Suggested"
        WHEN is_mandatory = 1 THEN "Mandatory"
        ELSE "Any"
        END) AS MandatoryValue'),
        'goal_types.name AS GoalTypeValue',
        DB::raw('(SELECT COUNT(*) FROM goals_shared_with WHERE goals_shared_with.goal_id = goals.id) AS Audience')
        )
        ->paginate(8);

        $newGoal = new Goal;
        $newGoal->user_id = Auth::id();

        $aud_org = $this->getOrgLevel0();

        $aud_level1 =  DB::table('employee_demo')
        ->select('level1_program')
        ->where(trim('level1_program'), '<>', '')
        ->groupby('level1_program')
        ->get();
        return view('hradmin.goals.goal-bank', compact('level0', 'bankgoals', 'goalTypes', 'mandatoryOrSuggested', 'newGoal', 'aud_org', 'aud_level1', 'request'));
    }







    public function goaledit($id, Request $request)
    {
        $goaltypes = GoalType::all();
        $this->getDropdownValues($mandatoryOrSuggested, $goalTypes);
        $bankgoal = DB::table('goals')
        ->where('id', $id)
        ->first();

        $aud_org = DB::table('organizations')
        ->select('organization')
        ->where(trim('organization'), '<>', '')
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
            ->from('employee_demo')
            ->whereColumn('employee_demo.deptid', 'organizations.deptid')
            ->whereRaw('employee_demo.pdp_excluded = 0');
        }
        )
        ->groupby('organization')
        ->get();

        $aud_level1 =  DB::table('organizations')
        ->select('level1')
        ->where(trim('level1'), '<>', '')
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
            ->from('employee_demo')
            ->whereColumn('employee_demo.deptid', 'organizations.deptid')
            ->whereRaw('employee_demo.pdp_excluded = 0');
        }
        )
        ->groupby('level1')
        ->get();

        return view('hradmin.goals.goal-edit', compact('bankgoal', 'goalTypes', 'mandatoryOrSuggested', 'aud_org', 'aud_level1', 'request'));
    }

    public function shareemployee(Request $request)
    {
        $this->getDropdownValues($mandatoryOrSuggested, $goalTypes);
        $level0 = $this->getOrgLevel0();

        $sharedElements = [['key' => 'all', 'value' => 'All']];

        $jobTitles = DB::table('employee_demo')
        ->select('position_title')
        ->distinct()
        ->get();

        $sEmpl = DB::table('goals')
        ->leftjoin('employee_demo', 'employee_demo.employee_id', '=', 'goals.user_id')
        ->where('employee_demo.employee_name', '!=', '')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->select('goals.user_id', 'employee_demo.employee_name', 'employee_demo.position_title', 'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division', 'employee_demo.level3_branch', 'employee_demo.level4')
        ->distinct()
        ->paginate(8);

        return view('hradmin.shared.shareemployee', compact('level0', 'sEmpl', 'jobTitles', 'sharedElements', 'request'));
    }

    public function manageshares(Request $request)
    {
        $level0 = null;
        if($request->level0) {
            $level0 = $request->level0;
        } else {
            $level0 = $this->getOrgLevel0();
        }

        $this->getSearchCriterias($crit);

        // $users = DB::table('users')
        // ->leftjoin('employee_demo', 'employee_demo.employee_id', '=', 'id')
        // ->select('id', 'employee_demo.employee_name', 'employee_demo.position_title', 'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division', 'employee_demo.level3_branch', 'employee_demo.level4')
        // ->paginate(8);

        $query = DB::table('employee_demo')
        ->join('users', function($join){
            $join->on('employee_Demo.employee_id', '=', 'users.id');
        })
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->select('employee_id', 'employee_demo.guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4', 'excused_start_date', 'excused_end_date')
        // ->wherenotnull('excused_start_date')
        ;

        if ($request->has('dd_level0') && $request->dd_level0 && $request->dd_level0 != 'all') {
            $query = $query->where('organization', $request->dd_level0);
        }

        if ($request->has('dd_level1') && $request->dd_level1 && $request->dd_level1 != 'all') {
            $query = $query->where('level1_program', $request->dd_level1);
        }

        if ($request->has('dd_level2') && $request->dd_level2 && $request->dd_level2 != 'all') {
            $query = $query->where('level2_division', $request->dd_level2);
        }

        if ($request->has('dd_level3') && $request->dd_level3 && $request->dd_level3 != 'all') {
            $query = $query->where('level3_branch', $request->dd_level3);
        }

        if ($request->has('dd_level4') && $request->dd_level4 && $request->dd_level4 != 'all') {
            $query = $query->where('level4', $request->dd_level4);
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'emp') {
            $query = $query->where('employee_id', 'like', "%" . $request->searchText . "%");
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'name') {
            $query = $query->where('employee_name', 'like', "%" . $request->searchText . "%");
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'cls') {
            $query = $query->where('classification', 'like', "%" . $request->searchText . "%");
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'dpt') {
            $query = $query->where('deptid', 'like', "%" . $request->searchText . "%");
        }

        if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'all') {
            $query = $query->where(function ($query2) use ($request) {
                $query2->where('employee_id', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('employee_name', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('classification', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('deptid', 'like', "%" . $request->searchText . "%");
            });
        }

        $sEmpl = $query->orderBy('employee_name')->paginate(10);

        return view('hradmin.shared.manageshares', compact('request', 'sEmpl', 'crit', 'level0'));
    }

    public function excuseemployee(Request $request)
    {
        $reasons = ExcusedReason::all();
        $aud_org = $this->getOrgLevel0();
        $aud_level1 =  null;
        // $aud_level1 =  DB::table('employee_demo')
        // ->select('level1_program')
        // ->where(trim('level1_program'), '<>', '')
        // ->groupby('level1_program')
        // ->get();
       return view('hradmin.excused.excuseemployee', compact('reasons', 'aud_org', 'aud_level1', 'request'));
    }





    public function manageexcused_old(Request $request)
    {
        // $users = User::with('employees')
        // ->whereHas('employees')
        // ->where('employees.employee_id', '<>', null)
        // // ->where('id', '=', 120008)
        // ->whereexists(function ($query){
        //     $query ->select('employee_demo.employee_id')
        //     ->from('employee_demo')
        //     ->whereRaw('employee_demo.employee_id = users.id')
        //     ->where('employee_demo.employee_id', '<>', null);
        // })
        // ->get();
        // // $users = $users->with('employees');
        // // dd($users->count());
        // foreach ($users as $user) {
        //     foreach ($user->employees as $emp) {
        //     // dd($user->id);
        //     if($user->employees) {
        //         // dd( $emp->employee_id );
        //     }
        // }
        // }


        $level0 = null;
        if($request->level0) {
            $level0 = $request->level0;
        } else {
            $level0 = $this->getOrgLevel0();
        }

        $this->getSearchCriterias($crit);

        $query = User::join('employee_demo', 'users.employee_id', 'employee_demo.employee_id')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->select('employee_id', 'employee_demo.guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4', 'excused_start_date', 'excused_end_date')
        ->whereNotNull('excused_start_date');
        // ->get();



        //     $query = DB::table('employee_demo')
        // ->join('users', function($join){
        //     $join->on('employee_Demo.employee_id', '=', 'users.id');
        // })
        // ->select('employee_id', 'employee_demo.guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4', 'excused_start_date', 'excused_end_date')
        // ->wherenotnull('excused_start_date')
        // ;

        $query->when($request->dd_level0, function($q){return $q->where('organization', $request->dd_level0);});
        $query->when($request->dd_level1, function($q){return $q->where('level1_program', $request->dd_level1);});
        $query->when($request->dd_level2, function($q){return $q->where('level2_division', $request->dd_level2);});
        $query->when($request->dd_level3, function($q){return $q->where('level3_branch', $request->dd_level3);});
        $query->when($request->dd_level4, function($q){return $q->where('level4', $request->dd_level4);});
        $query->when($request->criteria == 'emp', function($q){return $q->where('employee_id', 'like', "%" . $request->searchText . "%");});
        $query->when($request->criteria == 'name', function($q){return $q->where('employee_name', 'like', "%" . $request->searchText . "%");});
        $query->when($request->criteria == 'cls', function($q){return $q->where('classification', 'like', "%" . $request->searchText . "%");});
        $query->when($request->criteria == 'dpt', function($q){return $q->where('deptid', 'like', "%" . $request->searchText . "%");});
        $query->when($request->criteria == 'all', function($q){
            return $q->where(function ($query2) use ($request) {
                $query2->where('employee_id', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('employee_name', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('classification', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('deptid', 'like', "%" . $request->searchText . "%");
            });
        });

        // if ($request->has('dd_level0') && $request->dd_level0 && $request->dd_level0 != 'all') {
        //     $query = $query->where('organization', $request->dd_level0);
        // }

        // if ($request->has('dd_level1') && $request->dd_level1 && $request->dd_level1 != 'all') {
        //     $query = $query->where('level1_program', $request->dd_level1);
        // }

        // if ($request->has('dd_level2') && $request->dd_level2 && $request->dd_level2 != 'all') {
        //     $query = $query->where('level2_division', $request->dd_level2);
        // }

        // if ($request->has('dd_level3') && $request->dd_level3 && $request->dd_level3 != 'all') {
        //     $query = $query->where('level3_branch', $request->dd_level3);
        // }

        // if ($request->has('dd_level4') && $request->dd_level4 && $request->dd_level4 != 'all') {
        //     $query = $query->where('level4', $request->dd_level4);
        // }

        // if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'emp') {
        //     $query = $query->where('employee_id', 'like', "%" . $request->searchText . "%");
        // }

        // if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'name') {
        //     $query = $query->where('employee_name', 'like', "%" . $request->searchText . "%");
        // }

        // if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'cls') {
        //     $query = $query->where('classification', 'like', "%" . $request->searchText . "%");
        // }

        // if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'dpt') {
        //     $query = $query->where('deptid', 'like', "%" . $request->searchText . "%");
        // }

        // if ($request->has('searchText') && $request->searchText && $request->criteria && $request->criteria == 'all') {
        //     $query = $query->where(function ($query2) use ($request) {
        //         $query2->where('employee_id', 'like', "%" . $request->searchText . "%");
        //         $query2->orWhere('employee_name', 'like', "%" . $request->searchText . "%");
        //         $query2->orWhere('classification', 'like', "%" . $request->searchText . "%");
        //         $query2->orWhere('deptid', 'like', "%" . $request->searchText . "%");
        //     });
        // }

        // dd($query);


        $sEmpl = $query->orderBy('employee_name')->paginate(10);

        return view('hradmin.excused.manageexcused', compact('level0', 'sEmpl', 'crit', 'request'));
    }



    public function manageexcused(Request $request)
    {
        $level0 = null;
        if($request->level0) {
            $level0 = $request->level0;
        } else {
            $level0 = $this->getOrgLevel0();
        }

        $this->getSearchCriterias($crit);

        if ($request->ajax()) {
            $data = User::select('*');

            $query = User::join('employee_demo', 'users.employee_id', 'employee_demo.employee_id')
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->select('employee_id', 'employee_demo.guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4', 'excused_start_date', 'excused_end_date')
            ->whereNotNull('excused_start_date');
    
            $query->when($request->dd_level0, function($q){return $q->where('organization', $request->dd_level0);});
            $query->when($request->dd_level1, function($q){return $q->where('level1_program', $request->dd_level1);});
            $query->when($request->dd_level2, function($q){return $q->where('level2_division', $request->dd_level2);});
            $query->when($request->dd_level3, function($q){return $q->where('level3_branch', $request->dd_level3);});
            $query->when($request->dd_level4, function($q){return $q->where('level4', $request->dd_level4);});
            $query->when($request->criteria == 'emp', function($q){return $q->where('employee_id', 'like', "%" . $request->searchText . "%");});
            $query->when($request->criteria == 'name', function($q){return $q->where('employee_name', 'like', "%" . $request->searchText . "%");});
            $query->when($request->criteria == 'cls', function($q){return $q->where('classification', 'like', "%" . $request->searchText . "%");});
            $query->when($request->criteria == 'dpt', function($q){return $q->where('deptid', 'like', "%" . $request->searchText . "%");});
            $query->when($request->criteria == 'all', function($q){
                return $q->where(function ($query2) use ($request) {
                    $query2->where('employee_id', 'like', "%" . $request->searchText . "%");
                    $query2->orWhere('employee_name', 'like', "%" . $request->searchText . "%");
                    $query2->orWhere('classification', 'like', "%" . $request->searchText . "%");
                    $query2->orWhere('deptid', 'like', "%" . $request->searchText . "%");
                });
            });
        
            $sEmpl = $query->orderBy('employee_name')->paginate(10);


            return Datatables::of($sEmpl)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
     
                           $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
    
                            return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        
        return view('hradmin.excused.manageexcused', compact('level0', 'sEmpl', 'crit', 'request'));
    }










    public function managegoals(Request $request)
    {
        return view('hradmin.goals.managegoals');
    }

    public function createnotification(Request $request)
    {
        return view('hradmin.notifications.createnotification');
    }

    public function viewnotifications(Request $request)
    {
        return view('hradmin.notifications.viewnotifications');
    }

    public function goalsummary(Request $request)
    {
        return view('hradmin.statistics.goalsummary');
    }

    public function conversationsummary(Request $request)
    {
        return view('hradmin.statistics.conversationsummary');
    }

    public function sharedsummary(Request $request)
    {
        return view('hradmin.statistics.sharedsummary');
    }

    public function excusedsummary(Request $request)
    {
        return view('hradmin.statistics.excusedsummary');
    }


    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function goalupdate(CreateGoalRequest $request, $id)
    {
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->findOrFail($id);
        $input = $request->validated();

        $goal->update($input);

        return redirect()->route('url()->previous()');
    }

    /**
    * Add new goal to the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function goaladd(CreateGoalRequest $request)
    {
        $input = $request->validated();

        $input['user_id'] = Auth::id();

        Goal::create($input);

        return response()->json(['success' => true, 'message' => 'Goal Created successfully']);
    }

    public function getOrgLevel0() {
        $query = DB::table('employee_demo')
        ->select('organization as key0', 'organization')
        ->where(trim('organization'), '<>', '')
        ->groupby('organization');

        $level0 = $query->get('key0', 'organization');
        return $level0;
    }

    public function getOrgLevel1($id0) {
        if ($id0 == 'all') {
            $level1 = [['key1' => 'all', 'level1_program' => 'All']];
        }else{
            $query = DB::table('employee_demo')
            ->select('level1_program as key1', 'level1_program')
            ->where(trim('level1_program'), '<>', '')
            ->where('organization', $id0);
            $level1 = $query->groupby('level1_program')->pluck('level1_program', 'key1');
        };
        return $level1;
    }

    public function getOrgLevel2($id0, $id1) {
        if ($id1 == 'all') {
            $level2 = [['key2' => 'all', 'level2_division' => 'All']];
        }else{
            $query = DB::table('employee_demo')
            ->select("level2_division as key2", 'level2_division')
            ->where(trim('level2_division'), '<>', '')
            ->where('organization', $id0)
            ->where('level1_program', $id1)
            ->groupby('level2_division');
            $level2 = $query->pluck('level2_division', 'key2');
        };
        return $level2;
    }

    public function getOrgLevel3($id0, $id1, $id2) {
        if ($id2 == 'all') {
            $level3 = [['key3' => 'all', 'level3_branch' => 'All']];
        }else{
            $query = DB::table('employee_demo')
            ->select("level3_branch as key3", 'level3_branch')
            ->where(trim('level3_branch'), '<>', '')
            ->where('organization', $id0)
            ->where('level1_program', $id1)
            ->where('level2_division', $id2)
            ->groupby('level3_branch');
            $level3 = $query->pluck('level3_branch', 'key3');
        };
        return $level3;
    }

    public function getOrgLevel4($id0, $id1, $id2, $id3) {
        if ($id3 == 'all') {
            $level4 = [['key4' => 'all', 'level4' => 'All']];
        }else{
            $query = DB::table('employee_demo')
            ->select("level4 as key4", 'level4')
            ->where(trim('level4'), '<>', '')
            ->where('organization', $id0)
            ->where('level1_program', $id1)
            ->where('level2_division', $id2)
            ->where('level3_branch', $id3)
            ->groupby('level4');
            $level4 = $query->pluck('level4', 'key4');
    };
        return $level4;
    }

    public function getJobTitles() {
        $jobTitles = DB::table('employee_demo')
        ->select(DB::raw("REPLACE (REPLACE (REPLACE (REPLACE (REPLACE (REPLACE (REPLACE (REPLACE (jobcode_desc, '.', ''), '\"', ''), '\'', ''), '-', ''), ',', ''), ' ', ''), '&', ''), '/', '') as pkey"), 'jobcode_desc')
        ->where(trim('jobcode_desc'), '<>', '')
        ->groupby('jobcode_desc')
        ->pluck('pkey', 'jobcode_desc');
        return json_encode($jobTitles);
    }

    private function getDropdownValues(&$mandatoryOrSuggested, &$goalTypes) {
        $mandatoryOrSuggested = [
            [
                "id" => '',
                "name" => 'Any'
            ],
            [
                "id" => '1',
                "name" => 'Mandatory'
            ],
            [
                "id" => '0',
                "name" => 'Suggested'
            ]
        ];

        $goalType = GoalType::all()->pluck('name', 'id')->toArray();
        array_unshift($goalType, "Any");
        $goalTypes = [];
        foreach($goalType as $id => $type) {
            $goalTypes[] = [
                "id" => $id,
                "name" => $type
            ];
        }
    }

    private function getSearchCriterias(&$Criterias) {
        $Criterias = [
            [
                "id" => 'all',
                "name" => 'All'
            ],
            [
                "id" => 'emp',
                "name" => 'Employee ID'
            ],
            [
                "id" => 'name',
                "name" => 'Employee Name'
            ],
            [
                "id" => 'cls',
                "name" => 'Classification'
            ],
            [
                "id" => 'dpt',
                "name" => 'Department ID'
            ],
        ];
    }

    public function getOrganizations(Request $request) 
    {
        $orgs = OrganizationTree::orderby('name','asc')->select('id','name')
            ->where('level',0)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
            })
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getPrograms(Request $request) 
    {
        $level0 = $request->level0 ? OrganizationTree::where('id',$request->level0)->first() : null;
        $orgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',1)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization', $level0->name );
            })
            ->groupBy('name')
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getDivisions(Request $request) 
    {
        $level0 = $request->level0 ? OrganizationTree::where('id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::where('id', $request->level1)->first() : null;
        $orgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('level1_program', $level1->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getBranches(Request $request) 
    {
        $level0 = $request->level0 ? OrganizationTree::where('id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::where('id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::where('id', $request->level2)->first() : null;
        $orgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('level2_division', $level2->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getLevel4(Request $request) 
    {
        $level0 = $request->level0 ? OrganizationTree::where('id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::where('id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::where('id', $request->level2)->first() : null;
        $level3 = $request->level3 ? OrganizationTree::where('id', $request->level3)->first() : null;
        $orgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('level2_division', $level2->name );
            })
            ->when( $level3 , function ($q) use($level3) {
                return $q->where('level3_branch', $level3->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 




}
