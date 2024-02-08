<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Goal;
use App\Models\User;
use App\Models\GoalType;
use App\Models\Participant;
use Illuminate\Http\Request;
use App\Models\EmployeeShare;
use App\Models\ExcusedReason;
use App\Models\SharedProfile;
use App\Scopes\NonLibraryScope;
use App\Models\ConversationTopic;
use App\Models\ExcusedClassification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DashboardNotification;
use App\DataTables\MyEmployeesDataTable;
use App\Http\Requests\ShareMyGoalRequest;
use App\DataTables\SharedEmployeeDataTable;
use App\Http\Requests\MyTeams\ShareProfileRequest;
use App\Http\Requests\MyTeams\UpdateExcuseRequest;
use App\Http\Requests\Goals\AddGoalToLibraryRequest;
use App\Http\Requests\MyTeams\UpdateProfileSharedWithRequest;
use Carbon\Carbon;

class MyTeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function myEmployees(MyEmployeesDataTable $myEmployeesDataTable, SharedEmployeeDataTable $sharedEmployeeDataTable)
    {
        $tags = Tag::all()->sortBy("name")->toArray();
        $goaltypes = GoalType::all();
        // $eReasons = ExcusedReason::all();
        $eReasons = ExcusedReason::where('id', '>', 2)->get();
        $eReasons2 = ExcusedReason::where('id', '<=', 2)->get();
        $yesOrNo = [0 =>'No', 1 => 'Yes'];
        $conversationTopics = ConversationTopic::all();
        // $participants = Participant::all();

        $goals = Goal::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('user')
            ->with('sharedWith')
            ->with('goalType')->get();
        $employees = $this->myEmployeesAjax();

        $adminShared=SharedProfile::select('shared_id')
        ->join('users','users.id','shared_profiles.shared_id')
        ->join('employee_demo','employee_demo.employee_id', 'users.employee_id')
        ->whereNull('employee_demo.date_deleted')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->where('shared_with', '=', Auth::id())
        ->where(function ($sh) {
            $sh->where('shared_item', 'like', '%1%')
            ->orWhere('shared_item', 'like', '%2%');
        })
        ->pluck('shared_id');

        $adminemps = User::select('users.*')
        ->join('employee_demo','employee_demo.employee_id', 'users.employee_id')
        ->whereNull('employee_demo.date_deleted')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->whereIn('users.id', $adminShared)->get();

        $employees = $employees->merge($adminemps);

        $type = 'upcoming'; // Allow Editing
        $showSignoff = false;
        $myEmpTable = $myEmployeesDataTable->html();
        $sharedEmpTable = $sharedEmployeeDataTable->html();
        
        $type_desc_arr = array();
        foreach($goaltypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {
                $item = "<b>" . $goalType['name'] . " Goals</b> ". $goalType['description'];                
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>',$type_desc_arr);
        
        $employees_list = array();
        $i = 0;
        if(count($employees)>0) {
            foreach ($employees as $employee) {
                $employees_list[$i]["id"] = $employee->id;
                $employees_list[$i]["name"] = $employee->name;
                $i++;
            }
        }

        $shared_employees = DB::table('shared_profiles')
                    ->select('shared_profiles.shared_id', 'users.name')
                    ->join('users', 'users.id', '=', 'shared_profiles.shared_id')
                    ->join('employee_demo','employee_demo.employee_id', 'users.employee_id')
                    ->whereNull('employee_demo.date_deleted')
                    ->whereRaw('employee_demo.pdp_excluded = 0')
                    ->where('shared_profiles.shared_with', Auth::id())
                    ->where('shared_profiles.shared_item', 'like', '%1%')
                    ->get();
        
        if(count($shared_employees)>0) {
            foreach ($shared_employees as $shared_employee) {
                $employees_list[$i]["id"] = $shared_employee->shared_id;
                $employees_list[$i]["name"] = $shared_employee->name;
                $i++;
            }
        }

        $ClassificationArray = ExcusedClassification::select('jobcode')->get()->toArray();

        $yesOrNo = [
            [ "id" => 0, "name" => 'No' ],
            [ "id" => 1, "name" => 'Yes' ],
        ];

        $yesOrNo2 = [
            [ "id" => 0, "name" => 'No' ],
            [ "id" => 1, "name" => 'Yes' ],
        ];

        return view('my-team/my-employees',compact('goals', 'tags', 'employees', 'employees_list', 'goaltypes', 'type_desc_str', 'conversationTopics', 'type', 'myEmpTable', 'sharedEmpTable', 'eReasons', 'eReasons2', 'showSignoff', 'yesOrNo', 'yesOrNo2', 'ClassificationArray'));
        // return $myEmployeesDataTable->render('my-team/my-employees',compact('goals', 'employees', 'goaltypes', 'conversationTopics', 'participants', 'type'));
    }

    public function myEmployeesTable(MyEmployeesDataTable $myEmployeesDataTable) {
        return $myEmployeesDataTable->render('my-team/my-employees');
    }

    public function sharedEmployeesTable(SharedEmployeeDataTable $sharedEmployeeDataTable) {
        return $sharedEmployeeDataTable->render('my-team/my-employees');
    }

    public function myEmployeesAjax() {
        return User::find(Auth::id())->avaliableReportees()->get();
    }
    
    public function mySharedEmployeesAjax() {
        return SharedProfile::find(Auth::id())->sharedWithUser()->get();
    }

    public function getProfileSharedWith($user_id) {
        $sharedProfiles = SharedProfile::where('shared_id', $user_id)->with(['sharedWith' => function ($query) {
            $query->select('id', 'name');
        }])->get();
        
        session()->put('checking_user', $user_id);

        return view('my-team.partials.profile-shared-with', compact('sharedProfiles'));
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

    public function getProfileExcused($user_id) {
        $excusedreasons = ExcusedReason::all();
        $excusedprofile = DB::table('users')
            ->where('id', $user_id)
            ->select('id', 'name', 'excused_flag', 'excused_reason_id')
            ->get();
        return view('my-team.partials.employee-excused-modal', compact('excusedreasons', 'excusedprofile'));
        // return $this->respondeWith($sharedProfiles);
    }

    public function updateExcuseDetails(UpdateExcuseRequest $request)
    // public function updateExcuseDetails(Request $request)
    {
        $excused = User::find($request->user_id);
        $excused->excused_flag = $request->excused_flag;
        $excused->excused_reason_id = $request->excused_reason_id;
        $excused->excused_updated_by =  Auth::id();
        $excused->excused_updated_at = Carbon::now();
        $excused->save();

        return response()->json(['success' => true, 'message' => 'Participant Excused settings updated successfully']);
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

            // foreach ($sharedProfile as $result) {
            //     // Dashboard message added when an shared employee's profile (goals, conversations, or both)
            //     // DashboardNotification::create([
            //     //     'user_id' => $result->shared_id,
            //     //     'notification_type' => 'SP',         
            //     //     'comment' => 'Your profile has been shared with ' . $result->sharedWith->name,
            //     //     'related_id' => $result->id,
            //     // ]);
            //     // Use Class to create DashboardNotification
            //                 $notification = new \App\MicrosoftGraph\SendDashboardNotification();
            //                 $notification->user_id = $result->shared_id;
            //                 $notification->notification_type = 'SP';
            //                 $notification->comment = 'Your profile has been shared with ' . $result->sharedWith->name;
            //                 $notification->related_id =  $result->id;
            //     $notification->notify_user_id = $result->shared_id;
            //                 $notification->send(); 
            // }

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
        }                
        return response()->json(['success' => false, 'message' => $error_msg]);
    }

    public function userList(Request $request) {
        $current_user = '';
        if(session()->has('checking_user') && session()->get('checking_user') != '') {
            $current_user = session()->get('checking_user');
        }
        
        
        $search = $request->search;
        
        if ($current_user == '') {
            $user_query = User::where('name', 'LIKE', "%{$search}%")
                          ->join('employee_demo', 'employee_demo.employee_id','users.employee_id')
                          ->whereNull('employee_demo.date_deleted')  
                          ->whereRaw('employee_demo.pdp_excluded = 0')
                          ->paginate();
        } else {
            $user_query = User::where('name', 'LIKE', "%{$search}%")
                          ->where('id', '<>', $current_user)
                          ->join('employee_demo', 'employee_demo.employee_id','users.employee_id')
                          ->whereNull('employee_demo.date_deleted')  
                          ->whereRaw('employee_demo.pdp_excluded = 0')
                          ->paginate();
        }
        
        return $this->respondeWith($user_query);
        // return $this->respondeWith(User::leftjoin('employee_demo', 'employee_demo.guid', '=', 'users.guid')->where('name', 'LIKE', "%{$search}%")->paginate());
    }

    public function userOptions(Request $request) {
        $current_user = '';
        if(session()->has('checking_user') && session()->get('checking_user') != '') {
            $current_user = session()->get('checking_user');
        }
        
        
        $search = $request->search;
        
        if ($current_user == '') {
            $user_query = User::select('id', 'name', 'employee_demo.employee_email')
                          ->where('name', 'LIKE', "%{$search}%")
                          ->join('employee_demo', 'employee_demo.employee_id','users.employee_id')
                          ->whereNull('employee_demo.date_deleted')  
                          ->whereRaw('employee_demo.pdp_excluded = 0')
                          ->groupBy('id', 'name', 'employee_demo.employee_email')
                          ->paginate();
        } else {
            $user_query = User::select('id', 'name', 'employee_demo.employee_email')
                          ->where('name', 'LIKE', "%{$search}%")
                          ->where('id', '<>', $current_user)
                          ->join('employee_demo', 'employee_demo.employee_id','users.employee_id')
                          ->whereNull('employee_demo.date_deleted')  
                          ->whereRaw('employee_demo.pdp_excluded = 0')
                          ->groupBy('id', 'name', 'employee_demo.employee_email')
                          ->paginate();
        }
        
        return $this->respondeWith($user_query);
        // return $this->respondeWith(User::leftjoin('employee_demo', 'employee_demo.guid', '=', 'users.guid')->where('name', 'LIKE', "%{$search}%")->paginate());
    }

    public function performanceStatistics()
    {
        $goaltypes = GoalType::all();
        $eReasons = ExcusedReason::all();
        $conversationTopics = ConversationTopic::all();
        // $participants = Participant::all();
        // $participants = Participant::select('id', 'name')->get();
        $participants = [];

        $goals = Goal::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('user')
            ->with('goalType')->get();
        $employees = $this->myEmployeesAjax();
        $type = 'upcoming';
        // return view('my-team/performance-statistics', compact('goals','employees', 'goaltypes'));
        return view('my-team/performance-statistics', compact('goals', 'employees', 'goaltypes', 'conversationTopics', 'participants', 'type', 'eReasons'));

    }
    public function goalsHierarchy()
    {
        $goals = Goal::where('user_id', Auth::id())
            ->with('user')
            ->with('goalType')->get();
        $employees = $this->myEmployeesAjax();

        return view('my-team/goals-hierarchy', compact('goals','employees', 'goaltypes'));
    }

    public function syncGoals(ShareMyGoalRequest $request) {
        $input = $request->validated();
        if($request->has("share_with")) {
            $shareWith = $request->share_with;
            foreach ($shareWith as $goalId => $userIds) {
                $goal = Goal::find($goalId);
                $goal->sharedWith()->sync(array_filter($userIds));
            }
        }
        if($request->has("is_shared")) {
            $isSharedArray = $input['is_shared'];
            foreach ($isSharedArray as $goalId => $isShared) {
                if (!(bool) $isShared) {
                    $goal = Goal::find($goalId);
                    $goal->sharedWith()->detach();
                }
            }
            // dd((bool)$input['is_shared'][995]);
        }
        if (!$request->ajax()) {
            return redirect()->back();
        }
    }

    public function viewProfileAs($id, $landingPage = null) {
        $actualAuthId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
        //$hasAccess = User::with('reportingManagerRecursive')->find($id)->canBeSeenBy($actualAuthId);
        //$hasAccess = true;
        // If it is shared with Logged In user.
        $hasAccess = false;
        
        $employees = $this->myEmployeesAjax();
        foreach($employees as $employee) {
            if($id == $employee->id){
                $hasAccess = true;
            }
        }

        if($hasAccess || SharedProfile::where('shared_with', $actualAuthId)->where('shared_id', $id)->count() >= 1) {
            session()->put('view-profile-as', $id);
            if (!session()->has('original-auth-id')) {
                session()->put('original-auth-id', Auth::id());
            }
            Auth::loginUsingId($id);
            if (SharedProfile::where('shared_with', $actualAuthId)->where('shared_id', $id)->count()) {
                $sharedItems = SharedProfile::where('shared_with', $actualAuthId)->where('shared_id', $id)->pluck('shared_item')[0];
                // $sharedItem[0];
                $goalsAllowed = in_array(1, $sharedItems);
                $conversationAllowed = in_array(2, $sharedItems);
                session()->put('GOALS_ALLOWED', $goalsAllowed);
                session()->put('CONVERSATION_ALLOWED', $conversationAllowed);
            } else {
                session()->put('GOALS_ALLOWED', true);
                session()->put('CONVERSATION_ALLOWED', true);
            }
            if ($landingPage) {
                return redirect()->route($landingPage);
            }
            return (url()->previous() === Route('my-team.my-employee') || url()->previous() === Route('my-team.view-profile-as.direct-report', User::find($id)->reportingManager->id))
                ? ((session()->has('GOALS_ALLOWED') && session()->get('GOALS_ALLOWED')) ? redirect()->route('goal.current') : redirect()->route('conversation.upcoming'))
                : redirect()->back();
        } else {
            echo "You don't have the right permission to access this employee's file.";
        }
        
    }
    public function viewDirectReport($id, Request $request) {
        $userReportingTos = DB::table('user_reporting_tos')->where('user_id', $id)->pluck('reporting_to_id')->toArray();
        $can_access = false;
        
        $employees = $this->myEmployeesAjax();
        foreach($employees as $employee) {
            if($id == $employee->id){
                $can_access = true;
            }
        }

        if($can_access) {
            $myEmployeesDataTable = new MyEmployeesDataTable($id);
            $myEmployeesDataTable->setRoute(route('my-team.view-profile-as.direct-report', $id));
            if ($request->ajax()) {
                return $myEmployeesDataTable->render('my-team/my-employees');
            }
            $supervisorList = [];
            $supervisorList = User::find($id)->hierarchyParentNames($supervisorList, Auth::id());
            /* if (in_array($id, [1,2,3])) {
                array_push($supervisorList, 'Supervisor');
            } */
            $directReports = $myEmployeesDataTable->html();
            $userName = User::find($id)->name;
            return view('my-team.direct-report', compact('directReports', 'userName', 'supervisorList'));
        } else {
            echo "You don't have the right permission to access this report.";
        }
    }

    public function returnToMyProfile() {
        Auth::loginUsingId(session()->get('original-auth-id'));
        session()->forget('original-auth-id');
        session()->forget('view-profile-as');
        session()->forget('GOALS_ALLOWED');
        session()->forget('CONVERSATION_ALLOWED');
        return redirect()->route('my-team.my-employee');
    }

    public function addGoalToLibrary(AddGoalToLibraryRequest $request) {
        $input = $request->validated();
        $input['created_by'] = Auth::id();
        $input['user_id'] = Auth::id();
        $input['is_library'] = true;
        
        $share_with = '';
        if(isset($input['itemsToShare'])) {
            $share_with = $input['itemsToShare'];
            unset($input['itemsToShare']);
        }
        
        $tags = '';
        if(isset($input['tag_ids'])) {
            $tags = $input['tag_ids'];
        unset($input['tag_ids']);
        }
        
        DB::beginTransaction();
        $goal = Goal::create($input);
        if ($share_with) {
            $goal->sharedWith()->sync($share_with);
        }
        
        DB::commit();
        
        if ($tags) {
            $id = $goal->id;
            $add_tag_goal = Goal::withoutGlobalScope(NonLibraryScope::class)->findOrFail($id);
            $add_tag_goal->tags()->sync($tags);
        }

        // create Dashboard Notification displayed on Home page
        // foreach ($request->itemsToShare as $user_id) {
        //     // DashboardNotification::create([
        //     //     'user_id' => $user_id,
        //     //     'notification_type' => 'GB',        // Goal Bank
        //     //     'comment' => $goal->user->name . ' added a new goal to your goal bank.',
        //     //     'related_id' => $goal->id,
        //     // ]);
        //     // Use Class to create DashboardNotification
		// 	$notification = new \App\MicrosoftGraph\SendDashboardNotification();
		// 	$notification->user_id =  $user_id;
		// 	$notification->notification_type = 'GB';
		// 	$notification->comment = ($goal->display_name ? $goal->display_name : $goal->user->name) . ' added a new goal to your goal bank.';
		// 	$notification->related_id = $goal->id;
        //     $notification->notify_user_id = $user_id;
		// 	$notification->send(); 
        // }

        // Send out email to the user when the Goal Bank added
        foreach ($request->itemsToShare as $user_id) {

            $user = User::where('id', $user_id)
                            ->with('userPreference')
                            ->select('id','name','guid', 'employee_id')
                            ->first();

            if ($user && $user->allow_inapp_notification) {
                 // Use Class to create DashboardNotification
                $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                $notification->user_id =  $user_id;
                $notification->notification_type = 'GB';
                $notification->comment = ($goal->display_name ? $goal->display_name : $goal->user->name) . ' added a new goal to your goal bank.';
                $notification->related_id = $goal->id;
                $notification->notify_user_id = $user_id;
                $notification->send();
            }

            if ($user && $user->allow_email_notification && $user->userPreference->goal_bank_flag == 'Y') {

                // Send Out Email Notification to Employee
                $sendMail = new \App\MicrosoftGraph\SendMail();
                $sendMail->toRecipients = [ $user->id ];  
                $sendMail->sender_id = null; 
                $sendMail->useQueue = false;
                $sendMail->saveToLog = true;
                $sendMail->alert_type = 'N';
                $sendMail->alert_format = 'E';

                $sendMail->template = 'NEW_GOAL_IN_GOAL_BANK';
                array_push($sendMail->bindvariables, $user->name);                            // Recipient of the email
                array_push($sendMail->bindvariables, $goal->user ? ($goal->display_name ? $goal->display_name : $goal->user->name) : '');   // Person who added goal to goal bank
                array_push($sendMail->bindvariables, $goal->title);                           // goal title
                array_push($sendMail->bindvariables, $goal->mandatory_status_descr);          // Mandatory or suggested status
                $response = $sendMail->sendMailWithGenericTemplate();
            }
        }
                
        return response()->json(['success' => true, 'message' => 'Goal added to library successfully']);
        // return redirect()->back();
    }

    public function updateItemsToShare(Request $request) {
        $request->validate([
            'goal_id' => 'required|exists:goals,id'
        ]);

        $share_with = $request->itemsToShare;
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->find($request->goal_id); 
        
        $goal->sharedWith()->sync($share_with);
        return response()->json(['success' => true, 'message' => 'Goal synced successfully']);
    }

    public function showSugggestedGoals($viewName = 'my-team.suggested-goals', $returnView = true) {
        $goaltypes = GoalType::all();
        $eReasons = ExcusedReason::all();
        $conversationTopics = ConversationTopic::all();
        // $participants = Participant::all();
        // $participants = Participant::select('id', 'name')->get();
        $participants = [];
        $tags = Tag::all()->sortBy("name")->toArray();
        $goals = Goal::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('user')
            ->with('sharedWith')
            ->with('tags')    
            ->with('goalType')->get();
        $employees = $this->myEmployeesAjax();
       
        $type_desc_arr = array();
        foreach($goaltypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {                
                $item = "<b>" . $goalType['name'] . " Goals</b> ". str_replace($goalType['name'] . " Goals","",$goalType['description']);
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>',$type_desc_arr);
        
        $employees_list = array();
        
        $myself = DB::table('users')
                    ->select('id', 'name')
                    ->where('id', Auth::id())
                    ->first();
        $employees_list[0]["id"] = $myself->id;
        $employees_list[0]["name"] = $myself->name;
        $i = 1;
        
        //$i = 0;
        if(count($employees)>0) {
            foreach ($employees as $employee) {
                $employees_list[$i]["id"] = $employee->id;
                $employees_list[$i]["name"] = $employee->name;
                $i++;
            }
        }
        
        $shared_employees = DB::table('shared_profiles')
                    ->select('shared_profiles.shared_id', 'users.name')
                    ->join('users', 'users.id', '=', 'shared_profiles.shared_id')
                    ->where('shared_profiles.shared_with', Auth::id())
                    ->where('shared_profiles.shared_item', 'like', '%1%')
                    ->get();
        
        if(count($shared_employees)>0) {
            foreach ($shared_employees as $shared_employee) {
                $employees_list[$i]["id"] = $shared_employee->shared_id;
                $employees_list[$i]["name"] = $shared_employee->name;
                $i++;
            }
        }
        usort($employees_list, function($a, $b){ return strcmp($a["name"], $b["name"]); });
        
        $type = 'upcoming';
        $disableEdit = false;
        $allowEditModal = true;
        $suggestedGoals = Goal::withoutGlobalScope(NonLibraryScope::class)->where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('is_library', 1)
            ->where('by_admin', '=', 0)
            ->with('user')
            ->with('goalType')
            ->paginate(8);
        $goalDeleteConfirmationText = "You are about to delete a suggested goal, meaning it will no longer be visible to your direct reports. Are you sure you want to continue?";
        $viewData = compact('goals', 'goaltypes', 'type_desc_str', 'tags', 'conversationTopics', 'participants', 'eReasons', 'employees', 'employees_list', 'type', 'suggestedGoals', 'disableEdit', 'allowEditModal', 'goalDeleteConfirmationText');
        if (!$returnView) {
            return $viewData;
        }
        
        return view($viewName, $viewData);
    }

}
