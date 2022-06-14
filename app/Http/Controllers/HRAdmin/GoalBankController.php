<?php

namespace App\Http\Controllers\HRAdmin;


use App\Models\User;
use App\Models\Goal;
use App\Models\GoalType;
use App\Models\Tag;
use App\Models\GoalBankOrg;
use App\Models\EmployeeDemo;
use Illuminate\Http\Request;
use App\Models\OrganizationTree;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Goals\CreateGoalRequest;
use Carbon\Carbon;


class GoalBankController extends Controller
{
    public function createindex(Request $request) 
    {

        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);
        $tags = Tag::all(["id","name"])->toArray();

        $errors = session('errors');

        $request->firstTime = true;

        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];

        $eold_selected_emp_ids = []; // $request->eselected_emp_ids ? json_decode($request->eselected_emp_ids) : [];
        $eold_selected_org_nodes = []; // $request->eold_selected_org_nodes ? json_decode($request->eselected_org_nodes) : [];

        if ($errors) {
            $old = session()->getOldInput();

            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;

            $request->criteria = isset($old['criteria']) ? $old['criteria'] : null;
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

            $request->ecriteria = isset($old['ecriteria']) ? $old['ecriteria'] : null;
            $request->esearch_text = isset($old['esearch_text']) ? $old['esearch_text'] : null;
            
            $request->eorgCheck = isset($old['eorgCheck']) ? $old['eorgCheck'] : null;
            $request->euserCheck = isset($old['euserCheck']) ? $old['euserCheck'] : null;

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
                'ecriteria' => $request->ecriteria,
                'esearch_text' => $request->esearch_text,
                'eorgCheck' => $request->eorgCheck,
                'euserCheck' => $request->euserCheck,
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
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        
        $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
        $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
        $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
        $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
        $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;
        $request->session()->flash('euserCheck', $request->euserCheck);  // Dynamic load 

        $request->session()->flash('elevel0', $elevel0);
        $request->session()->flash('elevel1', $elevel1);
        $request->session()->flash('elevel2', $elevel2);
        $request->session()->flash('elevel3', $elevel3);
        $request->session()->flash('elevel4', $elevel4);

        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 
            'employee_demo.employee_id'
            , 'employee_demo.employee_name'
            , 'employee_demo.jobcode_desc'
            , 'employee_demo.employee_email'
            , 'employee_demo.organization'
            , 'employee_demo.level1_program'
            , 'employee_demo.level2_division'
            , 'employee_demo.level3_branch'
            , 'employee_demo.level4'
            , 'employee_demo.deptid'
            , 'employee_demo.job_title'
        ])
        ->orderBy('employee_demo.employee_id')
        ->pluck('employee_demo.employee_id');        
        
        // Matched Employees 
        $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
        $esql = clone $edemoWhere; 
        $ematched_emp_ids = $esql->select([ 
            'employee_demo.employee_id'
            , 'employee_demo.employee_name'
            , 'employee_demo.jobcode_desc'
            , 'employee_demo.employee_email'
            , 'employee_demo.organization'
            , 'employee_demo.level1_program'
            , 'employee_demo.level2_division'
            , 'employee_demo.level3_branch'
            , 'employee_demo.level4'
            , 'employee_demo.deptid'
        ])
        ->orderBy('employee_demo.employee_id')
        ->pluck('employee_demo.employee_id');        

        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();

        return view('hradmin.goalbank.createindex', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'ematched_emp_ids', 'old_selected_emp_ids', 'eold_selected_emp_ids', 'old_selected_org_nodes', 'eold_selected_org_nodes', 'goalTypes', 'mandatoryOrSuggested', 'tags') );
    
    }


    public function index(Request $request) 
    {
        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);

        $errors = session('errors');

        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $tags = Tag::all(["id","name"])->toArray();

        if ($errors) {
            $old = session()->getOldInput();

            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;

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

            $old_selected_emp_ids = isset($old['selected_emp_ids']) ? json_decode($old['selected_emp_ids']) : [];
            $old_selected_org_nodes = isset($old['selected_org_nodes']) ? json_decode($old['selected_org_nodes']) : [];

        } 

        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
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
                'ecriteria' => $request->ecriteria,
                'esearch_text' => $request->esearch_text,
                'eorgCheck' => $request->eorgCheck,
                'euserCheck' => $request->euserCheck,
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
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        
        $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
        $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
        $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
        $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
        $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;

        $request->session()->flash('elevel0', $elevel0);
        $request->session()->flash('elevel1', $elevel1);
        $request->session()->flash('elevel2', $elevel2);
        $request->session()->flash('elevel3', $elevel3);
        $request->session()->flash('elevel4', $elevel4);

        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 'employee_demo.employee_id', 'employee_demo.employee_name', 'employee_demo.jobcode_desc', 'employee_demo.employee_email', 
                'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division',
                'employee_demo.level3_branch','employee_demo.level4', 'employee_demo.deptid'])
            ->orderBy('employee_id')
                ->pluck('employee_demo.employee_id');        
        
        $criteriaList = $this->search_criteria_list();
        $roles = DB::table('roles')
        ->whereIntegerInRaw('id', [3, 4])
        ->pluck('longname', 'id');

        return view('hradmin.goalbank.index', compact('criteriaList','matched_emp_ids', 'old_selected_emp_ids', 'old_selected_org_nodes', 'roles', 'goalTypes', 'mandatoryOrSuggested', 'tags') );
    
    }

    public function getgoalorgs(Request $request, $goal_id) {
        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
        if ($request->ajax()) {
            $query = GoalBankOrg::where('goal_id', '=', $goal_id)
            ->when( $level0, function ($q) use($level0) {
                return $q->where('goal_bank_orgs.organization', '=', $level0->name);
            })
            ->when( $level1, function ($q) use($level1) {
                return $q->where('goal_bank_orgs.level1_program', $level1->name);
            })
            ->when( $level2, function ($q) use($level2) {
                return $q->where('goal_bank_orgs.level2_division', $level2->name);
            })
            ->when( $level3, function ($q) use($level3) {
                return $q->where('goal_bank_orgs.level3_branch', $level3->name);
            })
            ->when( $level4, function ($q) use($level4) {
                return $q->where('goal_bank_orgs.level4', $level4->name);
            })
            ->select (
                'organization',
                'level1_program',
                'level2_division',
                'level3_branch',
                'level4',
                'goal_id',
                'id'
            );
            return Datatables::of($query)
            ->addIndexColumn()
            ->addcolumn('action', function($row) {
                $btn = '<a href="/hradmin/goalbank/deleteorg/' . $row->id . '" class="btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete Org" id="delete_org" value="'. $row->id .'"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->rawColumns(['goal_type_name', 'created_by', 'action'])
             ->make(true);
        }
    }

    public function deleteorg(Request $request, $id)
    {
        $query = GoalBankOrg::where('id', '=', $id)
        ->delete();

        return redirect()->back();
    }

    public function deleteindividual(Request $request, $id)
    {
        $query = DB::table('goals_shared_with')
        ->where('id', '=', $id)
        ->delete();

        return redirect()->back();
    }

    public function editpage(Request $request, $id) 
    {
        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);

        $errors = session('errors');

        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $tags = Tag::all(["id","name"])->toArray();

        if ($errors) {
            $old = session()->getOldInput();

            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;

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

            $old_selected_emp_ids = isset($old['selected_emp_ids']) ? json_decode($old['selected_emp_ids']) : [];
            $old_selected_org_nodes = isset($old['selected_org_nodes']) ? json_decode($old['selected_org_nodes']) : [];

        } 

        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
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
                'ecriteria' => $request->ecriteria,
                'esearch_text' => $request->esearch_text,
                'eorgCheck' => $request->eorgCheck,
                'euserCheck' => $request->euserCheck,
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
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        
        $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
        $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
        $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
        $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
        $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;

        $request->session()->flash('elevel0', $elevel0);
        $request->session()->flash('elevel1', $elevel1);
        $request->session()->flash('elevel2', $elevel2);
        $request->session()->flash('elevel3', $elevel3);
        $request->session()->flash('elevel4', $elevel4);

        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 'employee_demo.employee_id', 'employee_demo.employee_name', 'employee_demo.jobcode_desc', 'employee_demo.employee_email', 
                'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division',
                'employee_demo.level3_branch','employee_demo.level4', 'employee_demo.deptid'])
            ->orderBy('employee_id')
                ->pluck('employee_demo.employee_id');        
        
        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();
        $roles = DB::table('roles')
        ->whereIntegerInRaw('id', [3, 4])
        ->pluck('longname', 'id');
        $goal_id = $id;

        $goaldetail = Goal::withoutGlobalScopes()->find($request->id);

        return view('hradmin.goalbank.editgoal', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'old_selected_emp_ids', 'old_selected_org_nodes', 'roles', 'goalTypes', 'mandatoryOrSuggested', 'tags', 'goaldetail', 'request', 'goal_id') );
    
    }

    public function editone(Request $request, $id) 
    {
        $goalTypes = GoalType::all()->toArray();
        $this->getDropdownValues($mandatoryOrSuggested);
        $this->getDropdownValues($amandatoryOrSuggested);

        $errors = session('errors');

        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $tags = Tag::all(["id","name"])->toArray();
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
                'acriteria' => $request->acriteria,
                'asearch_text' => $request->asearch_text,
                'aorgCheck' => $request->aorgCheck,
                'auserCheck' => $request->auserCheck,
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
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        
        $alevel0 = $request->add_level0 ? OrganizationTree::where('id', $request->add_level0)->first() : null;
        $alevel1 = $request->add_level1 ? OrganizationTree::where('id', $request->add_level1)->first() : null;
        $alevel2 = $request->add_level2 ? OrganizationTree::where('id', $request->add_level2)->first() : null;
        $alevel3 = $request->add_level3 ? OrganizationTree::where('id', $request->add_level3)->first() : null;
        $alevel4 = $request->add_level4 ? OrganizationTree::where('id', $request->add_level4)->first() : null;

        $request->session()->flash('alevel0', $alevel0);
        $request->session()->flash('alevel1', $alevel1);
        $request->session()->flash('alevel2', $alevel2);
        $request->session()->flash('alevel3', $alevel3);
        $request->session()->flash('alevel4', $alevel4);

        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 
            'employee_demo.employee_id'
            , 'employee_demo.employee_name'
            , 'employee_demo.jobcode_desc'
            , 'employee_demo.employee_email'
            , 'employee_demo.organization'
            , 'employee_demo.level1_program'
            , 'employee_demo.level2_division'
            , 'employee_demo.level3_branch'
            , 'employee_demo.level4'
            , 'employee_demo.deptid'])
        ->orderBy('employee_id')
        ->pluck('employee_demo.employee_id');        
        
        $ademoWhere = $this->abaseFilteredWhere($request, $alevel0, $alevel1, $alevel2, $alevel3, $alevel4);
        $asql = clone $ademoWhere; 
        $amatched_emp_ids = $asql->select([ 
            'employee_demo.employee_id'
            , 'employee_demo.employee_name'
            , 'employee_demo.jobcode_desc'
            , 'employee_demo.employee_email'
            , 'employee_demo.organization'
            , 'employee_demo.level1_program'
            , 'employee_demo.level2_division'
            , 'employee_demo.level3_branch'
            , 'employee_demo.level4'
            , 'employee_demo.deptid'])
        ->orderBy('employee_id')
        ->pluck('employee_demo.employee_id');        
        
        $criteriaList = $this->search_criteria_list();
        $acriteriaList = $this->search_criteria_list();
        $goal_id = $id;

        $goaldetail = Goal::withoutGlobalScopes()->find($request->id);

        return view('hradmin.goalbank.editone', compact('criteriaList', 'acriteriaList', 'matched_emp_ids', 'amatched_emp_ids', 'old_selected_emp_ids', 'aold_selected_emp_ids', 'old_selected_org_nodes', 'aold_selected_org_nodes', 'goalTypes', 'mandatoryOrSuggested', 'amandatoryOrSuggested', 'tags', 'atags', 'goaldetail', 'request', 'goal_id') );
    
    }

    public function savenewgoal(CreateGoalRequest $request) 
    {
        $request->userCheck = $request->selected_emp_ids;

        $current_user = User::find(Auth::id());

        $resultrec = Goal::withoutGlobalScopes()
        ->create(
            ['goal_type_id' => $request->input('goal_type_id')
            , 'is_library' => true
            , 'is_shared' => true
            , 'title' => $request->input('title')
            , 'what' => $request->input('what')
            , 'measure_of_success' => $request->input('measure_of_success')
            , 'start_date' => $request->input('start_date')
            , 'target_date' => $request->input('target_date')
            , 'measure_of_success' => $request->input('measure_of_success')
            , 'user_id' => $current_user->id
            , 'created_by' => $current_user->id
            ]
        );
        
        $resultrec->tags()->sync($request->tag_ids);

        $employee_ids = ($request->userCheck) ? $request->userCheck : [];

        if($request->opt_audience == "byEmp") {
            $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
            $toRecipients = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.guid', 'users.guid')
            ->whereIn('employee_demo.employee_id', $selected_emp_ids )
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
        }

        if($request->opt_audience == "byOrg") {
            $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
            $organizationList = OrganizationTree::select('id', 'organization', 'level1_program', 'level2_division', 'level3_branch', 'level4')
            ->whereIn('id', $selected_org_nodes)
            ->distinct()
            ->orderBy('id')
            ->get();
            foreach($organizationList as $org1) {
                $result = DB::table('goal_bank_orgs')
                ->insert(
                    ['goal_id' => $resultrec->id
                    // , 'version' => '5'
                    , 'version' => '1'
                    , 'organization' => $org1->organization
                    , 'level1_program' => $org1->level1_program
                    , 'level2_division' => $org1->level2_division
                    , 'level3_branch' => $org1->level3_branch
                    , 'level4' => $org1->level4
                    , 'created_at' => date('Y-m-d H:i:s')
                    , 'updated_at' => date('Y-m-d H:i:s') ],
                );
                if(!$result){
                    break;
                }
            }
        }
        return redirect()->route('hradmin.goalbank.createindex')
            ->with('success', 'Create new goal bank successful.');
    }

    public function loadOrganizationTree(Request $request) {

        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;

        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4);
        
        $rows = $sql_level4->groupBy('organization_trees.id')->select('organization_trees.id')
            ->union( $sql_level3->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $sql_level2->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $sql_level1->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $sql_level0->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->pluck('organization_trees.id'); 
        $orgs = OrganizationTree::whereIn('id', $rows->toArray() )->get()->toTree();

        // Employee Count by Organization
        $countByOrg = $sql_level4->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row"))
        ->union( $sql_level3->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level2->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level1->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level0->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row") ) )
        ->pluck('count_row', 'organization_trees.id');  
        
        // // Employee ID by Tree ID
        $empIdsByOrgId = [];
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql = clone $demoWhere; 
        $rows = $sql->join('organization_trees', function($join) use($request) {
                $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                    ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                    ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                    ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                    ->on('employee_demo.level4', '=', 'organization_trees.level4');
                })
                ->select('organization_trees.id','employee_demo.employee_id')
                ->groupBy('organization_trees.id', 'employee_demo.employee_id')
                ->orderBy('organization_trees.id')->orderBy('employee_demo.employee_id')
                ->get();

        $empIdsByOrgId = $rows->groupBy('id')->all();

        if($request->ajax()){
            return view('hradmin.goalbank.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
        } 

    }


    public function eloadOrganizationTree(Request $request) {

        $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
        $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
        $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
        $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
        $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;

            list($esql_level0, $esql_level1, $esql_level2, $esql_level3, $esql_level4) = 
                $this->ebaseFilteredSQLs($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
            
            $rows = $esql_level4->groupBy('organization_trees.id')->select('organization_trees.id')
                ->union( $esql_level3->groupBy('organization_trees.id')->select('organization_trees.id') )
                ->union( $esql_level2->groupBy('organization_trees.id')->select('organization_trees.id') )
                ->union( $esql_level1->groupBy('organization_trees.id')->select('organization_trees.id') )
                ->union( $esql_level0->groupBy('organization_trees.id')->select('organization_trees.id') )
                ->pluck('organization_trees.id'); 

            $eorgs = OrganizationTree::whereIn('id', $rows->toArray() )->get()->toTree();
            
            $ecountByOrg = $esql_level4->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row"))
            ->union( $esql_level3->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
            ->union( $esql_level2->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
            ->union( $esql_level1->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
            ->union( $esql_level0->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row") ) )
            ->pluck('count_row', 'organization_trees.id');  
            
            // // Employee ID by Tree ID
                $eempIdsByOrgId = [];
                $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
                $esql = clone $edemoWhere; 
                $rows = $esql->join('organization_trees', function($join) use($request) {
                        $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                            ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                            ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                            ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                            ->on('employee_demo.level4', '=', 'organization_trees.level4');
                        })
                        ->select('organization_trees.id','employee_demo.employee_id')
                        ->groupBy('organization_trees.id', 'employee_demo.employee_id')
                        ->orderBy('organization_trees.id')->orderBy('employee_demo.employee_id')
                        ->get();
        
                    $eempIdsByOrgId = $rows->groupBy('id')->all();

        if($request->ajax()){
            return view('hradmin.goalbank.partials.recipient-tree2', compact('eorgs','ecountByOrg','eempIdsByOrgId') );
        } 
    
    }

    public function aloadOrganizationTree(Request $request) {

        $alevel0 = $request->add_level0 ? OrganizationTree::where('id', $request->add_level0)->first() : null;
        $alevel1 = $request->add_level1 ? OrganizationTree::where('id', $request->add_level1)->first() : null;
        $alevel2 = $request->add_level2 ? OrganizationTree::where('id', $request->add_level2)->first() : null;
        $alevel3 = $request->add_level3 ? OrganizationTree::where('id', $request->add_level3)->first() : null;
        $alevel4 = $request->add_level4 ? OrganizationTree::where('id', $request->add_level4)->first() : null;

        list($asql_level0, $asql_level1, $asql_level2, $asql_level3, $asql_level4) = 
            $this->abaseFilteredSQLs($request, $alevel0, $alevel1, $alevel2, $alevel3, $alevel4);
        
        $rows = $asql_level4->groupBy('organization_trees.id')->select('organization_trees.id')
            ->union( $asql_level3->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $asql_level2->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $asql_level1->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $asql_level0->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->pluck('organization_trees.id'); 
        $aorgs = OrganizationTree::whereIn('id', $rows->toArray() )->get()->toTree();
        
        $acountByOrg = $asql_level4->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row"))
        ->union( $asql_level3->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $asql_level2->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $asql_level1->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $asql_level0->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row") ) )
        ->pluck('count_row', 'organization_trees.id');  
        
        // // Employee ID by Tree ID
        $aempIdsByOrgId = [];
        $ademoWhere = $this->abaseFilteredWhere($request, $alevel0, $alevel1, $alevel2, $alevel3, $alevel4);
        $asql = clone $ademoWhere; 
        $rows = $asql->join('organization_trees', function($join) use($request) {
                $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                    ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                    ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                    ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                    ->on('employee_demo.level4', '=', 'organization_trees.level4');
                })
                ->select('organization_trees.id','employee_demo.employee_id')
                ->groupBy('organization_trees.id', 'employee_demo.employee_id')
                ->orderBy('organization_trees.id')->orderBy('employee_demo.employee_id')
                ->get();

        $aempIdsByOrgId = $rows->groupBy('id')->all();

        if($request->ajax()){
            return view('hradmin.goalbank.partials.arecipient-tree', compact('aorgs','acountByOrg','aempIdsByOrgId') );
        } 
   
    }

  
    public function getDatatableEmployees(Request $request) {
        if($request->ajax()){

            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
    
            $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);

            $sql = clone $demoWhere; 

            $employees = $sql->select([ 
                'employee_id'
                , 'employee_name'
                , 'jobcode_desc'
                , 'employee_email'
                , 'employee_demo.organization'
                , 'employee_demo.level1_program'
                , 'employee_demo.level2_division'
                , 'employee_demo.level3_branch'
                , 'employee_demo.level4'
                , 'employee_demo.deptid']);

            return Datatables::of($employees)
                ->addColumn('select_users', static function ($employee) {
                        return '<input pid="1335" type="checkbox" id="userCheck'. 
                            $employee->employee_id .'" name="userCheck[]" value="'. $employee->employee_id .'" class="dt-body-center">';
                })->rawColumns(['select_users','action'])
                ->make(true);
        }
    }

    public function egetDatatableEmployees(Request $request) {
        if($request->ajax()){

            $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
            $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
            $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
            $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
            $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;
    
            $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);

            $esql = clone $edemoWhere; 

            $eemployees = $esql->select([ 
                'employee_id'
                , 'employee_name'
                , 'jobcode_desc'
                , 'employee_email'
                , 'employee_demo.organization'
                , 'employee_demo.level1_program'
                , 'employee_demo.level2_division'
                , 'employee_demo.level3_branch'
                , 'employee_demo.level4'
                , 'employee_demo.deptid']);
            return Datatables::of($eemployees)
                ->addColumn('eselect_users', static function ($eemployee) {
                        return '<input pid="1335" type="checkbox" id="euserCheck'. 
                            $eemployee->employee_id .'" name="euserCheck[]" value="'. $eemployee->employee_id .'" class="dt-body-center">';
                })->rawColumns(['eselect_users','eaction'])
                ->make(true);
        }
    }

    public function agetDatatableEmployees(Request $request) {
        if($request->ajax()){

            $alevel0 = $request->add_level0 ? OrganizationTree::where('id', $request->add_level0)->first() : null;
            $alevel1 = $request->add_level1 ? OrganizationTree::where('id', $request->add_level1)->first() : null;
            $alevel2 = $request->add_level2 ? OrganizationTree::where('id', $request->add_level2)->first() : null;
            $alevel3 = $request->add_level3 ? OrganizationTree::where('id', $request->add_level3)->first() : null;
            $alevel4 = $request->add_level4 ? OrganizationTree::where('id', $request->add_level4)->first() : null;
    
            $ademoWhere = $this->abaseFilteredWhere($request, $alevel0, $alevel1, $alevel2, $alevel3, $alevel4);

            $asql = clone $ademoWhere; 

            $aemployees = $asql->select([ 
                'employee_id'
                , 'employee_name'
                , 'jobcode_desc'
                , 'employee_email'
                , 'employee_demo.organization'
                , 'employee_demo.level1_program'
                , 'employee_demo.level2_division'
                , 'employee_demo.level3_branch'
                , 'employee_demo.level4'
                , 'employee_demo.deptid']);
            return Datatables::of($aemployees)
                ->addColumn('aselect_users', static function ($aemployee) {
                        return '<input pid="1335" type="checkbox" id="auserCheck'. 
                            $aemployee->employee_id .'" name="auserCheck[]" value="'. $aemployee->employee_id .'" class="dt-body-center">';
                })->rawColumns(['aselect_users','aaction'])
                ->make(true);
        }
    }

        // public function getDatatableEmployeesEmpty(Request $request) {

        //     if($request->ajax()){
    
        //         $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        //         $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        //         $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        //         $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        //         $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
        
        //         $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
    
        //         $sql = clone $demoWhere; 
    
        //         $employees = $sql->select([ 
        //             'employee_id'
        //             , 'employee_name'
        //             , 'jobcode_desc'
        //             , 'employee_email'
        //             , 'employee_demo.organization'
        //             , 'employee_demo.level1_program'
        //             , 'employee_demo.level2_division'
        //             , 'employee_demo.level3_branch'
        //             , 'employee_demo.level4'
        //             , 'employee_demo.deptid'])
        //         ->where('employee_id', '=', 98989898989898);
        //         return Datatables::of($employees)
        //             ->addColumn('select_users', static function ($employee) {
        //                     return '<input pid="1335" type="checkbox" id="userCheck'. 
        //                         $employee->employee_id .'" name="userCheck[]" value="'. $employee->employee_id .'" class="dt-body-center">';
        //             })->rawColumns(['select_users','action'])
        //             ->make(true);
        //     }
        // }
    
        
    public function addnewgoal(Request $request) 
    {
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $current_user = User::find(Auth::id());

            $organizationList = OrganizationTree::select('id', 'organization', 'level1_program', 'level2_division', 'level3_branch', 'level4')
            ->whereIn('id', $selected_org_nodes)
            ->distinct()
            ->orderBy('id')
            ->get();

            $resultrec = Goal::create(
                ['goal_type_id' => $request->input('goal_type_id')
                , 'is_library' => true
                , 'is_shared' => true
                , 'title' => $request->input('title')
                , 'what' => $request->input('what')
                , 'measure_of_success' => $request->input('measure_of_success')
                , 'start_date' => $request->input('start_date')
                , 'target_date' => $request->input('target_date')
                , 'measure_of_success' => $request->input('measure_of_success')
                , 'user_id' => $current_user->id
                , 'created_by' => $current_user->id
                ]
            );

            $resultrec->tags()->sync($request->tag_ids);
    
            foreach($organizationList as $org1) {
                $result = DB::table('goal_bank_orgs')
                ->insert(
                    ['goal_id' => $resultrec->id
                    // , 'version' => '5'
                    , 'version' => '1'
                    , 'organization' => $org1->organization
                    , 'level1_program' => $org1->level1_program
                    , 'level2_division' => $org1->level2_division
                    , 'level3_branch' => $org1->level3_branch
                    , 'level4' => $org1->level4
                    , 'created_at' => date('Y-m-d H:i:s')
                    , 'updated_at' => date('Y-m-d H:i:s') ],
                );
                if(!$result){
                    break;
                }
            }

        return redirect()->route('hradmin.goalbank.index')
            ->with('success', 'Add new goal successful.');
    }

    public function updategoal(CreateGoalRequest $request) 
    {
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $current_user = Auth::id();

            $organizationList = OrganizationTree::select('id', 'organization', 'level1_program', 'level2_division', 'level3_branch', 'level4')
            ->whereIn('id', $selected_org_nodes)
            ->distinct()
            ->orderBy('id')
            ->get();

            $resultrec = Goal::withoutGlobalScopes()->findorfail( $request->goal_id );
            $resultrec->update(
                ['goal_type_id' => $request->input('goal_type_id')
                , 'title' => $request->input('title')
                , 'what' => $request->input('what')
                , 'measure_of_success' => $request->input('measure_of_success')
                , 'start_date' => $request->input('start_date')
                , 'target_date' => $request->input('target_date')
                , 'measure_of_success' => $request->input('measure_of_success')
                ]
            );

            $resultrec->tags()->sync($request->tag_ids);
    
            foreach($organizationList as $org1) {
                $result = DB::table('goal_bank_orgs')
                ->updateorinsert(
                    ['goal_id' => $resultrec->id
                    , 'organization' => $org1->organization
                    , 'level1_program' => $org1->level1_program
                    , 'level2_division' => $org1->level2_division
                    , 'level3_branch' => $org1->level3_branch
                    , 'level4' => $org1->level4
                    ],
                );
                if(!$result){
                    break;
                }
            }

        return redirect()->route('hradmin.goalbank.manageindex')
            ->with('success', 'Goal update successful.');

    }

    public function updategoalone(CreateGoalRequest $request) 
    {
        $request->auserCheck = $request->aselected_emp_ids;
        Log::info($request->aselected_emp_ids);
        $aselected_emp_ids = $request->aselected_emp_ids ? json_decode($request->aselected_emp_ids) : [];
        $request->auserCheck = $aselected_emp_ids;
        $aselected_org_nodes = $request->aselected_org_nodes ? json_decode($request->aselected_org_nodes) : [];
        $current_user = Auth::id();
        $resultrec = Goal::withoutGlobalScopes()->findorfail( $request->goal_id );
        $resultrec->update(
            ['goal_type_id' => $request->input('goal_type_id')
            , 'title' => $request->input('title')
            , 'what' => $request->input('what')
            , 'measure_of_success' => $request->input('measure_of_success')
            , 'start_date' => $request->input('start_date')
            , 'target_date' => $request->input('target_date')
            , 'measure_of_success' => $request->input('measure_of_success')
            ]
        );

        $resultrec->tags()->sync($request->tag_ids);

        $aemployee_ids = ($request->auserCheck) ? $request->auserCheck : [];

        $toRecipients = EmployeeDemo::select('users.id')
        ->join('users', 'employee_demo.guid', 'users.guid')
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

        return redirect()->route('hradmin.goalbank.manageindex')
            ->with('success', 'Goal update successful.');

    }

   public function getUsers(Request $request)
    {

        $search = $request->search;
        $users =  User::whereRaw("lower(name) like '%". strtolower($search)."%'")
                    ->whereNotNull('email')->paginate();

        return ['data'=> $users];
    }


    public function getOrganizations(Request $request) {

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
        ->where('organization_trees.level',0)
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

    public function getPrograms(Request $request) {

        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id',$request->level0)->first() : null;

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
        ->where('organization_trees.level',1)
        ->when( $request->q , function ($q) use($request) {
            return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
            })
        ->when( $level0 , function ($q) use($level0) {
            return $q->where('organization_trees.organization', $level0->name );
        })
        ->groupBy('organization_trees.name')
        ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
    } 

    public function getDivisions(Request $request) {

        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
    } 

    public function getBranches(Request $request) {

        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level2)->first() : null;

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('organization_trees.level2_division', $level2->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
    } 

    public function getLevel4(Request $request) {
        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level2)->first() : null;
        $level3 = $request->level3 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level3)->first() : null;

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('organization_trees.level2_division', $level2->name );
            })
            ->when( $level3 , function ($q) use($level3) {
                return $q->where('organization_trees.level3_branch', $level3->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
    } 

    public function getEmployees(Request $request,  $id) {
        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;

        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4);
       
        $rows = $sql_level4->where('organization_trees.id', $id)
            ->union( $sql_level3->where('organization_trees.id', $id) )
            ->union( $sql_level2->where('organization_trees.id', $id) )
            ->union( $sql_level1->where('organization_trees.id', $id) )
            ->union( $sql_level0->where('organization_trees.id', $id) );

        $employees = $rows->get();

        $parent_id = $id;
        
        return view('hradmin.goalbank.partials.employee', compact('parent_id', 'employees') ); 
    }

    public function agetOrganizations(Request $request) {

        $aorgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
        ->where('organization_trees.level',0)
        ->when( $request->q , function ($q) use($request) {
            return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
        })
        ->get();

        $aformatted_orgs = [];
        foreach ($aorgs as $org) {
            $aformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($aformatted_orgs);
    } 

    public function agetPrograms(Request $request) {

        $alevel0 = $request->alevel0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id',$request->alevel0)->first() : null;

        $aorgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',1)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $alevel0 , function ($q) use($alevel0) {
                return $q->where('organization_trees.organization', $alevel0->name );
            })
            ->groupBy('organization_trees.name')
            ->get();

        $aformatted_orgs = [];
        foreach ($aorgs as $org) {
            $aformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($aformatted_orgs);
    } 

    public function agetDivisions(Request $request) {

        $alevel0 = $request->alevel0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel0)->first() : null;
        $alevel1 = $request->alevel1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel1)->first() : null;

        $aorgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $alevel0 , function ($q) use($alevel0) {
                return $q->where('organization_trees.organization', $alevel0->name) ;
            })
            ->when( $alevel1 , function ($q) use($alevel1) {
                return $q->where('organization_trees.level1_program', $alevel1->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();

        $aformatted_orgs = [];
        foreach ($aorgs as $org) {
            $aformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($aformatted_orgs);
    } 

    public function agetBranches(Request $request) {
        $alevel0 = $request->alevel0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel0)->first() : null;
        $alevel1 = $request->alevel1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel1)->first() : null;
        $alevel2 = $request->alevel2 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel2)->first() : null;

        $aorgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $alevel0 , function ($q) use($alevel0) {
                return $q->where('organization_trees.organization', $alevel0->name) ;
            })
            ->when( $alevel1 , function ($q) use($alevel1) {
                return $q->where('organization_trees.level1_program', $alevel1->name );
            })
            ->when( $alevel2 , function ($q) use($alevel2) {
                return $q->where('organization_trees.level2_division', $alevel2->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();

        $aformatted_orgs = [];
        foreach ($aorgs as $org) {
            $aformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($aformatted_orgs);
    } 

    public function agetLevel4(Request $request) {
        $alevel0 = $request->alevel0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel0)->first() : null;
        $alevel1 = $request->alevel1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel1)->first() : null;
        $alevel2 = $request->alevel2 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel2)->first() : null;
        $alevel3 = $request->alevel3 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel3)->first() : null;

        $aorgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $alevel0 , function ($q) use($alevel0) {
                return $q->where('organization_trees.organization', $alevel0->name) ;
            })
            ->when( $alevel1 , function ($q) use($alevel1) {
                return $q->where('organization_trees.level1_program', $alevel1->name );
            })
            ->when( $alevel2 , function ($q) use($alevel2) {
                return $q->where('organization_trees.level2_division', $alevel2->name );
            })
            ->when( $alevel3 , function ($q) use($alevel3) {
                return $q->where('organization_trees.level3_branch', $alevel3->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();

        $aformatted_orgs = [];
        foreach ($aorgs as $org) {
            $aformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($aformatted_orgs);
    } 

    public function agetEmployees(Request $request,  $id) {
        $alevel0 = $request->add_level0 ? OrganizationTree::where('id', $request->add_level0)->first() : null;
        $alevel1 = $request->add_level1 ? OrganizationTree::where('id', $request->add_level1)->first() : null;
        $alevel2 = $request->add_level2 ? OrganizationTree::where('id', $request->add_level2)->first() : null;
        $alevel3 = $request->add_level3 ? OrganizationTree::where('id', $request->add_level3)->first() : null;
        $alevel4 = $request->add_level4 ? OrganizationTree::where('id', $request->add_level4)->first() : null;

        list($asql_level0, $asql_level1, $asql_level2, $asql_level3, $asql_level4) = 
            $this->abaseFilteredSQLs($request, $alevel0, $alevel1, $alevel2, $alevel3, $alevel4);
       
        $rows = $asql_level4->where('organization_trees.id', $id)
            ->union( $asql_level3->where('organization_trees.id', $id) )
            ->union( $asql_level2->where('organization_trees.id', $id) )
            ->union( $asql_level1->where('organization_trees.id', $id) )
            ->union( $asql_level0->where('organization_trees.id', $id) );

        $aemployees = $rows->get();

        $aparent_id = $id;
        
        return view('hradmin.goalbank.partials.employee', compact('parent_id', 'employees') ); 
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'emp' => 'Employee ID', 
            'name'=> 'Employee Name',
            'job' => 'Job Title', 
            'dpt' => 'Department ID'
        ];
    }

    protected function baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4) {
        // Base Where Clause
        $demoWhere = EmployeeDemo::join('admin_orgs', function($join) {
            $join->on('employee_demo.organization', '=', 'admin_orgs.organization')
            ->on('employee_demo.level1_program', '=', 'admin_orgs.level1_program')
            ->on('employee_demo.level2_division', '=', 'admin_orgs.level2_division')
            ->on('employee_demo.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('employee_demo.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->when( $level0, function ($q) use($level0) {
            return $q->where('employee_demo.organization', $level0->name);
        })
        ->when( $level1, function ($q) use($level1) {
            return $q->where('employee_demo.level1_program', $level1->name);
        })
        ->when( $level2, function ($q) use($level2) {
            return $q->where('employee_demo.level2_division', $level2->name);
        })
        ->when( $level3, function ($q) use($level3) {
            return $q->where('employee_demo.level3_branch', $level3->name);
        })
        ->when( $level4, function ($q) use($level4) {
            return $q->where('employee_demo.level4', $level4->name);
        })
        ->when( $request->search_text && $request->criteria == 'all', function ($q) use($request) {
            $q->where(function($query) use ($request) {
                
                return $query->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->search_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->search_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.jobcode_desc) LIKE '%" . strtolower($request->search_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->search_text) . "%'");
            });
        })
        ->when( $request->search_text && $request->criteria == 'emp', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->search_text) . "%'");
        })
        ->when( $request->search_text && $request->criteria == 'name', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->search_text) . "%'");
        })
        ->when( $request->search_text && $request->criteria == 'job', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.jobcode_desc) LIKE '%" . strtolower($request->search_text) . "%'");
        })
        ->when( $request->search_text && $request->criteria == 'dpt', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->search_text) . "%'");
        });

        return $demoWhere;
    }

    protected function ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
        // Base Where Clause
        $edemoWhere = EmployeeDemo::join('admin_orgs', function($join) {
            $join->on('employee_demo.organization', '=', 'admin_orgs.organization')
            ->on('employee_demo.level1_program', '=', 'admin_orgs.level1_program')
            ->on('employee_demo.level2_division', '=', 'admin_orgs.level2_division')
            ->on('employee_demo.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('employee_demo.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->when( $elevel0, function ($q) use($elevel0) {
            return $q->where('employee_demo.organization', $elevel0->name);
        })
        ->when( $elevel1, function ($q) use($elevel1) {
            return $q->where('employee_demo.level1_program', $elevel1->name);
        })
        ->when( $elevel2, function ($q) use($elevel2) {
            return $q->where('employee_demo.level2_division', $elevel2->name);
        })
        ->when( $elevel3, function ($q) use($elevel3) {
            return $q->where('employee_demo.level3_branch', $elevel3->name);
        })
        ->when( $elevel4, function ($q) use($elevel4) {
            return $q->where('employee_demo.level4', $elevel4->name);
        });
        return $edemoWhere;
    }

    protected function abaseFilteredWhere($request, $alevel0, $alevel1, $alevel2, $alevel3, $alevel4) {
        // Base Where Clause
        $ademoWhere = EmployeeDemo::join('admin_orgs', function($join) {
            $join->on('employee_demo.organization', '=', 'admin_orgs.organization')
            ->on('employee_demo.level1_program', '=', 'admin_orgs.level1_program')
            ->on('employee_demo.level2_division', '=', 'admin_orgs.level2_division')
            ->on('employee_demo.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('employee_demo.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->when( $alevel0, function ($q) use($alevel0) {
            return $q->where('employee_demo.organization', $alevel0->name);
        })
        ->when( $alevel1, function ($q) use($alevel1) {
            return $q->where('employee_demo.level1_program', $alevel1->name);
        })
        ->when( $alevel2, function ($q) use($alevel2) {
            return $q->where('employee_demo.level2_division', $alevel2->name);
        })
        ->when( $alevel3, function ($q) use($alevel3) {
            return $q->where('employee_demo.level3_branch', $alevel3->name);
        })
        ->when( $alevel4, function ($q) use($alevel4) {
            return $q->where('employee_demo.level4', $alevel4->name);
        })
        ->when( $request->asearch_text && $request->acriteria == 'all', function ($q) use($request) {
            $q->where(function($query) use ($request) {
                
                return $query->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->asearch_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->asearch_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.jobcode_desc) LIKE '%" . strtolower($request->asearch_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->asearch_text) . "%'");
            });
        })
        ->when( $request->asearch_text && $request->acriteria == 'emp', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->asearch_text) . "%'");
        })
        ->when( $request->esearch_text && $request->acriteria == 'name', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->asearch_text) . "%'");
        })
        ->when( $request->esearch_text && $request->acriteria == 'job', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.jobcode_desc) LIKE '%" . strtolower($request->asearch_text) . "%'");
        })
        ->when( $request->esearch_text && $request->acriteria == 'dpt', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->asearch_text) . "%'");
        });
        return $ademoWhere;
    }

    protected function baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4) {
        // Base Where Clause
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);

        $sql_level0 = clone $demoWhere; 
        $sql_level0->join('organization_trees', function($join) use($level0) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->where('organization_trees.level', '=', 0);
            });
            
        $sql_level1 = clone $demoWhere; 
        $sql_level1->join('organization_trees', function($join) use($level0, $level1) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->where('organization_trees.level', '=', 1);
            });
            
        $sql_level2 = clone $demoWhere; 
        $sql_level2->join('organization_trees', function($join) use($level0, $level1, $level2) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->where('organization_trees.level', '=', 2);    
            });    
            
        $sql_level3 = clone $demoWhere; 
        $sql_level3->join('organization_trees', function($join) use($level0, $level1, $level2, $level3) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->where('organization_trees.level', '=', 3);    
            });
            
        $sql_level4 = clone $demoWhere; 
        $sql_level4->join('organization_trees', function($join) use($level0, $level1, $level2, $level3, $level4) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->on('employee_demo.level4', '=', 'organization_trees.level4')
                ->where('organization_trees.level', '=', 4);
            });
        return  [$sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4];
    }

    protected function ebaseFilteredSQLs($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
        // Base Where Clause
        $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);

        $esql_level0 = clone $edemoWhere; 
        $esql_level0->join('organization_trees', function($join) use($elevel0) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->where('organization_trees.level', '=', 0);
            });
            
        $esql_level1 = clone $edemoWhere; 
        $esql_level1->join('organization_trees', function($join) use($elevel0, $elevel1) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->where('organization_trees.level', '=', 1);
            });
            
        $esql_level2 = clone $edemoWhere; 
        $esql_level2->join('organization_trees', function($join) use($elevel0, $elevel1, $elevel2) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->where('organization_trees.level', '=', 2);    
            });    
            
        $esql_level3 = clone $edemoWhere; 
        $esql_level3->join('organization_trees', function($join) use($elevel0, $elevel1, $elevel2, $elevel3) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->where('organization_trees.level', '=', 3);    
            });
            
        $esql_level4 = clone $edemoWhere; 
        $esql_level4->join('organization_trees', function($join) use($elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->on('employee_demo.level4', '=', 'organization_trees.level4')
                ->where('organization_trees.level', '=', 4);
            });

        return  [$esql_level0, $esql_level1, $esql_level2, $esql_level3, $esql_level4];
    }

    protected function abaseFilteredSQLs($request, $alevel0, $alevel1, $alevel2, $alevel3, $alevel4) {
        // Base Where Clause
        $ademoWhere = $this->abaseFilteredWhere($request, $alevel0, $alevel1, $alevel2, $alevel3, $alevel4);

        $asql_level0 = clone $ademoWhere; 
        $asql_level0->join('organization_trees', function($join) use($alevel0) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->where('organization_trees.level', '=', 0);
            });
            
        $asql_level1 = clone $ademoWhere; 
        $asql_level1->join('organization_trees', function($join) use($alevel0, $alevel1) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->where('organization_trees.level', '=', 1);
            });
            
        $asql_level2 = clone $ademoWhere; 
        $asql_level2->join('organization_trees', function($join) use($alevel0, $alevel1, $alevel2) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->where('organization_trees.level', '=', 2);    
            });    
            
        $asql_level3 = clone $ademoWhere; 
        $asql_level3->join('organization_trees', function($join) use($alevel0, $alevel1, $alevel2, $alevel3) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->where('organization_trees.level', '=', 3);    
            });
            
        $asql_level4 = clone $ademoWhere; 
        $asql_level4->join('organization_trees', function($join) use($alevel0, $alevel1, $alevel2, $alevel3, $alevel4) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->on('employee_demo.level4', '=', 'organization_trees.level4')
                ->where('organization_trees.level', '=', 4);
            });

        return  [$asql_level0, $asql_level1, $asql_level2, $asql_level3, $asql_level4];
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function manageindex(Request $request)
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

        $criteriaList = array(
            'all' => 'All',
            'gt' => 'Goal Title', 
            'cby'=> 'Created By',
        );

        return view('hradmin.goalbank.manageindex', compact ('request', 'criteriaList'));
    }

    public function managegetList(Request $request) {
        if ($request->ajax()) {
            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;

            $query = Goal::withoutGlobalScopes()
            ->join('users', 'goals.user_id', '=', 'users.id')
            ->leftjoin('employee_demo', 'users.guid', '=', 'employee_demo.guid')
            ->join('admin_orgs', function($join) {
                $join->on('employee_demo.organization', '=', 'admin_orgs.organization')
                ->on('employee_demo.level1_program', '=', 'admin_orgs.level1_program')
                ->on('employee_demo.level2_division', '=', 'admin_orgs.level2_division')
                ->on('employee_demo.level3_branch', '=', 'admin_orgs.level3_branch')
                ->on('employee_demo.level4', '=', 'admin_orgs.level4');
            })
            ->where('admin_orgs.user_id', '=', Auth::id())
            ->where('is_library', true)
            ->when( $request->search_text && $request->criteria == 'all', function ($q) use($request) {
                $q->where(function($query) use ($request) {
                    return $query->whereRaw("LOWER(goals.title) LIKE '%" . strtolower($request->search_text) . "%'")
                        ->orWhereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->search_text) . "%'");
                });
            })
            ->when( $request->search_text && $request->criteria == 'gt', function ($q) use($request) {
                return $q->whereRaw("LOWER(goals.title) LIKE '%" . strtolower($request->search_text) . "%'");
            })
            ->when( $request->search_text && $request->criteria == 'cby', function ($q) use($request) {
                return $q->whereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->search_text) . "%'");
            })
            ->distinct()
            ->select
            (
                'goals.id',
                'goals.title',
                'goals.created_at',
            )
            ->addselect(['goal_type_name' => GoalType::select('name')->whereColumn('goal_type_id', 'goal_types.id')->limit(1)])
            ->addselect(['creator_name' => User::select('name')->whereColumn('goals.user_id', 'users.id')->limit(1)]);
            return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('mandatory', function ($row) {
                return $row->is_mandatory ? "Mandatory" : "Suggested";
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('F d, Y') : null;
            })
            ->addColumn('audience', function ($row) {
                return $row->sharedWith()->count();
            })
            ->addcolumn('action', function($row) {
                $btn = '<a href="' . route('hradmin.goalbank.editpage', $row->id) . '" class="view-modal btn btn-xs btn-primary" aria-label="Edit Goal for Organization" value="'. $row->id .'"><i class="fas fa-users"></i>&nbsp;Edit</a>';
                $btn = $btn . '&nbsp;<a href="' . route('hradmin.goalbank.editone', $row->id) . '" class="view-modal btn btn-xs btn-primary" aria-label="Edit Goal For Individuals" value="'. $row->id .'"><i class="fas fa-user-edit"></i>&nbsp;Edit</a>';
                $btn = $btn . '&nbsp;&nbsp;&nbsp;<a href="/hradmin/goalbank/deletegoal/' . $row->id . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_goal" value="'. $row->id .'"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->rawColumns(['goal_type_name', 'goals.created_by', 'action'])
            ->make(true);
        }
    }

    public function getgoalinds(Request $request, $goal_id) {
        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
        if ($request->ajax()) {
            $query = Goal::withoutGlobalScopes()
            ->where('goals.id', '=', $goal_id)
            ->leftjoin('goals_shared_with', 'goals.id', '=', 'goals_shared_with.goal_id')
            ->join('users', 'users.id', '=', 'goals_shared_with.user_id')
            ->leftjoin('employee_demo', 'employee_demo.guid', '=', 'users.guid')
            ->select (
                'employee_demo.employee_id',
                'employee_demo.employee_name',
                'employee_demo.jobcode_desc',
                'employee_demo.organization',
                'employee_demo.level1_program',
                'employee_demo.level2_division',
                'employee_demo.level3_branch',
                'employee_demo.level4',
                'employee_demo.deptid',
                'goals.id as goal_id',
                'goals_shared_with.id as share_id'
            );
            return Datatables::of($query)
            ->addIndexColumn()
            ->addcolumn('action', function($row) {
                $btn = '<a href="/hradmin/goalbank/deleteindividual/' . $row->share_id . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_user" value="'. $row->share_id .'"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
             ->make(true);
        }
    }

    private function getDropdownValues(&$mandatoryOrSuggested) {
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
        ->where('goal_tags.goal_id', '=', $goal_id)
        ->whereExists(function($exist) {
            $exist->select(DB::raw(1))
            ->from('goals')
            ->join('users', 'users.id', '=', 'goals.user_id')
            ->join('employee_demo', 'employee_demo.guid', '=', 'users.guid')
            ->join('admin_orgs', function($join) {
                $join->on('employee_demo.organization', '=', 'admin_orgs.organization')
                ->on('employee_demo.level1_program', '=', 'admin_orgs.level1_program')
                ->on('employee_demo.level2_division', '=', 'admin_orgs.level2_division')
                ->on('employee_demo.level3_branch', '=', 'admin_orgs.level3_branch')
                ->on('employee_demo.level4', '=', 'admin_orgs.level4');
            })
            ->where('admin_orgs.user_id', '=', Auth::id())
            ->where('goals.id', '=', 'goal_tags.goal_id'); 
        } )
        ->where('is_library', true)
        ->delete();
        $query2 = GoalBankOrg::where('goal_id', '=', $goal_id)
        ->whereExists(function($exist) {
            $exist->select(DB::raw(1))
            ->from('goals')
            ->join('users', 'users.id', '=', 'goals.user_id')
            ->join('employee_demo', 'employee_demo.guid', '=', 'users.guid')
            ->join('admin_orgs', function($join) {
                $join->on('employee_demo.organization', '=', 'admin_orgs.organization')
                ->on('employee_demo.level1_program', '=', 'admin_orgs.level1_program')
                ->on('employee_demo.level2_division', '=', 'admin_orgs.level2_division')
                ->on('employee_demo.level3_branch', '=', 'admin_orgs.level3_branch')
                ->on('employee_demo.level4', '=', 'admin_orgs.level4');
            })
            ->where('admin_orgs.user_id', '=', Auth::id())
            ->where('goals.id', '=', 'goal_bank_orgs.goal_id');
        })
        ->where('is_library', true)
        ->delete();
        $query3 = DB::table('goals_shared_with')
        ->where('goals_shared_with.goal_id', '=', $goal_id)
        ->whereExists(function($exist) {
            $exist->select(DB::raw(1))
            ->from('goals')
            ->join('users', 'users.id', '=', 'goals.user_id')
            ->join('employee_demo', 'employee_demo.guid', '=', 'users.guid')
            ->join('admin_orgs', function($join) {
                $join->on('employee_demo.organization', '=', 'admin_orgs.organization')
                ->on('employee_demo.level1_program', '=', 'admin_orgs.level1_program')
                ->on('employee_demo.level2_division', '=', 'admin_orgs.level2_division')
                ->on('employee_demo.level3_branch', '=', 'admin_orgs.level3_branch')
                ->on('employee_demo.level4', '=', 'admin_orgs.level4');
            })
            ->where('admin_orgs.user_id', '=', Auth::id())
            ->where('goals.id', '=', 'goals_shared_with.goal_id');
        })
        ->where('is_library', true)
        ->delete();
        $query4 = DB::table('goals')
        ->where('goals.id', '=', $goal_id)
        ->whereExists(function($exist) {
            $exist->select(DB::raw(1))
            ->from('users', 'users.id', '=', 'goals.user_id')
            ->join('employee_demo', 'employee_demo.guid', '=', 'users.guid')
            ->join('admin_orgs', function($join) {
                $join->on('employee_demo.organization', '=', 'admin_orgs.organization')
                ->on('employee_demo.level1_program', '=', 'admin_orgs.level1_program')
                ->on('employee_demo.level2_division', '=', 'admin_orgs.level2_division')
                ->on('employee_demo.level3_branch', '=', 'admin_orgs.level3_branch')
                ->on('employee_demo.level4', '=', 'admin_orgs.level4');
            })
            ->where('admin_orgs.user_id', '=', Auth::id())
            ->where('goals.id', '=', 'goals_shared_with.goal_id');
        })
        ->where('is_library', true)
        ->delete();
        return redirect()->back();
    }

}
