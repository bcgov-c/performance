<?php

namespace App\Http\Controllers\SysAdmin;


use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Goal;
use App\Models\User;
use App\Models\GoalType;
use App\Models\GoalBankOrg;
use App\Models\EmployeeDemo;
use Illuminate\Http\Request;
use App\Models\GoalSharedWith;
use App\Models\UserDemoJrView;
use App\Models\UserDemoJrForGoalbankView;
use App\MicrosoftGraph\SendMail;
use App\Models\EmployeeDemoTree;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Goals\CreateGoalRequest;
use Illuminate\Validation\ValidationException;
use App\Models\DashboardNotification;
use App\Models\NotificationLog;


class GoalBankController extends Controller
{
    public function createindex(Request $request) {
        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);
        $tags = Tag::all(["id","name"])->sortBy("name")->toArray();
        $errors = session('errors');
        $request->firstTime = true;
        $old_selected_emp_ids = []; 
        $old_selected_org_nodes = []; 
        $old_selected_inherited = []; 
        $eold_selected_emp_ids = []; 
        $eold_selected_org_nodes = [];
        $eold_selected_inherited = [];
        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->dd_superv = isset($old['dd_superv']) ? $old['dd_superv'] : null;
            $request->criteria = isset($old['criteria']) ? $old['criteria'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
            $request->orgCheck = isset($old['orgCheck']) ? $old['orgCheck'] : null;
            $request->userCheck = isset($old['userCheck']) ? $old['userCheck'] : null;
            $old_selected_emp_ids = isset($old['selected_emp_ids']) ? json_decode($old['selected_emp_ids']) : [];
            $old_selected_org_nodes = isset($old['selected_org_nodes']) ? json_decode($old['selected_org_nodes']) : [];
            $old_selected_inherited = isset($old['selected_inherited']) ? json_decode($old['selected_inherited']) : [];
            $request->edd_level0 = isset($old['edd_level0']) ? $old['edd_level0'] : null;
            $request->edd_level1 = isset($old['edd_level1']) ? $old['edd_level1'] : null;
            $request->edd_level2 = isset($old['edd_level2']) ? $old['edd_level2'] : null;
            $request->edd_level3 = isset($old['edd_level3']) ? $old['edd_level3'] : null;
            $request->edd_level4 = isset($old['edd_level4']) ? $old['edd_level4'] : null;
            $request->edd_superv = isset($old['edd_superv']) ? $old['edd_superv'] : null; 
            $request->ecriteria = isset($old['ecriteria']) ? $old['ecriteria'] : null;
            $request->esearch_text = isset($old['esearch_text']) ? $old['esearch_text'] : null;
            $request->eorgCheck = isset($old['eorgCheck']) ? $old['eorgCheck'] : null;
            $request->euserCheck = isset($old['euserCheck']) ? $old['euserCheck'] : null;
            $request->eselected_inherited = isset($old['einheritedCheck']) ? $old['einheritedCheck'] : null;
            $eold_selected_emp_ids = isset($old['eselected_emp_ids']) ? json_decode($old['eselected_emp_ids']) : [];
            $eold_selected_org_nodes = isset($old['eselected_org_nodes']) ? json_decode($old['eselected_org_nodes']) : [];
            $eold_selected_inherited = isset($old['eselected_inherited']) ? json_decode($old['eselected_inherited']) : [];
        } 
        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'dd_superv' => $request->dd_superv,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
                'orgCheck' => $request->orgCheck,
                'userCheck' => $request->userCheck,
            ]);
        }
        if ($request->ebtn_search) {
            session()->put('_old_input', [
                'edd_level0' => $request->edd_level0,
                'edd_level1' => $request->edd_level1,
                'edd_level2' => $request->edd_level2,
                'edd_level3' => $request->edd_level3,
                'edd_level4' => $request->edd_level4,
                'edd_superv' => $request->edd_superv, 
                'ecriteria' => $request->ecriteria,
                'esearch_text' => $request->esearch_text,
                'eorgCheck' => $request->eorgCheck,
                'euserCheck' => $request->euserCheck,
                'eselected_inherited' => $request->eselected_inherited,
            ]);
        }
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $request->session()->flash('dd_superv', $request->dd_superv); 
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        $request->session()->flash('euserCheck', $request->euserCheck);  // Dynamic load 
        $request->session()->flash('eselected_inherited', $request->eselected_inherited);  // Dynamic load 
        $request->session()->flash('edd_level0', $request->edd_level0);
        $request->session()->flash('edd_level1', $request->edd_elevel1);
        $request->session()->flash('edd_level2', $request->edd_elevel2);
        $request->session()->flash('edd_level3', $request->edd_elevel3);
        $request->session()->flash('edd_level4', $request->edd_elevel4);
        $request->session()->flash('edd_superv', $request->edd_superv); 
        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, "");
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql
            ->orderBy('employee_id')
            ->pluck('employee_id');        
        // Matched Employees 
        $edemoWhere = $this->baseFilteredWhere($request, "e");
        $esql = clone $edemoWhere; 
        $ematched_emp_ids = $esql
            ->orderBy('u.employee_id')
            ->pluck('u.employee_id');        
        $supervisorList = $this->supervisor_list();
        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();
        $type_desc_arr = array();
        foreach($goalTypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {                
                $item = "<b>" . $goalType['name'] . " Goals</b> ". str_replace($goalType['name'] . " Goals","",$goalType['description']);
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>', $type_desc_arr);
        //no need private in goalbank module
        unset($goalTypes[3]);
        $currentView = $request->segment(2);
        return view('shared.goalbank.createindex', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'ematched_emp_ids', 'old_selected_emp_ids', 'eold_selected_emp_ids', 'old_selected_org_nodes', 'eold_selected_org_nodes', 'old_selected_inherited', 'eold_selected_inherited', 'goalTypes', 'mandatoryOrSuggested', 'tags', 'type_desc_str', 'currentView', 'supervisorList') );
    }

    public function getFilteredList(Request $request) {
        $demoWhere = $this->baseFilteredWhere($request, $request->option);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 
                'u.employee_id', 
                'u.employee_name', 
                'u.jobcode_desc', 
                'u.employee_email', 
                'u.organization', 
                'u.level1_program', 
                'u.level2_division', 
                'u.level3_branch',
                'u.level4',
                'u.deptid', 
                'u.jobcode_desc'
            ])
            ->when($request->{$request->option.'dd_superv'} == 'sup', function($q) { return $q->whereRaw("(u.isSupervisor = 1 OR u.isDelegate = 1)"); }) 
            ->when($request->{$request->option.'dd_superv'} == 'non', function($q) { return $q->whereRaw("NOT u.isSupervisor = 1 AND NOT u.isDelegate = 1"); })
            ->pluck('u.employee_id');    
        return $matched_emp_ids;
    }

    public function index(Request $request) {
        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);
        $errors = session('errors');
        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $tags = Tag::all(["id","name"])->sortBy("name")->toArray();
        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->dd_superv = isset($old['dd_superv']) ? $old['dd_superv'] : null; 
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
            $request->orgCheck = isset($old['orgCheck']) ? $old['orgCheck'] : null;
            $request->userCheck = isset($old['userCheck']) ? $old['userCheck'] : null;
            $old_selected_emp_ids = isset($old['selected_emp_ids']) ? json_decode($old['selected_emp_ids']) : [];
            $old_selected_org_nodes = isset($old['selected_org_nodes']) ? json_decode($old['selected_org_nodes']) : [];
            $request->edd_level0 = isset($old['edd_level0']) ? $old['edd_level0'] : null;
            $request->edd_level1 = isset($old['edd_level1']) ? $old['edd_level1'] : null;
            $request->edd_level2 = isset($old['edd_level2']) ? $old['edd_level2'] : null;
            $request->edd_level3 = isset($old['edd_level3']) ? $old['edd_level3'] : null;
            $request->edd_level4 = isset($old['edd_level4']) ? $old['edd_level4'] : null;
            $request->edd_superv = isset($old['edd_superv']) ? $old['edd_superv'] : null; 
            $eold_selected_emp_ids = isset($old['eselected_emp_ids']) ? json_decode($old['eselected_emp_ids']) : []; 
            $eold_selected_org_nodes = isset($old['eselected_org_nodes']) ? json_decode($old['eselected_org_nodes']) : []; 
        } 
        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'dd_superv' => $request->dd_superv, 
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
                'orgCheck' => $request->orgCheck,
                'userCheck' => $request->userCheck,
            ]);
        }
        if ($request->ebtn_search) {
            session()->put('_old_input', [
                'edd_level0' => $request->edd_level0,
                'edd_level1' => $request->edd_level1,
                'edd_level2' => $request->edd_level2,
                'edd_level3' => $request->edd_level3,
                'edd_level4' => $request->edd_level4,
                'edd_superv' => $request->edd_superv, 
                'ecriteria' => $request->ecriteria,
                'esearch_text' => $request->esearch_text,
                'eorgCheck' => $request->eorgCheck,
                'euserCheck' => $request->euserCheck,
            ]);
        }
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $request->session()->flash('dd_superv', $request->dd_superv); 
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        $request->session()->flash('edd_level0', $request->edd_level0);
        $request->session()->flash('edd_level1', $request->edd_level1);
        $request->session()->flash('edd_level2', $request->edd_level2);
        $request->session()->flash('edd_level3', $request->edd_level3);
        $request->session()->flash('edd_level4', $request->edd_level4);
        $request->session()->flash('edd_superv', $request->edd_superv); 
        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, "");
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql
            ->orderBy('employee_id')
            ->pluck('employee_id');        
        $criteriaList = $this->search_criteria_list();
        $roles = DB::table('roles')
            ->whereIntegerInRaw('id', [3, 4])
            ->pluck('longname', 'id');
        $currentView = $request->segment(2);
        return view('shared.goalbank.index', compact('criteriaList','matched_emp_ids', 'old_selected_emp_ids', 'old_selected_org_nodes', 'roles', 'goalTypes', 'mandatoryOrSuggested', 'tags', 'currentView') );
    }

    public function getgoalorgs(Request $request, $goal_id) {
        if ($request->ajax()) {
            $query = GoalBankOrg::from('goal_bank_orgs AS b')
                ->where('b.goal_id', $goal_id)
                ->where('b.version', 2)
                ->join('employee_demo_tree AS t', 't.id', 'b.orgid')
                ->when( $request->dd_level0, function ($q) use($request) { return $q->where('t.organization_key', '=', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('t.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('t.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('t.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('t.level4_key', $request->dd_level4); })
                ->select (
                    'b.orgid AS orgid',
                    't.organization AS organization',
                    't.level1_program AS level1_program',
                    't.level2_division AS level2_division',
                    't.level3_branch AS level3_branch',
                    't.level4 AS level4',
                    'b.inherited AS inherited',
                    'b.goal_id',
                    'b.id'
                )
                ->orderBy('t.organization')
                ->orderBy('t.level1_program')
                ->orderBy('t.level2_division')
                ->orderBy('t.level3_branch')
                ->orderBy('t.level4');
            return Datatables::of($query)
                ->editColumn('inherited', function ($row) { return $row->inherited == 1 ? "Yes" : "No"; })
                ->addIndexColumn()
                ->addcolumn('action', function($row) {
                    $btn = '<a href="/'.request()->segment(1).'/goalbank/deleteorg/' . $row->id . '" class="btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete Org" id="delete_org" value="'. $row->id .'"><i class="fa fa-trash"></i></a>';
                    return $btn;
                })
                ->rawColumns(['goal_type_name', 'created_by', 'action'])
                ->make(true);
        }
    }

    public function deleteorg(Request $request, $id) {
        $query = GoalBankOrg::where('id', $id)
        ->where('version', 2)
        ->delete();
        return redirect()->back();
    }

    public function deleteindividual(Request $request, $id) {
        $query = DB::table('goals_shared_with')
        ->where('id', $id)
        ->delete();
        return redirect()->back();
    }

    public function editpage(Request $request, $id) {
        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);
        $errors = session('errors');
        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $old_selected_inherited = []; // $request->old_selected_inherited ? json_decode($request->selected_inherited) : [];
        $eold_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $eold_selected_inherited = []; // $request->old_selected_inherited ? json_decode($request->selected_inherited) : [];
        $tags = Tag::all(["id","name"])->sortBy("name")->toArray();
        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->dd_superv = isset($old['dd_superv']) ? $old['dd_superv'] : null; 
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
            $request->orgCheck = isset($old['orgCheck']) ? $old['orgCheck'] : null;
            $request->userCheck = isset($old['userCheck']) ? $old['userCheck'] : null;
            $old_selected_emp_ids = isset($old['selected_emp_ids']) ? json_decode($old['selected_emp_ids']) : [];
            $old_selected_org_nodes = isset($old['selected_org_nodes']) ? json_decode($old['selected_org_nodes']) : [];
            $old_selected_inherited = isset($old['selected_inherited']) ? json_decode($old['selected_inherited']) : [];
            $request->edd_level0 = isset($old['edd_level0']) ? $old['edd_level0'] : null;
            $request->edd_level1 = isset($old['edd_level1']) ? $old['edd_level1'] : null;
            $request->edd_level2 = isset($old['edd_level2']) ? $old['edd_level2'] : null;
            $request->edd_level3 = isset($old['edd_level3']) ? $old['edd_level3'] : null;
            $request->edd_level4 = isset($old['edd_level4']) ? $old['edd_level4'] : null;
            $request->edd_superv = isset($old['edd_superv']) ? $old['edd_superv'] : null;
            $eold_selected_emp_ids = isset($old['eselected_emp_ids']) ? json_decode($old['eselected_emp_ids']) : [];
            $eold_selected_org_nodes = isset($old['eselected_org_nodes']) ? json_decode($old['eselected_org_nodes']) : [];
            $eold_selected_inherited = isset($old['eselected_inherited']) ? json_decode($old['eselected_inherited']) : [];
        } 
        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'dd_superv' => $request->dd_superv, 
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
                'orgCheck' => $request->orgCheck,
                'userCheck' => $request->userCheck,
            ]);
        }
        if ($request->ebtn_search) {
            session()->put('_old_input', [
                'edd_level0' => $request->edd_level0,
                'edd_level1' => $request->edd_level1,
                'edd_level2' => $request->edd_level2,
                'edd_level3' => $request->edd_level3,
                'edd_level4' => $request->edd_level4,
                'edd_superv' => $request->edd_superv, 
                'ecriteria' => $request->ecriteria,
                'esearch_text' => $request->esearch_text,
                'eorgCheck' => $request->eorgCheck,
                'euserCheck' => $request->euserCheck,
            ]);
        }
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $request->session()->flash('dd_superv', $request->dd_superv); 
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        $request->session()->flash('edd_level0', $request->edd_level0);
        $request->session()->flash('edd_level1', $request->edd_level1);
        $request->session()->flash('edd_level2', $request->edd_level2);
        $request->session()->flash('edd_level3', $request->edd_level3);
        $request->session()->flash('edd_level4', $request->edd_level4);
        $request->session()->flash('edd_superv', $request->edd_superv); 
        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, "");
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql
            ->orderBy('u.employee_id')
            ->pluck('u.employee_id');        
        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();
        $roles = DB::table('roles')
            ->whereIntegerInRaw('id', [3, 4])
            ->pluck('longname', 'id');
        $goal_id = $id;
        $goaldetail = Goal::withoutGlobalScopes()->find($request->id);
        $type_desc_arr = array();
        foreach($goalTypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {                
                $item = "<b>" . $goalType['name'] . " Goals</b> ". str_replace($goalType['name'] . " Goals","",$goalType['description']);
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>',$type_desc_arr);
        $currentView = $request->segment(3);
        return view('shared.goalbank.editgoal', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'old_selected_emp_ids', 'old_selected_org_nodes', 'eold_selected_org_nodes', 'old_selected_inherited', 'eold_selected_inherited', 'roles', 'goalTypes', 'mandatoryOrSuggested', 'tags', 'goaldetail', 'request', 'goal_id', 'type_desc_str', 'currentView') );
    }

    public function editone(Request $request, $id) {
        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);
        $this->getDropdownValues($amandatoryOrSuggested);
        $errors = session('errors');
        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $tags = Tag::all(["id","name"])->sortBy("name")->toArray();
        $aold_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $aold_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $atags = Tag::all(["id","name"])->toArray();
        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->dd_superv = isset($old['dd_superv']) ? $old['dd_superv'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
            $request->orgCheck = isset($old['orgCheck']) ? $old['orgCheck'] : null;
            $request->userCheck = isset($old['userCheck']) ? $old['userCheck'] : null;
            $old_selected_emp_ids = isset($old['selected_emp_ids']) ? json_decode($old['selected_emp_ids']) : [];
            $old_selected_org_nodes = isset($old['selected_org_nodes']) ? json_decode($old['selected_org_nodes']) : [];
            $request->add_level0 = isset($old['add_level0']) ? $old['add_level0'] : null;
            $request->add_level1 = isset($old['add_level1']) ? $old['add_level1'] : null;
            $request->add_level2 = isset($old['add_level2']) ? $old['add_level2'] : null;
            $request->add_level3 = isset($old['add_level3']) ? $old['add_level3'] : null;
            $request->add_level4 = isset($old['add_level4']) ? $old['add_level4'] : null;
            $request->add_superv = isset($old['add_superv']) ? $old['add_superv'] : null;
            $request->asearch_text = isset($old['asearch_text']) ? $old['asearch_text'] : null;
            $request->aorgCheck = isset($old['aorgCheck']) ? $old['aorgCheck'] : null;
            $request->auserCheck = isset($old['auserCheck']) ? $old['auserCheck'] : null;
            $aold_selected_emp_ids = isset($old['aselected_emp_ids']) ? json_decode($old['aselected_emp_ids']) : [];
            $aold_selected_org_nodes = isset($old['aselected_org_nodes']) ? json_decode($old['aselected_org_nodes']) : [];
        } 
        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'dd_superv' => $request->dd_superv,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
                'orgCheck' => $request->orgCheck,
                'userCheck' => $request->userCheck,
            ]);
        }
        if ($request->ebtn_search) {
            session()->put('_old_input', [
                'add_level0' => $request->add_level0,
                'add_level1' => $request->add_level1,
                'add_level2' => $request->add_level2,
                'add_level3' => $request->add_level3,
                'add_level4' => $request->add_level4,
                'add_superv' => $request->add_superv,
                'acriteria' => $request->acriteria,
                'asearch_text' => $request->asearch_text,
                'aorgCheck' => $request->aorgCheck,
                'auserCheck' => $request->auserCheck,
            ]);
        }
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $request->session()->flash('dd_superv', $request->dd_superv); 
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        $request->session()->flash('add_level0', $request->add_level0);
        $request->session()->flash('add_level1', $request->add_level1);
        $request->session()->flash('add_level2', $request->add_level2);
        $request->session()->flash('add_level3', $request->add_level3);
        $request->session()->flash('add_level4', $request->add_level4);
        $request->session()->flash('add_superv', $request->add_superv); 
        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, "");
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql
            ->orderBy('employee_id')
            ->pluck('employee_id');        
        $ademoWhere = $this->baseFilteredWhere($request, "a");
        $asql = clone $ademoWhere; 
        $amatched_emp_ids = $asql
            ->orderBy('employee_id')
            ->pluck('employee_id');        
        $criteriaList = $this->search_criteria_list();
        $acriteriaList = $this->search_criteria_list();
        $goal_id = $id;
        $supervisorList = $this->supervisor_list();
        $goaldetail = Goal::withoutGlobalScopes()->find($request->id);
        $type_desc_arr = array();
        foreach($goalTypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {                
                $item = "<b>" . $goalType['name'] . " Goals</b> ". str_replace($goalType['name'] . " Goals","",$goalType['description']);
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>',$type_desc_arr);
        $currentView = $request->segment(3);
        return view('shared.goalbank.editone', compact('criteriaList', 'acriteriaList', 'matched_emp_ids', 'amatched_emp_ids', 'old_selected_emp_ids', 'aold_selected_emp_ids', 'old_selected_org_nodes', 'aold_selected_org_nodes', 'goalTypes', 'mandatoryOrSuggested', 'amandatoryOrSuggested', 'tags', 'atags', 'goaldetail', 'request', 'goal_id', 'type_desc_str', 'currentView', 'supervisorList') );    
    }

    public function editdetails(Request $request, $id) {
        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);
        $this->getDropdownValues($amandatoryOrSuggested);
        $errors = session('errors');
        $tags = Tag::all(["id","name"])->sortBy("name")->toArray();
        $goal_id = $id;
        $goaldetail = Goal::withoutGlobalScopes()->find($request->id);
        $type_desc_arr = array();
        foreach($goalTypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {                
                $item = "<b>" . $goalType['name'] . " Goals</b> ". str_replace($goalType['name'] . " Goals","",$goalType['description']);
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>',$type_desc_arr);
        $currentView = $request->segment(3);
        return view('shared.goalbank.editdetails', compact('goalTypes', 'mandatoryOrSuggested', 'amandatoryOrSuggested', 'tags', 'goaldetail', 'request', 'goal_id', 'type_desc_str', 'currentView') );
    }

    public function savenewgoal(Request $request) {
        if ($request->input('title') == '' || $request->input('what') == '' || $request->input('tag_ids') == '') {
            if($request->input('title') == '') {
                $request->session()->flash('title_miss', 'The title field is required');
            } elseif($request->input('tag_ids') == '') {
                $request->session()->flash('tags_miss', 'The tags field is required');
            }   elseif($request->input('what') == '') {
                $request->session()->flash('what_miss', 'The description field is required');
            }               
            return \Redirect::route('sysadmin.goalbank')->with('message', " There are one or more errors on the page. Please review and try again.");
        }  
        $current_user = User::find(Auth::id());

        $emailit = true;
        if($request->input('emailit') == '0'){
            $emailit = false;
        }

        $resultrec = Goal::withoutGlobalScopes()
        ->create(
            [ 'goal_type_id' => $request->input('goal_type_id')
            , 'is_library' => true
            , 'is_shared' => true
            , 'title' => $request->input('title')
            , 'what' => $request->input('what')
            , 'measure_of_success' => $request->input('measure_of_success')
            , 'start_date' => $request->input('start_date')
            , 'target_date' => $request->input('target_date')
            , 'user_id' => $current_user->id
            , 'created_by' => $current_user->id
            , 'by_admin' => 1
            , 'is_mandatory' => $request->input('is_mandatory')
            , 'display_name' => $request->input('display_name')
            ]
        );
        $resultrec->tags()->sync($request->tag_ids);
        $employee_ids = ($request->userCheck) ? $request->userCheck : [];
        $notify_audiences = [];
        if($request->opt_audience == "byEmp") {
            $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
            $toRecipients = EmployeeDemo::from('employee_demo AS d')
                ->select('u.id')
                ->join('users AS u', 'd.employee_id', 'u.employee_id')
                ->whereIn('d.employee_id', $selected_emp_ids )
                ->distinct()
                ->select ('u.id')
                ->orderBy('d.employee_name')
                ->get() ;
            foreach ($toRecipients as $newId) {
                $result = \DB::table('goals_shared_with')
                    ->updateOrInsert(
                        [
                            'goal_id' => $resultrec->id,
                            'user_id' => $newId->id
                        ],
                        [
                        ]
                    );
            }
            $notify_audiences = $selected_emp_ids;
        }
        if($request->opt_audience == "byOrg") {
            $selected_org_nodes = $request->eorgCheck ? $request->eorgCheck : [];
            $selected_inherited = $request->einheritedCheck ? $request->einheritedCheck : [];
            $organizationList = EmployeeDemoTree::select('id')
                ->whereIn('id', $selected_org_nodes)
                ->orWhereIn('level4_key', $selected_org_nodes)
                ->distinct()
                ->orderBy('id')
                ->get();
            $inheritedList = EmployeeDemoTree::select('id')
                ->whereIn('id', $selected_inherited)
                ->orWhereIn('level4_key', $selected_inherited)
                ->distinct()
                ->orderBy('id')
                ->get();
            foreach($organizationList as $org1) {
                $result = GoalBankOrg::create(
                    [
                        'goal_id' => $resultrec->id,
                        'version' => '2', 
                        'orgid' => $org1->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s') 
                    ]
                );
                if(!$result){
                    break;
                }
            }
            foreach($inheritedList as $org1) {
                $result = GoalBankOrg::updateOrCreate(
                    [
                        'goal_id' => $resultrec->id,
                        'version' => '2', 
                        'orgid' => $org1->id,
                    ],
                    [
                        'inherited' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s') 
                    ],
                );
                if(!$result){
                    break;
                }
            }
            $notify_audiences_static = $this->get_employees_by_selected_org_nodes($selected_org_nodes);
            $notify_audiences_inherited = $this->get_employees_by_selected_inherited($selected_inherited);
            $notify_audiences = array_unique(array_merge($notify_audiences_static, $notify_audiences_inherited), SORT_REGULAR);
        }
        // notify_on_dashboard when new goal added
        $this->notify_on_dashboard($resultrec, $notify_audiences, $emailit);
        return redirect()->route(request()->segment(1).'.goalbank')
            ->with('success', 'Create new goal bank successful.');
    }

    public function loadOrganizationTree(Request $request, $index) {
        switch ($index) {
            case 2:
                $option = 'e';
                break;
            case 3:
                $option = 'a';
                break;
            default:
                $option = '';
                break;
        }
        $demoWhere = $this->baseFilteredWhere($request, $option);
        // Employee Count by Organization
        $treecount0 = clone $demoWhere; 
        $treecount1 = clone $demoWhere; 
        $treecount2 = clone $demoWhere; 
        $treecount3 = clone $demoWhere; 
        $treecount4 = clone $demoWhere; 
        $countByOrg = $treecount0->groupBy('treeid')->select('organization_key as treeid', DB::raw("COUNT(*) as count_row"))
            ->union( $treecount1->groupBy('treeid')->select('level1_key as treeid', DB::raw("COUNT(*) as count_row")) )
            ->union( $treecount2->groupBy('treeid')->select('level2_key as treeid', DB::raw("COUNT(*) as count_row")) )
            ->union( $treecount3->groupBy('treeid')->select('level3_key as treeid', DB::raw("COUNT(*) as count_row")) )
            ->union( $treecount4->groupBy('treeid')->select('level4_key as treeid', DB::raw("COUNT(*) as count_row")) )
            ->pluck('count_row', 'treeid'); 
       $orgs = EmployeeDemoTree::whereIn('id', array_keys($countByOrg->toArray()))
            ->orderBy('organization')
            ->orderBy('level1_program')
            ->orderBy('level2_division')
            ->orderBy('level3_branch')
            ->orderBy('level4')
            ->get()
            ->toTree();
        // Employee ID by Tree ID
        $empIdsByOrgId = [];
        $sql = clone $demoWhere; 
        $rows = $sql->select('orgid AS id', 'employee_id')
            ->groupBy('orgid', 'employee_id')
            ->orderBy('orgid')->orderBy('employee_id')
            ->get();
        $empIdsByOrgId = $rows->groupBy('orgid')->all();
        $authorizedLevel = null;
        if($request->{$option.'dd_level0'}) { $authorizedLevel = 0; };
        if($request->{$option.'dd_level1'}) { $authorizedLevel = 1; };
        if($request->{$option.'dd_level2'}) { $authorizedLevel = 2; };
        if($request->{$option.'dd_level3'}) { $authorizedLevel = 3; };
        if($request->{$option.'dd_level4'}) { $authorizedLevel = 4; };
        if($request->ajax()){
            switch ($index) {
                case 2:
                    $eorgs = $orgs;
                    $ecountByOrg = $countByOrg;
                    $eempIdsByOrgId = $empIdsByOrgId;
                    return view('shared.goalbank.partials.recipient-tree2', compact('eorgs','ecountByOrg','eempIdsByOrgId', 'authorizedLevel') );
                    break;
                case 3:
                    $aorgs = $orgs;
                    $acountByOrg = $countByOrg;
                    $aempIdsByOrgId = $empIdsByOrgId;
                    return view('shared.goalbank.partials.arecipient-tree', compact('aorgs','acountByOrg','aempIdsByOrgId', 'authorizedLevel') );
                    break;
                default:
                    return view('shared.goalbank.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId', 'authorizedLevel') );
                    break;
            }
        }
    }

    public function getDatatableEmployees(Request $request, $option = null) {
        if($request->ajax()){
            $demoWhere = $this->baseFilteredWhere($request, $option);
            $sql = clone $demoWhere; 
            $employees = $sql->selectRaw(" 
                    u.employee_id, 
                    u.employee_name, 
                    u.jobcode_desc, 
                    u.employee_email, 
                    u.organization, 
                    u.level1_program, 
                    u.level2_division, 
                    u.level3_branch, 
                    u.level4, 
                    u.deptid,
                    CASE WHEN u.isSupervisor = 1 THEN 'Yes' ELSE 'No' END AS isSupervisor,
                    CASE WHEN u.isDelegate = 1 THEN 'Yes' ELSE 'No' END AS isDelegate
                ")
                ->when($request->{$option.'dd_superv'} == 'sup', function($q) { return $q->whereRaw("(u.isSupervisor = 1 OR u.isDelegate = 1)"); }) 
                ->when($request->{$option.'dd_superv'} == 'non', function($q) { return $q->whereRaw("NOT u.isSupervisor = 1 AND NOT u.isDelegate = 1"); }); 
            return Datatables::of($employees)
                ->addColumn($option.'select_users', static function ($employee) use ($option) { 
                    return '<input pid="1335" type="checkbox" id="'.$option.'userCheck'.  
                        $employee->employee_id.'" name="'.$option.'userCheck[]" value="'.$employee->employee_id.'" class="dt-body-center">'; 
                }) 
                ->rawColumns([$option.'select_users', 'action']) 
                ->make(true); 
        }
    }

    public function addnewgoal(Request $request) {
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $current_user = User::find(Auth::id());
        $organizationList = EmployeeDemoTree::select('id')
            ->whereIn('id', $selected_org_nodes)
            ->orWhereIn('level4_key', $selected_org_nodes)
            ->distinct()
            ->orderBy('id')
            ->get();

            $resultrec = Goal::create(
                [
                    'goal_type_id' => $request->input('goal_type_id'), 
                    'is_library' => true, 'is_shared' => true, 
                    'title' => $request->input('title'), 
                    'what' => $request->input('what'), 
                    'measure_of_success' => $request->input('measure_of_success'), 
                    'start_date' => $request->input('start_date'), 
                    'target_date' => $request->input('target_date'), 
                    'measure_of_success' => $request->input('measure_of_success'), 
                    'user_id' => $current_user->id, 
                    'created_by' => $current_user->id, 
                    'by_admin' => 1, 
                    'display_name' => $request->input('display_name')
                ]
                );
            $resultrec->tags()->sync($request->tag_ids);
            foreach($organizationList as $org1) {
                $result = GoalBankOrg::create(
                    [
                        'goal_id' => $resultrec->id, 
                        'version' => '2', 
                        'orgid' => $org1->id, 
                        'created_at' => date('Y-m-d H:i:s'), 
                        'updated_at' => date('Y-m-d H:i:s') 
                    ]
                );
                if(!$result){
                    break;
                }
            }
        return redirect()->route(request()->segment(1).'.goalbank.index')
            ->with('success', 'Add new goal successful.');
    }

    public function updategoal(Request $request) {
        $emailit = true;
        if($request->input('emailit') == '0'){
            $emailit = false;
        }

        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $selected_inherited = $request->selected_inherited ? json_decode($request->selected_inherited) : [];
        // Get the old employees listing 
        $old_ee_ids =  GoalSharedWith::join('users', 'goals_shared_with.user_id', 'users.id')
                                ->where('goal_id', $request->goal_id)->distinct()->pluck('users.employee_id')->toArray();
        $old_org_ee_ids = UserDemoJrForGoalbankView::from('user_demo_jr_for_goalbank_view AS u')
                                ->join('goal_bank_orgs', 'u.orgid', 'goal_bank_orgs.orgid')
                                ->where('goal_bank_orgs.goal_id', $request->goal_id)
                                ->pluck('u.employee_id')
                                ->toArray(); 
        $organizationList = EmployeeDemoTree::select('id')
            ->whereIn('id', $selected_org_nodes)
            ->orWhereIn('level4_key', $selected_org_nodes)
            ->distinct()
            ->orderBy('id')
            ->get();
        $inheritedList = EmployeeDemoTree::select('id')
            ->whereIn('id', $selected_inherited)
            ->orWhereIn('level4_key', $selected_inherited)
            ->distinct()
            ->orderBy('id')
            ->get();
        $resultrec = Goal::withoutGlobalScopes()->findorfail($request->goal_id);
        foreach($organizationList as $org1) {
            $result = DB::table('goal_bank_orgs')
            ->updateorinsert(
                [
                    'goal_id' => $resultrec->id,
                    'version' => '2',
                    'orgid' => $org1->id
                ],
                [
                    'inherited' => '0',
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
            if(!$result){
                break;
            }
        }
        foreach($inheritedList as $org1) {
            $result = DB::table('goal_bank_orgs')
            ->updateorinsert(
                [
                    'goal_id' => $resultrec->id,
                    'version' => '2',
                    'orgid' => $org1->id
                ],
                [
                    'inherited' => '1',
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
            if(!$result){
                break;
            }
        }
        // call notify_on_dashboard for the newly added emplid of the goal         
        $new_org_ee_ids_static = $this->get_employees_by_selected_org_nodes($selected_org_nodes);
        $new_org_ee_ids_inherited = $this->get_employees_by_selected_inherited($selected_inherited);
        $new_org_ee_ids = array_unique(array_merge($new_org_ee_ids_static, $new_org_ee_ids_inherited), SORT_REGULAR);
        $notify_audiences = array_diff($new_org_ee_ids, $old_ee_ids, $old_org_ee_ids);        
        $this->notify_on_dashboard($resultrec, $notify_audiences, $emailit);
        return redirect()->route(request()->segment(1).'.goalbank.manageindex')
            ->with('success', 'Goal update successful.');
    }

    public function updategoalone(Request $request, $id) {
        $emailit = true;
        if($request->input('emailit') == '0'){
            $emailit = false;
        }
        
        $aselected_emp_ids = $request->auserCheck ? $request->auserCheck : [];
        // Get the old employees listing 
        $old_ee_ids =  GoalSharedWith::join('users', 'goals_shared_with.user_id', 'users.id')
                                ->where('goal_id', $id)->distinct()->pluck('users.employee_id')->toArray();
        $old_org_ee_ids = UserDemoJrForGoalbankView::from('user_demo_jr_for_goalbank_view AS u')
                                ->join('goal_bank_orgs', 'u.orgid', 'goal_bank_orgs.orgid')
                                ->where('goal_bank_orgs.goal_id', $id)
                                ->pluck('u.employee_id')
                                ->toArray(); 
        $aselected_org_nodes = $request->aselected_org_nodes ? json_decode($request->aselected_org_nodes) : [];
        $current_user = Auth::id();
        $resultrec = Goal::withoutGlobalScopes()->findorfail( $id );
        $aemployee_ids = ($request->aselected_emp_ids) ? json_decode($request->aselected_emp_ids) : [];
        $toRecipients = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.employee_id', 'users.employee_id')
            ->whereIn('employee_demo.employee_id', $aselected_emp_ids)
            ->distinct()
            ->select ('users.id')
            ->orderBy('employee_demo.employee_name')
            ->get() ;
        foreach ($toRecipients as $newId) {
            $result = DB::table('goals_shared_with')
                ->updateOrInsert(
                    ['goal_id' => $resultrec->id
                    , 'user_id' => $newId->id
                    ],
                    []
                );
        }
        // call notify_on_dashboard for the newly added emplid of the goal 
        $notify_audiences = array_diff($aselected_emp_ids, $old_ee_ids, $old_org_ee_ids);
        $this->notify_on_dashboard($resultrec, $notify_audiences, $emailit);
        return redirect()->route(request()->segment(1).'.goalbank.manageindex')
            ->with('success', 'Goal update successful.');
    }

    public function updategoaldetails(Request $request, $id) {
        $resultrec = Goal::withoutGlobalScopes()->findorfail( $id );
        if ($request->title == '' || $request->what== ''  || $request->tag_ids== '') {
            if($request->title == '') {
                $request->session()->flash('title_miss', 'The title field is required');
            } elseif($request->what == '') {
                $request->session()->flash('what_miss', 'The description field is required');
            } elseif($request->tag_ids == '') {
                $request->session()->flash('tags_miss', 'The tags field is required');
            }                 
            return \Redirect::route('sysadmin.goalbank.editdetails', [$id])->with('message', " There are one or more errors on the page. Please review and try again.");
        } 
        $resultrec->update(
            [
                'goal_type_id' => $request->input('goal_type_id'), 
                'title' => $request->input('title'), 
                'what' => $request->input('what'), 
                'measure_of_success' => $request->input('measure_of_success'), 
                'start_date' => $request->input('start_date'), 
                'target_date' => $request->input('target_date'), 
                'is_mandatory' => $request->input('is_mandatory'), 
                'display_name' => $request->input('display_name')
            ]
        );
        $resultrec->tags()->sync($request->tag_ids);
        return redirect()->route(request()->segment(1).'.goalbank.manageindex')
            ->with('success', 'Goal update successful.');
    }

    public function getUsers(Request $request) {
        $users =  User::whereRaw("name like '%{$request->search}%'") 
            ->whereNotNull('email')->paginate(); 
        return ['data'=> $users];
    }

    public function getEmployees(Request $request, $id, $option = null) { 
        $employees = \DB::select("
                SELECT employee_id, employee_name, employee_email, jobcode_desc
                FROM employee_demo USE INDEX (idx_employee_demo_orgid_employeeid_emplrecord) 
                WHERE orgid = {$id}
                    AND date_deleted IS NULL
                ORDER BY employee_name
            ");
        $parent_id = $id;
        $page = 'shared.goalbank.partials.'.$option.'employee'; 
        if($option == 'e') { 
            $eparent_id = $parent_id;
            $eemployees = $employees;
        } 
        if($option == 'a') { 
            $aparent_id = $parent_id;
            $aemployees = $employees;
        } 
        return view($page, compact($option.'parent_id', $option.'employees') ); 
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

    protected function supervisor_list() {
        return [
            'all' => 'All',
            'sup' => 'Has Reports in PDP', 
            'non'=> 'Does Not Have Reports',
        ]; 
    } 
 
    protected function baseFilteredWhere($request, $option = null) { 
        $authId = Auth::id(); 
        return UserDemoJrForGoalbankView::from('user_demo_jr_for_goalbank_view AS u') 
            ->whereNull('u.date_deleted') 
            ->when("{$request->{$option.'dd_level0'}}", function($q) use($request, $option) { return $q->whereRaw("u.organization_key = {$request->{$option.'dd_level0'}}"); }) 
            ->when("{$request->{$option.'dd_level1'}}", function($q) use($request, $option) { return $q->whereRaw("u.level1_key = {$request->{$option.'dd_level1'}}"); }) 
            ->when("{$request->{$option.'dd_level2'}}", function($q) use($request, $option) { return $q->whereRaw("u.level2_key = {$request->{$option.'dd_level2'}}"); }) 
            ->when("{$request->{$option.'dd_level3'}}", function($q) use($request, $option) { return $q->whereRaw("u.level3_key = {$request->{$option.'dd_level3'}}"); }) 
            ->when("{$request->{$option.'dd_level4'}}", function($q) use($request, $option) { return $q->whereRaw("u.level4_key = {$request->{$option.'dd_level4'}}"); }) 
            ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" != 'all', function($q) use($request, $option) { return $q->whereRaw("u.{$request->{$option.'criteria'}} like '%{$request->{$option.'search_text'}}%'"); }) 
            ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" == 'all', function($q) use($request, $option) { return $q->whereRaw("(u.employee_id LIKE '%{$request->{$option.'search_text'}}%' OR u.employee_name LIKE '%{$request->{$option.'search_text'}}%' OR u.jobcode_desc LIKE '%{$request->{$option.'search_text'}}%' OR u.deptid LIKE '%{$request->{$option.'search_text'}}%')"); }); 
    } 
 
    protected function baseFilteredSQLs($request, $option = null) {
        $demoWhere = $this->baseFilteredWhere($request, $option);
        $sql_level0 = clone $demoWhere; 
        $sql_level0->join('employee_demo_tree AS o', function($join) {
            $join->on('u.organization_key', 'o.organization_key')
                ->where('o.level', 0);
            });
        $sql_level1 = clone $demoWhere; 
        $sql_level1->join('employee_demo_tree AS o', function($join) {
            $join->on('u.organization_key', 'o.organization_key')
                ->on('u.level1_key', 'o.level1_key')
                ->where('o.level', 1);
            });
        $sql_level2 = clone $demoWhere; 
        $sql_level2->join('employee_demo_tree AS o', function($join) {
            $join->on('u.organization_key', 'o.organization_key')
                ->on('u.level1_key', 'o.level1_key')
                ->on('u.level2_key', 'o.level2_key')
                ->where('o.level', 2);    
            });    
        $sql_level3 = clone $demoWhere; 
        $sql_level3->join('employee_demo_tree AS o', function($join) {
            $join->on('u.organization_key', 'o.organization_key')
                ->on('u.level1_key', 'o.level1_key')
                ->on('u.level2_key', 'o.level2_key')
                ->on('u.level3_key', 'o.level3_key')
                ->where('o.level',3);    
            });
        $sql_level4 = clone $demoWhere; 
        $sql_level4->join('employee_demo_tree AS o', function($join) {
            $join->on('u.organization_key', 'o.organization_key')
                ->on('u.level1_key', 'o.level1_key')
                ->on('u.level2_key', 'o.level2_key')
                ->on('u.level3_key', 'o.level3_key')
                ->on('u.level4_key', 'o.level4_key')
                ->where('o.level', 4);
            });
        return  [$sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function manageindex(Request $request) {
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
        $criteriaList = array(
            'all' => 'All',
            'gt' => 'Goal Title', 
            'cby'=> 'Created By',
        );
        $currentView = $request->segment(3);
        return view('shared.goalbank.manageindex', compact ('request', 'criteriaList', 'currentView'));
    }

    public function managegetList(Request $request) {
        if ($request->ajax()) {
            $query = Goal::withoutGlobalScopes()
                ->leftjoin('users as cu', 'cu.id', 'goals.created_by')
                ->leftjoin('employee_demo as ced', 'ced.employee_id', 'cu.employee_id')
                ->leftjoin('employee_demo_tree as edt', 'edt.id', 'ced.orgid')
                ->where('is_library', \DB::raw(1))
                ->whereIn('by_admin', [\DB::raw(1), \DB::raw(2)])
                ->when( $request->search_text && $request->criteria == 'all', function ($q) use($request) {
                    return $q->where(function($query) use ($request) { 
                        return $query->whereRaw("(goals.title LIKE '%".$request->search_text."%')")
                            ->orWhereRaw("((goals.display_name IS NULL AND ced.employee_name LIKE '%".$request->search_text."%') OR (NOT goals.display_name IS NULL AND goals.display_name LIKE '%".$request->search_text."%'))");
                    });
                })
                ->when( $request->search_text && $request->criteria == 'gt', function ($q) use($request) {
                    return $q->whereRaw("(goals.title LIKE '%".$request->search_text."%')");
                })
                ->when( $request->search_text && $request->criteria == 'cby', function ($q) use($request) {
                    return $q->whereRaw("((goals.display_name IS NULL AND ced.employee_name LIKE '%".$request->search_text."%') OR (NOT goals.display_name IS NULL AND goals.display_name LIKE '%".$request->search_text."%'))");
                })
                ->select
                    (
                        'goals.id',
                        'goals.title',
                        'goals.created_at',
                        'goals.is_mandatory',
                        'goals.display_name',
                        'ced.employee_name AS creator_name',
                        'edt.organization AS ced_organization',
                    )
                ->addSelect(['audience' =>
                    GoalSharedWith::whereColumn('goal_id', 'goals.id')
                        ->selectRAW('count(distinct id)')
                ] )
                ->addSelect(['org_audience' => 
                    GoalBankOrg::whereColumn('goal_id', 'goals.id')
                        ->where('version', \DB::raw(2))
                        ->whereNotNull('orgid')
                        ->selectRAW('count(distinct goal_bank_orgs.id)')
                ] )
                ->addSelect(['goal_type_name' => GoalType::select('name')->whereColumn('goal_type_id', 'goal_types.id')->limit(1)]);
            return Datatables::of($query)
                ->addIndexColumn()
                ->addcolumn('click_title', function ($row) {
                    return '<a href="'.route(request()->segment(1).'.goalbank.editdetails', $row->id).'" aria-label="Edit Goal Details - "'.$row->title.' value="'.$row->id.'">'.$row->title.'</a>';
                })
                ->addcolumn('click_goal_type', function ($row) {
                    return '<a href="'.route(request()->segment(1).'.goalbank.editdetails', $row->id).'" aria-label="Edit Goal Details - "'.$row->goal_type_name.' value="'.$row->id.'">'.$row->goal_type_name.'</a>';
                })
                ->addcolumn('click_display_name', function ($row) {
                    if ($row->display_name) {
                        return '<a href="'.route(request()->segment(1).'.goalbank.editdetails', $row->id).'" aria-label="Edit Goal Details - "'.$row->display_name.' value="'.$row->id.'">'.$row->display_name.'</a>';
                    } else {
                        return '<a href="'.route(request()->segment(1).'.goalbank.editdetails', $row->id).'" aria-label="Edit Goal Details - "'.$row->creator_name.' value="'.$row->id.'">'.$row->creator_name.'</a>';
                    }
                })
                ->addcolumn('click_creator_name', function ($row) {
                    return '<a href="'.route(request()->segment(1).'.goalbank.editdetails', $row->id).'" aria-label="Edit Goal Details - "'.$row->creator_name.' value="'.$row->id.'">'.$row->creator_name.'</a>';
                })
                ->addcolumn('click_creator_organization', function ($row) {
                    return '<a href="'.route(request()->segment(1).'.goalbank.editdetails', $row->id).'" aria-label="Edit Goal Details - "'.$row->ced_organization.' value="'.$row->id.'">'.$row->ced_organization.'</a>';
                })
                ->addColumn('mandatory', function ($row) {
                    return '<a href="'.route(request()->segment(1).'.goalbank.editdetails', $row->id).'" aria-label="Edit Goal Details - "'.($row->is_mandatory ? "Mandatory" : "Suggested").' value="'.$row->id.'">'.($row->is_mandatory ? "Mandatory" : "Suggested").'</a>';
                })
                ->editColumn('created_at', function ($row) {
                    return '<a href="'.route(request()->segment(1).'.goalbank.editdetails', $row->id).'" aria-label="Edit Goal Details - "'.($row->created_at ? $row->created_at->format('F d, Y') : null).' value="'.$row->id.'">'.($row->created_at ? $row->created_at->format('F d, Y') : null).'</a>';
                })
                ->editColumn('audience', function ($row) {
                    return '<a href="'.route(request()->segment(1).'.goalbank.editone', $row->id).'" aria-label="Edit Goal For Individuals" value="'.$row->id.'">'.$row->audience.' Employees</a>';
                })
                ->editColumn('org_audience', function ($row) {
                    return '<a href="'.route(request()->segment(1).'.goalbank.editpage', $row->id).'" aria-label="Edit Goal For Business Units" value="'.$row->id.'">'.$row->org_audience.' Business Units</a>';
                })
                ->addcolumn('action', function($row) {
                    $btn = '<a href="/'.request()->segment(1).'/goalbank/deletegoal/' . $row->id . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_goal" value="'. $row->id .'"><i class="fa fa-trash"></i></a>';
                    return $btn;
                })
                ->rawColumns(['click_title', 'click_goal_type', 'click_display_name', 'click_creator_name', 'click_creator_organization', 'mandatory', 'created_at', 'goal_type_name', 'created_by', 'audience', 'org_audience', 'action', 'title-link'])
                ->make(true);
        }
    }

    public function getgoalinds(Request $request, $goal_id) {
        if ($request->ajax()) {
            $query = Goal::withoutGlobalScopes()
                ->from('goals AS g')
                ->where('g.id', $goal_id)
                ->join('goals_shared_with AS s', 'g.id', 's.goal_id')
                ->join('user_demo_jr_for_goalbank_view AS u', 'u.user_id', 's.user_id')
                // ->distinct()
                ->when($request->dd_level0, function($q) use($request) {return $q->where('u.organization_key', $request->dd_level0);})
                ->when($request->dd_level1, function($q) use($request) {return $q->where('u.level1_key', $request->dd_level1);})
                ->when($request->dd_level2, function($q) use($request) {return $q->where('u.level2_key', $request->dd_level2);})
                ->when($request->dd_level3, function($q) use($request) {return $q->where('u.level3_key', $request->dd_level3);})
                ->when($request->dd_level4, function($q) use($request) {return $q->where('u.level4_key', $request->dd_level4);})
                ->when($request->dd_superv == 'sup', function($q) use($request) { return $q->whereRaw("(u.isSupervisor = 1 OR u.isDelegate = 1)"); })
                ->when($request->dd_superv == 'non', function($q) use($request) { return $q->whereRaw("NOT u.isSupervisor = 1 AND NOT u.isDelegate = 1"); })
                ->when($request->search_text && $request->criteria != 'all', function($q) use($request) { return $q->whereRaw("u.{$request->criteria} like '%{$request->search_text}%'"); })
                ->when($request->search_text && $request->criteria == 'all', function($q) use($request) { return $q->whereRaw("(u.employee_id LIKE '%{$request->search_text}%' OR u.employee_name LIKE '%{$request->search_text}%' OR u.jobcode_desc LIKE '%{$request->search_text}%' OR u.deptid LIKE '%{$request->search_text}%')"); })
                ->selectRaw ("
                    u.employee_id,
                    u.employee_name,
                    u.jobcode_desc,
                    u.organization AS organization,
                    u.level1_program AS level1_program,
                    u.level2_division AS level2_division,
                    u.level3_branch AS level3_branch,
                    u.level4 AS level4,
                    u.deptid,
                    g.id as goal_id,
                    s.id as share_id,
                    u.user_id,
                    g.display_name,
                    CASE WHEN u.isSupervisor = 1 THEN 'Yes' ELSE 'No' END AS isSupervisor,
                    CASE WHEN u.isDelegate = 1 THEN 'Yes' ELSE 'No' END AS isDelegate
                ");
            return Datatables::of($query)
                ->addIndexColumn()
                ->addcolumn('action', function($row) {
                    $btn = '<a href="/'.request()->segment(1).'/goalbank/deleteindividual/'.$row->share_id.'" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_user" value="'.$row->share_id.'"><i class="fa fa-trash"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    private function getDropdownValues(&$mandatoryOrSuggested) {
        $mandatoryOrSuggested = [
            [
                "id" => '0',
                "name" => 'Suggested'
            ],
            [
                "id" => '1',
                "name" => 'Mandatory'
            ],
        ];
    }

    public function get_access_entry($roleId, $modelId) {
        return DB::table('model_has_roles')
            ->whereIn('model_id', [3, 4])
            ->where('model_type', '=', 'App\Models\User')
            ->where('role_id', '=', $roleId)
            ->where('model_id', '=', $modelId);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deletegoal(Request $request, $goal_id) {
        $query1 = DB::table('goal_tags')
            ->where('goal_id', '=', $goal_id)
            ->delete();
        $query2 = DB::table('goal_bank_orgs')
            ->where('goal_id', '=', $goal_id)
            ->delete();
        $query3 = DB::table('goals_shared_with')
            ->where('goal_id', '=', $goal_id)
            ->delete();
        $query4 = DB::table('goals')
            ->where('id', '=', $goal_id)
            ->delete();
        DashboardNotification::where('notification_type', 'GB')
                                        ->where('related_id', $goal_id)
                                        ->delete();
        return redirect()->back();
    }

    protected function get_employees_by_selected_org_nodes($selected_org_nodes) {
        $employees = EmployeeDemo::from('employee_demo AS d')
            ->whereIn('d.orgid', $selected_org_nodes)
            ->whereNull('d.date_deleted')
            ->select('d.employee_id')
            ->pluck('d.employee_id'); 
        return ($employees ? $employees->toArray() : []); 
    }

    protected function get_employees_by_selected_inherited($selected_inherited) {
        $employees0 = EmployeeDemo::from('employee_demo AS d')
            ->join('employee_demo_tree AS t', 'd.orgid', 't.id')
            ->whereIn('t.organization_key', $selected_inherited)
            ->whereNull('d.date_deleted')
            ->select('d.employee_id');
        $employees1 = EmployeeDemo::from('employee_demo AS d')
            ->join('employee_demo_tree AS t', 'd.orgid', 't.id')
            ->whereIn('t.level1_key', $selected_inherited)
            ->whereNull('d.date_deleted')
            ->select('d.employee_id');
        $employees2 = EmployeeDemo::from('employee_demo AS d')
            ->join('employee_demo_tree AS t', 'd.orgid', 't.id')
            ->whereIn('t.level2_key', $selected_inherited)
            ->whereNull('d.date_deleted')
            ->select('d.employee_id');
        $employees3 = EmployeeDemo::from('employee_demo AS d')
            ->join('employee_demo_tree AS t', 'd.orgid', 't.id')
            ->whereIn('t.level3_key', $selected_inherited)
            ->whereNull('d.date_deleted')
            ->select('d.employee_id');
        $employees4 = EmployeeDemo::from('employee_demo AS d')
            ->join('employee_demo_tree AS t', 'd.orgid', 't.id')
            ->whereIn('t.level4_key', $selected_inherited)
            ->whereNull('d.date_deleted')
            ->select('d.employee_id');
        $employees5 = EmployeeDemo::from('employee_demo AS d')
            ->join('employee_demo_tree AS t', 'd.orgid', 't.id')
            ->whereIn('t.level5_key', $selected_inherited)
            ->whereNull('d.date_deleted')
            ->select('d.employee_id');
        $employees6 = EmployeeDemo::from('employee_demo AS d')
            ->join('employee_demo_tree AS t', 'd.orgid', 't.id')
            ->whereIn('t.level6_key', $selected_inherited)
            ->whereNull('d.date_deleted')
            ->select('d.employee_id');
        $employees = $employees0->union($employees1)->union($employees2)->union($employees3)->union($employees4)->union($employees5)->union($employees6)->pluck('employee_id');
        return ($employees ? $employees->toArray() : []); 
    }

    protected function notify_on_dashboard($goalBank, $employee_ids, $emailit) {
        foreach(array_chunk($employee_ids, 1000) as $employee_ids_chunk) {
            // Filter out the employee based on the Organization level and individual user preferences. 
            $core = User::whereIn('users.employee_id', $employee_ids_chunk)
                ->whereExists(function($exists){
                    return $exists->selectRaw(\DB::raw(1))
                        ->from('users_annex AS ua')
                        ->join('access_organizations AS ao', 'ao.orgid', 'ua.organization_key')
                        ->whereRaw('ua.user_id = users.id')
                        ->where('ao.allow_inapp_msg', \DB::raw("'Y'"))
                        ->limit(1);
                });
            $data = $core->whereRaw("NOT EXISTS (SELECT 1 FROM dashboard_notifications AS dn WHERE dn.user_id = users.id AND dn.notification_type = 'GB' AND dn.related_id = {$goalBank->id} AND dn.deleted_at IS NULL)")
                ->selectRaw("
                    users.id AS user_id, 
                    'GB' AS notification_type,
                    '".($goalBank->display_name ? $goalBank->display_name : $goalBank->user->name)." added a new goal to your goal bank.' AS comment,
                    ".$goalBank->id." AS related_id,
                    NOW() AS created_at,
                    NOW() AS updated_at
                ")
                ->get()
                ->makeHidden('is_goal_shared_with_auth_user')
                ->makeHidden('is_conversation_shared_with_auth_user')
                ->makeHidden('is_shared')
                ->makeHidden('allow_inapp_notification')
                ->makeHidden('allow_email_notification')
                ->toArray();
            DashboardNotification::insert($data);
            $data = $core->selectRaw("
                    ' ' AS recipients,
                    0 AS sender_id,
                    '".($goalBank->display_name ? $goalBank->display_name : $goalBank->user->name)." added a new goal to your goal bank.' AS subject,
                    '' AS description,
                    'N' AS alert_type,
                    'A' AS alert_format,
                    users.id AS notify_user_id,
                    NULL AS overdue_user_id,
                    NULL AS notify_due_date,
                    NULL AS notify_for_days,
                    NULL AS template_id,
                    NOW() AS date_sent,
                    NOW() AS created_at,
                    NOW() AS updated_at
                ")
                ->get()
                ->makeHidden('is_goal_shared_with_auth_user')
                ->makeHidden('is_conversation_shared_with_auth_user')
                ->makeHidden('is_shared')
                ->makeHidden('allow_inapp_notification')
                ->makeHidden('allow_email_notification')
                ->toArray();
            NotificationLog::insert($data);
        }
        // Additional Step -- sent out email message if required
        if($emailit){
            $this->notify_employees($goalBank, $employee_ids);
        }
    }

    protected function notify_employees($goalBank, $employee_ids) {
        // Filter out the employee based on the Organization level and individual user preferences. 
        $filtered_ee_ids = UserDemoJrForGoalbankView::join(\DB::raw('access_organizations USE INDEX (access_organizations_orgid_unique)'), function ($on1) {
                return $on1->on('access_organizations.orgid', 'user_demo_jr_for_goalbank_view.organization_key')
                    ->whereRaw("access_organizations.allow_email_msg = 'Y'");
            })
            ->leftjoin(\DB::raw('user_preferences USE INDEX (user_preferences_user_id_index)'), function ($on2) {
                return $on2->on('user_preferences.user_id', 'user_demo_jr_for_goalbank_view.user_id')
                    ->on(function ($where2){
                        return $where2->whereRaw("user_preferences.goal_bank_flag = 'Y'")
                            ->orWhereNull('user_preferences.goal_bank_flag');
                    });
            })
            ->whereIn('user_demo_jr_for_goalbank_view.employee_id', $employee_ids)
            ->whereNull('user_demo_jr_for_goalbank_view.excused_type')
            ->pluck('user_demo_jr_for_goalbank_view.user_id')
            ->toArray(); 
        if (count($filtered_ee_ids)) {
            // find user id based on the employee_id
            // $bcc_user_ids = User::whereIn('employee_id', $filtered_ee_ids)->pluck('id');
            $bcc_user_ids = $filtered_ee_ids;
            // Send Out Email Notification to Employee
            $sendMail = new SendMail();
            $sendMail->bccRecipients = $bcc_user_ids;  
            $sendMail->sender_id = null;
            $sendMail->useQueue = true;
            $sendMail->template = 'NEW_GOAL_IN_GOAL_BANK';
            array_push($sendMail->bindvariables, "");
            array_push($sendMail->bindvariables, $goalBank->user ? ($goalBank->display_name ? $goalBank->display_name : $goalBank->user->name) : '');   // Person who added goal to goal bank
            array_push($sendMail->bindvariables, $goalBank->title);       // goal title
            array_push($sendMail->bindvariables, $goalBank->mandatory_status_descr);           // Mandatory or suggested status
            $response = $sendMail->sendMailWithGenericTemplate();
        }
    }
}
