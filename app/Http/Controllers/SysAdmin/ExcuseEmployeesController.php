<?php

namespace App\Http\Controllers\SysAdmin;



use App\Models\EmployeeDemo;
use App\Models\ExcusedReason;
use App\Models\EmployeeDemoTree;
use App\Models\UserDemoJrView;
use App\Models\UserDemoJrHistoryView;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;


class ExcuseEmployeesController extends Controller {

    public function addindex(Request $request) {
        $errors = session('errors');
        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_evel4);
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request);
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
            ->pluck('u.employee_id');        
        $criteriaList = $this->search_criteria_list();
        $reasons = ExcusedReason::where('id', '>', 2)->get();
        $reasons2 = ExcusedReason::where('id', '<=', 2)->get();
        $yesOrNo = [
            [ "id" => 0, "name" => 'No' ],
            [ "id" => 1, "name" => 'Yes' ],
        ];
        $yesOrNo2 = [
            [ "id" => 0, "name" => 'No' ],
            [ "id" => 1, "name" => 'Yes' ],
        ];
        return view('shared.excuseemployees.addindex', compact('criteriaList','matched_emp_ids', 'old_selected_emp_ids', 'old_selected_org_nodes', 'reasons', 'reasons2', 'yesOrNo', 'yesOrNo2') );
    }

    public function managehistory(Request $request) {
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
        $criteriaList = $this->search_criteria_list_history();
        return view('shared.excuseemployees.managehistory', compact ('request', 'criteriaList'));
    }

    public function managehistorylist(Request $request) {
        if ($request->ajax()) {
            $query = UserDemoJrHistoryView::from('user_demo_jr_history_view as u')
            ->whereNull('u.date_deleted')
            ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
            ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
            ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
            ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
            ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
            ->when($request->search_text && $request->criteria != 'all', function($q) use($request) { return $q->whereRaw("{$request->criteria} like '%{$request->search_text}%'"); })
            ->when($request->search_text && $request->criteria == 'all', function($q) use($request) { return $q->whereRaw("(employee_id LIKE '%{$request->search_text}%' OR employee_name LIKE '%{$request->search_text}%' OR j_excusedtype LIKE '%{$request->search_text}%' OR j_excused_reason_desc LIKE '%{$request->search_text}%' OR excused_by_name LIKE '%{$request->search_text}%')"); })
            ->selectRaw ("
                u.user_id AS id
                , u.employee_id
                , u.employee_name
                , u.jobcode_desc
                , u.j_excusedtype
                , u.j_excused_reason_desc
                , u.excused_by_name
                , '' as startdate_string
                , '' as enddate_string
                , u.organization
                , u.level1_program
                , u.level2_division
                , u.level3_branch
                , u.level4
                , u.deptid
                ");
            return Datatables::of($query)
            ->addIndexColumn()
            ->editColumn('u.employee_name', function($row) {
                return $row->employee_name ? $row->employee_name : $row->name;
            })
            ->editColumn('startdate_string', function($row) {
                return Carbon::parse($row->j_created_at)->format('M d, Y');
            })
            ->editColumn('enddate_string', function($row) {
                $preStart = Carbon::parse($row->j_created_at)->toDateString();
                $preEnd = Carbon::parse($row->k_created_at)->subdays(1)->toDateString();
                if ($preEnd < $preStart) {
                    $preEnd = $preStart;
                }
                return Carbon::parse($preEnd)->format('M d, Y');
            })
            ->make(true);
        }
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
                    return view('shared.excuseemployees.partials.recipient-tree2', compact('eorgs','eempIdsByOrgId') );
                    break;
                default:
                    return view('shared.excuseemployees.partials.recipient-tree', compact('orgs', 'countByOrg', 'empIdsByOrgId') );
                    break;
            }
        }
    }

public function getDatatableEmployees(Request $request) {
        if($request->ajax()){
            $demoWhere = $this->baseFilteredWhere($request);
            // Store input values
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
            ]);
            $sql = clone $demoWhere; 
            $employees = $sql->selectRaw("
                user_id as id
                , guid
                , excused_flag
                , excused_reason_id
                , excused_updated_by
                , excused_updated_at
                , employee_id
                , employee_name
                , jobcode
                , jobcode_desc
                , employee_email
                , organization
                , level1_program
                , level2_division
                , level3_branch
                , level4
                , deptid
                , employee_status
                , due_date_paused
                , excused_type
                , current_manual_excuse
                , created_by_id
                , updated_by_id
                , updated_at
                , created_at as j_created_at
                , reason_id
                , reason_name
                , excusedtype
                , excusedlink
                , excused_by_name
                , created_at_string
                , employee_id_search
                , employee_name_search
                ");
            return Datatables::of($employees)
                ->addColumn('select_users', static function ($employee) {
                        return '<input pid="1335" type="checkbox" id="userCheck'. 
                            $employee->employee_id .'" name="userCheck[]" value="'. $employee->employee_id .'" class="dt-body-center">';
                })
                ->editColumn('created_at_string', function($row) {
                    if ($row->created_at_string) {
                        return Carbon::parse($row->created_at_string)->format('M d, Y');
                    } else {
                        return '';
                    }
                })
                ->editColumn('excusedlink', function($row) {
                    $text = $row->excusedlink;
                    $excused_type = $row->excused_type;
                    $current_status = $row->current_employee_status;
                    $excused = json_encode([
                        'excused_flag' => $row->excused_flag,
                        'reason_id' => $row->excused_reason_id
                    ]);
                    $reasons = ExcusedReason::where('id', '>', 2)->get();
                    $reasons2 = ExcusedReason::where('id', '<=', 2)->get();
                    $yesOrNo = [
                        [ "id" => 0, "name" => 'No' ],
                        [ "id" => 1, "name" => 'Yes' ],
                    ];
                    $yesOrNo2 = [
                        [ "id" => 0, "name" => 'No' ],
                        [ "id" => 1, "name" => 'Yes' ],
                    ];
                    return view('shared.excuseemployees.partials.link', compact(["row", "excused", "text", "excused_type", "current_status", "yesOrNo", "yesOrNo2"]));
                })
                ->rawColumns(['select_users'])
                ->make(true);
        }
    }

    public function saveexcuse(Request $request) {
        $input = $request->all();
        $rules = [
            'excused_reason' => 'required'
        ];
        $messages = [
            'required' => 'The :attribute field is required.',
        ];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route(request()->segment(1).'.excuseemployees')
            ->with('message', " There are one or more errors on the page. Please review and try again.")    
            ->withErrors($validator)
            ->withInput();
        }
        $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        Log::info($selected_emp_ids);
        $selection = EmployeeDemo::from('employee_demo as d')
            ->join('users as u', 'd.employee_id', 'u.employee_id')
            ->whereIn('d.employee_id', $selected_emp_ids )
            ->distinct()
            ->select ('u.id')
            ->get() ;
        foreach ($selection as $newId) {
            $result = User::where('id', '=', $newId->id)->update([
                'excused_flag' => true,
                'excused_reason_id' => $request->excused_reason,
                'excused_updated_by' => Auth::id(),
                'excused_updated_at' => Carbon::now(),
            ]);
        }
        return redirect()->route(request()->segment(1).'.excuseemployees.addindex')
            ->with('success', 'Excuse employee(s) successful.');
    }

    public function getUsers(Request $request){
        $search = $request->search;
        $users =  User::whereRaw("name like '%".$search."%'")->whereNotNull('email')->paginate();
        return ['data'=> $users];
    }

    public function getEmployees(Request $request,  $id) {
        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, "");
        $rows = $sql_level4->where('id', $id)
            ->union( $sql_level3->where('id', $id) )
            ->union( $sql_level2->where('id', $id) )
            ->union( $sql_level1->where('id', $id) )
            ->union( $sql_level0->where('id', $id) );
        $employees = $rows->get();
        $parent_id = $id;
        return view('shared.excuseemployees.partials.employee', compact('parent_id', 'employees') ); 
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'employee_id' => 'Employee ID', 
            'employee_name'=> 'Employee Name',
            'excusedtype' => 'Excuse Type', 
            'edj_excused_reason_desc' => 'Excuse Reason',
            'excused_by_name' => 'Excused By'
        ];
    }

    protected function search_criteria_list_history() {
        return [
            'all' => 'All',
            'employee_id' => 'Employee ID', 
            'employee_name'=> 'Employee Name',
            'j_excusedtype' => 'Excuse Type', 
            'j_excused_reason_desc' => 'Excuse Reason',
            'excused_by_name' => 'Excused By'
        ];
    }

    protected function baseFilteredWhere($request, $option = null) {
        return UserDemoJrView::from('user_demo_jr_view AS u')
        ->whereNull('u.date_deleted')
        ->when("{$request->{$option.'dd_level0'}}", function($q) use($request, $option) { return $q->whereRaw("u.organization_key = {$request->{$option.'dd_level0'}}"); })
        ->when("{$request->{$option.'dd_level1'}}", function($q) use($request, $option) { return $q->whereRaw("u.level1_key = {$request->{$option.'dd_level1'}}"); })
        ->when("{$request->{$option.'dd_level2'}}", function($q) use($request, $option) { return $q->whereRaw("u.level2_key = {$request->{$option.'dd_level2'}}"); })
        ->when("{$request->{$option.'dd_level3'}}", function($q) use($request, $option) { return $q->whereRaw("u.level3_key = {$request->{$option.'dd_level3'}}"); })
        ->when("{$request->{$option.'dd_level4'}}", function($q) use($request, $option) { return $q->whereRaw("u.level4_key = {$request->{$option.'dd_level4'}}"); })
        ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" != 'all', function($q) use($request, $option) { return $q->whereRaw("{$request->{$option.'criteria'}} like '%{$request->{$option.'search_text'}}%'"); })
        ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" == 'all', function($q) use($request, $option) { return $q->whereRaw("(employee_id LIKE '%{$request->{$option.'search_text'}}%' OR employee_name LIKE '%{$request->{$option.'search_text'}}%' OR excusedtype LIKE '%{$request->{$option.'search_text'}}%' OR reason_name LIKE '%{$request->{$option.'search_text'}}%' OR excused_by_name LIKE '%{$request->{$option.'search_text'}}%')"); });
    }

    protected function baseFilteredSQLs($request, $option = null) {
        // Base Where Clause
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function manageindexupdate(Request $request) {
        $query = User::where('id', '=', $request->id)
        ->update(['excused_flag' => $request->excused_flag
        , 'excused_reason_id' => $request->excused_reason_id
        , 'excused_updated_by' => Auth::id()
        , 'excused_updated_at' => Carbon::now()]);
        return redirect()->back();
    }



}
