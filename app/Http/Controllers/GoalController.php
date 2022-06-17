<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Goal;
use App\Models\User;
use App\Models\GoalType;
use App\Models\LinkedGoal;
use App\Models\GoalComment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Scopes\NonLibraryScope;
use App\DataTables\GoalsDataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DashboardNotification;
use App\Http\Requests\Goals\CreateGoalRequest;
use App\Http\Requests\Goals\EditSuggestedGoalRequest;
use App\MicrosoftGraph\SendMail;
use App\Jobs\SendEmailJob;
use App\Models\Tag;

class GoalController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(GoalsDataTable $goalDataTable, Request $request)
    {
        $authId = Auth::id();
        $goaltypes = GoalType::all()->toArray();
        $tags = Tag::all()->toArray();
        $user = User::find($authId);
        
        $myTeamController = new MyTeamController(); 
        $employees = $myTeamController->myEmployeesAjax();

        $query = Goal::where('user_id', $authId)
        ->with('user')
        ->with('goalType');
        $type = 'past';
        
        $type_desc_arr = array();
        foreach($goaltypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {                
                $item = "<b>" . $goalType['name'] . " Goals</b> ". str_replace($goalType['name'] . " Goals","",$goalType['description']);
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>',$type_desc_arr);
        
        if ($request->is("goal/current")) {
            $goals = $query->where('status', 'active')
            ->paginate(8);
            $type = 'current';
            return view('goal.index', compact('goals', 'type', 'goaltypes', 'user','employees', 'tags', 'type_desc_str'));
        } else if ($request->is("goal/supervisor")) {
            //$user = Auth::user();
            // TO remove already copied goals.
            // $referencedGoals = Goal::where('user_id', $authId)->whereNotNull('referenced_from')->pluck('referenced_from');
            $goals = $user->sharedGoals()
            /* ->whereNotIn('goals.id', $referencedGoals ) */
            ->paginate(8);
            $type = 'supervisor';
            return view('goal.index', compact('goals', 'type', 'goaltypes', 'user', 'tags', 'type_desc_str'));
        }
        $goals = $query->where('status', '<>', 'active')
        ->paginate(4);
        
        return view('goal.index', compact('goals', 'type', 'goaltypes', 'user', 'employees', 'tags', 'type_desc_str'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        $goaltypes = GoalType::all();
        return view('goal.create', compact('goaltypes'));
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(CreateGoalRequest $request)
    {
        $input = $request->validated();
        $tags = '';
        $input['user_id'] = Auth::id();
        
        if(isset($input['tag_ids'])) {
            $tags = $input['tag_ids'];
            unset($input['tag_ids']);
        }

        $goal = Goal::create($input);
        if ($tags != '') {
            $goal->tags()->sync($tags);
        }
        return response()->json(['success' => true, 'message' => 'Goal Created successfully']);
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        // TODO: Manage Auth when we are clear with Supervisor Logic.
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->/* where('user_id', Auth::id())
        -> */where('id', $id)
        ->with('goalType')
        ->with('comments')
        ->firstOrFail();


        $linkedGoalsIds = LinkedGoal::where('user_goal_id', $id)->pluck('supervisor_goal_id');

        /* $supervisorGoals = Goal::whereIn('id', [997, 998, 999])->with('goalType')
        ->whereNotIn('id', $linkedGoalsIds)
        ->with('comments')->get(); */
        $linkedGoals
        = Goal::with('goalType', 'comments')
        ->whereIn('id', $linkedGoalsIds)
        ->get();

        $user = User::findOrFail($goal->user_id);
        if (($goal->last_supervisor_comment == 'Y') and (($goal->user_id == session()->get('original-auth-id')) or (session()->get('original-auth-id') == null))) {

            $goal->last_supervisor_comment = 'N';
            $goal->save();
        };

        $affected = DashboardNotification::wherein('notification_type', ['GC', 'GR'])
        ->where('related_id', $goal->id)
        ->wherenull('status')
        ->update(['status' => 'R']);

        return view('goal.show', compact('goal', 'linkedGoals'));
    }

    public function getSupervisorGoals($id) {
        $goal = Goal::findOrFail($id);
        $linkedGoalsIds = LinkedGoal::where('user_goal_id', $id)->pluck('supervisor_goal_id');

        $supervisorGoals = Goal::whereIn('id', [997, 998, 999])->with('goalType')
        ->whereNotIn('id', $linkedGoalsIds)
        ->with('comments')->get();

        return view('goal.partials.supervisor-goal-content', compact('goal', 'supervisorGoals'));
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)
        ->where('user_id', Auth::id())
        ->where('id', $id)
        ->with('goalType')
        ->with('tags')        
        ->firstOrFail();

        $goaltypes = GoalType::all()->toArray();
        $tags = Tag::all(["id","name", "description"])->toArray();
        
        $type_desc_arr = array();
        foreach($goaltypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {                
                $item = "<b>" . $goalType['name'] . " Goals</b> ". str_replace($goalType['name'] . " Goals","",$goalType['description']);
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>',$type_desc_arr);

        return view('goal.edit', compact("goal", "goaltypes", "type_desc_str", "tags"));
        // return redirect()->route('goal.edit', $id);
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(CreateGoalRequest $request, $id)
    {
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->findOrFail($id);
        $input = $request->validated();

        $tags = $input['tag_ids'];
        unset($input['tag_ids']);        
        $goal->update($input);
        $goal->tags()->sync($tags);

        return redirect()->route($goal->is_library ? 'my-team.suggested-goals' : 'goal.index');
    }

    public function getSuggestedGoal($id) {
        return $this->respondeWith(Goal::withoutGlobalScope(NonLibraryScope::class)->findOrFail($id));
    }

    public function updateSuggestedGoal(EditSuggestedGoalRequest $request, $id)
    {
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->findOrFail($id);
        $input = $request->validated();

        $goal->update($input);

        return redirect()->route('my-team.suggested-goals');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->find($id);
        if (!$goal) {
            abort(404);
        }
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $goal->delete();

        return redirect()->back();
    }

    public function goalBank(Request $request) {
        $tags = Tag::all()->toArray();
        $tags_input = $request->tag_ids;     

        $adminGoals = Goal::withoutGlobalScopes()
        ->join('users', 'goals.user_id', '=', 'users.id')  
        ->join('goal_bank_orgs', 'goals.id', '=', 'goal_bank_orgs.goal_id')
        ->join('admin_orgs', function($join) use ($request) {
            $join->on('admin_orgs.organization', '=', 'goal_bank_orgs.organization')
            ->on('admin_orgs.level1_program', '=', 'goal_bank_orgs.level1_program')
            ->on('admin_orgs.level2_division', '=', 'goal_bank_orgs.level2_division')
            ->on('admin_orgs.level3_branch', '=', 'goal_bank_orgs.level3_branch')
            ->on('admin_orgs.level4', '=', 'goal_bank_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('admin_orgs.version', '=', 1)
        ->leftjoin('goal_tags', 'goal_tags.goal_id', '=', 'goals.id')
        ->leftjoin('tags', 'tags.id', '=', 'goal_tags.tag_id')    
        ->leftjoin('goal_types', 'goal_types.id', '=', 'goals.goal_type_id')            
        ->select('goals.id', 'goals.title', 'goals.goal_type_id', 'goals.created_at', 'goals.user_id', 'goals.is_mandatory','goal_types.name as typename','users.name as username',DB::raw('group_concat(tags.name) as tagnames'));
        $adminGoals = $adminGoals->groupBy('goals.id', 'goals.title', 'goals.goal_type_id', 'goals.created_at', 'goals.user_id', 'users.name', 'goals.is_mandatory');
        // ->paginate(10);

        $query = Goal::withoutGlobalScope(NonLibraryScope::class)
        ->where('is_library', true)
        ->join('users', 'goals.user_id', '=', 'users.id')          
        ->leftjoin('goal_types', 'goal_types.id', '=', 'goals.goal_type_id')    
        ->leftjoin('goal_tags', 'goal_tags.goal_id', '=', 'goals.id')
        ->leftjoin('tags', 'tags.id', '=', 'goal_tags.tag_id');    
        
        if ($request->has('is_mandatory') && $request->is_mandatory !== null) {
            if ($request->is_mandatory == "1") {
                $query = $query->where('is_mandatory', $request->is_mandatory);
            }
            else {
                $query = $query->where(function ($query) {
                    $query->whereNull('is_mandatory');
                    $query->orWhere('is_mandatory', 0);
                });
            }
        }

        if ($request->has('goal_type') && $request->goal_type) {
            $query = $query->whereHas('goalType', function($query) use ($request) {
                return $query->where('goal_type_id', $request->goal_type);
            });
        }
        
        if ($request->has('tag_id') && $request->tag_id) {
            $query = $query->where('goal_tags.tag_id', "=", "$request->tag_id");
        }

        if ($request->has('title') && $request->title) {
            $query = $query->where('goal_tags.goal_id', "LIKE", "%$request->title%");
        }

        if ($request->has('date_added') && $request->date_added && Str::lower($request->date_added) !== 'any') {
            $dateRange = explode("-",$request->date_added);
            $dateRange[0] = trim($dateRange[0]);
            $dateRange[1] = trim($dateRange[1]);

            $startDate = Carbon::createFromFormat('M d, Y', $dateRange[0]);
            $endDate = Carbon::createFromFormat('M d, Y', $dateRange[1]);

            $query = $query->whereDate('created_at', '>=', $startDate);
            $query = $query->whereDate('created_at', '<=', $endDate);
        }

        if ($request->has('created_by') && $request->created_by) {
            $query = $query->where('user_id', $request->created_by);
        }

        $query->whereHas('sharedWith', function($query) {
            $query->where('user_id', Auth::id());
        });
        $query->groupBy('goals.id', 'goals.title', 'goals.goal_type_id', 'goals.created_at', 'goals.user_id', 'goals.is_mandatory');
        // $bankGoals = $query->get();
        
        // $this->getDropdownValues($mandatoryOrSuggested, $createdBy, $goalTypes, $tagsList);
        $query = $query->select('goals.id', 'goals.title', 'goals.goal_type_id', 'goals.created_at', 'goals.user_id', 'goals.is_mandatory','goal_types.name as typename','users.name as username',DB::raw('group_concat(tags.name) as tagnames'));
        $query = $query->union($adminGoals);
        // $query = $query->groupBy('goals.id');
        $bankGoals = $query->paginate(10);
        $this->getDropdownValues($mandatoryOrSuggested, $createdBy, $goalTypes, $tagsList);


        $myTeamController = new MyTeamController();
        $suggestedGoalsData = $myTeamController->showSugggestedGoals('my-team.goals.bank', false);

        // $compacted = compact('bankGoals', 'tags', 'tagsList', 'goalTypes', 'mandatoryOrSuggested', 'createdBy');
        // dd($compacted);
        // $merged = array_merge(compact('bankGoals', 'tags', 'tagsList', 'goalTypes', 'mandatoryOrSuggested', 'createdBy'), $suggestedGoalsData);
        // dd($merged);
        $type_desc_arr = array();
        foreach($goalTypes as $goalType) {
            if(isset($goalType['description']) && isset($goalType['name'])) {                
                $item = "<b>" . $goalType['name'] . " Goals</b> ". str_replace($goalType['name'] . " Goals","",$goalType['description']);
                array_push($type_desc_arr, $item);
            }
        }
        $type_desc_str = implode('<br/><br/>',$type_desc_arr);

        return view('goal.bank', array_merge(compact('bankGoals', 'tags', 'tagsList', 'goalTypes', 'type_desc_str', 'mandatoryOrSuggested', 'createdBy'), $suggestedGoalsData));
    }

    private function getDropdownValues(&$mandatoryOrSuggested, &$createdBy, &$goalTypes, &$tagsList) {
        $mandatoryOrSuggested = [
            [
                "id" => '',
                "name" => 'Any'
            ],
            [
                "id" => '1',
                "name" => 'Mandatory'
            ],
            [
                "id" => '0',
                "name" => 'Suggested'
            ]
        ];
        $createdBy = Goal::withoutGlobalScope(NonLibraryScope::class)
            ->where('is_library', true)
            ->whereHas('sharedWith', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->with('user')
            ->groupBy('user_id')
            ->get()
            ->pluck('user')
            ->toArray();

        array_unshift($createdBy , [
            "id" => "0",
            "name" => "Any"
        ]);

        $goalTypes = GoalType::all()->toArray();
        array_unshift($goalTypes, [
            "id" => "0",
            "name" => "Any"
        ]);
        
        $tagsList = Tag::all()->toArray();
        array_unshift($tagsList, [
            "id" => "0",
            "name" => "Any"
        ]);

        // dd($goalTypes);
        /* $goalTypes = [];
        foreach($goalType as $id => $type) {
            $goalTypes[] = [
                "id" => $id,
                "name" => $type
            ];
        } */
    }

    public function library(Request $request)
    {
        $query = Goal::whereIn('id', [997, 998, 999]);
        $expanded = false;
        $currentSearch = "";
        if($request->has('search') && $request->search != '') {
            // $searchText = explode(' ', $request->search);
            $searchText = $request->search;
            $query->Where(function ($qq) use ($searchText) {
                foreach ($searchText as $search) {
                    $qq->orWhere(function ($q) use ($search) {
                        $q->orWhere('title', 'LIKE', '%' . $search . '%');
                        $q->orWhere('what', 'LIKE', '%' . $search . '%');
                        /* $q->orWhere('why', 'LIKE', '%' . $search . '%');
                        $q->orWhere('how', 'LIKE', '%' . $search . '%'); */
                        $q->orWhere('measure_of_success', 'LIKE', '%' . $search . '%');
                    });
                }
            });

            $expanded = true;
            $currentSearch = implode(' ',$request->search);
        }
        $sQuery = clone $query;

        /* $supervisorGoals = $sQuery->whereIn('id', [998])->with('goalType')
        ->with('comments')->get(); */
        $organizationGoals = $query->whereIn('id', [997, 999])->with('goalType')
        ->with('comments')->get();

        $user = Auth::user();
        // $sQuery = $user->sharedGoals()->withoutGlobalScope(NonLibraryScope::class);
        $sQuery = Goal::withoutGlobalScope(NonLibraryScope::class)->where('user_id', $user->reportingManager->id);

        // TODO: For User Experience
        // $sQuery = Goal::where('id', 998);
        // TODO: remove duplicate if once we resolve organizational goals
        if ($request->has('search') && $request->search != '') {
            // $searchText = explode(' ', $request->search);
            $searchText = $request->search;
            $sQuery->Where(function ($qq) use ($searchText) {
                foreach ($searchText as $search) {
                    $qq->orWhere(function ($q) use ($search) {
                        $q->orWhere('title', 'LIKE', '%' . $search . '%');
                        $q->orWhere('what', 'LIKE', '%' . $search . '%');
                        /* $q->orWhere('why', 'LIKE', '%' . $search . '%');
                        $q->orWhere('how', 'LIKE', '%' . $search . '%'); */
                        $q->orWhere('measure_of_success', 'LIKE', '%' . $search . '%');
                    });
                }
            });

            $expanded = true;
            $currentSearch = implode(' ', $request->search);
        };
        // TODO: For UserExperience Test
        // $supervisorGoals = $sQuery->where('is_library', 1)->with('goalType')
        $supervisorGoals = $sQuery->with('goalType')
        ->with('comments')->get();
        return view('goal.library', compact('organizationGoals', 'supervisorGoals', 'currentSearch', 'expanded'));
    }

    public function showForLibrary(Request $request, $id) {
        if ($request->has("add") && $request->add) {
            $showAddBtn = true;
        } else {
            $showAddBtn = false;
        }
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->find($id);
        return view('goal.partials.show', compact('goal', 'showAddBtn'));
    }

    private function copyFromLibrary(Goal $goal) {
        $newGoal = new Goal;
        $newGoal->title = $goal->title;
        // $newGoal->why = $goal->why;
        $newGoal->what = $goal->what;
        // $newGoal->how = $goal->how;
        $newGoal->measure_of_success = $goal->measure_of_success;
        $newGoal->start_date = $goal->start_date;
        $newGoal->target_date = $goal->target_date;
        $newGoal->status = $goal->status;
        $newGoal->goal_type_id = $goal->goal_type_id;
        $newGoal->user_id = Auth::id();
        $newGoal->created_by = $goal->user_id;
        $newGoal->save();
        return $newGoal;
    }

    public function saveFromLibraryMultiple(Request $request) {
        foreach ($request->goal_ids as $goal_id) {
            $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->find($goal_id);
            $newGoal = $this->copyFromLibrary($goal);
        }
        return redirect()->route('goal.current');
    }

    public function saveFromLibrary(Request $request)
    {
        $goal = Goal::withoutGlobalScope(NonLibraryScope::class)->find($request->selected_goal);
        $newGoal = $this->copyFromLibrary($goal);
        return response()->json(['success' => true, 'data' => $newGoal, 'message' => 'Goal Added Successfully']);
    }

    public function addComment(Request $request, $id)
    {
        if ($request->comment != null and $request->comment != '') {
            $goal = Goal::findOrFail($id);
            $comment = new GoalComment;

            $comment->goal_id = $goal->id;
            $comment->user_id = Auth::id();
            $comment->parent_id = $request->parent_id ?? null;

            if (session()->get('original-auth-id') != null) {
                $comment->user_id = session()->get('original-auth-id');
            }
            else {
                $comment->user_id = Auth::id();
            }

            $comment->comment = $request->comment;

            $comment->save();

            $user = User::findOrFail($goal->user_id);
            $curr_user = User::findOrFail(Auth::id());

            if (($goal->last_supervisor_comment != 'Y') and (session()->get('original-auth-id') != null) and ($user->reporting_to == session()->get('original-auth-id'))) {
                //update flag
                $goal->last_supervisor_comment = 'Y';
                $goal->save();
            }

            if ($request->parent_id != null) {
                $original_comment = GoalComment::withTrashed()->findOrFail($request->parent_id);
                if (($original_comment->user_id != Auth::id()) and ($goal->user_id != Auth::id())) {
                    //user replying to somebody else's comment
                    $newNotify = new DashboardNotification;
                    $newNotify->user_id = Auth::id();
                    $newNotify->notification_type = 'GR';
                    $newNotify->comment = $user->name . ' replied to your Goal comment.';
                    $newNotify->related_id = $goal->id;
                    $newNotify->save();
                }
            }
            else {
                if ((session()->get('original-auth-id') != null) and ($user->reporting_to == session()->get('original-auth-id'))) {
                    //add dashboard notification
                    $newNotify = new DashboardNotification;
                    $newNotify->user_id = Auth::id();
                    $newNotify->notification_type = 'GC';
                    $newNotify->comment = $comment->user->name . ' added a comment to your goal.';
                    $newNotify->related_id = $goal->id;
                    $newNotify->save();
                    //send email notification to employee
                    // $sendMail = new SendMail();
                    // $response = $sendMail->send(['email here'],   "Supervisor Added Goal Comment",
                    //      "Your Supervisor have added comment to your goal.");
                }
            }

            if (($curr_user->reporting_to == $goal->user_id) and ($goal->user_id != Auth::id())) {
                //add notification in Supervisor's Dashboard
                $newNotify = new DashboardNotification;
                $newNotify->user_id = $curr_user->reporting_to;
                $newNotify->notification_type = 'GC';
                $newNotify->comment = $curr_user->name . ' added a comment to your goal.';
                $newNotify->related_id = $goal->id;
                $newNotify->save();
            }

            // notify the employee when my supervisor reply my comment
            if (($curr_user->reporting_to == $goal->user_id) and ($goal->user_id != Auth::id())) {
                // Real-Time
                $sendMail = new SendMail();
                $sendMail->toRecipients = array($goal->user->id);  
                $sendMail->sender_id = $curr_user->id;
                $sendMail->template = 'SUPERVISOR_COMMENT_MY_GOAL';
                array_push($sendMail->bindvariables, $goal->user->name);
                array_push($sendMail->bindvariables, $goal->what);
                array_push($sendMail->bindvariables, $comment->comment);
                $response = $sendMail->sendMailWithGenericTemplate();
            }
        }
        return redirect()->back();
    }

    public function updateStatus($id, $status)
    {
        $goal = Goal::findOrFail($id);
        $goal->status = $status;

        $goal->save();
        return redirect()->back();
    }

    public function linkGoal(Request $request)
    {
        $linkedGoalIds = $request->linked_goal_id;
        if ($request->linked_goal_id) {

            $linkedGoalIds = explode(',', $linkedGoalIds);
            foreach ($linkedGoalIds as $key => $value) {
                LinkedGoal::updateOrCreate([
                    'user_goal_id' => $request->current_goal_id,
                    'supervisor_goal_id' => $value,
                ]);
            }
        }

        return redirect()->back();
    }

    public function copyGoal(Request $request, $id) {
        $goal = Goal::findOrFail($id);
        $userId = Auth::Id();

        // TODO: For UserExperience Test
        /* if (!$goal->sharedWith()->where('users.id', $userId)->exists()) {
        abort(403, __('You do not have access to the resource'));
    } */

        $newGoal = $goal->replicate();
        $newGoal->user_id = $userId;
        $newGoal->created_by = $goal->user_id;
        $newGoal->is_shared = 0;
        $newGoal->referenced_from = $goal->id;
        $newGoal->save();

        return redirect()->route('goal.current');
    }
}
