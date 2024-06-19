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
use App\Models\UserDemoJrView;
use App\Models\Organization;
use App\Models\User;
use App\Models\Goal;
use App\Models\GoalType;
use App\Models\Tag;
use App\Models\OrgNode;
use App\Models\ExcusedReason;
use App\Models\EmployeeDemo;
use App\Models\OrganizationTree;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
// use GuzzleHttp\Psr7\Request;


class SysadminController extends Controller
{
    public function current(Request $request)
    {
        $level0 = null;
        if($request->level0) {
            $level0 = $request->level0;
        } else {
            $level0 = $this->getOrgLevel0();
        }
        // $jobTitles = $this->getJobTitles();

        $query = DB::table('employee_demo')
        ->select('employee_id', 'guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4', 'effdt', 'hire_dt')
        ->wherein('employee_status', ['A', 'L', 'P', 'S']);

        $jobTitles = DB::table('employee_demo')
        ->select('jobcode_desc as title', 'jobcode_desc')
        ->wherein('employee_status', ['A', 'L', 'P', 'S'])
        ->where(trim('jobcode_desc'), '<>', '')
        ->groupby('jobcode_desc')
        ->get('title', 'jobcode_desc');

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

        if ($request->has('jobTitle') && $request->jobTitle && $request->jobTitle != 'all') {
            $query = $query->where('jobcode_desc', $request->jobTitle);
        }

        if ($request->has('activeSince') && $request->activeSince) {
            $query = $query->where('hire_dt', '>=', $request->activeSince);
        }

        if ($request->has('searchText') && $request->searchText) {
            $query = $query->where(function ($query2) use ($request) {
                $query2->where('employee_name', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('jobcode_desc', 'like', "%" . $request->searchText . "%");
                $query2->orWhere('position_title', 'like', "%" . $request->searchText . "%");
            });
        }

        $iEmpl = $query->orderBy('employee_name')->paginate(10);

        return view('sysadmin.employees.current', compact('level0', 'iEmpl', 'jobTitles', 'request'));
    }

    public function previous(Request $request)
    {
        $level0 = null;
        if($request->level0) {
            $level0 = $request->level0;
        } else {
            $level0 = $this->getOrgLevel0();
        }
        // $jobTitles = $this->getJobTitles();

        $query = DB::table('employee_demo')
        ->select('employee_id', 'guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4', 'effdt', 'hire_dt')
        ->wherenotin('employee_status', ['A', 'L', 'P', 'S']);

        $jobTitles = DB::table('employee_demo')
        ->select('jobcode_desc as title', 'jobcode_desc')
        ->wherenotin('employee_status', ['A', 'L', 'P', 'S'])
        ->where(trim('jobcode_desc'), '<>', '')
        ->groupby('jobcode_desc')
        ->get('title', 'jobcode_desc');

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

        if ($request->has('jobTitle') && $request->jobTitle && $request->jobTitle != 'all') {
            $query = $query->where('jobcode_desc', $request->jobTitle);
        }

        if ($request->has('inactiveSince') && $request->inactiveSince) {
            $query = $query->where('effdt', '>=', $request->inactiveSince);
        }

        $iEmpl = $query->orderBy('employee_name')->paginate(10);

        return view('sysadmin.employees.previous', compact('level0', 'iEmpl', 'jobTitles', 'request'));
    }

    public function shareemployee(Request $request)
    {
        $this->getDropdownValues($mandatoryOrSuggested, $goalTypes);
        $level0 = $this->getOrgLevel0();

        $shareelements = [['id' => 'all', 'name' => 'All']];
        $elements = ExcusedReason::all();
        $aud_org = $this->getOrgLevel0();
        $aud_level1 =  null;

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

        // $users = User::leftjoin('employee_demo', 'employee_demo.employee_id', '=', 'id')
        // ->select('id', 'employee_demo.employee_name', 'employee_demo.position_title', 'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division', 'employee_demo.level3_branch', 'employee_demo.level4')
        // ->paginate(8);;

        return view('sysadmin.shared.shareemployee', compact('shareelements', 'aud_org', 'aud_level1', 'request'));
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
            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
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

        return view('sysadmin.shared.manageshares', compact('request', 'sEmpl', 'crit', 'level0'));
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
       return view('sysadmin.excused.excuseemployee', compact('reasons', 'aud_org', 'aud_level1', 'request'));
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

        $query = DB::table('employee_demo')
        ->join('users', function($join){
            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
        })
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->select('employee_id', 'employee_demo.guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4', 'excused_start_date', 'excused_end_date')
        ->wherenotnull('excused_start_date')
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

        return view('sysadmin.excused.manageexcused', compact('level0', 'sEmpl', 'crit', 'request'));
    }

    public function managegoals(Request $request)
    {
        return view('sysadmin.goals.managegoals', compact('request'));
    }

    public function unlockconversation(Request $request)
    {
        return view('sysadmin.unlock.unlockconversation', compact('request'));
    }

    public function manageunlocked(Request $request)
    {
        $level0 = null;
        if($request->level0) {
            $level0 = $request->level0;
        } else {
            $level0 = $this->getOrgLevel0();
        }

        $this->getSearchCriterias($crit);

        $query = DB::table('employee_demo')
        ->join('users', function($join){
            $join->on('employee_Demo.employee_id', '=', 'users.employee_id');
        })
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->select('employee_id', 'employee_demo.guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4')
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

        return view('sysadmin.unlock.manageunlocked', compact('level0', 'sEmpl', 'crit', 'request'));
    }

    public function createnotification(Request $request)
    {
        return view('sysadmin.notifications.createnotification', compact('request'));
    }

    public function viewnotifications(Request $request)
    {
        return view('sysadmin.notifications.viewnotifications', compact('request'));
    }

    public function createaccess(Request $request)
    {
        $reasons = ExcusedReason::all();
        $aud_org = $this->getOrgLevel0();
        $aud_level1 =  null;
        $this->getAccessLevels($access_levels);
       return view('sysadmin.access.createaccess', compact('request', 'reasons', 'aud_org', 'aud_level1', 'access_levels'));
    }

    public function manageaccess(Request $request)
    {
        $level0 = null;
        if($request->level0) {
            $level0 = $request->level0;
        } else {
            $level0 = $this->getOrgLevel0();
        }

        $this->getSearchCriterias($crit);

        $query = DB::table('employee_demo')
        ->leftjoin('users', function($join){
            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
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

        return view('sysadmin.access.manageaccess', compact('level0', 'sEmpl', 'crit', 'request'));
    }

    public function goalsummary(Request $request)
    {
        return view('sysadmin.statistics.goalsummary', compact('request'));
    }

    public function conversationsummary(Request $request)
    {
        return view('sysadmin.statistics.conversationsummary', compact('request'));
    }

    public function sharedsummary(Request $request)
    {
        return view('sysadmin.statistics.sharedsummary', compact('request'));
    }

    public function excusedsummary(Request $request)
    {
        return view('sysadmin.statistics.excusedsummary', compact('request'));
    }

    public function statistics(Request $request)
    {
        return view('sysadmin.statistics', compact('request'));
    }

    public function addgoal(Request $request)
    {

        $tree = OrgNode::with('children')
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
            ->from('employee_demo')
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->where('employee_demo.deptid', 'deptid');
        }
        )
        ->get();

        $level0Value = 'all';
        $level1Value = 'all';
        $level2Value = 'all';
        $level3Value = 'all';
        $level4Value = 'all';

        $this->getDropdownValues($mandatoryOrSuggested, $goalTypes);
        $level0 = $this->getOrgLevel0();

        $authId = Auth::id();
        $goaltypes = GoalType::all();
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
        // $aud_level1 =  DB::table('organizations')
        // ->select('level1')
        // ->where(trim('level1'), '<>', '')
        // ->whereExists(function ($query) {
        //     $query->select(DB::raw(1))
        //     ->from('employee_demo')
        //     ->whereColumn('employee_demo.deptid', 'organizations.deptid');
        // }
        // )
        // ->groupby('level1')
        // ->get();

        // return view('sysadmin.goal-bank', compact('level1','level2','level3','level4', 'bankgoals', 'goalTypes', 'mandatoryOrSuggested', 'newGoal', 'aud_org', 'aud_level1'));
        return view('sysadmin.goals.goal-bank', compact('level0', 'bankgoals', 'goalTypes', 'mandatoryOrSuggested', 'newGoal', 'aud_org', 'aud_level1', 'request'));
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
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->whereColumn('employee_demo.deptid', 'organizations.deptid');
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
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->whereColumn('employee_demo.deptid', 'organizations.deptid');
        }
        )
        ->groupby('level1')
        ->get();

        return view('sysadmin.goal-edit', compact('bankgoal', 'goalTypes', 'mandatoryOrSuggested', 'aud_org', 'aud_level1', 'request'));
    }

    public function access(Request $request)
    {
        return view('sysadmin.access');
    }

    public function conversations(Request $request)
    {
        $level0 = $this->getOrgLevel0();
        $eelevel0 = $this->getOrgLevel0();

        $openConversations = DB::table('conversations')
        ->leftjoin('employee_demo', 'employee_demo.employee_id', '=', 'conversations.user_id')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->select('conversations.id', 'conversations.conversation_topic_id', 'conversations.date', 'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division', 'employee_demo.level3_branch', 'employee_demo.level4')
        ->where(function ($query) {
            $query->where('conversations.supervisor_signoff_id', null)
            ->orwhere('conversations.user_id', null);
        })
        ->paginate(8);

        $closedConversations = DB::table('conversations')
        ->leftjoin('employee_demo', 'employee_demo.employee_id', '=', 'conversations.user_id')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->select('conversations.id', 'conversations.conversation_topic_id', 'conversations.date', 'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division', 'employee_demo.level3_branch', 'employee_demo.level4')
        ->where('conversations.supervisor_signoff_id', '!=', null)
        ->where('conversations.user_id', '!=', null)
        ->paginate(8);




        return view('sysadmin.conversations', compact('level0', 'eelevel0', 'openConversations', 'closedConversations', 'request'));
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

    public function getOrgLevel0()
    {
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
        } else {
            $query = DB::table('employee_demo')
            ->select('level1_program as key1', 'level1_program')
            ->where(trim('level1_program'), '<>', '')
            ->where('organization', $id0);
            $level1 = $query->groupby('level1_program')->pluck('level1_program', 'key1');
        }
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

    private function getAccessLevels(&$accessLevels) {
        $accessLevels = [
            [
                "id" => 'select',
                "name" => ''
            ],
            [
                "id" => 'HRA',
                "name" => 'HR Administrator'
            ],
            [
                "id" => 'SA',
                "name" => 'System Administrator'
            ],
            [
                "id" => 'CCR',
                "name" => 'Contact Centre Representative'
            ],
        ];
    }

    public function switchIdentityAction(Request $request) {
        //$query = User::orderby('name','asc')->select('id','name','email');
        $userid = auth()->user()->id;
            
        if(session()->has('user_is_switched')) {
                $userid = $request->session()->get('existing_user_id');
        }
        
        $user_role = DB::table('model_has_roles')                        
                        ->where('model_id', $userid)
                        ->whereIntegerInRaw('role_id', [4, 5])
                        ->where('model_type', 'App\Models\User')
                        ->get();
    
        if(count($user_role) == 0) {
                return redirect()->to('/');
                exit;
        }
         
        if ($request->has('new_user_id') && $request->new_user_id) {
            $switched = session('user_is_switched');
            if(!$switched){
                $request->session()->put('existing_user_id', Auth::user()->id);
                $request->session()->put('user_is_switched', true);
            }
            $newuserId = $request->new_user_id;
            Auth::loginUsingId($newuserId);

            if (session()->has('sr_user')) {
                session()->put('SR_ALLOWED', true);
            }


            $switched_user_role = DB::table('model_has_roles')                        
                                ->where('model_id', $newuserId)
                                ->where('model_type', 'App\Models\User')
                                ->get(); 
            foreach($switched_user_role as $item){
                if($item->role_id == 5){
                    session()->put('sr_user', true);
                }

            }                    


            return redirect()->to('/');
        }  
    } 
    
    public function switchIdentity(Request $request) {
            $user = auth()->user();
            $switched_userid = $user->id;
            
            $user_roles = DB::table('model_has_roles')                        
                        ->where('model_id', $switched_userid)
                        ->whereIntegerInRaw('role_id', [4, 5])
                        ->where('model_type', 'App\Models\User')
                        ->get();
            
            if(count($user_roles) == 0) {
                return redirect()->to('/');
                exit;
            } else {
                foreach($user_roles as $item){
                    if($item->role_id == 5){
                        if (!Session::has('sr_user')) {
                            session()->put('sr_user', true);
                        } 
                    }
                }    
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

        // $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        // $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        // $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        // $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        // $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;

        $request->session()->flash('level0', $request->dd_level0);
        $request->session()->flash('level1', $request->dd_level1);
        $request->session()->flash('level2', $request->dd_level2);
        $request->session()->flash('level3', $request->dd_level3);
        $request->session()->flash('level4', $request->dd_level4);
        // $request->session()->flash('level0', $level0);
        // $request->session()->flash('level1', $level1);
        // $request->session()->flash('level2', $level2);
        // $request->session()->flash('level3', $level3);
        // $request->session()->flash('level4', $level4);

        $criteriaList = [
            'all' => 'All',
            'employee_id' => 'Employee ID', 
            'employee_name'=> 'Employee Name',
            'jobcode_desc' => 'Classification', 
            'deptid' => 'Department ID'
        ];
        
        //return view('sysadmin.switch-identity.index',compact('search_user'));   
        return view('sysadmin.switch-identity.index', compact ('request', 'criteriaList'));
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

    public function identityList(Request $request, $option = '')
    {  
        $get_data = 0;
        if ($request->ajax()) 
        {
            $query = UserDemoJrView::from('user_demo_jr_view AS u')
                ->whereNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" != 'all', function($q) use($request, $option) { 
                    return $q->where("u.{$request->{$option.'criteria'}}", 'LIKE', "%{$request->{$option.'search_text'}}%"); 
                })
                ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" == 'all', function($q) use($request, $option) { 
                    return $q->where(function($q1) use($request, $option) {
                        return $q1->where('u.employee_id', 'LIKE', "%{$request->{$option.'search_text'}}%")
                        ->orWhere('u.employee_name', 'LIKE', "%{$request->{$option.'search_text'}}%")
                        ->orWhere('u.jobcode_desc', 'LIKE', "%{$request->{$option.'search_text'}}%")
                        ->orWhere('u.deptid', 'LIKE', "%{$request->{$option.'search_text'}}%");
                    });
                })
                ->selectRaw ("
                    u.user_id AS id,
                    u.user_name,
                    u.employee_id,
                    u.employee_name, 
                    u.jobcode_desc,
                    u.organization,
                    u.level1_program,
                    u.level2_division,
                    u.level3_branch,
                    u.level4,
                    u.deptid
                ");
            
            if($request->dd_level0 == '' && $request->dd_level1 == '' && $request->dd_level2 == '' && $request->dd_level3 == '' && $request->dd_level4 == '' && $request->search_text == '') {
                $data = $query->take(0)->get();                
            } else {
                $data = $query->get();
            }
                        
            return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true); 
        }
    }
    
    public function tags(Request $request) {
        $tags = Tag::all()->sortBy("name")->toArray();    
 
        return view('sysadmin.tags.index', compact ('request', 'tags'));
    } 

    public function tagDetail(Request $request, $id) {
        $tag = Tag::findOrFail($id);        
        return view('sysadmin.tags.detail', compact ('request', 'tag'));
    } 
    
    public function tagUpdate(Request $request, $id) {
        $tag = Tag::findOrFail($id);
        $tag->name = $request->name;
        $tag->description = $request->description;
        $tag->save();
        //return $this->respondeWith($tag);
        return redirect('/sysadmin/tags');
    } 
    
    public function tagDelete(Request $request, $id) {
        $tag = Tag::findOrFail($id);
        DB::table('goal_tags')->where('tag_id', $id)->delete();
        $tag->delete();
        //return $this->respondeWith($tag);
        return redirect('/sysadmin/tags');
    }

    public function tagNew(Request $request) {
        
        return view('sysadmin.tags.new', compact ('request'));
    }
    
    public function tagInsert(Request $request) {
        DB::table('tags')->insert(
            ['name' => $request->name, 'description' => $request->description]
        );
        return redirect('/sysadmin/tags');
    }
}
