<?php

namespace App\Http\Controllers\SysAdmin;

use Validator;
use App\Models\Goal;
use App\Models\User;
use App\Models\Conversation;
use App\Models\EmployeeDemo;
use Illuminate\Http\Request;
use App\Models\SharedElement;
use App\Models\SharedProfile;
use App\Models\UserDemoJrView;
use App\Models\UsersAnnex;
use App\Models\EmployeeDemoTree;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DashboardNotification;
use Illuminate\Support\Facades\Route;
use App\Models\ConversationParticipant;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;   
use App\Http\Requests\MyTeams\ShareProfileRequest;   
use App\Http\Requests\MyTeams\UpdateProfileSharedWithRequest;    
use Carbon\Carbon;   


class EmployeeSharesController extends Controller {

    public function addnew(Request $request) {
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
        $matched_emp_ids = [];
        $ematched_emp_ids = [];
        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();
        $yesOrNo = [
            [ "id" => 0, "name" => 'No' ],
            [ "id" => 1, "name" => 'Yes' ],
        ];
        return view('shared.employeeshares.addnew', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'ematched_emp_ids', 'old_selected_emp_ids', 'eold_selected_emp_ids', 'old_selected_org_nodes', 'eold_selected_org_nodes', 'yesOrNo') );
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
            ->pluck('u.employee_id');    
        return $matched_emp_ids;
    }

    public function saveall(Request $request) {
        $input = $request->all();
        $rules = [ 'input_reason' => 'required' ];
        $messages = [ 'required' => 'This field is required.' ];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route(request()->segment(1).'.employeeshares')
            ->with('message', " There are one or more errors on the page. Please review and try again.")    
            ->withErrors($validator)
            ->withInput();
        }
        $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $eselected_emp_ids = $request->eselected_emp_ids ? json_decode($request->eselected_emp_ids) : [];
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $eselected_org_nodes = $request->eselected_org_nodes ? json_decode($request->eselected_org_nodes) : [];
        $current_user = User::find(Auth::id());
        $employee_ids = ($request->userCheck) ? $request->userCheck : [];
        $eeToShare = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.employee_id', 'users.employee_id')
            ->whereIn('employee_demo.employee_id', $selected_emp_ids )
            ->distinct()
            ->get() ;
        $shareTo = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.employee_id', 'users.employee_id')
            ->whereIn('employee_demo.employee_id', $eselected_emp_ids )
            ->distinct()
            ->get() ;
        $elements = array("1", "2");
        $reason = $request->input_reason;
        foreach ($eeToShare as $eeOne) {
            foreach ($shareTo as $toOne) {                
                //not allow direct team members be shared to their manager
                $get_direct = UsersAnnex::select('id')
                           ->where('user_id', '=', $eeOne->id)
                           ->where('reporting_to_userid', '=', $toOne->id)
                           ->count();                 
                if($get_direct > 0){
                    return redirect()->route(request()->segment(1).'.employeeshares')
                            ->with('message', " The employee already reports directly to that supervisor. Employees cannot be shared with their direct supervisor.");                    
                }    
                //not allow exsiting shared team members be shared to the same 
                $get_shared = sharedProfile::select('id')
                           ->where('shared_id', '=', $eeOne->id)
                           ->where('shared_with', '=', $toOne->id)
                           ->count(); 
                if($get_shared > 0){
                    return redirect()->route(request()->segment(1).'.employeeshares')
                            ->with('message', " The employee has already been shared with that supervisor. They cannot be shared with the same supervisor more than once.");                    
                }                 
            }
        }   
        foreach ($eeToShare as $eeOne) {
            foreach ($shareTo as $toOne) {
                //skip if same
                if ($eeOne->id <> $toOne->id) {
                    $result = SharedProfile::updateOrCreate(
                        [
                            'shared_id' => $eeOne->id, 
                            'shared_with' => $toOne->id
                        ],
                        [
                            'shared_item' => $elements, 
                            'comment' => $reason, 
                            'shared_by' => $current_user->id
                        ]
                    );

                    // Send email to person who their profile was shared 
                    $user = User::where('id', $result->shared_id)
                            ->with('userPreference')
                            ->select('id', 'name', 'guid', 'employee_id')
                            ->first();

                    if ($user && $user->allow_inapp_notification) {
                        // Use Class to create DashboardNotification
                        $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                        $notification->user_id = $result->shared_id;
                        $notification->notification_type = 'SP';
                        $notification->comment = 'Your profile has been shared with ' . $result->sharedWith->name;
                        $notification->related_id = $result->id;
                        $notification->notify_user_id = $result->shared_id;
                        $notification->send(); 
                    }
   
                    if ($user && $user->allow_email_notification && $user->userPreference->share_profile_flag == 'Y') {
                        // Send Out Email Notification to Employee
                        $sendMail = new \App\MicrosoftGraph\SendMail();
                        $sendMail->toRecipients = [ $user->id ];  
                        $sendMail->sender_id = null; 
                        $sendMail->useQueue = true;
                        $sendMail->saveToLog = true;
                        $sendMail->alert_type = 'N';
                        $sendMail->alert_format = 'E';
                        $sendMail->template = 'PROFILE_SHARED';
                        array_push($sendMail->bindvariables, $user->name);                 // Recipient of the email
                        array_push($sendMail->bindvariables, $result->sharedWith->name);   // Person who added goal to goal bank
                        array_push($sendMail->bindvariables, $result->sharedElementName);  // Shared element
                        array_push($sendMail->bindvariables, $result->comment);             // comment
                        $response = $sendMail->sendMailWithGenericTemplate();
                    }
                }
            }
        }
        return redirect()->route(request()->segment(1).'.employeeshares')
            ->with('success', 'Share user goal/conversation successful.');
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
                    return view('shared.employeeshares.partials.erecipient-tree', compact('eorgs','ecountByOrg','eempIdsByOrgId') );
                    break;
                default:
                    return view('shared.employeeshares.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
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
                u.deptid,
                CASE WHEN (SELECT 1 FROM shared_profiles AS sp WHERE sp.shared_id = u.user_id LIMIT 1) = 1 THEN 'Yes' ELSE 'No' END AS shared_status
            ");
            return Datatables::of($employees)
                ->addColumn("{$option}select_users", static function ($employee) use($option) {
                        return '<input pid="1335" type="checkbox" id="'.$option.'userCheck'. 
                            $employee->employee_id.'" name="'.$option.'userCheck[]" value="'.$employee->employee_id.'" class="dt-body-center" aria-label="Select Employee ID '.$employee->employee_id.'" >';
                })
                ->editColumn('shared_status', function($row) {
                    $text = $row->shared_status;
                    $yesOrNo = [
                        [ "id" => 0, "name" => 'No' ],
                        [ "id" => 1, "name" => 'Yes' ],
                    ];
                    return view('shared.employeeshares.partials.link', compact(["row", "yesOrNo", 'text']));
                })
                ->rawColumns(["{$option}select_users", 'shared_status'])
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
        $page = 'shared.employeeshares.partials.employee';
        if($option == 'e'){
            $eparent_id = $parent_id;
            $eemployees = $employees;
            $page = 'shared.employeeshares.partials.'.$option.'employee';
        } 
        if($option == 'a'){
            $aparent_id = $parent_id;
            $aemployees = $employees;
            $page = 'shared.employeeshares.partials.'.$option.'employee';
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
            'u.employee_name'=> 'Employee Name',
            'u.employee_id' => 'Employee ID', 
            'u2.employee_id' => 'Delegate ID', 
            'd2.employee_name'=> 'Delegate Name',
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
            });
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
        $sharedElements = SharedElement::all();
        return view('shared.employeeshares.manageindex', compact ('request', 'criteriaList', 'sharedElements', 'old_selected_emp_ids'));
    }

    public function manageindexlist(Request $request) {
        if ($request->ajax()) {
            $query = UserDemoJrView::from('user_demo_jr_view AS u')
                ->whereNull('u.date_deleted')
                ->join(\DB::raw("(SELECT DISTINCT sp1.id, sp1.shared_id, sp1.shared_with FROM shared_profiles AS sp1) AS sp2"), 'sp2.shared_id', 'u.user_id')
                ->join('shared_profiles AS sp', 'sp.id', 'sp2.id')
                ->leftjoin('users as u2', 'u2.id', 'sp.shared_with')
                ->leftjoin('employee_demo as d2', 'd2.employee_id', 'u2.employee_id')
                ->leftjoin('users as cc', 'cc.id', 'sp.shared_by')
                ->leftjoin('employee_demo as cd', 'cd.employee_id', 'cc.employee_id')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text && $request->criteria, function($q) use($request) {
                    return $q->where("{$request->criteria}", 'LIKE', "%{$request->search_text}%");
                })
                ->selectRaw ("
                    u.employee_id,
                    u.employee_name,
                    u2.employee_id as delegate_ee_id,
                    d2.employee_name as delegate_ee_name,
                    d2.employee_name as alternate_delegate_name,
                    'All' as shared_item,
                    u.jobcode_desc,
                    u.organization,
                    u.level1_program,
                    u.level2_division,
                    u.level3_branch,
                    u.level4,
                    u.deptid,
                    cd.employee_name as created_name,
                    sp.created_at,
                    sp.updated_at,
                    sp.id as shared_profile_id
                ");
            return Datatables::of($query)
                ->addColumn("select_users", static function ($row) {
                    return '<input pid="1335" type="checkbox" id="userCheck'.$row->shared_profile_id.'" name="userCheck[]" value="'.$row->shared_profile_id.'" class="dt-body-center">';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('M d, Y H:i:s') : null;
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('M d, Y H:i:s') : null;
                })
                ->addcolumn('action', function($row) {
                    $btn = '<a href="' . route(request()->segment(1) . '.employeeshares.deleteshare', ['id' => $row->shared_profile_id]) . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_goal" value="' . $row->shared_profile_id . '"><i class="fa fa-trash"></i></a>';
                    return $btn;
                })
                ->rawColumns(['select_users', 'created_at', 'updated_at', 'action'])
                ->make(true);
        }
    }

    public function manageindexviewshares(Request $request, $id) {
        if ($request->ajax()) {
            $query = UserDemoJrView::from('user_demo_jr_view AS u')
                ->join('employee_shares AS s', 's.user_id', 'u.user_id')
                ->leftjoin('user_demo_jr_view AS u2', 's.shared_with_id', 'u2.user_id')
                ->leftjoin('shared_elements AS e', 'e.id', 's.shared_element_id')
                ->where('u.user_id', $id)
                ->select (
                    'u2.employee_id',
                    'u2.employee_name', 
                    'u.user_id as user_id',
                    'e.name as element_name',
                    'u2.user_id as shared_with_id',
                )
                ->distinct();
        return Datatables::of($query)
            ->addIndexColumn()
            ->addcolumn('action', function($row) {
                $btn = '<a href="' . route(request()->segment(1) . '.employeeshares.deleteitem', ['id' => $row->user_id, 'part' => $row->shared_with_id]) . '" class="view-modal btn btn-xs btn-danger" onclick="return confirm(`Are you sure?`)" aria-label="Delete" id="delete_goal" value="'. $row->id . '_' . $row->part_id .'"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        };
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

    public function deleteshare(Request $request, $id) {
        $query1 = DB::table('shared_profiles')
            ->where('id', $id)
            ->delete();
        return redirect()->back();
    }

    public function deleteMultiShare(Request $request, $ids) {
        $decoded = json_decode($ids);
        $query1 = DB::table('shared_profiles')
            ->whereIn('id', $decoded)
            ->delete();
        return redirect()->back();
    }

    public function removeAllShare(Request $request, $id) {
        $decoded = json_decode($id);
        $query1 = DB::table('shared_profiles')
            ->where('shared_id', $decoded)
            ->delete();
        return redirect()->back();
    }

    public function deleteitem(Request $request, $id, $part) {
        $query2 = DB::table('employee_shares')
            ->where('user_id', $id)
            ->where('shared_with_id', $part)
            ->delete();
        return redirect()->back();
    }

    public function shareProfile(ShareProfileRequest $request) {
        $input = $request->validated();
        // dd($input);
        // 
        // 
        //check if shared_id is direct team member of shared with users
        $shared_id = $input['shared_id'];
        $skip_sharing = false;
        $error_msg = '';
        foreach ($input['share_with_users'] as $shared_with_user_id) {
            //not allow direct team members be shared to their manager
            $get_direct = User::select('id')
                           ->where('id', '=', $shared_id)
                           ->where('reporting_to', '=', $shared_with_user_id)
                           ->count();                 
            if($get_direct > 0){
                $skip_sharing = true;   
                $error_msg = 'The employee already reports directly to that supervisor. Employees cannot be shared with their direct supervisor.';
            }    
            //not allow exsiting shared team members be shared to the same 
            $get_shared = sharedProfile::select('id')
                           ->where('shared_id', '=', $shared_id)
                           ->where('shared_with', '=', $shared_with_user_id)
                           ->count(); 
            if($get_shared > 0){
                $skip_sharing = true;  
                $error_msg = 'The employee has already been shared with that supervisor. They cannot be shared with the same supervisor more than once.';
            }      
        }
        
        //check shared with users, if user dont have supervisor role, assign to the user
        foreach ($input['share_with_users'] as $shared_with_user_id) {
            $shared_with_user = User::findOrFail($shared_with_user_id);
            //$this->assignSupervisorRole($user);
            if (!($shared_with_user->hasRole('Supervisor'))) {
                $shared_with_user->assignRole('Supervisor');
            } 
        }

        $insert = [
            'shared_by' => Auth::id(),
            'shared_item' => $input['items_to_share'],
            'shared_id' => $input['shared_id'],
            'comment' => $input['reason']
        ];

        $sharedProfile = [];
        if (!$skip_sharing) {
            DB::beginTransaction();
            foreach ($input['share_with_users'] as $user_id) {
                $insert['shared_with'] = $user_id;
                array_push($sharedProfile, SharedProfile::updateOrCreate($insert));
            }

            // Send out email to the user when his profile was shared
            foreach ($sharedProfile as $result) {

                $user = User::where('id', $result->shared_id)
                                ->with('userPreference')
                                ->select('id','name','guid', 'employee_id')
                                ->first();

                if ($user && $user->allow_inapp_notification) {
                    $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                    $notification->user_id = $result->shared_id;
                    $notification->notification_type = 'SP';
                    $notification->comment = 'Your profile has been shared with ' . $result->sharedWith->name;
                    $notification->related_id =  $result->id;
                    $notification->notify_user_id = $result->shared_id;
                    $notification->send();                                 
                }

                if ($user && $user->allow_email_notification && $user->userPreference->share_profile_flag == 'Y') {

                    // Send Out Email Notification to Employee
                    $sendMail = new \App\MicrosoftGraph\SendMail();
                    $sendMail->toRecipients = [ $user->id ];  
                    $sendMail->sender_id = null; 
                    $sendMail->useQueue = false;
                    $sendMail->saveToLog = true;
                    $sendMail->alert_type = 'N';
                    $sendMail->alert_format = 'E';

                    $sendMail->template = 'PROFILE_SHARED';
                    array_push($sendMail->bindvariables, $user->name);                 // Recipient of the email
                    array_push($sendMail->bindvariables, $result->sharedWith->name);   // Person who added goal to goal bank
                    array_push($sendMail->bindvariables, $result->sharedElementName);  // Shared element
                    array_push($sendMail->bindvariables, $result->comment);             // comment
                    $response = $sendMail->sendMailWithGenericTemplate();
                }
            }

            DB::commit();
            return $this->respondeWith($sharedProfile);
            //return redirect('/sysadmin/employeeshares');
        }                
        return response()->json(['success' => false, 'message' => $error_msg]);
    }

    
    public function getProfileSharedWith($user_id) {
        $sharedProfiles = SharedProfile::where('shared_id', $user_id)->with(['sharedWith' => function ($query) {
            $query->select('id', 'name');
        }])->get();        
        session()->put('checking_user', $user_id);

        return view('shared.employeeshares.partials.profile-shared-with', compact('sharedProfiles'));
        // return $this->respondeWith($sharedProfiles);
    }

    public function updateProfileSharedWith($shared_profile_id, UpdateProfileSharedWithRequest $request) {
        $sharedProfile = SharedProfile::findOrFail($shared_profile_id);
        $input = $request->validated();
        $update = [];
        if ($input['action'] !== 'stop') {
            if($input['action'] === 'comment') {
                $update['comment'] = $input['comment'];
            }
            else if ($input['action'] === 'items') {
                $update['shared_item'] = $input['shared_item'];
            }
            $sharedProfile->update($update);
            /// $sharedProfile->save();
            return $this->respondeWith($sharedProfile);
        }
        
        //also clean up shared goals
        $shared_id = $sharedProfile->shared_id;
        $shared_with = $sharedProfile->shared_with;
        
        DB::table('goals_shared_with')
                    ->where('user_id', $shared_id)
                    ->whereIn('goal_id', function ($query) use ($shared_with) {
                        $query->select('id')->from('goals')->where('user_id', $shared_with);
                    })
                    ->delete();
        $sharedProfile->delete();        
        
        return $this->respondeWith('');
    }

 
 
}
