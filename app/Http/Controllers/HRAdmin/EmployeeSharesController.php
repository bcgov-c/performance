<?php

namespace App\Http\Controllers\HRAdmin;

use Validator;
use App\Models\Goal;
use App\Models\User;
use App\Models\Conversation;
use App\Models\EmployeeDemo;
use Illuminate\Http\Request;
use App\Models\SharedElement;
use App\Models\SharedProfile;
use App\Models\HRUserDemoJrView;
use App\Models\UserDemoJrView;
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


class EmployeeSharesController extends Controller {

    public function addnew(Request $request)  {
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
            ->orderBy('u.employee_id')
            ->pluck('u.employee_id');        
        $ematched_emp_ids = clone $matched_emp_ids;
        // $alert_format_list = NotificationLog::ALERT_FORMAT;
        $criteriaList = $this->search_criteria_list();
        $ecriteriaList = $this->search_criteria_list();
        
        return view('shared.employeeshares.addnew', compact('criteriaList', 'ecriteriaList', 'matched_emp_ids', 'ematched_emp_ids', 'old_selected_emp_ids', 'eold_selected_emp_ids', 'old_selected_org_nodes', 'eold_selected_org_nodes') );
    
    }

    public function saveall(Request $request) {
        $input = $request->all();
        $rules = [
            'input_reason' => 'required',
        ];
        $messages = [
            'required' => 'The :attribute field is required.',
        ];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route(request()->segment(1).'.employeeshares')
                ->with('message', " There are one or more errors on the page. Please review and try again.")    
                ->withErrors($validator)
                ->withInput();
        }
        
        $selected_emp_ids = $request->userCheck ? $request->userCheck : [];
        $eselected_emp_ids = $request->euserCheck ? $request->euserCheck : [];
        $selected_org_nodes = $request->selected_org_nodes ? json_decode($request->selected_org_nodes) : [];
        $eselected_org_nodes = $request->eselected_org_nodes ? json_decode($request->eselected_org_nodes) : [];
        $current_user = User::find(Auth::id());
        $employee_ids = ($request->userCheck) ? $request->userCheck : [];         

        $eeToShare = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.employee_id', 'users.employee_id')
            ->whereIn('employee_demo.employee_id', $selected_emp_ids )
            ->distinct()
            ->select ('users.id')
            ->orderBy('employee_demo.employee_name')
            ->get() ;

        $shareTo = EmployeeDemo::select('users.id')
            ->join('users', 'employee_demo.employee_id', 'users.employee_id')
            ->whereIn('employee_demo.employee_id', $eselected_emp_ids )
            ->distinct()
            ->select ('users.id')
            ->orderBy('employee_demo.employee_name')
            ->get() ;

        if ($request->input_elements == 0) {
            $elements = array("1", "2");
        } else if ($request->input_elements == 1) {
            $elements = array("1");
        } else {
            $elements = array("2");
        }

        $reason = $request->input_reason;
        
        foreach ($eeToShare as $eeOne) {
            foreach ($shareTo as $toOne) {                
                //not allow direct team members be shared to their manager
                $get_direct = User::select('id')
                           ->where('id', '=', $eeOne->id)
                           ->where('reporting_to', '=', $toOne->id)
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
                //skip if same adn
                if ($eeOne->id <> $toOne->id) {
                    $result = SharedProfile::updateOrCreate(
                        [
                            'shared_id' => $eeOne->id, 
                            'shared_with' => $toOne->id
                        ],
                        [
                            'shared_item' => $elements , 
                            'comment' => $reason, 
                            'shared_by' => $current_user->id
                        ]
                    );

                    // Use Class to create DashboardNotification
                    $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                    $notification->user_id = $result->shared_id;
                    $notification->notification_type = 'SP';
                    $notification->comment = 'Your profile has been shared with ' . $result->sharedWith->name;
                    $notification->related_id = $result->id;
                    $notification->notify_user_id = $result->shared_id;
                    $notification->send(); 

                    // Send email to person who their profile was shared 
                    $user = User::where('id', $result->shared_id)
                        ->with('userPreference')
                        ->select('id','name','guid')
                        ->first();

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
            }
        }
        return redirect()->route(request()->segment(1).'.employeeshares')
            ->with('success', 'Share user goal/conversation successful.');
    }

    public function loadOrganizationTree(Request $request) {
        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, "");
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
        // Employee ID by Tree ID
        $empIdsByOrgId = [];
        $demoWhere = $this->baseFilteredWhere($request, "");
        $sql = clone $demoWhere; 
        $rows = $sql->select('orgid AS id', 'employee_id')
            ->groupBy('orgid', 'employee_id')
            ->orderBy('orgid')->orderBy('employee_id')
            ->get();
        $empIdsByOrgId = $rows->groupBy('id')->all();
        if($request->ajax()){
            return view('shared.employeeshares.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
        } 
    }

    public function eloadOrganizationTree(Request $request) {
        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, "e");
        $rows = $sql_level4->groupBy('o.id')->select('o.id')
            ->union( $sql_level3->groupBy('o.id')->select('o.id') )
            ->union( $sql_level2->groupBy('o.id')->select('o.id') )
            ->union( $sql_level1->groupBy('o.id')->select('o.id') )
            ->union( $sql_level0->groupBy('o.id')->select('o.id') )
            ->pluck('o.id'); 
        $eorgs = EmployeeDemoTree::whereIn('id', $rows->toArray() )->get()->toTree();
        // Employee Count by Organization
        $ecountByOrg = $sql_level4->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row"))
        ->union( $sql_level3->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level2->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level1->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level0->groupBy('o.id')->select('o.id', DB::raw("COUNT(*) as count_row") ) )
        ->pluck('count_row', 'o.id');  
        // Employee ID by Tree ID
        $eempIdsByOrgId = [];
        $demoWhere = $this->baseFilteredWhere($request, "e");
        $sql = clone $demoWhere; 
        $rows = $sql->select('orgid AS id', 'employee_id')
            ->groupBy('orgid', 'employee_id')
            ->orderBy('orgid')->orderBy('employee_id')
            ->get();
        $eempIdsByOrgId = $rows->groupBy('id')->all();
        if($request->ajax()){
            return view('shared.employeeshares.partials.erecipient-tree', compact('eorgs', 'ecountByOrg', 'eempIdsByOrgId') );
        } 
    }
  
    public function getDatatableEmployees(Request $request, $option = null) {
        if($request->ajax()){
            $demoWhere = $this->baseFilteredWhere($request, $option);
            $userCheck = "{$option}userCheck";
            $select_users = "{$option}select_users";
            $sql = clone $demoWhere; 
            $employees = $sql->select([ 
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
            ]);
            return Datatables::of($employees)
                ->addColumn($select_users, static function ($employee) {
                        return '<input pid="1335" type="checkbox" id="'.$userCheck. 
                            $employee->employee_id.'" name="'.$userCheck.'[]" value="'.$employee->employee_id.'" class="dt-body-center">';
                })->rawColumns([$select_users, 'action'])
                ->make(true);
        }
    }

    // public function egetDatatableEmployees(Request $request) {
    //     if($request->ajax()){
    //         $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
    //         $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
    //         $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
    //         $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
    //         $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;
    //         $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
    //         $esql = clone $edemoWhere; 
    //         $eemployees = $esql->select([ 
    //             'employee_demo.employee_id as eemployee_id'
    //             , 'employee_demo.employee_name as eemployee_name'
    //             , 'employee_demo.jobcode_desc as ejobcode_desc'
    //             , 'employee_demo.employee_email as eemployee_email'
    //             , 'employee_demo.organization as eorganization'
    //             , 'employee_demo.level1_program as elevel1_program'
    //             , 'employee_demo.level2_division as elevel2_division'
    //             , 'employee_demo.level3_branch as elevel3_branch'
    //             , 'employee_demo.level4 as elevel4'
    //             , 'employee_demo.deptid as edeptid'
    //         ]);
    //         return Datatables::of($eemployees)
    //             ->addColumn('eselect_users', static function ($eemployee) {
    //                     return '<input pid="1335" type="checkbox" id="euserCheck'. 
    //                     $eemployee->employee_id .'" name="euserCheck[]" value="'. $eemployee->eemployee_id .'" class="dt-body-center">';
    //                 })->rawColumns(['eselect_users','action'])
    //             ->make(true);
    //     }
    // }

    public function getUsers(Request $request)
    {
        $search = $request->search;
        $users =  User::whereRaw("name like '%{ $search }%'")->whereNotNull('email')->paginate();
        return ['data' => $users];
    }

    // public function egetOrganizations(Request $request) {
    //     $eorgs = OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //     orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
    //     ->where('organization_trees.level',0)
    //     ->when( $request->q , function ($q) use($request) {
    //         return $q->whereRaw("name LIKE '%".$request->q."%'");
    //     })
    //     ->get();
    //     $eformatted_orgs = [];
    //     foreach ($eorgs as $org) {
    //         $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
    //     }
    //     return response()->json($eformatted_orgs);
    // } 

    // public function egetPrograms(Request $request) {
    //     $elevel0 = $request->elevel0 ? OrganizationTree::
    //     join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //     where('organization_trees.id',$request->elevel0)->first() : null;

    //     $eorgs = OrganizationTree::
    //     join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //     orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
    //         ->where('organization_trees.level',1)
    //         ->when( $request->q , function ($q) use($request) {
    //             return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
    //             })
    //         ->when( $elevel0 , function ($q) use($elevel0) {
    //             return $q->where('organization_trees.organization', $elevel0->name );
    //         })
    //         ->groupBy('organization_trees.name')
    //         ->get();
    //     $eformatted_orgs = [];
    //     foreach ($eorgs as $org) {
    //         $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
    //     }
    //     return response()->json($eformatted_orgs);
    // } 

    // public function egetDivisions(Request $request) {

    //     $elevel0 = $request->elevel0 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //     where('organization_trees.id', $request->elevel0)->first() : null;
    //     $elevel1 = $request->elevel1 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //     where('organization_trees.id', $request->elevel1)->first() : null;

    //     $eorgs = OrganizationTree::
    //     join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
    //         ->where('organization_trees.level',2)
    //         ->when( $request->q , function ($q) use($request) {
    //             return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
    //             })
    //         ->when( $elevel0 , function ($q) use($elevel0) {
    //             return $q->where('organization_trees.organization', $elevel0->name) ;
    //         })
    //         ->when( $elevel1 , function ($q) use($elevel1) {
    //             return $q->where('organization_trees.level1_program', $elevel1->name );
    //         })
    //         ->groupBy('organization_trees.name')
    //         ->limit(300)
    //         ->get();

    //     $eformatted_orgs = [];
    //     foreach ($eorgs as $org) {
    //         $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
    //     }

    //     return response()->json($eformatted_orgs);
    // } 

    // public function egetBranches(Request $request) {
    //     $elevel0 = $request->elevel0 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         where('organization_trees.id', $request->elevel0)->first() : null;
    //     $elevel1 = $request->elevel1 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         where('organization_trees.id', $request->elevel1)->first() : null;
    //     $elevel2 = $request->elevel2 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         where('organization_trees.id', $request->elevel2)->first() : null;

    //     $eorgs = OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
    //         ->where('organization_trees.level',3)
    //         ->when( $request->q , function ($q) use($request) {
    //             return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
    //             })
    //         ->when( $elevel0 , function ($q) use($elevel0) {
    //             return $q->where('organization_trees.organization', $elevel0->name) ;
    //         })
    //         ->when( $elevel1 , function ($q) use($elevel1) {
    //             return $q->where('organization_trees.level1_program', $elevel1->name );
    //         })
    //         ->when( $elevel2 , function ($q) use($elevel2) {
    //             return $q->where('organization_trees.level2_division', $elevel2->name );
    //         })
    //         ->groupBy('organization_trees.name')
    //         ->limit(300)
    //         ->get();

    //     $eformatted_orgs = [];
    //     foreach ($eorgs as $org) {
    //         $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
    //     }

    //     return response()->json($eformatted_orgs);
    // } 

    // public function egetLevel4(Request $request) {
    //     $elevel0 = $request->elevel0 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         where('organization_trees.id', $request->elevel0)->first() : null;
    //     $elevel1 = $request->elevel1 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         where('organization_trees.id', $request->elevel1)->first() : null;
    //     $elevel2 = $request->elevel2 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         where('organization_trees.id', $request->elevel2)->first() : null;
    //     $elevel3 = $request->elevel3 ? OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         where('organization_trees.id', $request->elevel3)->first() : null;

    //     $eorgs = OrganizationTree::
    //         join('admin_orgs', function($join) {
    //         $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
    //         ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
    //         ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
    //         ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
    //         ->on('organization_trees.level4', '=', 'admin_orgs.level4');
    //     })
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->
    //         orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
    //         ->where('organization_trees.level',4)
    //         ->when( $request->q , function ($q) use($request) {
    //             return $q->whereRaw("organization_trees.name LIKE '%".$request->q."%'");
    //             })
    //         ->when( $elevel0 , function ($q) use($elevel0) {
    //             return $q->where('organization_trees.organization', $elevel0->name) ;
    //         })
    //         ->when( $elevel1 , function ($q) use($elevel1) {
    //             return $q->where('organization_trees.level1_program', $elevel1->name );
    //         })
    //         ->when( $elevel2 , function ($q) use($elevel2) {
    //             return $q->where('organization_trees.level2_division', $elevel2->name );
    //         })
    //         ->when( $elevel3 , function ($q) use($elevel3) {
    //             return $q->where('organization_trees.level3_branch', $elevel3->name );
    //         })
    //         ->groupBy('organization_trees.name')
    //         ->limit(300)
    //         ->get();

    //     $eformatted_orgs = [];
    //     foreach ($eorgs as $org) {
    //         $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
    //     }

    //     return response()->json($eformatted_orgs);
    // } 

    public function getEmployees(Request $request,  $id, $option = null) {
        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = $this->baseFilteredSQLs($request, $option);
        $rows = $sql_level4->where('id', $id)
            ->union( $sql_level3->where('id', $id) )
            ->union( $sql_level2->where('id', $id) )
            ->union( $sql_level1->where('id', $id) )
            ->union( $sql_level0->where('id', $id) );
        $employees = $rows->orderBy('employee_name')->get();
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

    // public function egetEmployees(Request $request,  $id) {
    //     $elevel0 = $request->edd_level0 ? OrganizationTree::where('id', $request->edd_level0)->first() : null;
    //     $elevel1 = $request->edd_level1 ? OrganizationTree::where('id', $request->edd_level1)->first() : null;
    //     $elevel2 = $request->edd_level2 ? OrganizationTree::where('id', $request->edd_level2)->first() : null;
    //     $elevel3 = $request->edd_level3 ? OrganizationTree::where('id', $request->edd_level3)->first() : null;
    //     $elevel4 = $request->edd_level4 ? OrganizationTree::where('id', $request->edd_level4)->first() : null;

    //     list($esql_level0, $esql_level1, $esql_level2, $esql_level3, $esql_level4) = 
    //         $this->ebaseFilteredSQLs($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);
       
    //     $rows = $esql_level4->where('organization_trees.id', $id)
    //         ->union( $esql_level3->where('organization_trees.id', $id) )
    //         ->union( $esql_level2->where('organization_trees.id', $id) )
    //         ->union( $esql_level1->where('organization_trees.id', $id) )
    //         ->union( $esql_level0->where('organization_trees.id', $id) );

    //     $eemployees = $rows->get();

    //     $parent_id = $id;
        
    //         return view('shared.employeeshares.partials.employee', compact('eparent_id', 'eemployees') ); 
    // }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'employee_id' => 'Employee ID', 
            'employee_name'=> 'Employee Name',
            'jobcode_desc' => 'Classification', 
            'deptid' => 'Department ID'
        ];
    }

    protected function baseFilteredWhere($request, $option = null) {
        $authId = Auth::id();
        $field0 = "{$option}dd_level0";
        $field1 = "{$option}dd_level1";
        $field2 = "{$option}dd_level2";
        $field3 = "{$option}dd_level3";
        $field4 = "{$option}dd_level4";
        $criteria = "{$option}criteria";
        $search_text = "{$option}search_text";
        return HRUserDemoJrView::from('hr_user_demo_jr_view AS u')
            ->whereRaw("u.ao_user_id = {$authId}")
            ->whereNull('u.date_deleted')
            ->when($request->{$field0}, function($q) use($request, $field0) { return $q->whereRaw("u.organization_key = {$request->{$field0}}"); })
            ->when($request->{$field1}, function($q) use($request, $field1) { return $q->whereRaw("u.level1_key = {$request->{$field1}}"); })
            ->when($request->{$field2}, function($q) use($request, $field2) { return $q->whereRaw("u.level2_key = {$request->{$field2}}"); })
            ->when($request->{$field3}, function($q) use($request, $field3) { return $q->whereRaw("u.level3_key = {$request->{$field3}}"); })
            ->when($request->{$field4}, function($q) use($request, $field4) { return $q->whereRaw("u.level4_key = {$request->{$field4}}"); })
            ->when($request->{$search_text} && $request->{$criteria} != 'all', function($q) use ($request, $criteria, $search_text) { return $q->whereRaw("u.{$request->{$criteria}} like '%{$request->{$search_text}}%'"); })
            ->when($request->{$search_text} && $request->{$criteria} == 'all', function($q) use($request, $search_text) { return $q->whereRaw("(u.employee_id LIKE '%{$request->{$search_text}}%' OR u.employee_name LIKE '%{$request->{$search_text}}%' OR u.jobcode_desc LIKE '%{$request->{$search_text}}%' OR u.deptid LIKE '%{$request->{$search_text}}%')"); });
    }

    // protected function ebaseFilteredWhere(Request $request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
    //     // Base Where Clause
    //     $edemoWhere = EmployeeDemo::whereNull('employee_demo.date_deleted')
    //     ->join('admin_orgs', function ($j1) {
    //         $j1->on(function ($j1a) {
    //             $j1a->whereRAW('admin_orgs.organization = employee_demo.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (employee_demo.organization = "" OR employee_demo.organization IS NULL))');
    //         } )
    //         ->on(function ($j2a) {
    //             $j2a->whereRAW('admin_orgs.level1_program = employee_demo.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (employee_demo.level1_program = "" OR employee_demo.level1_program IS NULL))');
    //         } )
    //         ->on(function ($j3a) {
    //             $j3a->whereRAW('admin_orgs.level2_division = employee_demo.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (employee_demo.level2_division = "" OR employee_demo.level2_division IS NULL))');
    //         } )
    //         ->on(function ($j4a) {
    //             $j4a->whereRAW('admin_orgs.level3_branch = employee_demo.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (employee_demo.level3_branch = "" OR employee_demo.level3_branch IS NULL))');
    //         } )
    //         ->on(function ($j5a) {
    //             $j5a->whereRAW('admin_orgs.level4 = employee_demo.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (employee_demo.level4 = "" OR employee_demo.level4 IS NULL))');
    //         } );
    //     } )
    //     ->where('admin_orgs.user_id', '=', Auth::id())
    //     ->when( $elevel0, function ($q) use($elevel0) {
    //         return $q->where('employee_demo.organization', $elevel0->name);
    //     })
    //     ->when( $elevel1, function ($q) use($elevel1) {
    //         return $q->where('employee_demo.level1_program', $elevel1->name);
    //     })
    //     ->when( $elevel2, function ($q) use($elevel2) {
    //         return $q->where('employee_demo.level2_division', $elevel2->name);
    //     })
    //     ->when( $elevel3, function ($q) use($elevel3) {
    //         return $q->where('employee_demo.level3_branch', $elevel3->name);
    //     })
    //     ->when( $elevel4, function ($q) use($elevel4) {
    //         return $q->where('employee_demo.level4', $elevel4->name);
    //     })
    //     ->when( $request->esearch_text && $request->ecriteria == 'all', function ($q) use($request) {
    //         $q->where(function($query) use ($request) {
                
    //             return $query->whereRaw("employee_demo.employee_id LIKE '%".$request->esearch_text."%'")
    //                 ->orWhereRaw("employee_demo.employee_name LIKE '%".$request->esearch_text."%'")
    //                 ->orWhereRaw("employee_demo.jobcode_desc LIKE '%".$request->esearch_text."%'")
    //                 ->orWhereRaw("employee_demo.deptid LIKE '%".$request->esearch_text."%'");
    //         });
    //     })
    //     ->when( $request->esearch_text && $request->ecriteria == 'emp', function ($q) use($request) {
    //         return $q->whereRaw("employee_demo.employee_id LIKE '%".$request->esearch_text."%'");
    //     })
    //     ->when( $request->esearch_text && $request->ecriteria == 'name', function ($q) use($request) {
    //         return $q->whereRaw("employee_demo.employee_name LIKE '%".$request->esearch_text."%'");
    //     })
    //     ->when( $request->esearch_text && $request->ecriteria == 'job', function ($q) use($request) {
    //         return $q->whereRaw("employee_demo.jobcode_desc LIKE '%".$request->esearch_text."%'");
    //     })
    //     ->when( $request->esearch_text && $request->ecriteria == 'dpt', function ($q) use($request) {
    //         return $q->whereRaw("employee_demo.deptid LIKE '%".$request->esearch_text."%'");
    //     });
    //     return $edemoWhere;
    // }

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

    // protected function baseFilteredSQLs(Request $request, $level0, $level1, $level2, $level3, $level4) {
    //     // Base Where Clause
    //     $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);

    //     $sql_level0 = clone $demoWhere; 
    //     $sql_level0->join('organization_trees', function($join) use($level0) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->where('organization_trees.level', '=', 0);
    //         });
            
    //     $sql_level1 = clone $demoWhere; 
    //     $sql_level1->join('organization_trees', function($join) use($level0, $level1) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
    //             ->where('organization_trees.level', '=', 1);
    //         });
            
    //     $sql_level2 = clone $demoWhere; 
    //     $sql_level2->join('organization_trees', function($join) use($level0, $level1, $level2) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
    //             ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
    //             ->where('organization_trees.level', '=', 2);    
    //         });    
            
    //     $sql_level3 = clone $demoWhere; 
    //     $sql_level3->join('organization_trees', function($join) use($level0, $level1, $level2, $level3) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
    //             ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
    //             ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
    //             ->where('organization_trees.level', '=', 3);    
    //         });
            
    //     $sql_level4 = clone $demoWhere; 
    //     $sql_level4->join('organization_trees', function($join) use($level0, $level1, $level2, $level3, $level4) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
    //             ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
    //             ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
    //             ->on('employee_demo.level4', '=', 'organization_trees.level4')
    //             ->where('organization_trees.level', '=', 4);
    //         });
    //     return  [$sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4];
    // }

    // protected function ebaseFilteredSQLs(Request $request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
    //     // Base Where Clause
    //     $edemoWhere = $this->ebaseFilteredWhere($request, $elevel0, $elevel1, $elevel2, $elevel3, $elevel4);

    //     $esql_level0 = clone $edemoWhere; 
    //     $esql_level0->join('organization_trees', function($join) use($elevel0) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->where('organization_trees.level', '=', 0);
    //         });
            
    //     $esql_level1 = clone $edemoWhere; 
    //     $esql_level1->join('organization_trees', function($join) use($elevel0, $elevel1) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
    //             ->where('organization_trees.level', '=', 1);
    //         });
            
    //     $esql_level2 = clone $edemoWhere; 
    //     $esql_level2->join('organization_trees', function($join) use($elevel0, $elevel1, $elevel2) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
    //             ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
    //             ->where('organization_trees.level', '=', 2);    
    //         });    
            
    //     $esql_level3 = clone $edemoWhere; 
    //     $esql_level3->join('organization_trees', function($join) use($elevel0, $elevel1, $elevel2, $elevel3) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
    //             ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
    //             ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
    //             ->where('organization_trees.level', '=', 3);    
    //         });
            
    //     $esql_level4 = clone $edemoWhere; 
    //     $esql_level4->join('organization_trees', function($join) use($elevel0, $elevel1, $elevel2, $elevel3, $elevel4) {
    //         $join->on('employee_demo.organization', '=', 'organization_trees.organization')
    //             ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
    //             ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
    //             ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
    //             ->on('employee_demo.level4', '=', 'organization_trees.level4')
    //             ->where('organization_trees.level', '=', 4);
    //         });

    //     return  [$esql_level0, $esql_level1, $esql_level2, $esql_level3, $esql_level4];
    // }

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

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);

        $criteriaList = $this->search_criteria_list();
        $sharedElements = SharedElement::all();

        return view('shared.employeeshares.manageindex', compact ('request', 'criteriaList', 'sharedElements'));
    }

    public function manageindexlist(Request $request) {
        if ($request->ajax()) {
            $authId = Auth::id();
            $query = HRUserDemoJrView::from('hr_user_demo_jr_view AS u')
                ->whereRaw("u.ao_user_id = {$authId}")
                ->join('shared_profiles AS sp', 'sp.shared_id', 'u.user_id')
                ->leftjoin('hr_user_demo_jr_view AS u2', 'u2.user_id', 'sp.shared_with')
                ->whereRaw("u2.ao_user_id = {$authId}")
                ->leftjoin('user_demo_jr_view AS cc', 'cc.user_id', 'sp.shared_by')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text && $request->criteria == 'employee_name', function($q) use($request) {
                    return $q->where(function ($r) use($request) {
                        return $r->whereRaw("u.{$request->criteria} LIKE '%{$request->search_text}%'")
                            ->orWhereRaw("u2.{$request->criteria} LIKE '%{$request->search_text}%'")
                            ->orWhereRaw("cc.{$request->criteria} LIKE '%{$request->search_text}%'");
                    });
                })
                ->when($request->search_text && $request->criteria == 'employee_id', function($q) use($request) {
                    return $q->where(function ($r) use($request) {
                        return $r->whereRaw("u.{$request->criteria} LIKE '%{$request->search_text}%'")
                            ->orWhereRaw("u2.{$request->criteria} LIKE '%{$request->search_text}%'");
                    });
                })
            ->when($request->search_text && ($request->criteria == 'jobcode_desc' || $request->criteria == 'deptid'), function($q) use($request) {
                return $q->whereRaw("u.{$request->criteria} LIKE '%{$request->search_text}%'");
            })
            ->when($request->search_text && $request->criteria == 'all', function($q) use($request) {
                return $q->whereRaw("u.employee_id LIKE '%{$request->search_text}%'")
                    ->orWhereRaw("u.employee_name LIKE '%{$request->search_text}%'")
                    ->orWhereRaw("u2.employee_id LIKE '%{$request->search_text}%'")
                    ->orWhereRaw("u2.employee_name LIKE '%{$request->search_text}%'")
                    ->orWhereRaw("cc.employee_name LIKE '%{$request->search_text}%'")
                    ->orWhereRaw("u.jobcode_desc LIKE '%{$request->search_text}%'")
                    ->orWhereRaw("u.deptid LIKE '%{$request->search_text}%'");
            })
            ->select (
                'u.employee_id',
                'u.employee_name', 
                'u2.employee_id as delegate_ee_id',
                'u2.employee_name as delegate_ee_name',
                'u2.name as alternate_delegate_name',
                'sp.shared_item',
                'u.jobcode_desc',
                'u.organization',
                'u.level1_program',
                'u.level2_division',
                'u.level3_branch',
                'u.level4',
                'u.deptid',
                'cc.employee_name as created_name',
                'sp.created_at',
                'sp.updated_at',
                'sp.id as shared_profile_id',
            )
            ->orderBy('u.employee_id')
            ->orderBy('delegate_ee_id');
            return Datatables::of($query)
            ->addIndexColumn()
            ->editColumn('shared_item', function ($row) {
                $dcode = json_decode ($row->shared_item);
                return count($dcode) == 2 ? 'All' : ($dcode[0] == 1 ? 'Goal' : 'Conversation');
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
            ->rawColumns(['created_at', 'updated_at', 'action'])
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
        ->where('id', '=', $id)
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
        return view('shared.employeeshares.partials.access-edit-modal', compact('roles', 'access', 'email'));
    }


}
