<?php

namespace App\Http\Controllers\SysAdmin;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserDemoJrView;
use App\Models\EmployeeDemoTree;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;   
use Carbon\Carbon;   
use App\Models\EmployeeSupervisor;
use App\Http\Controllers\DashboardController;


class SupervisorOverridesController extends Controller {

    public function addnew(Request $request) {
        $errors = session('errors');
        $old_selected_emp_ids = [];
        $eold_selected_emp_ids = []; 
        $old_selected_org_nodes = []; 
        $eold_selected_org_nodes = []; 
        $old_input_reason = null; 
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
            $old_input_reason = isset($old['input_reason']) ? $old['input_reason'] : null;
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
        session()->put('_old_input', [
            'input_reason' => $request->input_reason,
        ]);
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        $request->session()->flash('edd_level0', $request->edd_level0);
        $request->session()->flash('edd_level1', $request->edd_level1);
        $request->session()->flash('edd_level2', $request->edd_level2);
        $request->session()->flash('edd_level3', $request->edd_level3);
        $request->session()->flash('edd_level4', $request->edd_level4);
        $request->session()->flash('euserCheck', $request->euserCheck);  // Dynamic load 
        $request->session()->flash('input_reason', $request->input_reason);  
        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, "");
        $edemoWhere = $this->baseFilteredWhere($request, "e");
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
                'u.deptid'
            ])
            ->pluck('u.employee_id');        
        $ematched_emp_ids = clone $matched_emp_ids;
        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();
        $yesOrNo = [
            [ "id" => 0, "name" => 'No' ],
            [ "id" => 1, "name" => 'Yes' ],
        ];
        return view('sysadmin.supervisoroverrides.addnew', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'ematched_emp_ids', 'old_selected_emp_ids', 'eold_selected_emp_ids', 'old_selected_org_nodes', 'eold_selected_org_nodes', 'yesOrNo', 'old_input_reason') );
    }

    public function saveall(Request $request) {
        $input = $request->all();
        $rules = [ 
            'input_reason' => 'required',
            // 'userCheck' => 'required|array',
            'euserCheck' => 'required|array|between:1,1',
        ];
        $messages = [ 
            'required' => 'This field is required.',
            // 'min' => 'At lease 1 employee is required.',
            'between' => '1 supervisor is required.',
        ];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route(request()->segment(1).'.supervisoroverrides')
                ->with('message', " There are one or more errors on the page. Please review and try again.")    
                ->withErrors($validator)
                ->withInput();
        }
        $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $eselected_emp_ids = $request->eselected_emp_ids ? json_decode($request->eselected_emp_ids) : [];
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $eselected_org_nodes = $request->eselected_org_nodes ? json_decode($request->eselected_org_nodes) : [];
        $employee_ids = ($request->userCheck) ? $request->userCheck : [];
        $employees = User::select('users.id', 'users.employee_id')
            ->whereIn('users.employee_id', $selected_emp_ids )
            ->whereRaw("EXISTS (SELECT 1 FROM employee_demo AS ed WHERE ed.employee_id = users.employee_id AND ed.date_deleted IS NULL)")
            ->distinct()
            ->get() ;
        $supervisor = User::select('users.id', 'users.employee_id')
            ->whereIn('users.employee_id', $eselected_emp_ids )
            ->whereRaw("EXISTS (SELECT 1 FROM employee_demo AS ed WHERE ed.employee_id = users.employee_id AND ed.date_deleted IS NULL)")
            ->first() ;

        $reason = $request->input_reason;
        $action = new DashboardController;
        foreach ($employees as $eeOne) {
            EmployeeSupervisor::updateOrCreate([
                'user_id' => $eeOne->id,
            ], [
                'supervisor_id' => $supervisor->id,
                'reason' => $reason,
                'updated_by' => Auth::id(),
            ]);
            $action->updateSupervisorDetails($eeOne->id);
        }   
        return redirect()->route(request()->segment(1).'.supervisoroverrides')
            ->with('success', 'Supervisor override successful.');
    }

    public function loadOrganizationTree(Request $request, $index) {
        switch ($index) {
            case 2:
                $option = 'e';
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
        if($request->ajax()){
            switch ($index) {
                case 2:
                    $eorgs = $orgs;
                    $ecountByOrg = $countByOrg;
                    $eempIdsByOrgId = $empIdsByOrgId;
                    return view('sysadmin.supervisoroverrides.partials.erecipient-tree', compact('eorgs','ecountByOrg','eempIdsByOrgId') );
                    break;
                default:
                    return view('sysadmin.supervisoroverrides.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
                    break;
            }
        }
    }

    public function getDatatableEmployees(Request $request, $index) {
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
        if($request->ajax()){
            $demoWhere = $this->baseFilteredWhere($request, $option);
            $sql = clone $demoWhere; 
            $employees = $sql->selectRaw("
                u.user_id,
                u.employee_id, 
                u.employee_name, 
                u.jobcode_desc, 
                u.employee_email, 
                u.organization, 
                u.level1_program, 
                u.level2_division, 
                u.level3_branch, 
                u.level4, 
                u.deptid
            ");
            if($option == '') {
                $employees = $employees->whereRaw("NOT EXISTS (SELECT 1 FROM employee_supervisor AS xes WHERE xes.user_id = u.user_id AND xes.deleted_at IS NULL)");
            }
            switch ($index) {
                case 2:
                    break;
                case 3:
                    break;
                default:
                    $option = '';
                    break;
            }
            return Datatables::of($employees)
                ->addColumn("{$option}select_users", static function ($employee) use($option) {
                        return '<input pid="1335" type="checkbox" id="'.$option.'userCheck'. 
                            $employee->employee_id.'" name="'.$option.'userCheck[]" value="'.$employee->employee_id.'" class="dt-body-center">';
                })
                ->rawColumns(["{$option}select_users"])
                ->make(true);
        }
    }

    public function getUsers(Request $request) {
        $search = $request->search;
        $users =  User::whereRaw("name like '%".$search."%'")->whereNotNull('email')->paginate();
        return ['data'=> $users];
    }

    public function getEmployees(Request $request, $id, $index) {
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
        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = $this->baseFilteredSQLs($request, $option);
        $rows = $sql_level4->where('id', $id)
            ->union( $sql_level3->where('id', $id) )
            ->union( $sql_level2->where('id', $id) )
            ->union( $sql_level1->where('id', $id) )
            ->union( $sql_level0->where('id', $id) );
        $employees = $rows->get();
        $parent_id = $id;
        $page = 'sysadmin.supervisoroverrides.partials.employee';
        if($option == 'e'){
            $eparent_id = $parent_id;
            $eemployees = $employees;
            $page = 'sysadmin.supervisoroverrides.partials.'.$option.'employee';
        } 
        if($option == 'a'){
            $aparent_id = $parent_id;
            $aemployees = $employees;
            $page = 'sysadmin.supervisoroverrides.partials.'.$option.'employee';
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

    protected function search_criteria_list_v2() {
        return [
            'all' => 'All',
            'u.employee_name'=> 'Employee Name',
            'u.employee_id' => 'Employee ID', 
            'su.employee_id' => 'Supervisor ID', 
            'su.employee_name'=> 'Supervisor Name',
        ];
    }

    protected function baseFilteredWhere(Request $request, $option = null) {
        return UserDemoJrView::from('user_demo_jr_view AS u')
            ->whereNull('u.date_deleted')
            ->when("{$request->{$option.'dd_level0'}}", function($q) use($request, $option) { return $q->whereRaw("u.organization_key = {$request->{$option.'dd_level0'}}"); })
            ->when("{$request->{$option.'dd_level1'}}", function($q) use($request, $option) { return $q->whereRaw("u.level1_key = {$request->{$option.'dd_level1'}}"); })
            ->when("{$request->{$option.'dd_level2'}}", function($q) use($request, $option) { return $q->whereRaw("u.level2_key = {$request->{$option.'dd_level2'}}"); })
            ->when("{$request->{$option.'dd_level3'}}", function($q) use($request, $option) { return $q->whereRaw("u.level3_key = {$request->{$option.'dd_level3'}}"); })
            ->when("{$request->{$option.'dd_level4'}}", function($q) use($request, $option) { return $q->whereRaw("u.level4_key = {$request->{$option.'dd_level4'}}"); })
            ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" != 'all', function($q) use($request, $option) { return $q->whereRaw("u.{$request->{$option.'criteria'}} like '%{$request->{$option.'search_text'}}%'"); })
            ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" == 'all', function($q) use($request, $option) { return $q->whereRaw("(u.employee_id LIKE '%{$request->{$option.'search_text'}}%' OR u.employee_name LIKE '%{$request->{$option.'search_text'}}%' OR u.jobcode_desc LIKE '%{$request->{$option.'search_text'}}%' OR u.deptid LIKE '%{$request->{$option.'search_text'}}%')"); })
            ;
        }

    protected function baseFilteredSQLs(Request $request, $option = null) {
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
                ->where('o.level', 3);    
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

    protected function baseFilteredSQLs2(Request $request, $option = null) {
        $demoWhere = $this->baseFilteredWhere($request, $option);
        $sql_level0 = clone $demoWhere; 
        $sql_level0->where('u.level', 0);
        $sql_level1 = clone $demoWhere; 
        $sql_level1->where('u.level', 1);
        $sql_level2 = clone $demoWhere; 
        $sql_level2->where('u.level', 2);    
        $sql_level3 = clone $demoWhere; 
        $sql_level3->where('u.level', 3);    
        $sql_level4 = clone $demoWhere; 
        $sql_level4->where('u.level', 4);
        return  [$sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function manageindex(Request $request)
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
        $criteriaList = $this->search_criteria_list_v2();
        return view('sysadmin.supervisoroverrides.manageindex', compact ('request', 'criteriaList', 'old_selected_emp_ids'));
    }

    public function manageindexlist(Request $request) {
        if ($request->ajax()) {
            $query = UserDemoJrView::from('user_demo_jr_view AS u')
                ->join('employee_supervisor AS es', 'es.user_id', 'u.user_id')
                ->join('user_demo_jr_view AS su', 'su.user_id', 'es.supervisor_id')
                ->leftjoin('user_demo_jr_view AS au', 'au.user_id', 'es.updated_by')
                ->whereNull('es.deleted_at')
                ->whereNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text && $request->criteria != 'all', function($q) use($request) { return $q->whereRaw("{$request->criteria} like '%{$request->search_text}%'"); })
                ->when($request->search_text && $request->criteria == 'all', function($q) use($request) { return $q->whereRaw("(u.employee_id LIKE '%{$request->search_text}%' OR u.employee_name LIKE '%{$request->search_text}%' OR su.employee_id LIKE '%{$request->search_text}%' OR su.employee_name LIKE '%{$request->search_text}%')"); })
                ->selectRaw ("
                    u.employee_id,
                    u.employee_name,
                    u.employee_email,
                    su.employee_id as supervisor_emplid,
                    su.employee_name as supervisor_name,
                    su.employee_email as supervisor_email,
                    u.jobcode_desc,
                    u.organization,
                    u.level1_program,
                    u.level2_division,
                    u.level3_branch,
                    u.level4,
                    u.deptid,
                    es.created_at,
                    es.updated_at,
                    es.updated_by,
                    au.employee_name AS updated_by_name,
                    es.id as override_id
                ");
            return Datatables::of($query)
                ->addColumn("select_users", static function ($row) {
                    return '<input pid="1335" type="checkbox" id="userCheck'.$row->override_id.'" name="userCheck[]" value="'.$row->override_id.'" class="dt-body-center">';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('M d, Y H:i:s') : null;
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('M d, Y H:i:s') : null;
                })
                ->addcolumn('action', function($row) {
                    $btn = '<a href="' . route(request()->segment(1) . '.supervisoroverrides.deleteoverride', ['id' => $row->override_id]) . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_goal" value="' . $row->override_id . '"><i class="fa fa-trash"></i></a>';
                    return $btn;
                })
                ->rawColumns(['select_users', 'created_at', 'updated_at', 'action'])
                ->make(true);
        }
    }

    public function deleteOverride(Request $request, $id) {
        EmployeeSupervisor::where('id', $id)->update( [ 'updated_by' => Auth::id() ] );
        $entry = EmployeeSupervisor::whereRaw("id = {$id}")->first();
        $item = $entry->user_id;
        EmployeeSupervisor::where('id', $id)->delete();
        $action = new DashboardController;
        $action->updateSupervisorDetails($item);
        return redirect()->back();
    }

    public function deleteMultiOverride(Request $request, $ids) {
        $decoded = json_decode($ids);
        EmployeeSupervisor::whereIn('id', $decoded)->update( [ 'updated_by' => Auth::id() ] );
        $entries = EmployeeSupervisor::whereIn('id', $decoded)->select('user_id')->get();
        EmployeeSupervisor::whereIn('id', $decoded)->delete();
        $action = new DashboardController;
        foreach($entries AS $entry) {
            $action->updateSupervisorDetails($entry->user_id);
        }
        return redirect()->back();
    }

}
