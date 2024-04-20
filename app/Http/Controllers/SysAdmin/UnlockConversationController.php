<?php

namespace App\Http\Controllers\SysAdmin;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\ConversationTopic;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UnlockConversationController extends Controller
{
    //
    public function index(Request $request) {
        $criteriaList = $this->search_criteria_list();
        $topicList = ConversationTopic::orderBy('name')->get();
        $type = 'past';
        return view('sysadmin.unlock.unlockconversation', compact('criteriaList', 'topicList', 'request', 'type'));
    }

    public function indexManageUnlocked(Request $request) {
        $criteriaList = $this->search_criteria_list();
        $topicList = ConversationTopic::orderBy('name')->get();
        $type = 'past';
        return view('sysadmin.unlock.manageunlocked', compact('criteriaList', 'topicList', 'request', 'type'));
    }

    public function getDatatableConversations(Request $request) {
        if($request->ajax()){
            $sql = $this->baseFilteredWhere($request);
            $conversations = $sql->where(function($q) use ($request) {
                $q->whereNotNull('signoff_user_id')
                    ->whereNotNull('supervisor_signoff_id')
                    ->whereNotNull('sign_off_time')
                    ->whereNotNull('supervisor_signoff_time');
            })
            ->whereRaw( "initial_signoff < adddate(date(sysdate()), INTERVAL -14 day)" )
            ->where(function($q) use ($request) {
                $q->whereNull('conversations.unlock_until')
                    ->orWhere('conversations.unlock_until','<', today() );
            })
            ->select('conversations.*', DB::raw('GREATEST(sign_off_time, supervisor_signoff_time) as completed_date') )
            ->with(['topic']);
            return Datatables::of($conversations)
                ->addColumn('participants', function ($conversation) {
                    $userIds = $conversation->conversationParticipants()->pluck('participant_id')->toArray();
                    $users = User::whereIn('id', $userIds)->pluck('name');
                     return implode('; ', $users->toArray() );
                })
                ->addColumn('action', function ($conversation) {
                    $locked = true;
                    if ( (is_null($conversation->sign_off_time) ||  
                            $conversation->sign_off_time >= today()->addDays(14)) && 
                         (is_null($conversation->supervisor_signoff_time) || 
                            $conversation->supervisor_signoff_time  >= today()->addDays(14)) ) {
                        $locked = false;    
                    }
                    if ( $conversation->unlock_until && $conversation->unlock_until >= today() ) {
                        $locked = false;
                    }
                    $out = '<button class="btn btn-primary btn-sm  ml-2 btn-view-conversation" '.
                            'data-id="'. $conversation->id . '" data-toggle="modal" data-target="#viewConversationModal">View</button>';
                    if ($locked) {
                        $out = $out . '<button class="btn btn-primary btn-sm ml-2 unlock-modal" data-id="'. $conversation->id . '" unlock-until="' .
                        $conversation->unlock_until . '">Unlock</button>';
                    }
                    return $out;
                })
                ->addColumn('unlock', function ($conversation) {
                    $icon = 'fa-lock';
                    if (!( $conversation->is_locked )) {
                        $icon = 'fa-unlock-alt';    
                    } 
                    if ( $conversation->is_unlock ) {
                        $icon = 'fa-unlock-alt';    
                    }
                    return '<a  class="btn btn-sm btn-secondary" data-id="'. 
                         $conversation->id .'" unlock-until="'. $conversation->unlock_until .'"><i class="fa ' . $icon . '" aria-hidden="true"></i></a>';
                })
                ->rawColumns(['unlock', 'action'])
                ->make(true);
        }
    }

    public function getDatatableManagedUnlocked(Request $request) {
        if($request->ajax()){
            $sql = $this->baseFilteredWhere($request);
            $conversations = $sql->whereNotNull('unlock_until')
                                  ->where('unlock_until','>=', today() );
            return Datatables::of($conversations)
                ->addColumn('topic', function ($conversation) {
                    return $conversation->topic->name;
                })
                ->addColumn('participants', function ($conversation) {
                    $userIds = $conversation->conversationParticipants()->pluck('participant_id')->toArray();
                    $users = User::whereIn('id', $userIds)->pluck('name');
                     return implode('; ', $users->toArray() );
                })
                ->addColumn('action', function ($conversation) {
                    return '<button class="btn btn-primary btn-sm  ml-2 btn-view-conversation" '.
                    'data-id="'. $conversation->id . '" data-toggle="modal" data-target="#viewConversationModal">View</button>' .
                    '<button class="btn btn-primary btn-sm ml-2 unlock-modal" data-id="'. $conversation->id . '" unlock-until="' .
                     $conversation->unlock_until->format('Y-m-d') . '">Modify</button>';
                })
                ->addColumn('unlock', function ($conversation) {
                    $icon = $conversation->unlock_until ? 'fa-unlock-alt' : 'fa-lock';
                    return '<a class="btn btn-sm btn-secondary" data-id="'. 
                        $conversation->id .'" unlock-until="'. $conversation->unlock_until .'"><i class="fa ' . $icon . '" aria-hidden="true"></i></a>';
                })
                ->rawColumns(['unlock', 'action'])
                ->make(true);
        }
    }

    protected function update(Request $request, $id) {
        $request->validate([
            'unlock_until'   => 'required|date|after_or_equal:yesterday',
        ]);
        $conversation  = Conversation::find($id);
        $conversation  = Conversation::find($id);
        $conversation->unlock_until = request('unlock_until');
        $conversation->unlock_by = Auth::id();
        $conversation->unlock_at = now();
        $conversation->save();
        return response()->json($conversation );
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'employee_demo.employee_id' => 'Employee ID', 
            'employee_demo.employee_name'=> 'Employee Name',
            'employee_demo.classification_group' => 'Classification', 
            'employee_demo.deptid' => 'Department ID'
        ];
    }

    protected function baseFilteredWhere($request) {
        $sql = Conversation::when( $request->topic_id , function ($q) use($request) { return $q->where('conversations.conversation_topic_id', $request->topic_id); })
            ->when( $request->completion_date_from, function ($q) use($request) { return $q->whereRaw( "GREATEST(sign_off_time, supervisor_signoff_time) >= '" . $request->completion_date_from . "'"); })
            ->when( $request->completion_date_to, function ($q) use($request) { return $q->whereRaw( "GREATEST(sign_off_time, supervisor_signoff_time) <= '" . $request->completion_date_to . " 23:59:59'"); })
            ->when( $request->dd_level0 or $request->dd_level1 or $request->dd_level2 or $request->dd_level3 or $request->dd_level4 or $request->search_text, function ($q) use($request) {
                return $q->whereIn('conversations.id', function($q) use ($request) {
                    return $q->select('conversation_id')
                        ->from('conversation_participants')
                        ->join('users', 'users.id', 'conversation_participants.participant_id')
                        ->join('employee_demo', 'employee_demo.employee_id', 'users.employee_id')
                        ->join('employee_demo_tree AS u', 'u.id', 'employee_demo.orgid')
                        ->whereRaw('employee_demo.pdp_excluded = 0')
                        ->when($request->dd_level0, function($q) use($request) { return $q->whereRaw("u.organization_key = {$request->dd_level0}"); })
                        ->when($request->dd_level1, function($q) use($request) { return $q->whereRaw("u.level1_key = {$request->dd_level1}"); })
                        ->when($request->dd_level2, function($q) use($request) { return $q->whereRaw("u.level2_key = {$request->dd_level2}"); })
                        ->when($request->dd_level3, function($q) use($request) { return $q->whereRaw("u.level3_key = {$request->dd_level3}"); })
                        ->when($request->dd_level4, function($q) use($request) { return $q->whereRaw("u.level4_key = {$request->dd_level4}"); })
                        ->when($request->search_text && $request->criteria != 'all', function($q) use($request) { 
                            return $q->where("{$request->criteria}", 'LIKE', "%{$request->search_text}%"); 
                        })
                        ->when($request->search_text && $request->criteria == 'all', function($q) use($request) { 
                            return $q->where(function($q1) use($request) {
                                return $q1->where('employee_demo.employee_id', 'LIKE', "%{$request->search_text}%")
                                ->orWhere('employee_demo.employee_name', 'LIKE', "%{$request->search_text}%")
                                ->orWhere('employee_demo.classification_group', 'LIKE', "%{$request->search_text}%")
                                ->orWhere('employee_demo.deptid', 'LIKE', "%{$request->search_text}%"); 
                            });
                        });
                });
            });    
        return $sql;
    }

}
