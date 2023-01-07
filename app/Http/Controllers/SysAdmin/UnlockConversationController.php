<?php

namespace App\Http\Controllers\SysAdmin;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\OrganizationTree;
use Yajra\Datatables\Datatables;
use App\Models\ConversationTopic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UnlockConversationController extends Controller
{
    //
    public function index(Request $request) {

        //if($request->ajax())
        $errors = session('errors');

        // $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        if ($errors) {
            $old = session()->getOldInput();

            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;

            // $request->job_titles = isset($old['job_titles']) ? $old['job_titles'] : null;
            $request->topic_id = isset($old['topic_id']) ? $old['topic_id'] : null;
            //$request->active_since = isset($old['active_since']) ? $old['active_since'] : null;
            $request->completion_date_from = isset($old['completion_date_from']) ? $old['completion_date_from'] : null;
            $request->completion_date_to = isset($old['completion_date_to']) ? $old['completion_date_to'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
            
        } 

        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                // 'job_titles' => $request->job_titles,
                'topic_id' => $request->topic_id,
                //'active_since' => $request->active_since,
                'completion_date_from' => $request->completion_date_from,
                'completion_date_to' => $request->completion_date_to,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
            ]);
        }

        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
        // $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
        //             ->groupBy('job_title')->pluck('job_title') : null;

        $request->session()->flash('level0', $level0);
        $request->session()->flash('level1', $level1);
        $request->session()->flash('level2', $level2);
        $request->session()->flash('level3', $level3);
        $request->session()->flash('level4', $level4);
        //$request->session()->flash('job_titles', $job_titles);

        
        $criteriaList = $this->search_criteria_list();
        $topicList = ConversationTopic::orderBy('name')->get();
        $type = 'past';

        return view('sysadmin.unlock.unlockconversation', compact('criteriaList', 'topicList', 'request', 'type'));

    }


    public function indexManageUnlocked(Request $request) {

        //if($request->ajax())
        $errors = session('errors');

        // $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        if ($errors) {
            $old = session()->getOldInput();

            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;

            $request->topic_id = isset($old['topic_id']) ? $old['topic_id'] : null;
            $request->due_date_from = isset($old['due_date_from']) ? $old['due_date_from'] : null;
            $request->due_date_to = isset($old['due_date_to']) ? $old['due_date_to'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
            
        } 

        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                // 'job_titles' => $request->job_titles,
                'topic_id' => $request->topic_id,
                //'active_since' => $request->active_since,
                'due_date_from' => $request->due_date_from,
                'due_date_to' => $request->due_date_to,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
            ]);
        }

        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
        // $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
        //             ->groupBy('job_title')->pluck('job_title') : null;

        $request->session()->flash('level0', $level0);
        $request->session()->flash('level1', $level1);
        $request->session()->flash('level2', $level2);
        $request->session()->flash('level3', $level3);
        $request->session()->flash('level4', $level4);
        //$request->session()->flash('job_titles', $job_titles);

        
        $criteriaList = $this->search_criteria_list();
        $topicList = ConversationTopic::orderBy('name')->get();
        $type = 'past';

        return view('sysadmin.unlock.manageunlocked', compact('criteriaList', 'topicList', 'request', 'type'));

    }



    public function getDatatableConversations(Request $request) {

        if($request->ajax()){

            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
            // $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
            //             ->groupBy('job_title')->pluck('job_title') : null;
    
            $sql = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);

            $conversations = $sql->where(function($q) use ($request) {
                // $q->whereNotNull('initial_signoff');
                $q->whereNotNull('signoff_user_id')
                    ->whereNotNull('supervisor_signoff_id');
            })->where(function($q) use ($request) {
                $q->whereNull('conversations.unlock_until')
                    ->orWhere('conversations.unlock_until','<', today() );
            });

            return Datatables::of($conversations)
                ->addColumn('topic', function ($conversation) {
                    return $conversation->topic->name;
                })
                ->addColumn('participants', function ($conversation) {
                    $userIds = $conversation->conversationParticipants()->pluck('participant_id')->toArray();
                    $users = User::whereIn('id', $userIds)->pluck('name');
                     return implode('; ', $users->toArray() );
                })
                ->addColumn('completed_date', function ($conversation) {
                     return max($conversation->sign_off_time, $conversation->supervisor_signoff_time);
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
                    //$icon = $conversation->getIsLockedAttribute() ? 'fa-unlock-alt' : 'fa-lock';
                    // return '<a href="#" class="unlock-modal btn btn-sm btn-primary" data-id="'. 
                    //     $conversation->id .'" unlock-until="'. $conversation->unlock_until .'"><i class="fa ' . $icon . '" aria-hidden="true"></i></a>';
                    return '<a  class="btn btn-sm btn-secondary" data-id="'. 
                         $conversation->id .'" unlock-until="'. $conversation->unlock_until .'"><i class="fa ' . $icon . '" aria-hidden="true"></i></a>';


                })
                ->rawColumns(['unlock', 'action'])
                ->make(true);
        }
    }



    public function getDatatableManagedUnlocked(Request $request) {

        if($request->ajax()){

            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
            // $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
            //             ->groupBy('job_title')->pluck('job_title') : null;
    
            $sql = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4);
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
                // ->addColumn('due_date', function ($conversation) {
                //      return max($conversation->sign_off_time, $conversation->supervisor_signoff_time);
                // })
                ->addColumn('action', function ($conversation) {
                    return '<button class="btn btn-primary btn-sm  ml-2 btn-view-conversation" '.
                    'data-id="'. $conversation->id . '" data-toggle="modal" data-target="#viewConversationModal">View</button>' .
                    '<button class="btn btn-primary btn-sm ml-2 unlock-modal" data-id="'. $conversation->id . '" unlock-until="' .
                     $conversation->unlock_until . '">Modify</button>';
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
            'unlock_until'   => 'required|date|after:yesterday',
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
            'emp' => 'Employee ID', 
            'name'=> 'Employee Name',
            'cls' => 'Classification', 
            'dpt' => 'Department ID'
        ];
    }

    // public function getTopics() {

    //     $rows = ConversationTopic::select('id','name')->orderBy('name')->get();

    //     $formatted_data = [];
    //        foreach ($rows as $item) {
    //            $formatted_data[] = ['id' => $item->id, 'text' => $item->name ];
    //     }
   
    //     return response()->json($formatted_data);

    // }


    protected function baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4) 
    {

        // 
        $sql = Conversation::when( $request->topic_id , function ($q) use($request) {
                return $q->where('conversations.conversation_topic_id', $request->topic_id);
             })
            ->when( $request->completion_date_from, function ($q) use($request) {
                return $q->where('conversations.sign_off_time', '>=', $request->completion_date_from)
                        ->orWhere('conversations.supervisor_signoff_time', '>=', $request->completion_date_from);
             })
             ->when( $request->completion_date_to, function ($q) use($request) {
                return $q->where('conversations.sign_off_time', '<=', $request->completion_date_to)
                        ->orWhere('conversations.supervisor_signoff_time', '<=', $request->completion_date_to);
             })
             ->when( $request->due_date_from, function ($q) use($request) {
                return $q->where('conversations.unlock_until', '>=', $request->due_date_from);
             })
             ->when( $request->due_date_to, function ($q) use($request) {
                return $q->where('conversations.unlock_until', '<=', $request->due_date_to);
             })
             ->when( $level0 or $level1 or $level2 or $level3 or $level4 or $request->search_text, function ($q) 
                        use($request, $level0, $level1, $level2, $level3, $level4) {

                return $q->whereIn('id', function($q) use ($request, $level0, $level1, $level2, $level3, $level4) {
                    $q->select('conversation_id')->from('conversation_participants')
                    ->join('users', 'users.id', 'conversation_participants.participant_id')
                    ->whereIn('users.guid', function($q) use ($request, $level0, $level1, $level2, $level3, $level4) {
                        $q->select('guid')->from('employee_demo')
                            ->when( $level0, function ($q) use($level0) {
                                return $q->where('employee_demo.organization', $level0->name);
                            })
                            ->when( $level1, function ($q) use($level1) {
                                return $q->where('employee_demo.level1_program', $level1->name);
                            })
                            ->when( $level2, function ($q) use($level2) {
                                return $q->where('employee_demo.level2_division', $level2->name);
                            })
                            ->when( $level3, function ($q) use($level3) {
                                return $q->where('employee_demo.level3_branch', $level3->name);
                            })
                            ->when( $level4, function ($q) use($level4) {
                                return $q->where('employee_demo.level4', $level4->name);
                            })
                            ->when( $request->search_text && $request->criteria == 'all', function ($q) use($request) {
                                $q->where(function($query) use ($request) {
                                    
                                    return $query->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->search_text) . "%'")
                                        ->orWhereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->search_text) . "%'")
                                        ->orWhereRaw("LOWER(employee_demo.classification_group) LIKE '%" . strtolower($request->search_text) . "%'")
                                        ->orWhereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->search_text) . "%'");
                                });
                            })
                            ->when( $request->search_text && $request->criteria == 'emp', function ($q) use($request) {
                                return $q->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->search_text) . "%'");
                            })
                            ->when( $request->search_text && $request->criteria == 'name', function ($q) use($request) {
                                return $q->whereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->search_text) . "%'");
                            })
                            ->when( $request->search_text && $request->criteria == 'cls', function ($q) use($request) {
                                return $q->whereRaw("LOWER(employee_demo.classification_group) LIKE '%" . strtolower($request->search_text) . "%'");
                            })
                            ->when( $request->search_text && $request->criteria == 'dpt', function ($q) use($request) {
                                return $q->whereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->search_text) . "%'");
                            });
                });
            });    
        });
            
        return $sql;
    }

}
