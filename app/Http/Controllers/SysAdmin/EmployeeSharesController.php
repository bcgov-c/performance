<?php

namespace App\Http\Controllers\SysAdmin;



use App\Models\User;
use App\Models\Goal;
use App\Models\Conversation;
use App\Models\SharedElement;
use App\Models\EmployeeShare;
use App\Models\ConversationParticipant;
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


class EmployeeSharesController extends Controller
{

    public function addnew(Request $request) 
    {
        $errors = session('errors');

        $old_selected_emp_ids = [];
        $eold_selected_emp_ids = []; 
        $old_selected_org_nodes = []; 
        $eold_selected_org_nodes = []; 


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
                'jobtitle_' => $request->jobcode_desc,
                'active_since' => $request->active_since,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
                'orgCheck' => $request->orgCheck,
                'userCheck' => $request->userCheck,
            ]);
        }

        // no validation and move filter variable to old 
        if ($request->ebtn_search) {
            session()->put('_old_input', [
                'edd_level0' => $request->edd_level0,
                'edd_level1' => $request->edd_level1,
                'edd_level2' => $request->edd_level2,
                'edd_level3' => $request->edd_level3,
                'edd_level4' => $request->edd_level4,
                'ejob_titles' => $request->ejobcode_desc,
                'eactive_since' => $request->eactive_since,
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

        $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
        $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
        $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
        $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
        $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;

        $request->session()->flash('level0', $level0);
        $request->session()->flash('level1', $level1);
        $request->session()->flash('level2', $level2);
        $request->session()->flash('level3', $level3);
        $request->session()->flash('level4', $level4);
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        
        $request->session()->flash('elevel0', $elevel0);
        $request->session()->flash('elevel1', $elevel1);
        $request->session()->flash('elevel2', $elevel2);
        $request->session()->flash('elevel3', $elevel3);
        $request->session()->flash('elevel4', $elevel4);
        $request->session()->flash('euserCheck', $request->euserCheck);  // Dynamic load 
        

        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 'employee_id', 'employee_name', 'jobcode_desc', 'employee_email', 
                'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division',
                'employee_demo.level3_branch','employee_demo.level4', 'employee_demo.deptid'])
                ->orderBy('employee_id')
                ->pluck('employee_demo.employee_id');        
                $ematched_emp_ids = clone $matched_emp_ids;
        // $alert_format_list = NotificationLog::ALERT_FORMAT;
        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();
        
        $sharedElements = SharedElement::select('id', 'name')->get('name', 'id');

        return view('sysadmin.employeeshares.addnew', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'ematched_emp_ids', 'old_selected_emp_ids', 'eold_selected_emp_ids', 'old_selected_org_nodes', 'eold_selected_org_nodes', 'sharedElements') );
    
    }

    public function addindex(Request $request) 
    {

        $errors = session('errors');

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
        if ($request->btn_search || $request->ebtn_search) {
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
        $request->session()->flash('userCheck', $request->euserCheck);  // Dynamic load 

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
            , 'employee_demo.jobcode_desc'])
        ->orderBy('employee_id')
        ->pluck('employee_demo.employee_id');        

        $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
        $esql = clone $edemoWhere; 
        $ematched_emp_ids = $esql->select([ 
            'employee_demo.employee_id as eemployee_id'
            , 'employee_demo.employee_name as eemployee_name'
            , 'employee_demo.jobcode_desc as ejobcode_desc'
            , 'employee_demo.employee_email as eemployee_email'
            , 'employee_demo.organization as eorganization'
            , 'employee_demo.level1_program as elevel1_program'
            , 'employee_demo.level2_division as elevel2_division'
            , 'employee_demo.level3_branch as elevel3_branch'
            , 'employee_demo.level4 as elevel4'
            , 'employee_demo.deptid as edeptid'
            ])
        ->orderBy('eemployee_id')
        ->pluck('eemployee_id');        
        
        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();

        $sharedElements = SharedElement::select('id', 'name')->get('name', 'id');

        return view('sysadmin.employeeshares.addindex', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'ematched_emp_ids', 'old_selected_emp_ids', 'eold_selected_emp_ids', 'old_selected_org_nodes', 'eold_selected_org_nodes', 'sharedElements') );
    }

    public function saveall(Request $request) {
        $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $eselected_emp_ids = $request->eselected_emp_ids ? json_decode($request->eselected_emp_ids) : [];
        $request->userCheck = $selected_emp_ids;
        $request->euserCheck = $eselected_emp_ids;
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $eselected_org_nodes = $request->eselected_org_nodes ? json_decode($request->eselected_org_nodes) : [];

        $current_user = User::find(Auth::id());

        $employee_ids = ($request->userCheck) ? $request->userCheck : [];

        $eeToShare = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.guid', 'users.guid')
            ->whereIn('employee_demo.employee_id', $selected_emp_ids )
            ->distinct()
            ->select ('users.id')
            ->orderBy('employee_demo.employee_name')
            ->get() ;

        $shareTo = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.guid', 'users.guid')
            ->whereIn('employee_demo.employee_id', $eselected_emp_ids )
            ->distinct()
            ->select ('users.id')
            ->orderBy('employee_demo.employee_name')
            ->get() ;

        $elements = $request->input_elements;
        $reason = $request->input_reason;

        foreach ($eeToShare as $eeOne) {
            foreach ($shareTo as $toOne) {
                //skip if same
                if ($eeOne->id <> $toOne->id) {
                    $result = EmployeeShare::updateOrInsert(
                        ['user_id' => $eeOne->id
                        , 'shared_with_id' => $toOne->id],
                        ['shared_element_id' => $elements
                        , 'reason' => $reason]
                    );
                }
            }
        }

        return redirect()->route('sysadmin.employeeshares.addnew')
            ->with('success', 'Share user goal/conversation successful.');
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
            return view('sysadmin.employeeshares.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
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
            
        $erows = $esql_level4->groupBy('organization_trees.id')->select('organization_trees.id')
            ->union( $esql_level3->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $esql_level2->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $esql_level1->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $esql_level0->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->pluck('organization_trees.id'); 

        $eorgs = OrganizationTree::whereIn('id', $erows->toArray() )->get()->toTree();
        
        // Employee Count by Organization
        $ecountByOrg = $esql_level4->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row"))
        ->union( $esql_level3->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $esql_level2->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $esql_level1->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $esql_level0->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row") ) )
        ->pluck('count_row', 'organization_trees.id');  
        
        $eempIdsByOrgId = [];
        $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
        $esql = clone $edemoWhere; 
        $erows = $esql->join('organization_trees', function($join) use($request) {
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

        $eempIdsByOrgId = $erows->groupBy('id')->all();

        if($request->ajax()){
            return view('sysadmin.employeeshares.partials.erecipient-tree', compact('eorgs','eempIdsByOrgId') );
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
                , 'employee_demo.deptid'
            ]);
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
                'employee_demo.employee_id as eemployee_id'
                , 'employee_demo.employee_name as eemployee_name'
                , 'employee_demo.jobcode_desc as ejobcode_desc'
                , 'employee_demo.employee_email as eemployee_email'
                , 'employee_demo.organization as eorganization'
                , 'employee_demo.level1_program as elevel1_program'
                , 'employee_demo.level2_division as elevel2_division'
                , 'employee_demo.level3_branch as elevel3_branch'
                , 'employee_demo.level4 as elevel4'
                , 'employee_demo.deptid as edeptid'
            ]);
            return Datatables::of($eemployees)
                ->addColumn('eselect_users', static function ($eemployee) {
                        return '<input pid="1335" type="checkbox" id="euserCheck'. 
                            $eemployee->employee_id .'" name="euserCheck[]" value="'. $eemployee->eemployee_id .'" class="dt-body-center">';
                })->rawColumns(['eselect_users','action'])
                ->make(true);
        }
    }

    public function getUsers(Request $request)
    {
        $search = $request->search;
        $users =  User::whereRaw("lower(name) like '%". strtolower($search)."%'")
                    ->whereNotNull('email')->paginate();
        return ['data'=> $users];
    }


    public function getOrganizations(Request $request) {
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

    public function egetOrganizations(Request $request) {
        $eorgs = OrganizationTree::orderby('name','asc')->select('id','name')
            ->where('level',0)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
            })
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function getPrograms(Request $request) {
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

    public function egetPrograms(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id',$request->elevel0)->first() : null;
        $eorgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',1)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization', $elevel0->name );
            })
            ->groupBy('name')
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function getDivisions(Request $request) {

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

    public function egetDivisions(Request $request) {

        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;

        $eorgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('level1_program', $elevel1->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();

        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($eformatted_orgs);
    } 

    public function getBranches(Request $request) {

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

    public function egetBranches(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;
        $elevel2 = $request->elevel2 ? OrganizationTree::where('id', $request->elevel2)->first() : null;

        $eorgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('level1_program', $elevel1->name );
            })
            ->when( $elevel2 , function ($q) use($elevel2) {
                return $q->where('level2_division', $elevel2->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();

        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($eformatted_orgs);
    } 

    public function getLevel4(Request $request) {
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

    public function egetLevel4(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;
        $elevel2 = $request->elevel2 ? OrganizationTree::where('id', $request->elevel2)->first() : null;
        $elevel3 = $request->elevel3 ? OrganizationTree::where('id', $request->elevel3)->first() : null;

        $eorgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('level1_program', $elevel1->name );
            })
            ->when( $elevel2 , function ($q) use($elevel2) {
                return $q->where('level2_division', $elevel2->name );
            })
            ->when( $elevel3 , function ($q) use($elevel3) {
                return $q->where('level3_branch', $elevel3->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();

        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($eformatted_orgs);
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
        
        // if($request->ajax()){
            return view('sysadmin.employeeshares.partials.employee', compact('parent_id', 'employees') ); 
        // } 
    }

    public function egetEmployees(Request $request,  $id) {
        $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
        $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
        $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
        $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
        $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;

        list($esql_level0, $esql_level1, $esql_level2, $esql_level3, $esql_level4) = 
            $this->ebaseFilteredSQLs($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
       
        $rows = $esql_level4->where('organization_trees.id', $id)
            ->union( $esql_level3->where('organization_trees.id', $id) )
            ->union( $esql_level2->where('organization_trees.id', $id) )
            ->union( $esql_level1->where('organization_trees.id', $id) )
            ->union( $esql_level0->where('organization_trees.id', $id) );

        $eemployees = $rows->get();

        $parent_id = $id;
        
        // if($request->ajax()){
            return view('sysadmin.employeeshares.partials.employee', compact('parent_id', 'employees') ); 
        // } 
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

    protected function baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4) {
        // Base Where Clause
        $demoWhere = EmployeeDemo::when( $level0, function ($q) use($level0) {
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
        $edemoWhere = EmployeeDemo::when( $elevel0, function ($q) use($elevel0) {
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
        })
        ->when( $request->esearch_text && $request->ecriteria == 'all', function ($q) use($request) {
            $q->where(function($query) use ($request) {
                
                return $query->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->esearch_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->esearch_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.jobcode_desc) LIKE '%" . strtolower($request->esearch_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->esearch_text) . "%'");
            });
        })
        ->when( $request->esearch_text && $request->ecriteria == 'emp', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->esearch_text) . "%'");
        })
        ->when( $request->esearch_text && $request->ecriteria == 'name', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->esearch_text) . "%'");
        })
        ->when( $request->esearch_text && $request->ecriteria == 'job', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.jobcode_desc) LIKE '%" . strtolower($request->esearch_text) . "%'");
        })
        ->when( $request->esearch_text && $request->ecriteria == 'dpt', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->esearch_text) . "%'");
        });
        return $edemoWhere;
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

        $criteriaList = $this->search_criteria_list();
        $sharedElements = SharedElement::all();

        return view('sysadmin.employeeshares.manageindex', compact ('request', 'criteriaList', 'sharedElements'));
    }

    public function manageindexlist(Request $request) {
        if ($request->ajax()) {
            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;

            $query = User::withoutGlobalScopes()
            ->join('employee_shares', 'employee_shares.user_id', '=', 'users.id')
            ->leftjoin('employee_demo', 'users.guid', '=', 'employee_demo.guid')
            ->when($level0, function($q) use($level0) {return $q->where('organization', $level0->name);})
            ->when($level1, function($q) use($level1) {return $q->where('level1_program', $level1->name);})
            ->when($level2, function($q) use($level2) {return $q->where('level2_division', $level2->name);})
            ->when($level3, function($q) use($level3) {return $q->where('level3_branch', $level3->name);})
            ->when($level4, function($q) use($level4) {return $q->where('level4', $level4->name);})
            ->when($request->criteria == 'name', function($q) use($request){return $q->where('employee_name', 'like', "%" . $request->search_text . "%");})
            ->when($request->criteria == 'emp', function($q) use($request){return $q->where('employee_demo.employee_id', 'like', "%" . $request->search_text . "%");})
            ->when($request->criteria == 'job', function($q) use($request){return $q->where('jobcode_desc', 'like', "%" . $request->search_text . "%");})
            ->when($request->criteria == 'dpt', function($q) use($request){return $q->where('deptid', 'like', "%" . $request->search_text . "%");})
            ->when($request->criteria == 'all', function($q) use ($request) {
                return $q->where(function ($query2) use ($request) {
                    if($request->search_text) {
                        $query2->where('employee_demo.employee_id', 'like', "%" . $request->search_text . "%")
                        ->orWhere('employee_name', 'like', "%" . $request->search_text . "%")
                        ->orWhere('jobcode_desc', 'like', "%" . $request->search_text . "%")
                        ->orWhere('deptid', 'like', "%" . $request->search_text . "%");
                    }
                });
            })
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
                'employee_shares.user_id',
            )
            ->groupBy('employee_shares.user_id');
            return Datatables::of($query)
            ->addIndexColumn()
            ->addcolumn('action', function($row) {
                $btn = '<button class="btn btn-xs btn-primary modalbutton" role="button" data-userid="' . $row->user_id . '" data-username="' . $row->employee_name . '" data-toggle="modal" data-target="#editModal" role="button">Edit</button>';
                $btn = $btn . '&nbsp;&nbsp;&nbsp;<a href="' . route('sysadmin.employeeshares.deleteshare', $row->user_id) . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_goal" value="'. $row->user_id .'"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    public function manageindexviewshares(Request $request, $id) {
        if ($request->ajax()) {
            $query = User::withoutGlobalScopes()
            ->join('employee_shares', 'employee_shares.user_id', '=', 'users.id')
            ->join('users as u2', 'employee_shares.shared_with_id', '=', 'u2.id')
            ->leftjoin('employee_demo', 'users.guid', '=', 'employee_demo.guid')
            ->leftjoin('employee_demo as ed2', 'u2.guid', '=', 'ed2.guid')
            ->where('users.id', '=', $id)
            ->select (
                'ed2.employee_id',
                'ed2.employee_name', 
                'users.id as user_id',
                'u2.id as shared_with_id',
            )
            ->distinct();
            return Datatables::of($query)
            ->addIndexColumn()
            ->addcolumn('action', function($row) {
                $btn = '<a href="' . route('sysadmin.employeeshares.deleteitem', ['id' => $row->user_id, 'part' => $row->shared_with_id]) . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_goal" value="'. $row->id . '_' . $row->part_id .'"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        };
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

    public function deleteshare(Request $request, $id) {
        $query1 = DB::table('employee_shares')
        ->where('user_id', '=', $id)
        ->delete();
        return redirect()->back();
    }

    public function deleteitem(Request $request, $id, $part) {
        $query2 = DB::table('employee_shares')
        ->where('user_id', '=', $id)
        ->where('shared_with_id', '=', $part)
        ->delete();
        return redirect()->back();
    }

    // public function getAdminOrgs(Request $request, $model_id) {

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function manageEdit($id) {
        $users = User::where('id', '=', $id)
        ->select('email')
        ->get();
        $email = $users->first()->email;
        $roles = DB::table('roles')
        ->whereIntegerInRaw('id', [3, 4])
        ->get();
        $access = DB::table('model_has_roles')
        ->where('model_id', '=', $id)
        ->where('model_has_roles.model_type', 'App\Models\User')
        ->get();
        return view('sysadmin.employeeshares.partials.access-edit-modal', compact('roles', 'access', 'email'));
    }


}
