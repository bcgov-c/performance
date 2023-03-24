<?php

namespace App\Http\Controllers\SysAdmin;



use Exception;
use App\Models\User;
use App\Models\AdminOrg;
use App\Models\AdminOrgUser;
use App\Models\EmployeeDemo;
use App\Models\UserListView;
use Illuminate\Http\Request;
use App\Models\UserDemoJrView;
use App\Models\EmployeeDemoTree;
use App\Models\OrganizationTree;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\UserManageAccessView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class AccessPermissionsController extends Controller
{
    public function index(Request $request) 
    {

        $errors = session('errors');

        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];

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
            ]);
        }

        $level0 = $request->dd_level0 ? EmployeeDemoTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? EmployeeDemoTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? EmployeeDemoTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? EmployeeDemoTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? EmployeeDemoTree::where('id', $request->dd_level4)->first() : null;

        $request->session()->flash('level0', $level0);
        $request->session()->flash('level1', $level1);
        $request->session()->flash('level2', $level2);
        $request->session()->flash('level3', $level3);
        $request->session()->flash('level4', $level4);
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        
        $elevel0 = $request->edd_level0 ? EmployeeDemoTree::where('id', $request->edd_level0)->first() : null;
        $elevel1 = $request->edd_level1 ? EmployeeDemoTree::where('id', $request->edd_level1)->first() : null;
        $elevel2 = $request->edd_level2 ? EmployeeDemoTree::where('id', $request->edd_level2)->first() : null;
        $elevel3 = $request->edd_level3 ? EmployeeDemoTree::where('id', $request->edd_level3)->first() : null;
        $elevel4 = $request->edd_level4 ? EmployeeDemoTree::where('id', $request->edd_level4)->first() : null;

        $request->session()->flash('elevel0', $elevel0);
        $request->session()->flash('elevel1', $elevel1);
        $request->session()->flash('elevel2', $elevel2);
        $request->session()->flash('elevel3', $elevel3);
        $request->session()->flash('elevel4', $elevel4);

        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->selectRaw("
            u.employee_id
            , u.display_name
            , u.jobcode_desc
            , u.user_email
            , u.organization
            , u.level1_program
            , u.level2_division
            , u.level3_branch
            , u.level4
            , u.deptid
        ")      
        ->orderBy('u.employee_id')
        ->pluck('u.employee_id');

        $criteriaList = $this->search_criteria_list();
        $roles = DB::table('roles')
        ->whereIntegerInRaw('id', [3, 4])
        ->pluck('longname', 'id');

        return view('sysadmin.accesspermissions.index', compact('criteriaList','matched_emp_ids', 'old_selected_emp_ids', 'old_selected_org_nodes', 'roles') );
    }

    public function loadOrganizationTree(Request $request) {
        $level0 = $request->dd_level0 ? EmployeeDemoTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? EmployeeDemoTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? EmployeeDemoTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? EmployeeDemoTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? EmployeeDemoTree::where('id', $request->dd_level4)->first() : null;
        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4);
        $rows = $sql_level4->groupBy('o.id')->select('o.id')
            ->union( $sql_level3->groupBy('o.id')->select('o.id') )
            ->union( $sql_level2->groupBy('o.id')->select('o.id') )
            ->union( $sql_level1->groupBy('o.id')->select('o.id') )
            ->union( $sql_level0->groupBy('o.id')->select('o.id') )
            ->pluck('o.id'); 
        $orgs = EmployeeDemoTree::whereIn('id', $rows->toArray() )->get()->toTree();
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
        $rows = $sql->join('employee_demo_tree AS o', function($join) use($request) {
                $join->on('u.organization', 'o.organization')
                    ->on('u.level1_program', 'o.level1_program')
                    ->on('u.level2_division', 'o.level2_division')
                    ->on('u.level3_branch', 'o.level3_branch')
                    ->on('u.level4', 'o.level4');
                })
                ->select('o.id', 'u.employee_id')
                ->groupBy('o.id', 'u.employee_id')
                ->orderBy('o.id')->orderBy('u.employee_id')
                ->get();
        $empIdsByOrgId = $rows->groupBy('o.id')->all();
        if($request->ajax()){
            return view('sysadmin.accesspermissions.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
        } 
    }


    public function eloadOrganizationTree(Request $request) {
        list($esql_level0, $esql_level1, $esql_level2, $esql_level3, $esql_level4) = $this->ebaseFilteredSQLs($request);
        $rows = $esql_level4->groupBy('o.id')->select('o.id')
        ->union( $esql_level3->groupBy('o.id')->select('o.id') )
        ->union( $esql_level2->groupBy('o.id')->select('o.id') )
        ->union( $esql_level1->groupBy('o.id')->select('o.id') )
        ->union( $esql_level0->groupBy('o.id')->select('o.id') )
        ->pluck('o.id'); 
        $eorgs = EmployeeDemoTree::whereIn('id', $rows->toArray())->get()->toTree();
        $eempIdsByOrgId = [];
        $eempIdsByOrgId = $rows->groupBy('o.id')->all();
        if($request->ajax()) { return view('sysadmin.accesspermissions.partials.recipient-tree2', compact('eorgs', 'eempIdsByOrgId')); } 
    }

  
    public function getDatatableEmployees(Request $request) {
        if($request->ajax()){
            $level0 = $request->dd_level0 ? EmployeeDemoTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? EmployeeDemoTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? EmployeeDemoTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? EmployeeDemoTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? EmployeeDemoTree::where('id', $request->dd_level4)->first() : null;
            $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
            $sql = clone $demoWhere; 
            $employees = $sql->selectRaw("
               u.employee_id
               , u.display_name
               , u.jobcode_desc
               , u.user_email
               , u.organization
               , u.level1_program
               , u.level2_division
               , u.level3_branch
               , u.level4
               , u.deptid
            ");
            return Datatables::of($employees)
                ->addColumn('select_users', static function ($employee) {
                    return '<input pid="1335" type="checkbox" id="userCheck'. 
                        $employee->employee_id .'" name="userCheck[]" value="'. $employee->employee_id .'" class="dt-body-center">';
                })
                ->rawColumns(['select_users','action'])
                ->make(true);
        }
    }


    public function saveAccess(Request $request) 
    {
        $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $request->userCheck = $selected_emp_ids;
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];

        $current_user = User::find(Auth::id());

        $employee_ids = ($request->userCheck) ? $request->userCheck : [];

        $toRecipients = UserListView::select('user_id AS id')
            ->whereIn('employee_id', $selected_emp_ids )
            ->distinct()
            ->orderBy('display_name')
            ->get() ;

        if($request->input('accessselect') == 3) {
            $organizationList = EmployeeDemoTree::select('id')
            ->whereIn('id', $selected_org_nodes)
            ->distinct()
            ->orderBy('id')
            ->get();
        }

        foreach ($toRecipients as $newId) {
            $result = DB::table('model_has_roles')
            ->updateOrInsert(
                [
                    'model_id' => $newId->id,
                    'role_id' => $request->input('accessselect'),
                    'model_type' => 'App\\Models\\User'
                ],
                [
                    'reason' => $request->input('reason')  
                ]
            );

            if($request->input('accessselect') == '3') {
                foreach($organizationList as $org1) {
                    $result = AdminOrg::updateOrCreate(
                        [
                            'user_id' => $newId->id,
                            'version' => '2',
                            'orgid' => $org1->id
                        ],
                        [
                            'updated_at' => date('Y-m-d H:i:s')
                        ],
                    );
                    if(!$result){
                        break;
                    }
                }

                $this->refreshAdminOrgUsersById($newId->id);

            };  
        }

        return redirect()->route('sysadmin.accesspermissions.index')->with('success', 'Create HR/SYS Admin access successful.');
    }

    public function getUsers(Request $request)
    {
        $search = $request->search;
        $users =  User::whereRaw("name like '%".$search."%'")
            ->whereNotNull('email')->paginate();
        return ['data'=> $users];
    }


    public function getOrganizations(Request $request) {
        $orgs = OrganizationTreeView::orderby('name','asc')->select('id','name')
            ->where('level',0)
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function geteOrganizations(Request $request) {
        $eorgs = OrganizationTree::orderby('name','asc')->select('id','name')
            ->where('level',0)
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
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
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->when( $level0 , function ($q) use($level0) { $q->where('organization', $level0->name); })
            ->groupBy('name')
            ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getePrograms(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id',$request->elevel0)->first() : null;
        $eorgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',1)
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->when( $elevel0 , function ($q) use($elevel0) { $q->where('organization', $elevel0->name); })
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
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->when( $level0 , function ($q) use($level0) { $q->where('organization', $level0->name); })
            ->when( $level1 , function ($q) use($level1) { $q->where('level1_program', $level1->name); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function geteDivisions(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;
        $eorgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',2)
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->when( $elevel0 , function ($q) use($elevel0) { $q->where('organization', $elevel0->name); })
            ->when( $elevel1 , function ($q) use($elevel1) { $q->where('level1_program', $elevel1->name); })
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
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->when( $level0 , function ($q) use($level0) { $q->where('organization', $level0->name); })
            ->when( $level1 , function ($q) use($level1) { $q->where('level1_program', $level1->name); })
            ->when( $level2 , function ($q) use($level2) { $q->where('level2_division', $level2->name); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function geteBranches(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;
        $elevel2 = $request->elevel2 ? OrganizationTree::where('id', $request->elevel2)->first() : null;
        $eorgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',3)
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->when( $elevel0 , function ($q) use($elevel0) { $q->where('organization', $elevel0->name); })
            ->when( $elevel1 , function ($q) use($elevel1) { $q->where('level1_program', $elevel1->name); })
            ->when( $elevel2 , function ($q) use($elevel2) { $q->where('level2_division', $elevel2->name); })
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
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->when( $level0 , function ($q) use($level0) { $q->where('organization', $level0->name); })
            ->when( $level1 , function ($q) use($level1) { $q->where('level1_program', $level1->name); })
            ->when( $level2 , function ($q) use($level2) { $q->where('level2_division', $level2->name); })
            ->when( $level3 , function ($q) use($level3) { $q->where('level3_branch', $level3->name); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function geteLevel4(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::where('id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::where('id', $request->elevel1)->first() : null;
        $elevel2 = $request->elevel2 ? OrganizationTree::where('id', $request->elevel2)->first() : null;
        $elevel3 = $request->elevel3 ? OrganizationTree::where('id', $request->elevel3)->first() : null;
        $eorgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',4)
            ->when( $request->q , function ($q) use($request) { $q->whereRaw("name LIKE '%".$request->q."%'"); })
            ->when( $elevel0 , function ($q) use($elevel0) { $q->where('organization', $elevel0->name); })
            ->when( $elevel1 , function ($q) use($elevel1) { $q->where('level1_program', $elevel1->name); })
            ->when( $elevel2 , function ($q) use($elevel2) { $q->where('level2_division', $elevel2->name); })
            ->when( $elevel3 , function ($q) use($elevel3) { $q->where('level3_branch', $elevel3->name); })
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
        $level0 = $request->dd_level0 ? EmployeeDemoTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? EmployeeDemoTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? EmployeeDemoTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? EmployeeDemoTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? EmployeeDemoTree::where('id', $request->dd_level4)->first() : null;
        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4);
        $rows = $sql_level4->where('o.id', $id)
            ->union( $sql_level3->where('o.id', $id) )
            ->union( $sql_level2->where('o.id', $id) )
            ->union( $sql_level1->where('o.id', $id) )
            ->union( $sql_level0->where('o.id', $id) );
        $employees = $rows->get();
        $parent_id = $id;
        // if($request->ajax()){
            return view('sysadmin.accesspermissions.partials.employee', compact('parent_id', 'employees') ); 
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

    protected function baseFilteredWhere($request) {
        // Base Where Clause
        return UserListView::from('user_list_view AS u')
        ->whereRaw("(TRIM(u.guid) <> '' AND NOT u.guid IS NULL)")
        ->when( $request->dd_level0, function ($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
        ->when( $request->dd_level1, function ($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
        ->when( $request->dd_level2, function ($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
        ->when( $request->dd_level3, function ($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
        ->when( $request->dd_level4, function ($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
        ->when( $request->search_text && $request->criteria == 'emp', function ($q) use($request) { return $q->whereRaw("u.employee_id LIKE '%{$request->search_text}%'"); })
        ->when( $request->search_text && $request->criteria == 'name', function ($q) use($request) { return $q->whereRaw("u.display_name LIKE '%{$request->search_text}%'"); })
        ->when( $request->search_text && $request->criteria == 'job', function ($q) use($request) { return $q->whereRaw("u.jobcode_desc LIKE '%{$request->search_text}%'"); })
        ->when( $request->search_text && $request->criteria == 'dpt', function ($q) use($request) { return $q->whereRaw("u.deptid LIKE '%{$request->search_text}%'"); })
        ->when( $request->search_text && $request->criteria == 'all', function ($q) use($request) { return $q->whereRaw("(u.employee_id LIKE '%{$request->search_text}%' OR u.display_name LIKE '%{$request->search_text}%' OR u.jobcode_desc LIKE '%{$request->search_text}%' OR u.deptid LIKE '%{$request->search_text}%')"); });
    }

    protected function ebaseFilteredWhere($request) {
        // Base Where Clause
        return UserListView::from('user_list_view AS u')
        ->whereRaw("TRIM(u.guid) <> '' AND NOT u.guid IS NULL")
        ->when( $request->edd_level0, function ($q) use($request) { return $q->where('u.organization_key', $request->edd_level0); })
        ->when( $request->edd_level1, function ($q) use($request) { return $q->where('u.level1_key', $request->edd_level1); })
        ->when( $request->edd_level2, function ($q) use($request) { return $q->where('u.level2_key', $request->edd_level2); })
        ->when( $request->edd_level3, function ($q) use($request) { return $q->where('u.level3_key', $request->edd_level3); })
        ->when( $request->edd_level4, function ($q) use($request) { return $q->where('u.level4_key', $request->edd_level4); });
    }

    protected function baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4) {
        // Base Where Clause
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
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

    protected function ebaseFilteredSQLs($request) {
        // Base Where Clause
        Log::info($request);
        $demoWhere = $this->ebaseFilteredWhere($request);
        $esql_level0 = clone $demoWhere; 
        $esql_level0->join('employee_demo_tree AS o', function($join) {
            return $join->on('u.organization', 'o.organization')
                ->where('o.level', 0);
            });
        $esql_level1 = clone $demoWhere; 
        $esql_level1->join('employee_demo_tree AS o', function($join) {
            return $join->on('u.organization', 'o.organization')
                ->on('u.level1_program', 'o.level1_program')
                ->where('o.level', 1);
            });
        $esql_level2 = clone $demoWhere; 
        $esql_level2->join('employee_demo_tree AS o', function($join) {
            return $join->on('u.organization', 'o.organization')
                ->on('u.level1_program', 'o.level1_program')
                ->on('u.level2_division', 'o.level2_division')
                ->where('o.level', 2);    
            });    
        $esql_level3 = clone $demoWhere; 
        $esql_level3->join('employee_demo_tree AS o', function($join) {
            return $join->on('u.organization', 'o.organization')
                ->on('u.level1_program', 'o.level1_program')
                ->on('u.level2_division', 'o.level2_division')
                ->on('u.level3_branch', 'o.level3_branch')
                ->where('o.level', 3);    
            });
        $esql_level4 = clone $demoWhere; 
        $esql_level4->join('employee_demo_tree AS o', function($join) {
            return $join->on('u.organization', 'o.organization')
                ->on('u.level1_program', 'o.level1_program')
                ->on('u.level2_division', 'o.level2_division')
                ->on('u.level3_branch', 'o.level3_branch')
                ->on('u.level4', 'o.level4')
                ->where('o.level', 4);
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
        $level0 = $request->dd_level0 ? EmployeeDemoTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? EmployeeDemoTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? EmployeeDemoTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? EmployeeDemoTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? EmployeeDemoTree::where('id', $request->dd_level4)->first() : null;
        $request->session()->flash('level0', $level0);
        $request->session()->flash('level1', $level1);
        $request->session()->flash('level2', $level2);
        $request->session()->flash('level3', $level3);
        $request->session()->flash('level4', $level4);
        $criteriaList = $this->search_criteria_list();
        $roles = DB::table('roles')
        ->whereIntegerInRaw('id', [3, 4])
        ->pluck('longname', 'id');
        return view('sysadmin.accesspermissions.manageexistingaccess', compact ('request', 'criteriaList', 'roles'));
    }

    public function getList(Request $request) {
        if ($request->ajax()) {
            $level0 = $request->dd_level0 ? EmployeeDemoTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? EmployeeDemoTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? EmployeeDemoTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? EmployeeDemoTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? EmployeeDemoTree::where('id', $request->dd_level4)->first() : null;
            $query = UserManageAccessView::from('user_manage_access_view AS u')
            ->when($level0, function($q) use($level0) { $q->where('organization', $level0->name); })
            ->when($level1, function($q) use($level1) { $q->where('level1_program', $level1->name); })
            ->when($level2, function($q) use($level2) { $q->where('level2_division', $level2->name); })
            ->when($level3, function($q) use($level3) { $q->where('level3_branch', $level3->name); })
            ->when($level4, function($q) use($level4) { $q->where('level4', $level4->name); })
            ->when($request->search_text && $request->criteria == 'name', function($q) use($request) { $q->whereRaw("display_name like '%".$request->search_text."%'"); })
            ->when($request->search_text && $request->criteria == 'emp', function($q) use($request) { $q->whereRaw("employee_id like '%".$request->search_text."%'"); })
            ->when($request->search_text && $request->criteria == 'job', function($q) use($request) { $q->whereRaw("jobcode_desc like '%".$request->search_text."%'"); })
            ->when($request->search_text && $request->criteria == 'dpt', function($q) use($request) { $q->whereRaw("deptid like '%".$request->search_text."%'"); })
            ->when($request->search_text && $request->criteria == 'all', function($q) use ($request) { $q->whereRaw("(employee_id like '%".$request->search_text."%' OR display_name like '%".$request->search_text."%' OR jobcode_desc like '%".$request->search_text."%' OR deptid like '%".$request->search_text."%')"); })
            ->selectRaw("
                employee_id
                , display_name 
                , user_email
                , jobcode
                , jobcode_desc
                , organization
                , level1_program
                , level2_division
                , level3_branch
                , level4
                , deptid
                , role_id
                , reason
                , role_longname
                , model_id
                , sysadmin
            ");
            return Datatables::of($query)
            ->addIndexColumn()
            ->addcolumn('action', function($row) {
                return '<button 
                class="btn btn-xs btn-primary modalbutton" 
                role="button" 
                data-roleid="'.$row->role_id.'" 
                data-modelid="'.$row->model_id.'" 
                data-reason="'.$row->reason.'" 
                data-sysadmin="'.($row->sysadmin?"Y":"").'" 
                data-email="'.$row->user_email.'" 
                data-longname="'.$row->role_longname.'" 
                data-toggle="modal"
                data-target="#editModal"
                role="button">Update</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    public function getAdminOrgs(Request $request, $model_id) {
        if ($request->ajax()) {
            $query = AdminOrg::where('user_id', $model_id)
            ->where('version', '2')
            ->leftJoin('employee_demo_tree', 'admin_orgs.orgid', 'employee_demo_tree.id')
            ->orderBy('employee_demo_tree.organization')
            ->orderBy('employee_demo_tree.level1_program')
            ->orderBy('employee_demo_tree.level2_division')
            ->orderBy('employee_demo_tree.level3_branch')
            ->orderBy('employee_demo_tree.level4')
            ->select (
                'employee_demo_tree.organization',
                'employee_demo_tree.level1_program',
                'employee_demo_tree.level2_division',
                'employee_demo_tree.level3_branch',
                'employee_demo_tree.level4',
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
        ->where('model_type', 'App\Models\User')
        ->where('role_id', $roleId)
        ->where('model_id', $modelId);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function manageEdit($id) {
        $users = User::where('id', $id)
        ->select('email')
        ->get();
        $email = $users->first()->email;
        $roles = DB::table('roles')
        ->whereIntegerInRaw('id', [3, 4])
        ->get();
        $access = DB::table('model_has_roles')
        ->where('model_id', $id)
        ->where('model_has_roles.model_type', 'App\Models\User')
        ->get();
        return view('sysadmin.accesspermissions.partials.access-edit-modal', compact('roles', 'access', 'email'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function manageUpdate(Request $request) {
        Log::info('$request->accessselect = '.$request->accessselect);
        if($request->accessselect) {
            if($request->accessselect == 4) {
                try {
                    $query = DB::table('model_has_roles')
                    ->where('model_id', $request->model_id)
                    ->whereIn('role_id', [3, 4])
                    ->update(['role_id' => $request->accessselect, 'reason' => $request->reason]);
                    $orgs = AdminOrg::where('user_id', '=', $request->input('model_id'))
                    ->delete();

                    $this->refreshAdminOrgUsersById( $request->model_id );

                return redirect()->back();
                }
                catch (Exception $e) {
                    return response()->with('error', 'System Administrator already assigned to selected user.');
                }
            }
            if($request->accessselect == 3) {
                $query = DB::table('model_has_roles')
                ->where('model_id', $request->model_id)
                ->where('role_id', 3)
                ->update(['reason' => $request->reason]);

                $this->refreshAdminOrgUsersById( $request->model_id );

                return redirect()->back();
            }
        } else {
            $query = DB::table('model_has_roles')
            ->where('model_id', $request->model_id)
            ->update(['reason' => $request->reason]);
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function manageDestroy(Request $request) {
        $query = DB::table('model_has_roles')
        ->where('model_id', $request->input('model_id'))
        ->where('role_id', $request->input('role_id'))
        ->delete();
        if ($request->input('role_id') == 3) {
            $orgs = AdminOrg::where('user_id', $request->input('model_id'))
            ->delete();

            $this->refreshAdminOrgUsersById( $request->input('model_id') );
        }
        return redirect()->back();
    }

    protected function refreshAdminOrgUsersById($user_id) 
    {

        // #809 Update the model 'AdminOrgUsers' on the latest updated user, and instantly available on Statistics and Reporting 
        // Step 1 -- Clean up for the updated user id
        AdminOrgUser::where('granted_to_id', $user_id)->where('access_type', 0)->where('shared_profile_id', 0)->delete();

        // Step 2 -- insert record
        AdminOrgUser::insertUsing([
            'granted_to_id', 'allowed_user_id',
            'admin_org_id'
        ], 
            UserDemoJrView::join('admin_orgs', 'admin_orgs.orgid', 'user_demo_jr_view.orgid')
                ->where('admin_orgs.version', 2)
                ->whereNull('user_demo_jr_view.date_deleted')
                ->where('admin_orgs.user_id',  $user_id )
                ->select('admin_orgs.user_id', 'user_demo_jr_view.user_id', 'admin_orgs.id')
                ->distinct()

        );

    }

}
