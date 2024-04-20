<?php

namespace App\Http\Controllers\HRAdmin;



use App\Models\EmployeeDemo;
use App\Models\ExcusedReason;
use App\Models\EmployeeDemoTree;
use App\Models\HRUserDemoJrHistoryView;
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
use App\Classes\PDPDataClass;


class ExcuseEmployeesController extends Controller {

    public $pdpData;

    public function __construct() {
        // parent::__construct();
        $this->pdpData = new PDPDataClass;
    }

    public function addindex(Request $request) {
        $errors = session('errors');
        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        $matched_emp_ids = [];
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

    public function getFilteredList(Request $request) {
        return $this->baseFilteredWhere($request, $request->option)->pluck('users.employee_id');
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
        $authId = Auth::id();
        if ($request->ajax()) {
            $query = HRUserDemoJrHistoryView::from('hr_user_demo_jr_history_view as u')
                ->whereRaw("u.auth_id = {$authId}")
                ->whereNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text && $request->criteria != 'all', function($q) use($request) { 
                    return $q->where("{$request->criteria}", 'LIKE', "%{$request->search_text}%");
                })
                ->when($request->search_text && $request->criteria == 'all', function($q) use($request) { 
                    return $q->where(function($q1) use($request) {
                        return $q1->where('employee_id', 'LIKE', "%{$request->search_text}%")
                        ->orWhere('employee_name', 'LIKE', "%{$request->search_text}%")
                        ->orWhere('u.j_excusedtype', 'LIKE', "%{$request->search_text}%")
                        ->orWhere('j_excused_reason_desc', 'LIKE', "%{$request->search_text}%")
                        ->orWhere('excused_by_name', 'LIKE', "%{$request->search_text}%"); 
                    });
                })
                ->distinct()
                ->selectRaw ("
                    u.user_id AS id
                    , u.guid
                    , u.user_name
                    , u.employee_id
                    , u.employee_name
                    , u.jobcode
                    , u.jobcode_desc
                    , u.organization
                    , u.level1_program
                    , u.level2_division
                    , u.level3_branch
                    , u.level4
                    , u.deptid
                    , u.j_created_at
                    , u.j_updated_at
                    , u.j_excused_type
                    , u.j_updated_by_id
                    , u.j_updated_by_name
                    , u.k_created_at
                    , u.k_excused_type
                    , u.reason_id
                    , u.reason_name
                    , u.j_excusedtype
                    , u.excusedlink AS j_excusedlink
                    , u.excused_by_name
                    , u.excused_updated_by
                    , u.employee_id_search
                    , u.employee_name_search
                    , u.j_updated_by_name AS j_excused_updated_by_name
                    , u.j_excused_reason_id
                    , u.j_excused_reason_desc
                    , '' as created_at_string
                    , u.j_created_at as startdate_string
                    , u.k_created_at as enddate_string
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
        $rows = $sql->select('employee_demo.orgid AS id', 'users.employee_id')
            ->groupBy('employee_demo.orgid', 'users.employee_id')
            ->orderBy('employee_demo.orgid')->orderBy('users.employee_id')
            ->get();
        $empIdsByOrgId = $rows->groupBy('employee_demo.orgid')->all();
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
            $demoWhere = $this->baseFilteredWhere($request, "");
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
            $employees = $demoWhere->selectRaw("
                users.id
                , users.guid
                , users.excused_flag
                , users.excused_reason_id
                , users.excused_updated_by
                , users.excused_updated_at
                , users.employee_id
                , employee_demo.employee_name
                , employee_demo.jobcode
                , employee_demo.jobcode_desc
                , employee_demo.employee_email
                , users_annex.organization
                , users_annex.level1_program
                , users_annex.level2_division
                , users_annex.level3_branch
                , users_annex.level4
                , employee_demo.deptid
                , employee_demo.employee_status
                , users_annex.jr_due_date_paused AS due_date_paused
                , users_annex.jr_excused_type AS excused_type
                , users_annex.jr_current_manual_excuse AS current_manual_excuse
                , users_annex.jr_created_by_id AS created_by_id
                , users_annex.jr_updated_by_id AS updated_by_id
                , users_annex.jr_updated_at AS updated_at
                , users_annex.jr_created_at AS j_created_at
                , CASE when users_annex.jr_excused_type = 'A' THEN users_annex.reason_id ELSE CASE when users.excused_flag = 1 THEN users.excused_reason_id ELSE '' END END AS reason_id
                , CASE when users_annex.jr_excused_type = 'A' THEN users_annex.reason_name ELSE CASE when users.excused_flag = 1 THEN excused_reasons.name ELSE '' END END AS reason_name
                , CASE when users_annex.jr_excused_type = 'A' THEN 'Auto' ELSE CASE when users.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype
                , CASE when users_annex.jr_excused_type = 'A' THEN 'Auto' ELSE CASE when users.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink
                , CASE when users_annex.jr_excused_type = 'A' THEN users_annex.excused_by_name ELSE CASE when users.excused_flag = 1 THEN en.name ELSE '' END END AS excused_by_name
                , CASE when users_annex.jr_excused_type = 'A' THEN users_annex.created_at_string ELSE CASE when users.excused_flag = 1 THEN users.excused_updated_at ELSE '' END END AS created_at_string
                , users.id AS employee_id_search
                , employee_demo.employee_name AS employee_name_search
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
                    $current_status = $row->employee_status;
                    $excused = json_encode([
                        'excused_flag' => $row->excused_flag,
                        'reason_id' => $row->reason_id
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
        $selection = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.employee_id', 'users.employee_id')
            ->whereIn('employee_demo.employee_id', $selected_emp_ids )
            ->distinct()
            ->select ('users.id')
            ->get() ;
        foreach ($selection as $newId) {
            $result = User::where('id', '=', $newId->id)->update([
                'excused_flag' => 1,
                'excused_reason_id' => $request->excused_reason
            ]);
        }
        return redirect()->route(request()->segment(1).'.excuseemployees.addindex')
            ->with('success', 'Excuse employee(s) successful.');
    }

    public function getUsers(Request $request)
    {
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
            'users.employee_id' => 'Employee ID', 
            'employee_demo.employee_name'=> 'Employee Name',
            'excusedtype'=> 'Excuse Type',
            'reason_name'=> 'Excuse Reason',
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
        return $this->pdpData->HRUserDemoAnnexSQL_Base($request)
        ->leftjoin(\DB::raw('users AS en USE INDEX (IDX_USERS_ID)'), 'users.excused_updated_by', 'en.id')
        ->leftjoin('excused_reasons', 'users.excused_reason_id', 'excused_reasons.id')
        ->whereNull('employee_demo.date_deleted')
        ->when("{$request->{$option.'dd_level0'}}", function($q) use($request, $option) { return $q->where('users_annex.organization_key', "{$request->{$option.'dd_level0'}}"); })
        ->when("{$request->{$option.'dd_level1'}}", function($q) use($request, $option) { return $q->where('users_annex.level1_key', "{$request->{$option.'dd_level1'}}"); })
        ->when("{$request->{$option.'dd_level2'}}", function($q) use($request, $option) { return $q->where('users_annex.level2_key', "{$request->{$option.'dd_level2'}}"); })
        ->when("{$request->{$option.'dd_level3'}}", function($q) use($request, $option) { return $q->where('users_annex.level3_key', "{$request->{$option.'dd_level3'}}"); })
        ->when("{$request->{$option.'dd_level4'}}", function($q) use($request, $option) { return $q->where('users_annex.level4_key', "{$request->{$option.'dd_level4'}}"); })
        ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" == 'all', function($q) use($request, $option) { 
            return $q->where(function($q1) use($request, $option) {
                return $q1->where('users.employee_id', 'LIKE', "%{$request->{$option.'search_text'}}%")
                ->orWhere('employee_demo.employee_name', 'LIKE', "%{$request->{$option.'search_text'}}%")
                ->orWhere('excusedtype', 'LIKE', "%{$request->{$option.'search_text'}}%")
                ->orWhere('reason_name', 'LIKE', "%{$request->{$option.'search_text'}}%")
                ->orWhere('excused_by_name', 'LIKE', "%{$request->{$option.'search_text'}}%");
            });
        })
        ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" != 'all', function($q) use($request, $option) { 
            return $q->where("{$request->{$option.'criteria'}}", 'LIKE', "%{$request->{$option.'search_text'}}%"); 
        });
    }

    protected function baseFilteredSQLs($request, $option = null) {
        $demoWhere = $this->baseFilteredWhere($request, $option);
        $sql_level0 = clone $demoWhere; 
        $sql_level0->join('employee_demo_tree AS o', function($join) {
            $join->on('users_annex.organization_key', 'o.organization_key')
                ->where('o.level', 0);
            });
        $sql_level1 = clone $demoWhere; 
        $sql_level1->join('employee_demo_tree AS o', function($join) {
            $join->on('users_annex.organization_key', 'o.organization_key')
                ->on('users_annex.level1_key', 'o.level1_key')
                ->where('o.level', 1);
            });
        $sql_level2 = clone $demoWhere; 
        $sql_level2->join('employee_demo_tree AS o', function($join) {
            $join->on('users_annex.organization_key', 'o.organization_key')
                ->on('users_annex.level1_key', 'o.level1_key')
                ->on('users_annex.level2_key', 'o.level2_key')
                ->where('o.level', 2);    
            });    
        $sql_level3 = clone $demoWhere; 
        $sql_level3->join('employee_demo_tree AS o', function($join) {
            $join->on('users_annex.organization_key', 'o.organization_key')
                ->on('users_annex.level1_key', 'o.level1_key')
                ->on('users_annex.level2_key', 'o.level2_key')
                ->on('users_annex.level3_key', 'o.level3_key')
                ->where('o.level',3);    
            });
        $sql_level4 = clone $demoWhere; 
        $sql_level4->join('employee_demo_tree AS o', function($join) {
            $join->on('users_annex.organization_key', 'o.organization_key')
                ->on('users_annex.level1_key', 'o.level1_key')
                ->on('users_annex.level2_key', 'o.level2_key')
                ->on('users_annex.level3_key', 'o.level3_key')
                ->on('users_annex.level4_key', 'o.level4_key')
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
