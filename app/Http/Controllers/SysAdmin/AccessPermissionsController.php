<?php

namespace App\Http\Controllers\SysAdmin;



use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AdminOrg;
use App\Models\AdminOrgUser;
use App\Models\EmployeeDemo;
use App\Models\UserListView;
use Illuminate\Http\Request;
use App\Models\UserDemoJrView;
use App\Models\EmployeeDemoTree; 
use App\Models\ModelHasRoleAudit; 
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
    public function index(Request $request) {
        $errors = session('errors');
        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $old_selected_org_nodes = []; // $request->old_selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $old_selected_inherited = []; // $request->old_selected_inherited ? json_decode($request->selected_inherited) : [];
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
        $matched_emp_ids = [];
        $criteriaList = $this->search_criteria_list();
        $roles = DB::table('roles')
            ->whereIntegerInRaw('id', [3, 4, 5])
            ->pluck('longname', 'id');
        return view('sysadmin.accesspermissions.index', compact('criteriaList','matched_emp_ids', 'old_selected_emp_ids', 'old_selected_org_nodes', 'old_selected_inherited', 'roles') );
    }

    public function getFilteredList(Request $request) {
        $demoWhere = $this->baseFilteredWhere($request, $request->option);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 'u.employee_id' ])->pluck('u.employee_id');    
        return $matched_emp_ids;
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
                    return view('sysadmin.accesspermissions.partials.recipient-tree2', compact('eorgs', 'eempIdsByOrgId'));
                    break;
                default:
                    return view('sysadmin.accesspermissions.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId'));
                    break;
            }
        }
    }

    public function getDatatableEmployees(Request $request) {
        if($request->ajax()){
            $demoWhere = $this->baseFilteredWhere($request, "");
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

    public function saveAccess(Request $request) {
        $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $request->userCheck = $selected_emp_ids;
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $selected_inherited = $request->selected_inherited ? json_decode($request->selected_inherited) : [];
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
            ->orWhereIn('level4_key', $selected_org_nodes)
            ->distinct()
            ->get();
            $inheritedList = EmployeeDemoTree::select('id')
            ->whereIn('id', $selected_inherited)
            ->orWhereIn('level4_key', $selected_inherited)
            ->distinct()
            ->get();
        }
        foreach ($toRecipients as $newId) {
            $role_id = $request->input('accessselect');
            $result = DB::table('model_has_roles')
            ->updateOrInsert(
                [
                    'model_id' => $newId->id,
                    'role_id' => $role_id,
                    'model_type' => 'App\\Models\\User'
                ],
                [
                    'reason' => $request->input('reason')  
                ]
            );
            if($request->input('accessselect') == '3') {
                // Update inherited orgs
                foreach($inheritedList as $org1) {
                    $result = AdminOrg::updateOrCreate(
                        [
                            'user_id' => $newId->id,
                            'version' => '2',
                            'orgid' => $org1->id
                        ],
                        [
                            'inherited' => 1,
                            'updated_at' => date('Y-m-d H:i:s')
                        ],
                    );
                    if(!$result){
                        break;
                    }
                }
                // Updae non-inherited orgs
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
            if($request->input('accessselect') == 5) {
                $demo = EmployeeDemo::withoutGlobalScopes()
                    ->whereNull('date_deleted')
                    ->join('users', 'users.employee_id', 'employee_demo.employee_id')
                    ->whereRaw("users.id = {$newId->id}")
                    ->selectRaw('employee_demo.deptid, employee_demo.position_number')
                    ->whereRaw('employee_demo.empl_record = (SELECT MIN(ed1.empl_record) FROM employee_demo AS ed1 WHERE ed1.employee_id = employee_demo.employee_id AND ed1.date_deleted IS NULL)')
                    ->first();
                if ($demo) {
                    $demo_deptid = $demo->deptid;
                    $demo_posn = $demo->position_number;
                } else {
                    $demo_deptid = null;
                    $demo_posn = null;
                }
                ModelHasRoleAudit::updateOrCreate([
                    'model_id' => $newId->id,
                    'role_id' => $request->input('accessselect'),
                ], [
                    'deptid' => $demo_deptid,
                    'position_number' => $demo_posn,
                    'updated_by' => Auth::id(),
                ]);
            }
        }
        return redirect()->route('sysadmin.accesspermissions.index')->with('success', 'Create HR/SYS Admin access successful.');
    }

    public function getUsers(Request $request) {
        $search = $request->search;
        $users =  User::whereRaw("name like '%".$search."%'")->whereNotNull('email')->paginate();
        return ['data'=> $users];
    }

    public function getEmployees(Request $request,  $id) {
        $employees = \DB::select("
                SELECT employee_id, employee_name, employee_email, jobcode_desc
                FROM employee_demo USE INDEX (idx_employee_demo_orgid_employeeid_emplrecord) 
                WHERE orgid = {$id}
                    AND date_deleted IS NULL
                ORDER BY employee_name
            ");
        $parent_id = $id;
        return view('sysadmin.accesspermissions.partials.employee', compact('parent_id', 'employees') ); 
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'employee_id' => 'Employee ID', 
            'display_name'=> 'Employee Name',
            'jobcode_desc' => 'Classification', 
            'deptid' => 'Department ID'
        ];
    }

    protected function baseFilteredWhere($request, $option = null) {
        return UserListView::from('user_list_view AS u')
        ->whereRaw("(TRIM(u.guid) <> '' AND NOT u.guid IS NULL)")
        ->when("{$request->{$option.'dd_level0'}}", function($q) use($request, $option) { return $q->whereRaw("u.organization_key = {$request->{$option.'dd_level0'}}"); })
        ->when("{$request->{$option.'dd_level1'}}", function($q) use($request, $option) { return $q->whereRaw("u.level1_key = {$request->{$option.'dd_level1'}}"); })
        ->when("{$request->{$option.'dd_level2'}}", function($q) use($request, $option) { return $q->whereRaw("u.level2_key = {$request->{$option.'dd_level2'}}"); })
        ->when("{$request->{$option.'dd_level3'}}", function($q) use($request, $option) { return $q->whereRaw("u.level3_key = {$request->{$option.'dd_level3'}}"); })
        ->when("{$request->{$option.'dd_level4'}}", function($q) use($request, $option) { return $q->whereRaw("u.level4_key = {$request->{$option.'dd_level4'}}"); })
        ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" != 'all', function($q) use($request, $option) { return $q->whereRaw("u.{$request->{$option.'criteria'}} like '%{$request->{$option.'search_text'}}%'"); })
        ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" == 'all', function($q) use($request, $option) { return $q->whereRaw("(u.employee_id LIKE '%{$request->{$option.'search_text'}}%' OR u.display_name LIKE '%{$request->{$option.'search_text'}}%' OR u.jobcode_desc LIKE '%{$request->{$option.'search_text'}}%' OR u.deptid LIKE '%{$request->{$option.'search_text'}}%')"); });
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
    public function manageindex(Request $request)
    {
        $errors = session('errors');
        $old_selected_emp_ids = [];
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
        $criteriaList = $this->search_criteria_list();
        $roles = DB::table('roles')
        ->whereIntegerInRaw('id', [3, 4, 5])
        ->pluck('longname', 'id');
        return view('sysadmin.accesspermissions.manageexistingaccess', compact ('request', 'criteriaList', 'roles', 'old_selected_emp_ids'));
    }

    public function getList(Request $request, $option = null) {
        if ($request->ajax()) {
            $query = UserManageAccessView::from('user_manage_access_view AS u')
            ->when("{$request->{$option.'dd_level0'}}", function($q) use($request, $option) { return $q->whereRaw("u.organization_key = {$request->{$option.'dd_level0'}}"); })
            ->when("{$request->{$option.'dd_level1'}}", function($q) use($request, $option) { return $q->whereRaw("u.level1_key = {$request->{$option.'dd_level1'}}"); })
            ->when("{$request->{$option.'dd_level2'}}", function($q) use($request, $option) { return $q->whereRaw("u.level2_key = {$request->{$option.'dd_level2'}}"); })
            ->when("{$request->{$option.'dd_level3'}}", function($q) use($request, $option) { return $q->whereRaw("u.level3_key = {$request->{$option.'dd_level3'}}"); })
            ->when("{$request->{$option.'dd_level4'}}", function($q) use($request, $option) { return $q->whereRaw("u.level4_key = {$request->{$option.'dd_level4'}}"); })
            ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" != 'all', function($q) use($request, $option) { return $q->whereRaw("u.{$request->{$option.'criteria'}} like '%{$request->{$option.'search_text'}}%'"); })
            ->when("{$request->{$option.'search_text'}}" && "{$request->{$option.'criteria'}}" == 'all', function($q) use($request, $option) { return $q->whereRaw("(u.employee_id LIKE '%{$request->{$option.'search_text'}}%' OR u.display_name LIKE '%{$request->{$option.'search_text'}}%' OR u.jobcode_desc LIKE '%{$request->{$option.'search_text'}}%' OR u.deptid LIKE '%{$request->{$option.'search_text'}}%')"); })
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
                , org_count
            ");
            return Datatables::of($query)
            ->editColumn('org_count', function ($row) { return $row->role_id == 3 ? $row->org_count : NULL; })
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
                ->selectRaw("
                    admin_orgs.id,
                    employee_demo_tree.organization,
                    employee_demo_tree.level1_program,
                    employee_demo_tree.level2_division,
                    employee_demo_tree.level3_branch,
                    employee_demo_tree.level4,
                    CASE WHEN inherited = 1 THEN 'Yes' ELSE 'No' END AS inherited,
                    user_id
            ");
            return Datatables::of($query)
                ->addColumn("select_orgs", static function ($row) {
                    return '<input pid="1335" type="checkbox" id="orgCheck'.$row->id.'" name="orgCheck[]" value="'.$row->id.'" class="dt-body-center">';
                })
                ->addcolumn('action', function($row) {
                    $btn = '<a href="' . route(request()->segment(1) . '.accesspermissions.deleteitem', ['item_id' => $row->id]) . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_access" value="'. $row->id .'"><i class="fa fa-trash"></i></a>';
                    return $btn;
                })
                ->rawColumns(['select_orgs', 'action'])
                ->make(true);
        }
    }

    public function deleteitem(Request $request, $item_id) {
        $item = AdminOrg::find($item_id);
        if($item){
            $query2 = DB::table('admin_orgs')
                ->where('id', $item_id)
                ->delete();
            $this->refreshAdminOrgUsersById($item->user_id);
            return redirect()->back();
        }
    }

    public function deleteMultiOrgs(Request $request, $item_ids) {
        $decoded = json_decode($item_ids);
        $item = AdminOrg::find($decoded[0]);
        if($item){
            $query1 = DB::table('admin_orgs')
                ->whereIn('id', $decoded)
                ->delete();
            $this->refreshAdminOrgUsersById($item->user_id);
            return redirect()->back();
        }
    }

    public function get_access_entry($roleId, $modelId) {
        return DB::table('model_has_roles')
        ->whereIn('model_id', [3, 4, 5])
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
        ->whereIntegerInRaw('id', [3, 4, 5])
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
            if($request->accessselect == 5) {
                $query = DB::table('model_has_roles')
                ->where('model_id', $request->model_id)
                ->where('role_id', 5)
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
        if ($request->input('role_id') == 5){
            ModelHasRoleAudit::updateOrCreate([
                'model_id' => $request->input('model_id'),
                'role_id' => $request->input('role_id'),
            ], [
                'deleted_at' => Carbon::now(),
                'deleted_by' => Auth::id(),
            ]);

        }
        return redirect()->back();
    }

    protected function refreshAdminOrgUsersById($user_id) {
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
                ->select('admin_orgs.user_id', 'user_demo_jr_view.user_id', 'admin_orgs.orgid')
                ->distinct()
        );
        // Populate auth_users tables
        \DB::statement("
            DELETE 
            FROM auth_users 
            WHERE type = 'HR'
                AND auth_id = {$user_id}
        ");
        $now = date('Y-m-d H:i:s', strtotime(Carbon::now()->format('c')));
        // Insert non-inherited orgs
        \DB::statement("
            INSERT IGNORE INTO auth_users (type, auth_id, user_id, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    ao.user_id AS auth_id,
                    u.id AS user_id,
                    '{$now}',
                    '{$now}'
                FROM 
                    users 
                        AS u 
                    INNER JOIN employee_demo 
                        AS ed 
                        USE INDEX(idx_employee_demo_employee_id_date_deleted)
                        ON ed.employee_id = u.employee_id 
                            AND ed.date_deleted IS NULL
                    INNER JOIN admin_orgs 
                        AS ao
                        ON ao.user_id = {$user_id}
                            AND ao.version = 2
                            AND inherited = 0
                            AND ao.orgid = ed.orgid
            )
        ");
        // Insert inherited orgs
        \DB::statement("
            INSERT IGNORE INTO auth_users (type, auth_id, user_id, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    aotv.user_id AS auth_id,
                    u.id AS user_id,
                    '{$now}',
                    '{$now}'
                FROM 
                    users 
                        AS u 
                    INNER JOIN employee_demo 
                        AS ed 
                        USE INDEX(idx_employee_demo_employee_id_date_deleted)
                        ON ed.employee_id = u.employee_id 
                            AND ed.date_deleted IS NULL
                    INNER JOIN employee_demo_tree
                        AS edt
                        ON edt.id = ed.orgid
                    INNER JOIN admin_org_tree_view 
                        AS aotv 
                        ON aotv.user_id = {$user_id}
                            AND aotv.version = 2 
                            AND aotv.inherited = 1
                            AND aotv.level = 0 AND aotv.organization_key = edt.organization_key
                WHERE 
                    NOT EXISTS (SELECT DISTINCT 1 FROM auth_users WHERE auth_users.type = 'HR' AND auth_users.auth_id = aotv.user_id AND auth_users.user_id = u.id)
            )
        ");
        $level = 0;
        do {
            $level += 1;
            \DB::statement("
                INSERT IGNORE INTO auth_users (type, auth_id, user_id, created_at, updated_at) (
                    SELECT DISTINCT
                        'HR',
                        aotv.user_id AS auth_id,
                        u.id AS user_id,
                        '{$now}',
                        '{$now}'
                    FROM 
                        users 
                            AS u 
                        INNER JOIN employee_demo 
                            AS ed 
                            USE INDEX(idx_employee_demo_employee_id_date_deleted)
                            ON ed.employee_id = u.employee_id 
                                AND ed.date_deleted IS NULL
                        INNER JOIN employee_demo_tree
                            AS edt
                            ON edt.id = ed.orgid
                        INNER JOIN admin_org_tree_view 
                            AS aotv 
                            ON aotv.user_id = {$user_id}
                                AND aotv.version = 2 
                                AND aotv.inherited = 1
                                AND aotv.level = {$level} AND aotv.level{$level}_key = edt.level{$level}_key
                    WHERE 
                        NOT EXISTS (SELECT DISTINCT 1 FROM auth_users WHERE auth_users.type = 'HR' AND auth_users.auth_id = aotv.user_id AND auth_users.user_id = u.id)
                )
            ");
        } while ($level < 4);
        // Populate auth_org table
        \DB::statement("
            DELETE 
            FROM auth_orgs 
            WHERE type = 'HR'
                AND auth_id = {$user_id}
        ");
        \DB::statement("
            INSERT IGNORE INTO auth_orgs (type, auth_id, orgid, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    ao.user_id AS auth_id,
                    ao.orgid AS orgid,
                    '{$now}',
                    '{$now}'
                FROM 
                    admin_orgs 
                        AS ao
                WHERE ao.user_id = {$user_id}
                    AND ao.version = 2
                    AND inherited = 0
            )
        ");
        \DB::statement("
            INSERT IGNORE INTO auth_orgs (type, auth_id, orgid, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    aotv.user_id AS auth_id,
                    edt.id AS orgid,
                    '{$now}',
                    '{$now}'
                FROM 
                    employee_demo_tree
                        AS edt
                    INNER JOIN admin_org_tree_view 
                        AS aotv 
                        ON aotv.user_id = {$user_id}
                            AND aotv.version = 2 
                            AND aotv.inherited = 1
                            AND aotv.level = 0 AND aotv.organization_key = edt.organization_key
                WHERE 
                    NOT EXISTS (SELECT DISTINCT 1 FROM auth_orgs WHERE auth_orgs.type = 'HR' AND auth_orgs.auth_id = aotv.user_id AND auth_orgs.orgid = edt.id)
            )
        ");        
        $level = 0;
        do {
            $level += 1;
            \DB::statement("
                INSERT IGNORE INTO auth_orgs (type, auth_id, orgid, created_at, updated_at) (
                    SELECT DISTINCT
                        'HR',
                        aotv.user_id AS auth_id,
                        edt.id AS orgid,
                        '{$now}',
                        '{$now}'
                    FROM 
                        employee_demo_tree
                            AS edt
                        INNER JOIN admin_org_tree_view 
                            AS aotv 
                            ON aotv.user_id = {$user_id}
                                AND aotv.version = 2 
                                AND aotv.inherited = 1
                                AND aotv.level = {$level} AND aotv.level{$level}_key = edt.level{$level}_key
                    WHERE 
                        NOT EXISTS (SELECT DISTINCT 1 FROM auth_orgs WHERE auth_orgs.type = 'HR' AND auth_orgs.auth_id = aotv.user_id AND auth_orgs.orgid = edt.id)
                )
            ");
        } while ($level < 4);
    }

}
