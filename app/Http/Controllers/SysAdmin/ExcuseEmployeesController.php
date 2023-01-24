<?php

namespace App\Http\Controllers\SysAdmin;



use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;
use App\Models\ExcusedReason;
use App\Models\OrganizationTree;
use App\Models\AdminOrg;
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


class ExcuseEmployeesController extends Controller
{

    public function addindex(Request $request) 
    {

        $errors = session('errors');

        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];

        // if ($errors) {
        //     $old = session()->getOldInput();

        //     $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
        //     $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
        //     $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
        //     $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
        //     $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;

        //     $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
            
        //     $request->orgCheck = isset($old['orgCheck']) ? $old['orgCheck'] : null;
        //     $request->userCheck = isset($old['userCheck']) ? $old['userCheck'] : null;

        //     $old_selected_emp_ids = isset($old['selected_emp_ids']) ? json_decode($old['selected_emp_ids']) : [];
        //     $old_selected_org_nodes = isset($old['selected_org_nodes']) ? json_decode($old['selected_org_nodes']) : [];
        // } 

        // // no validation and move filter variable to old 
        // if ($request->btn_search) {
        //     session()->put('_old_input', [
        //         'dd_level0' => $request->dd_level0,
        //         'dd_level1' => $request->dd_level1,
        //         'dd_level2' => $request->dd_level2,
        //         'dd_level3' => $request->dd_level3,
        //         'dd_level4' => $request->dd_level4,
        //         'criteria' => $request->criteria,
        //         'search_text' => $request->search_text,
        //         'orgCheck' => $request->orgCheck,
        //         'userCheck' => $request->userCheck,
        //     ]);
        // }

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
        
        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql
        ->select([ 
            'employee_id'
            , 'employee_name'
            , 'jobcode_desc'
            , 'employee_email'
            , 'organization'
            , 'level1_program'
            , 'level2_division'
            , 'level3_branch'
            , 'level4'
            , 'deptid'
            , 'jobcode_desc'
            ])
            ->orderBy('employee_id')
            ->pluck('employee_id');        
        
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
        $reasons = ExcusedReason::where('id', '>', 2)->get();
        $reasons2 = ExcusedReason::where('id', '<=', 2)->get();

        $yesOrNo = [
            [ "id" => 0, "name" => 'No' ],
            [ "id" => 1, "name" => 'Yes' ],
        ];

        return view('shared.excuseemployees.manageindex', compact ('request', 'criteriaList', 'reasons', 'reasons2', 'yesOrNo'));
    }


     public function managehistory(Request $request)
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
        return view('shared.excuseemployees.managehistory', compact ('request', 'criteriaList'));
    }


   public function managehistorylist(Request $request) {
        if ($request->ajax()) {
            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
            $query = UserDemoJrHistoryView::from('user_demo_jr_history_view as u')
            ->when($level0, function($q) use($level0) {$q->where('u.organization', $level0->name);})
            ->when($level1, function($q) use($level1) {$q->where('u.level1_program', $level1->name);})
            ->when($level2, function($q) use($level2) {$q->where('u.level2_division', $level2->name);})
            ->when($level3, function($q) use($level3) {$q->where('u.level3_branch', $level3->name);})
            ->when($level4, function($q) use($level4) {$q->where('u.level4', $level4->name);})
            ->when($request->search_text && $request->criteria == 'name', function($q) use($request) {$q->whereRaw("u.employee_name like '%".$request->search_text."%'");})
            ->when($request->search_text && $request->criteria == 'emp', function($q) use($request) {$q->whereRaw("u.employee_id like '%".$request->search_text."%'");})
            ->when($request->search_text && $request->criteria == 'ext', function($q) use($request) {$q->havingRaw("u.j_excusedtype like '%".$request->search_text."%'");})
            ->when($request->search_text && $request->criteria == 'rsn', function($q) use($request) {$q->whereRaw("u.j_excused_reason_desc like '%".$request->search_text."%'"); })
            ->when($request->search_text && $request->criteria == 'exb', function($q) use($request) {$q->havingRaw("u.excused_by_name like '%".$request->search_text."%'");})
            ->when($request->search_text && $request->criteria == 'all', function($q) use ($request) {
                $q->havingRaw("u.employee_id_search like '%".$request->search_text."%' or u.employee_name_search like '%".$request->search_text."%' or u.j_excusedtype like '%".$request->search_text."%' or u.j_excused_reason_desc like '%".$request->search_text."%' or u.excused_by_name like '%".$request->search_text."%'");
            })
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
                , u.j_excused_type
                , u.j_updated_by_id
                , u.j_updated_by_name
                , u.k_created_at
                , u.k_excused_type
                , u.reason_id
                , u.reason_name
                , u.j_excusedtype
                , u.j_excusedlink
                , u.excused_by_name
                , u.excused_updated_by
                , u.employee_id_search
                , u.employee_name_search
                , u.j_excused_updated_by_name
                , u.j_excused_reason_id
                , u.j_excused_reason_desc
                , '' as created_at_string
                , '' as startdate_string
                , '' as enddate_string
                ")
                ->orderBy('u.employee_id')
                ->orderBy('u.jr_id');
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


    public function manageindexlist(Request $request) {
        if ($request->ajax()) {
            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
            $query = User::withoutGlobalScopes()
            ->from('users as u')
            ->leftjoin('employee_demo as d', 'u.employee_id', 'd.employee_id')
            ->leftjoin('employee_demo_jr as j', 'u.employee_id', 'j.employee_id')
            ->whereRaw("j.id = (select max(j1.id) from employee_demo_jr as j1 where j1.employee_id = j.employee_id) and (j.due_date_paused = 'Y' or u.excused_flag = 1) and d.date_deleted is null")
            ->whereRaw("trim(u.guid) <> ''")
            ->whereNotNull('u.guid')
            ->when($level0, function($q) use($level0) {$q->where('organization', $level0->name);})
            ->when($level1, function($q) use($level1) {$q->where('level1_program', $level1->name);})
            ->when($level2, function($q) use($level2) {$q->where('level2_division', $level2->name);})
            ->when($level3, function($q) use($level3) {$q->where('level3_branch', $level3->name);})
            ->when($level4, function($q) use($level4) {$q->where('level4', $level4->name);})
            ->when($request->criteria == 'name', function($q) use($request){$q->whereRaw("d.employee_name like '%".$request->search_text."%'");})
            ->when($request->criteria == 'emp', function($q) use($request){$q->whereRaw("d.employee_id like '%".$request->search_text."%'");})
            ->when($request->criteria == 'job', function($q) use($request){$q->whereRaw("d.jobcode_desc like '%".$request->search_text."%'");})
            ->when($request->criteria == 'dpt', function($q) use($request){$q->whereRaw("d.deptid like '%".$request->search_text."%'");})
            ->when($request->criteria == 'all' && $request->search_text, function($q) use ($request) {$q->whereRaw("(d.employee_id like '%".$request->search_text."%' or d.employee_name like '%".$request->search_text."%' or d.jobcode_desc like '%".$request->search_text."%' or d.deptid like '%".$request->search_text."%')");})
            ->select (
                'u.id',
                'u.guid',
                'u.name',
                'u.excused_flag',
                'u.excused_reason_id',
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
                'j.excused_type',
            );
            return Datatables::of($query)
            ->addIndexColumn()
            ->editColumn('d.employee_name', function($row) {
                return $row->employee_name ? $row->employee_name : $row->name;
            })
            ->addColumn('excused_status', function($row) {
                $text = 'No';
                $jr = EmployeeDemoJunior::where('employee_id', $row->employee_id)->getQuery()->orderBy('id', 'desc')->first();
                if ($jr) {
                    if ($jr->excused_type) {
                        if ($jr->excused_type == 'A') {
                            $text = 'Auto';
                        }
                        if ($jr->excused_type == 'M' ) {
                            $text = 'Manual';
                        }
                    }
                }
                if ($row->excused_flag) {
                    $text = 'Manual';
                }
                $excused = json_encode([
                    'excused_flag' => $row->excused_flag,
                    'reason_id' => $row->excused_reason_id
                ]);
                $excused_type = $jr->excused_type;
                $current_status = $jr->current_employee_status;
                return view('shared.excuseemployees.partials.link', compact(["row", "excused", "text", "excused_type", "current_status"]));
            })
            ->rawColumns(['excused_status'])
            ->make(true);
        }
    }


    public function loadOrganizationTree(Request $request) {

        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;

        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4);
        
        $rows = $sql_level4->groupBy('o.id')->select('o.id')
            ->union( $sql_level3->groupBy('o.id')->select('o.id') )
            ->union( $sql_level2->groupBy('o.id')->select('o.id') )
            ->union( $sql_level1->groupBy('o.id')->select('o.id') )
            ->union( $sql_level0->groupBy('o.id')->select('o.id') )
            ->pluck('o.id'); 
        $orgs = OrganizationTree::whereIn('id', $rows->toArray() )->get()->toTree();

        // Employee Count by Organization
        $countByOrg = $sql_level4->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row"))
        ->union( $sql_level3->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level2->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level1->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level0->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row") ) )
        ->pluck('count_row', 'o.id');  
        
        // // Employee ID by Tree ID
        $empIdsByOrgId = [];
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql = clone $demoWhere; 
        $rows = $sql->join('organization_trees as o', function($join) use($request) {
                $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                    ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                    ->on('user_demo_jr_view.level2_division', '=', 'o.level2_division')
                    ->on('user_demo_jr_view.level3_branch', '=', 'o.level3_branch')
                    ->on('user_demo_jr_view.level4', '=', 'o.level4');
                })
                ->select('o.id','employee_id')
                ->groupBy('o.id', 'employee_id')
                ->orderBy('o.id')->orderBy('employee_id')
                ->get();

        $empIdsByOrgId = $rows->groupBy('id')->all();

        if($request->ajax()){
            return view('shared.excuseemployees.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
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
            
            $rows = $esql_level4->groupBy('o.id')->select('o.id')
                ->union( $esql_level3->groupBy('o.id')->select('o.id') )
                ->union( $esql_level2->groupBy('o.id')->select('o.id') )
                ->union( $esql_level1->groupBy('o.id')->select('o.id') )
                ->union( $esql_level0->groupBy('o.id')->select('o.id') )
                ->pluck('o.id'); 

            $eorgs = OrganizationTree::whereIn('id', $rows->toArray() )->get()->toTree();
            
            $eempIdsByOrgId = [];
            $eempIdsByOrgId = $rows->groupBy('id')->all();

        if($request->ajax()){
            return view('shared.excuseemployees.partials.recipient-tree2', compact('eorgs','eempIdsByOrgId') );
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


    public function saveexcuse(Request $request) 
    {
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
            ->orderBy('d.employee_name')
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

    public function getUsers(Request $request)
    {
        $search = $request->search;
        $users =  User::whereRaw("name like '%".$search."%'")->whereNotNull('email')->paginate();
        return ['data'=> $users];
    }


    public function getOrganizations(Request $request) {
        $orgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
            ->where('organization_trees.level',0)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("name LIKE '%".$request->q."%'");
            })
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getPrograms(Request $request) {
        $level0 = $request->level0 ? OrganizationTree::
            where('organization_trees.id',$request->level0)->first() : null;
        $orgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',1)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
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
        $level0 = $request->level0 ? OrganizationTree::
            where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::
            where('organization_trees.id', $request->level1)->first() : null;
        $orgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
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
        $level0 = $request->level0 ? OrganizationTree::
            where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::
            where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::
            where('organization_trees.id', $request->level2)->first() : null;
        $orgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
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
        $level0 = $request->level0 ? OrganizationTree::
            where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::
            where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::
            where('organization_trees.id', $request->level2)->first() : null;
        $level3 = $request->level3 ? OrganizationTree::
            where('organization_trees.id', $request->level3)->first() : null;
        $orgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
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

    public function geteOrganizations(Request $request) {
        $eorgs = OrganizationTree::orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
            ->where('organization_trees.level',0)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
            })
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function getePrograms(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id',$request->elevel0)->first() : null;
        $eorgs = OrganizationTree::orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',1)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization_trees.organization', $elevel0->name );
            })
            ->groupBy('organization_trees.name')
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function geteDivisions(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;
        $eorgs = OrganizationTree::orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization_trees.organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('organization_trees.level1_program', $elevel1->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function geteBranches(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;
        $elevel2 = $request->elevel2 ? OrganizationTree::where('id', $request->elevel2)->first() : null;
        $eorgs = OrganizationTree::orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization_trees.organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('organization_trees.level1_program', $elevel1->name );
            })
            ->when( $elevel2 , function ($q) use($elevel2) {
                return $q->where('organization_trees.level2_division', $elevel2->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function geteLevel4(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;
        $elevel2 = $request->elevel2 ? OrganizationTree::where('id', $request->elevel2)->first() : null;
        $elevel3 = $request->elevel3 ? OrganizationTree::where('id', $request->elevel3)->first() : null;
        $eorgs = OrganizationTree::orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization_trees.organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('organization_trees.level1_program', $elevel1->name );
            })
            ->when( $elevel2 , function ($q) use($elevel2) {
                return $q->where('organization_trees.level2_division', $elevel2->name );
            })
            ->when( $elevel3 , function ($q) use($elevel3) {
                return $q->where('organization_trees.level3_branch', $elevel3->name );
            })
            ->groupBy('organization_trees.name')
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
        return view('shared.excuseemployees.partials.employee', compact('parent_id', 'employees') ); 
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'emp' => 'Employee ID', 
            'name'=> 'Employee Name',
            'ext' => 'Excuse Type', 
            'rsn' => 'Excuse Reason', 
            'exb' => 'Excused By'
        ];
    }

    protected function search_criteria_list_history() {
        return [
            'all' => 'All',
            'emp' => 'Employee ID', 
            'name'=> 'Employee Name',
            'ext' => 'Excuse Type', 
            'exb' => 'Excused By'
        ];
    }

    protected function baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4) {
        // Base Where Clause
        return UserDemoJrView::whereNull('date_deleted')
        ->when( $level0, function ($q) use($level0) { $q->whereRaw("user_demo_jr_view.organization = '".$level0->name."'"); }) 
        ->when( $level1, function ($q) use($level1) { $q->whereRaw("user_demo_jr_view.level1_program = '".$level1->name."'"); })
        ->when( $level2, function ($q) use($level2) { $q->whereRaw("user_demo_jr_view.level2_division = '".$level2->name."'");  })
        ->when( $level3, function ($q) use($level3) { $q->whereRaw("user_demo_jr_view.level3_branch = '".$level3->name."'"); })
        ->when( $level4, function ($q) use($level4) { $q->whereRaw("user_demo_jr_view.level4 = '".$level4->name."'"); })
        ->when( $request->search_text && $request->criteria == 'all', function ($q) use($request) { 
            $q->havingRaw("employee_id_search like '%".$request->search_text."%' or employee_name_search like '%".$request->search_text."%' or excusedtype like '%".$request->search_text."%' or reason_name like '%".$request->search_text."%' or excused_by_name like '%".$request->search_text."%'"); 
        })
        ->when( $request->search_text && $request->criteria == 'emp', function ($q) use($request) { $q->whereRaw("employee_id like '%" . $request->search_text . "%'"); })
        ->when( $request->search_text && $request->criteria == 'name', function ($q) use($request) { $q->whereRaw("employee_name like '%" . $request->search_text . "%'"); })
        ->when( $request->search_text && $request->criteria == 'ext', function ($q) use($request) { $q->havingRaw("excusedtype like '%" . $request->search_text . "%'"); })
        ->when( $request->search_text && $request->criteria == 'rsn', function ($q) use($request) { $q->whereRaw("reason_name like '%" . $request->search_text . "%'"); })
        ->when( $request->search_text && $request->criteria == 'exb', function ($q) use($request) { $q->havingRaw("excused_by_name like '%" . $request->search_text . "%'"); })
        ;
    }

    protected function ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
        // Base Where Clause
        $demoWhere = EmployeeDemo::when( $elevel0, function ($q) use($elevel0) { $q->whereRaw("user_demo_jr_view.organization = '".$elevel0->name."'"); }) 
        ->when( $elevel1, function ($q) use($elevel1) { $q->whereRaw("user_demo_jr_view.level1_program = '".$elevel1->name."'"); })
        ->when( $elevel2, function ($q) use($elevel2) { $q->whereRaw("user_demo_jr_view.level2_division = '".$elevel2->name."'");  })
        ->when( $elevel3, function ($q) use($elevel3) { $q->whereRaw("user_demo_jr_view.level3_branch = '".$elevel3->name."'"); })
        ->when( $elevel4, function ($q) use($elevel4) { $q->whereRaw("user_demo_jr_view.level4 = '".$elevel4->name."'"); });
        return $demoWhere;
    }

    protected function baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4) {
        // Base Where Clause
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql_level0 = clone $demoWhere; 
        $sql_level0->join('organization_trees as o', function($join) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->where('o.level', '=', 0);
            });
        $sql_level1 = clone $demoWhere; 
        $sql_level1->join('organization_trees as o', function($join) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                ->where('o.level', '=', 1);
            });
        $sql_level2 = clone $demoWhere; 
        $sql_level2->join('organization_trees as o', function($join) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                ->on('user_demo_jr_view.level2_division', '=', 'o.level2_division')
                ->where('o.level', '=', 2);    
            });    
        $sql_level3 = clone $demoWhere; 
        $sql_level3->join('organization_trees as o', function($join) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                ->on('user_demo_jr_view.level2_division', '=', 'o.level2_division')
                ->on('user_demo_jr_view.level3_branch', '=', 'o.level3_branch')
                ->where('o.level', '=', 3);    
            });
        $sql_level4 = clone $demoWhere; 
        $sql_level4->join('organization_trees as o', function($join) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                ->on('user_demo_jr_view.level2_division', '=', 'o.level2_division')
                ->on('user_demo_jr_view.level3_branch', '=', 'o.level3_branch')
                ->on('user_demo_jr_view.level4', '=', 'o.level4')
                ->where('o.level', '=', 4);
            });
        return  [$sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4];
    }

    protected function ebaseFilteredSQLs($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
        // Base Where Clause
        $demoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);

        $esql_level0 = clone $demoWhere; 
        $esql_level0->join('organization_trees as o', function($join) use($elevel0) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->where('o.level', '=', 0);
            });
            
        $esql_level1 = clone $demoWhere; 
        $esql_level1->join('organization_trees as o', function($join) use($elevel0, $elevel1) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                ->where('o.level', '=', 1);
            });
            
        $esql_level2 = clone $demoWhere; 
        $esql_level2->join('organization_trees as o', function($join) use($elevel0, $elevel1, $elevel2) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                ->on('user_demo_jr_view.level2_division', '=', 'o.level2_division')
                ->where('o.level', '=', 2);    
            });    
            
        $esql_level3 = clone $demoWhere; 
        $esql_level3->join('organization_trees as o', function($join) use($elevel0, $elevel1, $elevel2, $elevel3) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                ->on('user_demo_jr_view.level2_division', '=', 'o.level2_division')
                ->on('user_demo_jr_view.level3_branch', '=', 'o.level3_branch')
                ->where('o.level', '=', 3);    
            });
            
        $esql_level4 = clone $demoWhere; 
        $esql_level4->join('organization_trees as o', function($join) use($elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
            $join->on('user_demo_jr_view.organization', '=', 'o.organization')
                ->on('user_demo_jr_view.level1_program', '=', 'o.level1_program')
                ->on('user_demo_jr_view.level2_division', '=', 'o.level2_division')
                ->on('user_demo_jr_view.level3_branch', '=', 'o.level3_branch')
                ->on('user_demo_jr_view.level4', '=', 'o.level4')
                ->where('o.level', '=', 4);
            });

        return  [$esql_level0, $esql_level1, $esql_level2, $esql_level3, $esql_level4];
    }

    public function getAdminOrgs(Request $request, $model_id) {
        if ($request->ajax()) {
            $query = AdminOrg::where('user_id', '=', $model_id)
            ->where('version', '=', '1')
            ->select (
                'organization',
                'level1_program',
                'level2_division',
                'level3_branch',
                'level4',
                'user_id',
            );
            return Datatables::of($query)
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function get_access_entry($roleId, $modelId) {
        return DB::table('model_has_roles')
        ->whereIn('model_id', [3, 4])
        ->where('model_type', '=', 'App\Models\User')
        ->where('role_id', '=', $roleId)
        ->where('model_id', '=', $modelId);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function manageindexedit(Request $request, $id) {
        $users = User::where('id', '=', $id)
        ->select('id', 'excused_start_date', 'excused_end_date', 'excused_reason_id')
        ->leftjoin('employee_demo', 'users.employee_id', '=', 'employee_demo.employee_id')
        ->get();
        $excused_flag = $users->excused_flag;
        $excused_reason_id = $users->excused_reason_id;
        $employee_name = $users->employee_demo->employee_name;
        $reasons = ExcusedReason::all();
        return view('shared.excuseemployees.partials.excused-edit-modal', compact('id', 'excused_start_date', 'excused_end_date', 'excused_reason_id', 'employee_name', 'reasons'));
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function manageindexclear(Request $request) {
        $query = User::where('id', '=', $request->id)
        ->update(['excused_start_date' => null, 'excused_end_date' => null, 'excused_reason_id' => null]);
        return redirect()->back();
    }


}
