<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Participant;
use App\Models\Conversation;
use App\Models\EmployeeDemo;
use Illuminate\Http\Request;
use App\Models\EmployeeShare;
use App\Models\SharedProfile;
use App\Models\ConversationTopic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\DashboardNotification;
use Illuminate\Support\Facades\Route;
use App\Models\ConversationParticipant;
use App\Http\Requests\Conversation\UpdateRequest;
use App\Http\Requests\Conversation\SignoffRequest;
use App\Http\Requests\Conversation\UnSignoffRequest;
use App\Http\Requests\Conversation\ConversationRequest;
use Illuminate\Support\Facades\Log;
use App\Models\EmployeeSupervisor;
use App\Models\EmployeeManager;
use App\Models\PreferredSupervisor;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $viewType = 'conversations')
    {
        $authId = Auth::id();
        $user = User::find($authId);
        $supervisor = $user->reportingManager()->first();
        $supervisorId = (!$supervisor) ? null : $supervisor->id;
        $conversationMessage = Conversation::warningMessage();
        $conversationTopics = ConversationTopic::all();
        // $participants = Participant::all();
        $query = Conversation::with('conversationParticipants');
        
        //get historic conversations
        $history_conversations = DB::table('conversation_participants')  
                                    ->select('conversation_id')
                                    ->join('conversations', 'conversation_participants.conversation_id', '=', 'conversations.id')
                                    ->where('conversation_participants.participant_id', '=', $authId)
                                    ->whereNull('conversations.deleted_at')
                                    ->distinct()
                                    ->get();
        $consersation_ids = array();
        foreach($history_conversations as $history_conversation) {
            $consersation_ids[] = $history_conversation->conversation_id;
        }
        //get historic supervisors
        $history_supervisors = DB::table('conversation_participants')
                                    ->select('conversation_participants.participant_id', 'users.name')
                                    ->join('users','users.id','=','conversation_participants.participant_id')
                                    ->whereIn('conversation_id', $consersation_ids)
                                    ->where('role', '=', 'mgr')
                                    ->where('participant_id', '<>', $authId)
                                    ->distinct()
                                    ->get();
        
        //get reportee and shared with listing
        $reporteeIds = $user->avaliableReportees()
            ->pluck('id'); // Plucking the explicitly selected id from users

        $sharedIds = SharedProfile::where('shared_with', $user->id)
            ->select('shared_id AS id')
            ->pluck('id');

        $reporteeNshared = $reporteeIds->union($sharedIds)->toArray();
        

        //get historic team members
        $history_teammembers = DB::table('conversation_participants')
                                    ->select('conversation_participants.participant_id', 'users.name')
                                    ->join('users','users.id','=','conversation_participants.participant_id')
                                    ->whereIn('conversation_id', $consersation_ids)
                                    ->where('role', '=', 'emp')
                                    ->where('participant_id', '<>', $authId)
                                    ->distinct()
                                    ->get();            
        
        $supervisor_ids = array();
        foreach($history_supervisors as $history_supervisor){
            $supervisor_ids[] = $history_supervisor->participant_id;
        }
                        
        
        $myCurrentTeam_array = array();
        foreach($history_teammembers as $item){
            $myCurrentTeam_array[] = $item->participant_id;
        }
        if(count($myCurrentTeam_array) > 0) {
            $myCurrentTeam_list = implode( ',', $myCurrentTeam_array );
        } else {
            $myCurrentTeam_list = "''";
        }
                
        $type = 'upcoming';
        $sub = false;
        
        if ($request->is('conversation/past') || $request->is('my-team/conversations/past')) {
            $sharedSupervisorIds = SharedProfile::where('shared_id', Auth::id())->with('sharedWithUser')->get()->pluck('shared_with')->toArray();
            array_push($sharedSupervisorIds, $supervisorId);
            foreach ($supervisor_ids as $supervisor_id) {
                if (!in_array($supervisor_id, $sharedSupervisorIds)) {
                    array_push($sharedSupervisorIds, $supervisor_id);
                }
            }
            
            $query->where(function($query) use ($authId, $supervisorId, $sharedSupervisorIds, $viewType) {
                $query->where('user_id', $authId)->
                    orWhereHas('conversationParticipants', function($query) use ($authId, $supervisorId, $sharedSupervisorIds, $viewType) {
                        $query->where('participant_id', $authId);
                        if ($viewType === 'my-team') {
                            $query->where('participant_id', '<>', $supervisorId);
                        } 
                        return $query;
                    });
            })->whereNotNull('signoff_user_id')->whereNotNull('supervisor_signoff_id')
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNotNull('signoff_user_id')
                          ->whereNotNull('supervisor_signoff_id');                          
                        //   ->whereNull('unlock_until');
                });
                // ->orWhere(function($query) {
                //     $query->whereNotNull('signoff_user_id')
                //           ->whereNotNull('supervisor_signoff_id')
                //           ->whereDate('unlock_until', '<', Carbon::today() );
                // });
            });   
            
            $myTeamQuery = clone $query;

            if ($request->has('sup_conversation_topic_id') && $request->sup_conversation_topic_id) {
                $query->where('conversation_topic_id', $request->sup_conversation_topic_id);
            }
            
            if ($request->has('supervisors') && $request->supervisors) {
                $query->where('supervisor_signoff_id', $request->supervisors);
            }
            if ($request->has('sup_signoff_date') && $request->sup_signoff_date) {
                $query->whereRaw("GREATEST(sign_off_time, supervisor_signoff_time)>= '$request->sup_signoff_date"." 00:00:00' and
                                GREATEST(sign_off_time, supervisor_signoff_time)<= '$request->sup_signoff_date"." 23:59:59'
                                ");
            }
            
            if ($request->has('team_members') && $request->team_members) {
                $user_name = $request->team_members;
                $myTeamQuery->where(function ($myTeamQuery) use ($user_name) {
                    $myTeamQuery->whereHas('user', function ($myTeamQuery) use ($user_name) {
                        $myTeamQuery->where('id', '=', $user_name);
                    })
                    ->orWhereHas('conversationParticipants', function($myTeamQuery) use ($user_name) {
                        $myTeamQuery->whereHas('participant', function($myTeamQuery) use ($user_name) {
                            $myTeamQuery->where('participant_id', '=', $user_name);
                        });
                    });
                });
            } 
            if ($request->has('signoff_date') && $request->signoff_date) {
                $myTeamQuery->whereRaw("GREATEST(sign_off_time, supervisor_signoff_time)>= '$request->signoff_date"." 00:00:00' and
                                GREATEST(sign_off_time, supervisor_signoff_time)<= '$request->signoff_date"." 23:59:59'
                                ");
            }
            if ($request->has('conversation_topic_id') && $request->conversation_topic_id) {
                $myTeamQuery->where('conversation_topic_id', $request->conversation_topic_id);
            }

            // With my Supervisor            
            $query->where(function($query) use ($sharedSupervisorIds) {
                $query->whereIn('user_id', $sharedSupervisorIds)->
                orWhereHas('conversationParticipants', function ($query) use ($sharedSupervisorIds) {
                    $query->whereIn('participant_id', $sharedSupervisorIds);
                });
            })
            ->WhereIn('supervisor_signoff_id', $sharedSupervisorIds);
            
             // With My Team
             if ($sharedSupervisorIds && $sharedSupervisorIds[0]) {
                $myTeamQuery->where(function($query) use ($sharedSupervisorIds, $myCurrentTeam_array, $reporteeNshared) {
                    $query->where(function($shared) use ($sharedSupervisorIds){
                        return $shared->whereNotIn('user_id', $sharedSupervisorIds)
                        ->orWhere(function($part) {
                            return $part->whereHas('conversationParticipants', function($mgr) {
                                return $mgr->where('participant_id', \DB::raw(Auth::id()))
                                ->whereRaw("role = 'mgr'");
                            });
                        });
                    })
                    ->whereHas('conversationParticipants', function ($query) use ($myCurrentTeam_array ) {
                        $query->whereIn('participant_id', $myCurrentTeam_array);
                    })
                    ->whereHas('conversationParticipants', function ($query) use ($reporteeNshared ) {
                        $query->whereIn('participant_id', $reporteeNshared);
                    });
                });
            }
            $myTeamQuery->where('signoff_user_id','<>', $authId);          
            $team_sql_test = $myTeamQuery->toSql();
            $team_sql_par = $myTeamQuery->getBindings();
            $type = 'past';
            $conversations = $query->orderBy('id', 'DESC')->get(); 
            $myTeamConversations = $myTeamQuery->orderBy('id', 'DESC')->get();
        } else { // Upcoming
            //conversations with my supervisor
            $sup_query = "SELECT conversations.id, conversations.signoff_user_id, conversations.supervisor_signoff_id, conversations.sign_off_time, conversations.supervisor_signoff_time, conversations.unlock_until
            , conversation_topics.name, empusers.name as empname, mgrusers.name as mgrname, conversations.created_at, conversations.updated_at 
                                    FROM conversations 
                                    INNER JOIN conversation_topics  ON conversations.conversation_topic_id = conversation_topics.id
                                    LEFT JOIN conversation_participants emp_participants ON conversations.id = emp_participants.conversation_id AND emp_participants.role = 'emp'
                                    LEFT JOIN conversation_participants mgr_participants ON conversations.id = mgr_participants.conversation_id AND mgr_participants.role = 'mgr'
                                    INNER JOIN users empusers ON empusers.id = emp_participants.participant_id
                                    INNER JOIN users mgrusers ON mgrusers.id = mgr_participants.participant_id
                                    WHERE (emp_participants.participant_id = $authId)
                                    AND `conversations`.`deleted_at` is null
                                    AND 
                                    ((`signoff_user_id` is null or `supervisor_signoff_id` is null))";
            
            if ($request->has('created_at') && $request->created_at) {
                $sup_query .= "  AND (created_at BETWEEN '".$request->created_at." 00:00:00' AND '".$request->created_at." 23:59:59')";   
            } 
            if ($request->has('updated_at') && $request->updated_at) {
                $sup_query .= "  AND (updated_at BETWEEN '".$request->updated_at." 00:00:00' AND '".$request->updated_at." 23:59:59')";   
            } 
            if ($request->has('sup_conversation_topic_id') && $request->sup_conversation_topic_id) {
                $sup_query .= " AND conversations.conversation_topic_id = $request->sup_conversation_topic_id"; 
            }
            if ($request->has('user_name') && $request->user_name) {
                $sup_query .= " AND (empusers.name LIKE '%$request->user_name%' OR mgrusers.name LIKE '%$request->user_name%')"; 
            }
            if ($request->has('supervisors') && $request->supervisors) {
                $sup_query .= " AND (mgr_participants.participant_id = '".$request->supervisors."')";
            }            
            if (!$request->has('sup_employee_signed') && !$request->has('sup_supervisor_signed')) {
                $sup_query .= "  AND (conversations.signoff_user_id IS NULL OR conversations.supervisor_signoff_id IS NULL)";             
            }else{
                if($request->sup_employee_signed == 'any' && $request->sup_supervisor_signed == 'any'){
                    $sup_query .= "  AND (conversations.signoff_user_id IS NULL OR conversations.supervisor_signoff_id IS NULL)";
                } else {
                    if($request->sup_employee_signed == 'any'){
                        if($request->sup_supervisor_signed == 0){
                            $sup_query .= "  AND (conversations.supervisor_signoff_id IS NULL)";   
                        }else{
                            $sup_query .= "  AND (conversations.signoff_user_id IS NULL AND conversations.supervisor_signoff_id IS NOT NULL)";  
                        }                        
                    }
                    if($request->sup_supervisor_signed == 'any'){
                        if($request->sup_employee_signed == 0){
                            $sup_query .= "  AND (conversations.signoff_user_id IS NULL)";   
                        }else{
                            $sup_query .= "  AND (conversations.supervisor_signoff_id IS NULL AND conversations.signoff_user_id IS NOT NULL)";  
                        }                        
                    }
                }   
                if ($request->sup_employee_signed == 0 && $request->sup_supervisor_signed == 0){
                    $sup_query .= "  AND (conversations.signoff_user_id IS NULL AND conversations.supervisor_signoff_id IS NULL)";   
                }
                if ($request->sup_employee_signed == 0 && $request->sup_supervisor_signed == 1){
                    $sup_query .= "  AND (conversations.signoff_user_id IS NULL AND conversations.supervisor_signoff_id IS NOT NULL)";   
                } 
                if ($request->sup_employee_signed == 1 && $request->sup_supervisor_signed == 0){
                    $sup_query .= "  AND (conversations.signoff_user_id IS NOT NULL AND conversations.supervisor_signoff_id IS NULL)";   
                }
                if ($request->sup_employee_signed == 1 && $request->sup_supervisor_signed == 1){
                    $sup_query .= "  AND (1 = 0)";   
                }  
            }   
            
            $sup_query .= " ORDER BY conversations.id DESC";
            $conversations = DB::select($sup_query);
                        
            //conversations with my team            
            $emp_query = "SELECT conversations.id, conversations.signoff_user_id, conversations.supervisor_signoff_id, conversations.sign_off_time, conversations.supervisor_signoff_time,conversations.unlock_until
            , conversation_topics.name, empusers.name as empname, mgrusers.name as mgrname, conversations.created_at, conversations.updated_at 
                                    FROM conversations 
                                    INNER JOIN conversation_topics  ON conversations.conversation_topic_id = conversation_topics.id
                                    LEFT JOIN conversation_participants emp_participants ON conversations.id = emp_participants.conversation_id AND emp_participants.role = 'emp'
                                    LEFT JOIN conversation_participants mgr_participants ON conversations.id = mgr_participants.conversation_id AND mgr_participants.role = 'mgr'
                                    INNER JOIN users empusers ON empusers.id = emp_participants.participant_id AND emp_participants.participant_id IN ($myCurrentTeam_list)
                                    INNER JOIN users mgrusers ON mgrusers.id = mgr_participants.participant_id
                                    WHERE (mgr_participants.participant_id = $authId)
                                    AND `conversations`.`deleted_at` is null";
            if ($request->has('conversation_topic_id') && $request->conversation_topic_id) {
                $emp_query .= " AND conversations.conversation_topic_id = $request->conversation_topic_id"; 
            }
            if ($request->has('user_name') && $request->user_name) {
                $emp_query .= " AND (empusers.name LIKE '%$request->user_name%' OR mgrusers.name LIKE '%$request->user_name%')"; 
            }
            if ($request->has('team_members') && $request->team_members) {
                $emp_query .= " AND emp_participants.participant_id = $request->team_members"; 
            }
            if (!$request->has('employee_signed') && !$request->has('supervisor_signed')) {
                $emp_query .= "  AND (conversations.signoff_user_id IS NULL OR conversations.supervisor_signoff_id IS NULL)";             
            }else{
                if($request->employee_signed == 'any' && $request->supervisor_signed == 'any'){
                    $emp_query .= "  AND (conversations.signoff_user_id IS NULL OR conversations.supervisor_signoff_id IS NULL)";
                } else {
                    if($request->employee_signed == 'any'){
                        if($request->supervisor_signed == 0){
                            $emp_query .= "  AND (conversations.supervisor_signoff_id IS NULL)";   
                        }else{
                            $emp_query .= "  AND (conversations.signoff_user_id IS NULL AND conversations.supervisor_signoff_id IS NOT NULL)";  
                        }                        
                    }
                    if($request->supervisor_signed == 'any'){
                        if($request->employee_signed == 0){
                            $emp_query .= "  AND (conversations.signoff_user_id IS NULL)";   
                        }else{
                            $emp_query .= "  AND (conversations.supervisor_signoff_id IS NULL AND conversations.signoff_user_id IS NOT NULL)";  
                        }                        
                    }
                }   
                if ($request->employee_signed == 0 && $request->supervisor_signed == 0){
                    $emp_query .= "  AND (conversations.signoff_user_id IS NULL AND conversations.supervisor_signoff_id IS NULL)";   
                }
                if ($request->employee_signed == 0 && $request->supervisor_signed == 1){
                    $emp_query .= "  AND (conversations.signoff_user_id IS NULL AND conversations.supervisor_signoff_id IS NOT NULL)";   
                } 
                if ($request->employee_signed == 1 && $request->supervisor_signed == 0){
                    $emp_query .= "  AND (conversations.signoff_user_id IS NOT NULL AND conversations.supervisor_signoff_id IS NULL)";   
                }
                if ($request->employee_signed == 1 && $request->supervisor_signed == 1){
                    $emp_query .= "  AND (1 = 0)";   
                }  
            }            
            $emp_query .= " ORDER BY conversations.id DESC";
            $myTeamConversations = DB::select($emp_query);
            
            if ($request->has('sub') && $request->sub) {
                $sub = true;
            }
        }
        
        $supervisor_conversations = array();
        foreach ($conversations as $conversation) {
            array_push($supervisor_conversations, $conversation->id);
        }
        
        $view = 'conversation.upcoming';
        if($type == 'past'){        
            $view = 'conversation.past';
        }
        $reportees = $user->reportees()->get();
        $topics = ConversationTopic::all();
        if ($type === 'past') {
            $textAboveFilter = 'The list below contains all conversations that have been signed by both employee and supervisor. There is a two week period from the date of sign-off when either participant can un-sign the conversation and return it to the Open Conversations tab for further edits. Conversations marked with a locked icon have passed the two-week time period and require approval and assistance to re-open. If you need to unlock a conversation, submit an AskMyHR service request to Myself > HR Software Systems Support > Performance Development Platform.';            
        } else {            
            $textAboveFilter = 'The list below contains all planned conversations that have yet to be signed-off by both employee and supervisor. Once a conversation has been signed-off by both participants, it will move to the Completed Conversations tab and become an official performance development record for the employee.';
        }
          
        // redirect from DashboardController with the related id, then open modal box
        $open_modal_id = (session('open_modal_id'));
        
        $conversationList = ConversationTopic::all()->sortBy("name")->toArray();
        array_unshift($conversationList, [
            "id" => "0",
            "name" => "Any"
        ]);
        
        //prepare participants list
        $team_members = array();
        $team_members[0]["id"] = '';
        $team_members[0]["name"] = 'Any';
        $i = 1;
        foreach($history_teammembers as $item){
            $team_members[$i]["id"] = $item->participant_id;
            $team_members[$i]["name"] = $item->name;
            $i++;
        }
        
        $supervisor_members = array();
        $supervisor_members[0]["id"] = '';
        $supervisor_members[0]["name"] = 'Any';
        $i = 1;
        foreach($history_supervisors as $item){
            $supervisor_members[$i]["id"] = $item->participant_id;
            $supervisor_members[$i]["name"] = $item->name;
            $i++;
        }
        
        $myTeamConversations_arr = array();
        $i = 0;
        foreach($myTeamConversations as $item){
            $myTeamConversations_arr[$i]['id'] = $item->id;
            if(isset($item->name)){
                $myTeamConversations_arr[$i]['name'] = $item->name;
            }else{
                $myTeamConversations_arr[$i]['name'] = $item->topic->name;
            }
            if(isset($item->mgrname) && isset($item->empname)){
                $myTeamConversations_arr[$i]['participants'] = $item->mgrname . ", " . $item->empname;
            } else {
                if($item->conversationParticipants[1]->participant->id == $authId){                    
                    $myTeamConversations_arr[$i]['participants'] = $item->conversationParticipants[1]->participant->name . ", " . $item->conversationParticipants[0]->participant->name;
                }else{
                    $myTeamConversations_arr[$i]['participants'] = $item->conversationParticipants[0]->participant->name . ", " . $item->conversationParticipants[1]->participant->name;
                }                
            }            
            $myTeamConversations_arr[$i]['signoff_user_id'] = $item->signoff_user_id;
            if($item->signoff_user_id != ''){
                $myTeamConversations_arr[$i]['employee_signed'] = 'Yes';
            } else {
                $myTeamConversations_arr[$i]['employee_signed'] = 'No';
            }
            $myTeamConversations_arr[$i]['supervisor_signoff_id'] = $item->supervisor_signoff_id;
            if($item->supervisor_signoff_id != ''){
                $myTeamConversations_arr[$i]['supervisor_signed'] = 'Yes';
            } else {
                $myTeamConversations_arr[$i]['supervisor_signed'] = 'No';
            }
            if(isset($item->is_locked)){
                if($item->is_locked){
                    $myTeamConversations_arr[$i]['is_locked'] = 'Locked';
                    $myTeamConversations_arr[$i]['status'] = '<div tabindex="0"><i class="fa fa-lock" aria-label="Locked Conversation"></i></div>';                    
                } else {
                    $myTeamConversations_arr[$i]['is_locked'] = 'Unlocked';
                    $myTeamConversations_arr[$i]['status'] = '<div tabindex="0"><i class="fa fa-unlock" aria-label="Unlocked Conversation"></i></div>';     
                }
            }
            $last_sign_off_date = '';
            if ($item->sign_off_time != '' && $item->supervisor_signoff_time != '' ){
                $date1 = Carbon::parse($item->sign_off_time);
                $date2 = Carbon::parse($item->supervisor_signoff_time);
                $sign_datetime = $date1->max($date2);
                $sign_datetime = $sign_datetime->format('Y-m-d H:i:s');
                $myTeamConversations_arr[$i]['sign_date'] = $sign_datetime;
            } else {
                if ($item->sign_off_time != '') {
                    $sign_datetime = $item->sign_off_time;
                    $myTeamConversations_arr[$i]['sign_date'] = $sign_datetime;
                } elseif($item->supervisor_signoff_time != '') {
                    $sign_datetime = $item->supervisor_signoff_time;
                    $myTeamConversations_arr[$i]['sign_date'] = $sign_datetime;
                } else {
                    $myTeamConversations_arr[$i]['sign_date'] = '';
                }
            }
            if(isset($item->updated_at)){
                $update_datetime = Carbon::parse($item->updated_at);
                $update_date = $update_datetime->toDateString();
                $myTeamConversations_arr[$i]['update_date'] = $update_date;
            } 
            if(isset($item->created_at)){
                $create_datetime = Carbon::parse($item->created_at);
                $create_date = $create_datetime->toDateString();
                $myTeamConversations_arr[$i]['create_date'] = $create_date;
            }                      
           
            $i++;
        }
        
        if($request->has('status')){
            if($request->status == 'locked'){
                $myTeamConversations_arr = array_filter($myTeamConversations_arr, function($item) {
                    return $item['is_locked'] == "Locked";
                });
            } elseif($request->status == 'unlocked'){
                $myTeamConversations_arr = array_filter($myTeamConversations_arr, function($item) {
                    return $item['is_locked'] === "Unlocked";
                });
            }         
        }
        
        $json_myTeamConversations = json_encode($myTeamConversations_arr);   

        $conversations_arr = array();
        $i = 0;


        foreach($conversations as $item){
            $conversations_arr[$i]['id'] = $item->id;
            //$conversations_arr[$i]['conversation_topic_id'] = $item->conversation_topic_id;            
            if(isset($item->name)){
                $conversations_arr[$i]['name'] = $item->name;
            }else{
                $conversations_arr[$i]['name'] = $item->topic->name;
            }
            if(isset($item->mgrname) && isset($item->empname)){
                $conversations_arr[$i]['participants'] = $item->mgrname . ", " . $item->empname;
            } else {
                if($item->conversationParticipants[0]->participant->id == $authId){                    
                    $conversations_arr[$i]['participants'] = $item->conversationParticipants[1]->participant->name . ", " . $item->conversationParticipants[0]->participant->name;
                }else{
                    $conversations_arr[$i]['participants'] = $item->conversationParticipants[0]->participant->name . ", " . $item->conversationParticipants[1]->participant->name;
                }
            }
            $conversations_arr[$i]['signoff_user_id'] = $item->signoff_user_id;
            if($item->signoff_user_id != ''){
                $conversations_arr[$i]['employee_signed'] = 'Yes';
            } else {
                $conversations_arr[$i]['employee_signed'] = 'No';
            }
            $conversations_arr[$i]['supervisor_signoff_id'] = $item->supervisor_signoff_id;
            if($item->supervisor_signoff_id != ''){
                $conversations_arr[$i]['supervisor_signed'] = 'Yes';
            } else {
                $conversations_arr[$i]['supervisor_signed'] = 'No';
            }
            if(isset($item->is_locked)){
                if($item->is_locked){
                    $conversations_arr[$i]['is_locked'] = 'Locked';
                    $conversations_arr[$i]['status'] = '<div tabindex="0"><i class="fa fa-lock" aria-label="Locked Conversation"></i></div>';      
                } else {
                    $conversations_arr[$i]['is_locked'] = 'Unlocked';
                    $conversations_arr[$i]['status'] = '<div tabindex="0"><i class="fa fa-unlock" aria-label="Unlocked Conversation"></i></div>';      
                }
            }
            $last_sign_off_date = '';
            if ($item->sign_off_time != '' && $item->supervisor_signoff_time != '' ){
                $date1 = Carbon::parse($item->sign_off_time);
                $date2 = Carbon::parse($item->supervisor_signoff_time);
                $sign_datetime = $date1->max($date2);
                $sign_datetime = $sign_datetime->format('Y-m-d H:i:s');
                $conversations_arr[$i]['sign_date'] = $sign_datetime;
            } else {
                if ($item->sign_off_time != '') {
                    $sign_datetime = $item->sign_off_time;
                    $conversations_arr[$i]['sign_date'] = $sign_datetime;
                } elseif($item->supervisor_signoff_time != '') {
                    $sign_datetime = $item->supervisor_signoff_time;
                    $conversations_arr[$i]['sign_date'] = $sign_datetime;
                } else {
                    $conversations_arr[$i]['sign_date'] = '';
                }
            }
            if(isset($item->updated_at)){
                $update_datetime = Carbon::parse($item->updated_at);
                $update_date = $update_datetime->toDateString();
                $conversations_arr[$i]['update_date'] = $update_date;
            } 
            if(isset($item->created_at)){
                $create_datetime = Carbon::parse($item->created_at);
                $create_date = $create_datetime->toDateString();
                $conversations_arr[$i]['create_date'] = $create_date;
            } 
            $i++;
        }
        
        if($request->has('sup_status')){
            if($request->sup_status == 'locked'){
                $conversations_arr = array_filter($conversations_arr, function($item) {
                    return $item['is_locked'] == "Locked";
                });
            } elseif($request->sup_status == 'unlocked'){
                $conversations_arr = array_filter($conversations_arr, function($item) {
                    return $item['is_locked'] === "Unlocked";
                });
            }         
        }
        $json_conversations = json_encode($conversations_arr);  
        

        if($request->id){
            $open_modal_id = $request->id;
        }
        $owner_role = '';
        if($request->ownerrole){
            $owner_role = $request->ownerrole;
        }

        return view($view, compact('type', 'conversations', 'myTeamConversations', 'conversationTopics', 'conversationMessage', 'viewType', 'reportees', 'topics', 'textAboveFilter', 'user',
                                    'supervisor_conversations', 'open_modal_id', 'owner_role', 'conversationList','team_members', 'supervisor_members', 'sub', 'json_myTeamConversations', 'json_conversations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ConversationRequest $request)
    {
        //DB::beginTransaction();
        $authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
        $isDirectRequest = true;
        if(route('my-team.my-employee') == url()->previous()) {
            $isDirectRequest = false;
        }

        $actualOwner = $isDirectRequest ? $authId : $request->owner_id ?? $authId;
        $actualOwnerRole = 'emp';

        $conversation = new Conversation();
        $conversation->conversation_topic_id = $request->conversation_topic_id;
        // $conversation->comment = $request->comment ?? '';
        $conversation->user_id = $actualOwner;
        $conversation->date = $request->date;
        $conversation->time = $request->time;
        $conversation->save();
        if(is_array($request->participant_id)){
            foreach ($request->participant_id as $key => $value) {
                $is_direct = false;
                $switch = false;
                // $mgrinfo_0 = DB::table('users')                        
                //                         ->select('reporting_to')
                //                         ->where('id', '=', $value)
                //                         ->get();
                // if($mgrinfo_0[0]->reporting_to == $actualOwner){
                if($this->getReportingTo($value) == $actualOwner){
                    ConversationParticipant::updateOrCreate([
                        'conversation_id' => $conversation->id,
                        'participant_id' => $value,
                        'role' => 'emp',
                        'position_number' => $this->getPositionNumber($value),
                    ]);

                    ConversationParticipant::updateOrCreate([
                        'conversation_id' => $conversation->id,
                        'participant_id' => $actualOwner,
                        'role' => 'mgr',
                    ]);
                    $actualOwnerRole = 'mgr';
                    $is_direct = true;
                    $switch = true;
                } 

                // $mgrinfo_1 = DB::table('users')                        
                //                         ->select('reporting_to')
                //                         ->where('id', '=', $actualOwner)
                //                         ->get();
                // if($mgrinfo_1[0]->reporting_to == $value){
                if($this->getReportingTo($actualOwner) == $value){
                    ConversationParticipant::updateOrCreate([
                        'conversation_id' => $conversation->id,
                        'participant_id' => $actualOwner,
                        'role' => 'emp',
                        'position_number' => $this->getPositionNumber($actualOwner),
                    ]);

                    ConversationParticipant::updateOrCreate([
                        'conversation_id' => $conversation->id,
                        'participant_id' => $value,
                        'role' => 'mgr',
                    ]);
                    $is_direct = true;
                    $switch = true;
                }

                if(!$switch){
                    $mgrinfo_0 = DB::table('users')                        
                                     ->select('reporting_to')
                                     ->where('id', '=', $value)
                                     ->get();
                    if($mgrinfo_0[0]->reporting_to == $actualOwner){
                        ConversationParticipant::updateOrCreate([
                            'conversation_id' => $conversation->id,
                            'participant_id' => $value,
                            'role' => 'emp',
                            'position_number' => $this->getPositionNumber($value),
                        ]);
    
                        ConversationParticipant::updateOrCreate([
                            'conversation_id' => $conversation->id,
                            'participant_id' => $actualOwner,
                            'role' => 'mgr',
                        ]);
                        $actualOwnerRole = 'mgr';
                        $is_direct = true;
                        $switch = true;
                    }    

                    $mgrinfo_1 = DB::table('users')                        
                                     ->select('reporting_to')
                                     ->where('id', '=', $actualOwner)
                                     ->get();
                    if($mgrinfo_1[0]->reporting_to == $value){
                        ConversationParticipant::updateOrCreate([
                            'conversation_id' => $conversation->id,
                            'participant_id' => $actualOwner,
                            'role' => 'emp',
                            'position_number' => $this->getPositionNumber($actualOwner),
                        ]);
    
                        ConversationParticipant::updateOrCreate([
                            'conversation_id' => $conversation->id,
                            'participant_id' => $value,
                            'role' => 'mgr',
                        ]);
                        $is_direct = true;
                        $switch = true;
                    }
                }


                if (!$is_direct) {
                   $shareinfo_0 = DB::table('shared_profiles')                        
                                        ->select('shared_with')
                                        ->where('shared_id', '=', $value)
                                        ->where('shared_item', 'like', '%2%')
                                        ->get();   
                    foreach($shareinfo_0 as $shareitem){
                       if($shareitem->shared_with == $actualOwner) {
                            ConversationParticipant::updateOrCreate([
                                'conversation_id' => $conversation->id,
                                'participant_id' => $actualOwner,
                                'role' => 'mgr',
                            ]);

                            ConversationParticipant::updateOrCreate([
                                'conversation_id' => $conversation->id,
                                'participant_id' => $value,
                                'role' => 'emp',
                            ]);
                            $actualOwnerRole = 'mgr';
                        }
                    }

                    $shareinfo_1 = DB::table('shared_profiles')                        
                                        ->select('shared_with')
                                        ->where('shared_id', '=', $actualOwner)
                                        ->where('shared_item', 'like', '%2%')
                                        ->get(); 
                    foreach($shareinfo_1 as $shareitem){
                        if($shareitem->shared_with == $value) {
                            ConversationParticipant::updateOrCreate([
                                'conversation_id' => $conversation->id,
                                'participant_id' => $value,
                                'role' => 'mgr',
                            ]);

                            ConversationParticipant::updateOrCreate([
                                'conversation_id' => $conversation->id,
                                'participant_id' => $actualOwner,
                                'role' => 'emp',
                                'position_number' => $this->getPositionNumber($actualOwner),
                            ]);
                        }

                    } 
                }
            }
        } else {
                $value = $request->participant_id;
                $is_direct = false;
                $switch = false;
                // $mgrinfo_0 = DB::table('users')                        
                //                         ->select('reporting_to')
                //                         ->where('id', '=', $value)
                //                         ->get();
                // if($mgrinfo_0[0]->reporting_to == $actualOwner){
                if($this->getReportingTo($value) == $actualOwner){
                    ConversationParticipant::updateOrCreate([
                        'conversation_id' => $conversation->id,
                        'participant_id' => $value,
                        'role' => 'emp',
                        'position_number' => $this->getPositionNumber($value),
                    ]);

                    ConversationParticipant::updateOrCreate([
                        'conversation_id' => $conversation->id,
                        'participant_id' => $actualOwner,
                        'role' => 'mgr',
                    ]);
                    $is_direct = true;
                    $actualOwnerRole = 'mgr';

                    $switch = true;
                } 

                // $mgrinfo_1 = DB::table('users')                        
                //                         ->select('reporting_to')
                //                         ->where('id', '=', $actualOwner)
                //                         ->get();
                // if($mgrinfo_1[0]->reporting_to == $value){
                if($this->getReportingTo($actualOwner) == $value){
                    ConversationParticipant::updateOrCreate([
                        'conversation_id' => $conversation->id,
                        'participant_id' => $actualOwner,
                        'role' => 'emp',
                        'position_number' => $this->getPositionNumber($actualOwner),
                    ]);

                    ConversationParticipant::updateOrCreate([
                        'conversation_id' => $conversation->id,
                        'participant_id' => $value,
                        'role' => 'mgr',
                    ]);
                    $is_direct = true;

                    $switch = true;
                }

                if (!$switch) {
                    $mgrinfo_0 = DB::table('users')                        
                                         ->select('reporting_to')
                                         ->where('id', '=', $value)
                                         ->get();
                    if($mgrinfo_0[0]->reporting_to == $actualOwner){
                        ConversationParticipant::updateOrCreate([
                            'conversation_id' => $conversation->id,
                            'participant_id' => $value,
                            'role' => 'emp',
                            'position_number' => $this->getPositionNumber($value),
                        ]);
    
                        ConversationParticipant::updateOrCreate([
                            'conversation_id' => $conversation->id,
                            'participant_id' => $actualOwner,
                            'role' => 'mgr',
                        ]);
                        $is_direct = true;
                        $actualOwnerRole = 'mgr';
    
                        $switch = true;
                    } 
                    
                    $mgrinfo_1 = DB::table('users')                        
                                         ->select('reporting_to')
                                         ->where('id', '=', $actualOwner)
                                         ->get();
                    if($mgrinfo_1[0]->reporting_to == $value){
                        ConversationParticipant::updateOrCreate([
                            'conversation_id' => $conversation->id,
                            'participant_id' => $actualOwner,
                            'role' => 'emp',
                            'position_number' => $this->getPositionNumber($actualOwner),
                        ]);
    
                        ConversationParticipant::updateOrCreate([
                            'conversation_id' => $conversation->id,
                            'participant_id' => $value,
                            'role' => 'mgr',
                        ]);
                        $is_direct = true;
                        $switch = true;
                    }
                }


                if (!$is_direct) {
                   $shareinfo_0 = DB::table('shared_profiles')                        
                                        ->select('shared_with')
                                        ->where('shared_id', '=', $value)
                                        ->where('shared_item', 'like', '%2%')
                                        ->get();   
                    foreach($shareinfo_0 as $shareitem){
                       if($shareitem->shared_with == $actualOwner) {
                            ConversationParticipant::updateOrCreate([
                                'conversation_id' => $conversation->id,
                                'participant_id' => $actualOwner,
                                'role' => 'mgr',
                            ]);

                            ConversationParticipant::updateOrCreate([
                                'conversation_id' => $conversation->id,
                                'participant_id' => $value,
                                'role' => 'emp',
                                'position_number' => $this->getPositionNumber($value),
                            ]);
                            $actualOwnerRole = 'mgr';
                        }
                    }

                    $shareinfo_1 = DB::table('shared_profiles')                        
                                        ->select('shared_with')
                                        ->where('shared_id', '=', $actualOwner)
                                        ->where('shared_item', 'like', '%2%')
                                        ->get(); 
                    foreach($shareinfo_1 as $shareitem){
                        if($shareitem->shared_with == $value) {
                            ConversationParticipant::updateOrCreate([
                                'conversation_id' => $conversation->id,
                                'participant_id' => $value,
                                'role' => 'mgr',
                            ]);

                            ConversationParticipant::updateOrCreate([
                                'conversation_id' => $conversation->id,
                                'participant_id' => $actualOwner,
                                'role' => 'emp',
                                'position_number' => $this->getPositionNumber($actualOwner),
                            ]);
                        }

                    } 
                }
        }
        //DB::commit();

        // create a message on the participant's dasboard under home page
        if(is_array($request->participant_id)){
            foreach ($request->participant_id as $key => $value) {
                // DashboardNotification::create([
                //     'user_id' => $value,
                //     'notification_type' => 'CA',        // Conversation Added
                //     'comment' => $conversation->user->name . ' would like to schedule a performance conversation with you.',
                //     'related_id' => $conversation->id,
                // ]);

                // Send Out email when the conversation added
                $user = User::where('id', $value)
                                ->with('userPreference')
                                ->first();

                if ($user && $user->allow_inapp_notification) {                                
                $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                            $notification->user_id = $value;
                            $notification->notification_type = 'CA';
                            $notification->comment = $conversation->user->name . ' would like to schedule a performance conversation with you.';
                            $notification->related_id = $conversation->id;
                            $notification->notify_user_id = $value;
                            $notification->send(); 
                }

                if ($user && $user->allow_email_notification && $user->userPreference->conversation_setup_flag == 'Y') {                            

                    $due = Conversation::nextConversationDue( $user );

                    $topic = ConversationTopic::find($request->conversation_topic_id);
                    $sendMail = new \App\MicrosoftGraph\SendMail();
                    $sendMail->toRecipients = [ $value ];
                    $sendMail->sender_id = null;  // default sender is System
                    $sendMail->useQueue = true;
                    $sendMail->template = 'ADVICE_SCHEDULE_CONVERSATION';
                    array_push($sendMail->bindvariables, $user->name);
                    array_push($sendMail->bindvariables, $conversation->user->name );
                    array_push($sendMail->bindvariables, $conversation->topic->name );
                    array_push($sendMail->bindvariables, $due );
                    $response = $sendMail->sendMailWithGenericTemplate();
                }

            }       
        } else {
                $value = $request->participant_id;
                // DashboardNotification::create([
                //     'user_id' => $value,
                //     'notification_type' => 'CA',        // Conversation Added
                //     'comment' => $conversation->user->name . ' would like to schedule a performance conversation with you.',
                //     'related_id' => $conversation->id,
                // ]);

                // Send Out email when the conversation added
                $user = User::where('id', $value)
                                ->with('userPreference')
                                ->first();

                if ($user && $user->allow_inapp_notification) {
                    $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                    $notification->user_id = $value;
                    $notification->notification_type = 'CA';
                    $notification->comment = $conversation->user->name . ' would like to schedule a performance conversation with you.';
                    $notification->related_id = $conversation->id;
                    $notification->notify_user_id = $value;
                    $notification->send(); 
                }

                if ($user && $user->allow_email_notification && $user->userPreference->conversation_setup_flag == 'Y') {                            

                    $due = Conversation::nextConversationDue( $user );

                    $topic = ConversationTopic::find($request->conversation_topic_id);
                    $sendMail = new \App\MicrosoftGraph\SendMail();
                    $sendMail->toRecipients = [ $value ];
                    $sendMail->sender_id = null;  // default sender is System
                    $sendMail->useQueue = true;
                    $sendMail->template = 'ADVICE_SCHEDULE_CONVERSATION';
                    array_push($sendMail->bindvariables, $user->name);
                    array_push($sendMail->bindvariables, $conversation->user->name );
                    array_push($sendMail->bindvariables, $conversation->topic->name );
                    array_push($sendMail->bindvariables, $due );
                    $response = $sendMail->sendMailWithGenericTemplate();
                }
        }

        if(request()->ajax()){
            return response()->json(['success' => true, 'message' => 'Conversation Created successfully']);
        }else{
            //return redirect()->route('conversation.upcoming');
            /* if ($conversation->is_with_supervisor) {
                return redirect()->route('conversation.upcoming');
            } else {
                return redirect()->route('my-team.conversations.upcoming');
            } */
            $conversation_id = $conversation->id;
            return redirect()->route('conversation.upcoming', ['id' => $conversation_id, 'ownerrole'=>$actualOwnerRole]);
        }
    }

    public function getReportingTo ($param)
    {
        $user = User::findOrFail($param);
        $user_validPreferredSupervisorID = $user->validPreferredSupervisor();
        $user_supervisors = $user->supervisorListPrimaryJob();
        $user_supervisor_id = null;
        $user_supervisor_default = null;
        $user_supervisor_default_id = null;
        foreach($user_supervisors as $user_sup) {
            if($user_sup->employee_id == $user_validPreferredSupervisorID) {
                $user_supervisor_id = $user_sup->supervisor_id;
            }
            if($user_supervisor_default && $user_sup->employee_id > $user_supervisor_default) {
                $user_supervisor_default = $user_sup->employee_id;
                $user_supervisor_default_id = $user_sup->supervisor_id;
            }
            if(!$user_supervisor_default) {
                $user_supervisor_default = $user_sup->employee_id;
                $user_supervisor_default_id = $user_sup->supervisor_id;
            }
        }
        return $user_supervisor_id ? $user_supervisor_id : $user_supervisor_default_id;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Conversation $conversation, Request $request)
    {
        $conversation->topics = ConversationTopic::all();
        
        $conversation_id = $conversation->id;
        $conversation->disable_signoff = false;
        
        $conversation->is_locked = $conversation->getIsLockedAttribute();
             
        $conversation_topic_details = DB::table('conversation_topics')   
                            ->select('conversation_topics.*')
                            ->join('conversations','conversations.conversation_topic_id','=','conversation_topics.id')  
                            ->where('conversations.id', $conversation_id)
                            ->get(); 
        $conversation->preparing_for_conversation = $conversation_topic_details[0]->preparing_for_conversation;
        
        $conversation_participants = DB::table('conversation_participants')                        
                            ->where('conversation_id', $conversation_id)
                            ->orderBy('role','desc')
                            ->get();    
        $conversation->participants = $conversation_participants;
        $is_viewer = false;
        $view_as_supervisor = false;
        $disable_signoff = false;
        $mgr = '';
        $emp = '';

        if(session()->has('view-profile-as')) {
            $original_user =  $request->session()->get('view-profile-as');
            foreach($conversation_participants as $participant) {
                if($participant->role == 'mgr' && $participant->participant_id == $original_user) {
                    $mgr = $participant->participant_id;
                    $view_as_supervisor = true;
                } else {
                    $emp = $participant->participant_id;
                }
            } 
            if ($mgr == '' && $emp == ''){
                $is_viewer = true;
                $disable_signoff = true;
            }
        }else {
            foreach($conversation_participants as $participant) {
                $current_user = auth()->user()->id;
                if($participant->role == 'mgr' && $participant->participant_id == $current_user) {
                    $mgr = $participant->participant_id;
                    $view_as_supervisor = true;
                } else {
                    $emp = $participant->participant_id;
                }
            }        
        }
        $conversation->is_viewer = $is_viewer;
        $conversation->view_as_supervisor = $view_as_supervisor;
        $conversation->disable_signoff = $disable_signoff;
        $conversation->mgr = $mgr;
        $conversation->emp = $emp;
        $request->session()->put('view_as_supervisor', $view_as_supervisor);
        
        
        /*
        if(!session()->has('view-profile-as')) {
            $current_user = auth()->user()->id;
            $conversation_participants = DB::table('conversation_participants')                        
                            ->where('conversation_id', $conversation_id)
                            ->where('participant_id', '<>', $current_user)
                            ->get();         
            $participant = $conversation_participants[0]->participant_id;

            $participant_is_mgr = false;
            $user_is_mgr = false; 

            //check direct report
            $check_mgr = DB::table('users')                        
                            ->where('reporting_to', $participant)
                            ->where('id', $current_user)
                            ->count(); 

            $own_conversation = false;
            if($check_mgr > 0) {
                $participant_is_mgr = true;
            } else {
                $check_mgr = DB::table('users')                        
                            ->where('reporting_to', $current_user)
                            ->where('id', $participant)
                            ->count(); 
                if($check_mgr > 0) {
                    $user_is_mgr = true;                
                } else {
                    // check shared
                    $check_mgr = DB::table('shared_profiles')                        
                            ->where('shared_id', $current_user)
                            ->where('shared_with', $participant)
                            ->count(); 
                    if($check_mgr > 0) {
                        $participant_is_mgr = true;
                    } else {
                        $user_is_mgr = true;     
                    }
                }
            }
        } else {
            $current_user = $request->session()->get('view-profile-as');
            $original_user = $request->session()->get('original-auth-id');
            $conversation_participants = DB::table('conversation_participants')                        
                            ->where('conversation_id', $conversation_id)
                            ->where('participant_id', '=', $original_user)
                            ->count();         
            if ($conversation_participants) {
                $user_is_mgr = true;
            } else {
                $user_is_mgr = false;
                $conversation->disable_signoff = true;
            }                 
        }
        
        if($user_is_mgr ==  true) {
            $view_as_supervisor = true;
        } else {
            $view_as_supervisor = false;
        }
        $request->session()->put('view_as_supervisor', $view_as_supervisor);
        $conversation->view_as_supervisor = $view_as_supervisor;
         */
        //error_log(print_r($conversation,true));
        return $conversation;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Conversation $conversation)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Conversation $conversation)
    {
        if ($request->field != 'conversation_participant_id' && $request->field != 'info_comments') {
            $conversation->{$request->field} = $request->value;
        } elseif($request->field == 'info_comments'){
          foreach ($request->value as $key => $value) {
                $conversation->{$key} = $value;
            }
        } elseif ($request->field == 'conversation_participant_id') {
            ConversationParticipant::where('conversation_id', $conversation->id)->delete();
            foreach ($request->value as $key => $value) {
                ConversationParticipant::updateOrCreate([
                    'conversation_id' => $conversation->id,
                    'participant_id' => $value,
                ]);
            }
        }

        $conversation->update();

        return $conversation;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Conversation $conversation)
    {
        $conversation->delete();

        return redirect()->back();
    }
    
    public function agreement(Conversation $conversation)
    {
        $view_as_supervisor = session()->get('view_as_supervisor');
        $authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();

        if (!$view_as_supervisor){            
            // #646 Create a message for the supervisor when the people check on Team member disagrees 
            // with the information contained in this performance review
            if ($conversation->id) {
		$conversation->team_member_agreement = 0;
                $conversation->update();
                return response()->json(['success' => true, 'Message' => 'Your agree supervisor comments.', 'data' => $conversation]);
            }
        }
    }
    
    public function disagreement(Conversation $conversation)
    {
        $view_as_supervisor = session()->get('view_as_supervisor');
        $authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();

        if (!$view_as_supervisor){            
            // #646 Create a message for the supervisor when the people check on Team member disagrees 
            // with the information contained in this performance review
            if ($conversation->id) {
		$signoff_user = User::where('id', $authId)->first();
                $conversation->team_member_agreement = 1;
                
                if ($signoff_user && $signoff_user->reporting_to ) {
                    
                    
                    $conversation_participants = DB::table('conversation_participants')                        
                            ->where('conversation_id', $conversation->id)
                            ->where('participant_id', '<>', $authId)
                            ->first();
                    //error_log(print_r($conversation_participants->participant_id,true));

                    $user = User::where('id', $conversation_participants->participant_id)
                                ->with('userPreference')
                                ->select('id','name','guid', 'employee_id')
                                ->first();

                    if ($user && $user->allow_inapp_notification) {                                
                        $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                    $notification->user_id = $conversation_participants->participant_id;
                    
                    $notification->notification_type = 'CS';
                    $notification->comment = $signoff_user->name . ' has selected the "disagree" option on a performance conversation with you.';
                    $notification->related_id = $conversation->id;
                    $notification->notify_user_id =  $signoff_user->reporting_to;
                    $notification->send(); 
                    }

                     // Send a email notification to the participants when someone sign the conversation
                    //error_log(print_r($user,true));
                    error_log(print_r($conversation->conversation_topic_id,true));
                    if ($user && $user->allow_email_notification && $user->userPreference->conversation_disagree_flag == 'Y') {     
                        $topic = ConversationTopic::find($conversation->conversation_topic_id);
                        $sendMail = new \App\MicrosoftGraph\SendMail();
                        $sendMail->toRecipients = [ $user->id ];
                        $sendMail->sender_id = null;  // default sender is System
                        $sendMail->useQueue = true;
                        $sendMail->template = 'CONVERSATION_DISAGREED';
                        array_push($sendMail->bindvariables, $user->name);
                        array_push($sendMail->bindvariables, $signoff_user->name );   //Person who signed the conversation 
                        array_push($sendMail->bindvariables, $conversation->topic->name );  // Conversation topic
                        $response = $sendMail->sendMailWithGenericTemplate();
                    }

                }
                $conversation->update();
                return response()->json(['success' => true, 'Message' => 'Your disagree notification has been sent.', 'data' => $conversation]);
            }
        }
    }

    public function signOff(SignoffRequest $request, Conversation $conversation)
    {
        $view_as_supervisor = session()->get('view_as_supervisor');
        $authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
        $current_employee = DB::table('employee_demo')
                            ->select('employee_demo.employee_id')
                            ->join('users', 'employee_demo.employee_id', '=', 'users.employee_id')
                            ->where('users.id', $authId)
                            ->get();
        
        if (count($current_employee) > 0 ){
            if ($current_employee[0]->employee_id != $request->employee_id) {
                return response()->json(['success' => false, 'Message' => 'Invalid Employee ID', 'data' => $conversation]);            
            }
        } else {
            return response()->json(['success' => false, 'Message' => 'Invalid Employee ID', 'data' => $conversation]);   
        }
  
        //if (!$conversation->is_with_supervisor) {
        if ($view_as_supervisor){
            $conversation->supervisor_signoff_id = $authId;
            $conversation->supervisor_signoff_time = Carbon::now();

            $conversation->supv_agree1 = $request->check_one_;
            $conversation->supv_agree2 = $request->check_two_;
            $conversation->supv_agree3 = $request->check_three_;
        } else {
            $conversation->signoff_user_id = $authId;
            $conversation->sign_off_time = Carbon::now();
            $conversation->empl_agree1 = $request->check_one;
            $conversation->empl_agree2 = $request->check_two;
            $conversation->empl_agree3 = $request->check_three;

            if (!$conversation->initial_signoff) {
                $conversation->initial_signoff = Carbon::now();
            }
        }
        $conversation->update();

        // Notification the participants when someone sign the conversation
        $current_user = User::where('id', $authId)->first();

        $participants = $conversation->conversationParticipants->map(function ($item, $key) { 
                                return $item->participant; 
                        });
        $to_ids   = $participants->pluck('id')->toArray();
        $to_ids = array_diff( $to_ids, [ $current_user->id ] );
        $to_names = User::whereIn('id', $to_ids)->pluck('name')->toArray();

        // create a message on the dasboard under home page when signoff 
        foreach ($to_ids as $key => $value) {
            // DashboardNotification::create([
            //     'user_id' => $value,
            //     'notification_type' => 'CS',        // Conversation signoff 
            //     'comment' => $current_user->name . ' signed your performance conversation.',
            //     'related_id' => $conversation->id,
            // ]);
            // Use Class to create DashboardNotification

            $user = User::where('id', $value)
                        ->with('userPreference')
                        ->select('id','name','guid', 'employee_id')
                        ->first();

            if ($user && $user->allow_inapp_notification) {                        
                $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                $notification->user_id = $value;
                $notification->notification_type = 'CS';
                $notification->comment = $current_user->name . ' signed your performance conversation.';
                $notification->related_id = $conversation->id;
                $notification->notify_user_id = $value;
                $notification->send(); 
            }

            // Send a email notification to the participants when someone sign the conversation
            if ($user && $user->allow_email_notification && $user->userPreference->conversation_signoff_flag == 'Y') {                            
    
                $topic = ConversationTopic::find($request->conversation_topic_id);
                $sendMail = new \App\MicrosoftGraph\SendMail();
                $sendMail->toRecipients = [ $value ];
                $sendMail->sender_id = null;  // default sender is System
                $sendMail->useQueue = true;
                $sendMail->template = 'CONVERSATION_SIGN_OFF';
                array_push($sendMail->bindvariables, $user->name);
                array_push($sendMail->bindvariables, $current_user->name );   //Person who signed the conversation 
                array_push($sendMail->bindvariables, $conversation->topic->name );  // Conversation topic
                $response = $sendMail->sendMailWithGenericTemplate();
            }

        }      

        return response()->json(['success' => true, 'Message' => 'Sign Off Successfull', 'data' => $conversation]);
    }

    public function unsignOff(UnSignoffRequest $request, Conversation $conversation)
    {
        $view_as_supervisor = session()->get('view_as_supervisor');

        $authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
        error_log(print_r($authId,true));
        $current_employee = DB::table('employee_demo')
                            ->select('employee_demo.employee_id')
                            ->join('users', 'employee_demo.employee_id', '=', 'users.employee_id')
                            ->where('users.id', $authId)
                            ->get();
        error_log(print_r($current_employee,true));
        if ($current_employee[0]->employee_id != $request->employee_id) {
            return response()->json(['success' => false, 'Message' => 'Invalid Employee ID', 'data' => $conversation]);            
        }
        
        //if (!$conversation->is_with_supervisor) {
        if ($view_as_supervisor) {
            $conversation->supervisor_signoff_id = null;
            $conversation->supervisor_signoff_time = null;
        } else {
            $conversation->signoff_user_id = null;
            $conversation->sign_off_time = null;
        }
        $conversation->update();

        return
        response()->json(['success' => true, 'Message' => 'UnSign Successfull', 'data' => $conversation]);
    }

    public function templates(Request $request, $viewType = 'conversations') {
        $authId = Auth::id();
        $user = User::find($authId);
        $query = new ConversationTopic;
        if ($request->has('search') && $request->search) {
            $query = $query->where('name', 'LIKE', "%$request->search%");
        }
        $templates = $query->orderBy('sort')->get();
        $searchValue = $request->search ?? '';
        $conversationMessage = Conversation::warningMessage();

        $reportees = User::join('users_annex', function($join) {
            return $join->on(function ($on) {
                return $on->on('users_annex.employee_id', 'users.employee_id')
                    ->on('users_annex.empl_record', 'users.empl_record');
            });
        })
        ->selectRaw("users.id")
        ->whereRaw("users_annex.reporting_to_userid = {$authId}")
        ->pluck("users.id");
        $participants = session()->has('original-auth-id') ? User::where('id', Auth::id())->get() : 
            User::join('employee_demo as ed', 'ed.employee_id', 'users.employee_id')
                ->whereIn('users.id', $reportees)
                ->whereNull('ed.date_deleted')
                ->get();

        $array_mgr_ids = User::join('users_annex', function($join) {
            return $join->on(function ($on) {
                return $on->on('users_annex.employee_id', 'users.employee_id')
                    ->on('users_annex.empl_record', 'users.empl_record');
            });
        })
        ->selectRaw("users_annex.reporting_to_employee_id")
        ->whereRaw("users.id = {$authId}")
        ->pluck('reporting_to_employee_id');

        $reportingManager = User::whereIn('users.employee_id', $array_mgr_ids)
            ->join('employee_demo AS ed', 'ed.employee_id', 'users.employee_id')
            ->whereNull('ed.date_deleted')
            ->get();

        $sharedProfile = SharedProfile::where('shared_with', Auth::id())
                         ->join('users','users.id','shared_profiles.shared_id')
                         ->join('employee_demo','employee_demo.employee_id', 'users.employee_id')
                         ->whereNull('employee_demo.date_deleted')
                         ->whereRaw('employee_demo.pdp_excluded = 0')
                         ->with('sharedUser')->where('shared_item', 'like', '%2%')->get()->pluck('sharedUser'); 

        $participants = $participants->toBase()->merge($reportingManager)->merge($sharedProfile);

        $adminShared=SharedProfile::select('shared_with')
        ->join('users','users.id','shared_profiles.shared_with')
        ->join('employee_demo','employee_demo.employee_id', 'users.employee_id')
        ->whereNull('employee_demo.date_deleted')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->where('shared_id', '=', Auth::id())
        ->where('shared_item', 'like', '%2%')
        ->pluck('shared_with');

        $adminemps = User::select('users.*')
        ->join('employee_demo','employee_demo.employee_id', 'users.employee_id')
        ->whereNull('employee_demo.date_deleted')
        ->whereRaw('employee_demo.pdp_excluded = 0')
        ->whereIn('users.id', $adminShared)->get('id', 'name');

        $participants = $participants->merge($adminemps);
        
        $participant_users = array();
        $i = 0;
        foreach ($participants as $participant){
            $participant_users[$i]["id"] = $participant->id;
            $participant_users[$i]["name"] = $participant->name;
            $i++;
        }
        usort($participant_users, function($a, $b){ return strcmp($a["name"], $b["name"]); });
        $participant_users = collect($participant_users)->unique('id')->values()->all();

        $type = 'template';

        return view('conversation.templates', compact('templates', 'searchValue', 'conversationMessage', 'viewType', 'user', 'type', 'participants', 'participant_users'));
    }

    public function templateDetail($id) {
        $authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
        $user = User::find($authId);
        $template = ConversationTopic::findOrFail($id);
        $allTemplates = ConversationTopic::all();
        
        $participants = session()->has('original-auth-id') ? User::where('id', Auth::id())->get() : $user->avaliableReportees()->get();
        $reportingManager = $user->reportingManager()->get();
        $sharedProfile = SharedProfile::where('shared_with', Auth::id())->with('sharedUser')->get()->pluck('sharedUser');
        $participants = $participants->toBase()->merge($reportingManager)->merge($sharedProfile);
 
        $adminShared=SharedProfile::select('shared_with')
        ->where('shared_id', '=', Auth::id())
        ->where('shared_item', 'like', '%2%')
        ->pluck('shared_with');
        $adminemps = User::select('users.*')
        ->whereIn('users.id', $adminShared)->get('id', 'name');
        $participants = $participants->merge($adminemps);
        
        return view('conversation.partials.template-detail-modal-body', compact('template','allTemplates','participants','reportingManager'));
    }
    
    public function conversationTemplate($id) {
        $template = ConversationTopic::findOrFail($id);
        
        return
        response()->json(['success' => true, 'data' => $template]);
    }

    public function sendNotification($id)
    {
        $authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
        $userPart = ConversationParticipant::whereRaw("conversation_id = {$id}")
            ->whereRaw("participant_id = {$authId}")
            ->select('participant_id')
            ->first();
        $authUser = User::find($authId);
        if ($userPart) {
            $conversation = Conversation::find($id);
            $otherPart = ConversationParticipant::whereRaw("conversation_id = {$id}")
                ->whereRaw("participant_id <> {$authId}")
                ->select('participant_id')
                ->get();
            foreach ($otherPart as $value) {
                $user = User::where('id', $value->participant_id)
                                ->with('userPreference')
                                ->first();
                if ($user && $user->allow_inapp_notification) {                                
                    $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                    $notification->user_id = $value->participant_id;
                    $notification->notification_type = 'CN';
                    $notification->comment = "{$authUser->name} has made updates to your conversation";
                    $notification->related_id = $conversation->id;
                    $notification->notify_user_id = $value->participant_id;
                    $notification->send(); 
                }
                if ($user && $user->allow_email_notification) {                            
                    $topic = ConversationTopic::find($conversation->conversation_topic_id);
                    $sendMail = new \App\MicrosoftGraph\SendMail();
                    $sendMail->toRecipients = [ $value->participant_id ];
                    $sendMail->sender_id = null;  // default sender is System
                    $sendMail->useQueue = true;
                    $sendMail->template = 'CONVERSATION_CHANGE_NOTIFY';
                    array_push($sendMail->bindvariables, $user->name);
                    array_push($sendMail->bindvariables, $authUser->name);
                    array_push($sendMail->bindvariables, $conversation->topic->name );
                    $response = $sendMail->sendMailWithGenericTemplate();
                }
            }       
        }
        return null;
    }

    public function getPositionNumber($id)
    {
        $position_number = null;
        $position_array = [];
        $users = User::where('id', $id)->select('id', 'employee_id')->first();
        if($users){
            $demo = EmployeeDemo::where('employee_id', $users->employee_id)->whereNull('date_deleted')->get();
            if(!$demo){ return $position_number; }
            if($demo->count() == 1){ 
                // use only employee demo position number
                $position_number = $demo[0]->position_number;
            } else {
                $position_array = array_column($demo->toArray(), 'position_number');
                $preferred = PreferredSupervisor::where('employee_id', $users->employee_id)->first();
                if($preferred && in_array($preferred->position_number, $position_array, true)){
                    // use preferred position number, if matching 1 in the array
                    $position_number = $preferred->position_number;
                } else {
                    $first = EmployeeDemo::where('employee_id', $users->employee_id)->whereNull('date_deleted')->orderBy('empl_record')->first();
                    if($first){
                        // use position number from lowest empl record in employee demo
                        $position_number = $first->position_number;
                    }
                };
            };
        }
        return $position_number;
    }
    
}
