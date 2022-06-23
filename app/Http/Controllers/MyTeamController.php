<?php

namespace App\Http\Controllers;

use App\DataTables\MyEmployeesDataTable;
use App\DataTables\SharedEmployeeDataTable;
use App\Http\Requests\Goals\AddGoalToLibraryRequest;
use App\Http\Requests\MyTeams\ShareProfileRequest;
use App\Http\Requests\MyTeams\UpdateExcuseRequest;
use App\Http\Requests\MyTeams\UpdateProfileSharedWithRequest;
use App\Http\Requests\ShareMyGoalRequest;
use App\Models\ConversationTopic;
use App\Models\ExcusedReason;
use App\Models\Goal;
use App\Models\GoalType;
use App\Models\EmployeeShare;
use App\Models\Participant;
use App\Models\SharedProfile;
use App\Models\User;
use App\Scopes\NonLibraryScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;

class MyTeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function myEmployees(MyEmployeesDataTable $myEmployeesDataTable, SharedEmployeeDataTable $sharedEmployeeDataTable)
    {
        $tags = Tag::all()->toArray();
        $goaltypes = GoalType::all();
        $eReasons = ExcusedReason::all();
        $conversationTopics = ConversationTopic::all();
        // $participants = Participant::all();

        $goals = Goal::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('user')
            ->with('sharedWith')
            ->with('goalType')->get();
        $employees = $this->myEmployeesAjax();

        // $adminShared=EmployeeShare::select('shared_with_id')
        // ->where('user_id', '=', Auth::id())
        // ->whereIn('shared_element_id', ['B', 'G'])
        // ->pluck('shared_with_id');
        // $adminemps = User::select('users.*')
        // ->whereIn('users.id', $adminShared)->get();
        // $employees = $employees->merge($adminemps);

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
                $employees_list[0]["id"] = $employee->id;
                $employees_list[0]["name"] = $employee->name;
                $i++;
            }
        }

        $shared_employees = DB::table('shared_profiles')
                    ->select('shared_profiles.shared_id', 'users.name')
                    ->join('users', 'users.id', '=', 'shared_profiles.shared_id')
                    ->where('shared_profiles.shared_with', Auth::id())
                    ->get();
        
        if(count($shared_employees)>0) {
            foreach ($shared_employees as $shared_employee) {
                $employees_list[$i]["id"] = $shared_employee->shared_id;
                $employees_list[$i]["name"] = $shared_employee->name;
                $i++;
            }
        }
        
        return view('my-team/my-employees',compact('goals', 'tags', 'employees', 'employees_list', 'goaltypes', 'type_desc_str', 'conversationTopics', 'type', 'myEmpTable', 'sharedEmpTable', 'eReasons', 'showSignoff'));
        // return $myEmployeesDataTable->render('my-team/my-employees',compact('goals', 'employees', 'goaltypes', 'conversationTopics', 'participants', 'type'));
    }

    public function myEmployeesTable(MyEmployeesDataTable $myEmployeesDataTable) {
        return $myEmployeesDataTable->render('my-team/my-employees');
    }

    public function sharedEmployeesTable(SharedEmployeeDataTable $sharedEmployeeDataTable) {
        return $sharedEmployeeDataTable->render('my-team/my-employees');
    }

    public function myEmployeesAjax() {
        return User::find(Auth::id())->reportees()->get();
    }
    
    public function mySharedEmployeesAjax() {
        return SharedProfile::find(Auth::id())->sharedWithUser()->get();
    }

    public function getProfileSharedWith($user_id) {
        $sharedProfiles = SharedProfile::where('shared_id', $user_id)->with(['sharedWith' => function ($query) {
            $query->select('id', 'name');
        }])->get();

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

        $sharedProfile->delete();
        return $this->respondeWith('');
    }

    public function getProfileExcused($user_id) {
        $excusedreasons = ExcusedReason::all();
        $excusedprofile = DB::table(users)
            ->where('id', $user_id)
            ->select('id', 'name', 'excused_start_date', 'excused_end_date')
            ->get();
        return view('my-team.partials.employee-excused-modal', $excusedreasons);
        // return $this->respondeWith($sharedProfiles);
    }

    public function updateExcuseDetails(UpdateExcuseRequest $request)
    {
        $excused = User::find($request->user_id);
        $excused->excused_start_date = $request->excused_start_date;
        $excused->excused_end_date = $request->excused_end_date;
        $excused->excused_reason_id = $request->excused_reason_id;
        $excused->save();

        return response()->json(['success' => true, 'message' => 'Participant Excused settings updated successfully']);
    }

    public function shareProfile(ShareProfileRequest $request) {
        $input = $request->validated();
        // dd($input);
        // 
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
        DB::beginTransaction();
        foreach ($input['share_with_users'] as $user_id) {
            $insert['shared_with'] = $user_id;
            array_push($sharedProfile, SharedProfile::updateOrCreate($insert));
        }

        DB::commit();
        return $this->respondeWith($sharedProfile);
    }

    public function userList(Request $request) {
        $search = $request->search;
        return $this->respondeWith(User::where('name', 'LIKE', "%{$search}%")->paginate());
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
        $hasAccess = true;
        // If it is shared with Logged In user.

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
        }
        if ($landingPage) {
            return redirect()->route($landingPage);
        }
        return (url()->previous() === Route('my-team.my-employee') || url()->previous() === Route('my-team.view-profile-as.direct-report', User::find($id)->reportingManager->id))
            ? ((session()->has('GOALS_ALLOWED') && session()->get('GOALS_ALLOWED')) ? redirect()->route('goal.current') : redirect()->route('conversation.upcoming'))
            : redirect()->back();
    }
    public function viewDirectReport($id, Request $request) {
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
        
        return response()->json(['success' => true, 'message' => 'Goal added to library successfully']);
        // return redirect()->back();
    }

    public function updateItemsToShare(Request $request) {
        $request->validate([
            'itemsToShare' => 'required|array',
            'itemsToShare.*' => 'exists:users,id',
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
        $tags = Tag::all()->toArray();
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
        $i = 0;
        if(count($employees)>0) {
            foreach ($employees as $employee) {
                $employees_list[0]["id"] = $employee->id;
                $employees_list[0]["name"] = $employee->name;
                $i++;
            }
        }
        
        $shared_employees = DB::table('shared_profiles')
                    ->select('shared_profiles.shared_id', 'users.name')
                    ->join('users', 'users.id', '=', 'shared_profiles.shared_id')
                    ->where('shared_profiles.shared_with', Auth::id())
                    ->get();
        
        if(count($shared_employees)>0) {
            foreach ($shared_employees as $shared_employee) {
                $employees_list[$i]["id"] = $shared_employee->shared_id;
                $employees_list[$i]["name"] = $shared_employee->name;
                $i++;
            }
        }
        
        
        $type = 'upcoming';
        $disableEdit = false;
        $allowEditModal = true;
        $suggestedGoals = Goal::withoutGlobalScope(NonLibraryScope::class)->where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('is_library', 1)
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
