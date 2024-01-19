<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Goal;
use App\Models\SharedProfile;
use App\Models\UserDemoJrView;
use App\Models\Position;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;

class LoadTestController extends Controller
{
    public function loadTest()
    {
        return view('load-test');
    }
    
    public function simulateLoad(Request $request)
    {
        $selected_query = $request->input('queries', 'employee');
        
        $numberOfUsers = $request->input('number_of_users', 1); // Default to 1 users

        $limit = $request->input('limit', 100); // Default to 100 rows

        $startTime = microtime(true);

        echo ucfirst($selected_query) . " query result: ";
        // Run a loop to simulate user creation
        for ($i = 0; $i < $numberOfUsers; $i++) {
            if($selected_query == 'employee') {                
                $users = User::where('excused_flag', '<>', '1')
                        ->take($limit)
                        ->get();
            } else if($selected_query == 'goal') {
                $total_goals = UserDemoJrView::selectRaw('count(*) as goal_count, goals.goal_type_id')
                                ->join('goals', 'goals.user_id', 'user_demo_jr_view.user_id') 
                                ->join('goal_types', 'goals.goal_type_id', 'goal_types.id')
                                ->where(function($query) {
                                    $query->where(function($query) {
                                        $query->where('due_date_paused', 'N')
                                            ->orWhereNull('due_date_paused');
                                    });
                                })
                                ->where(function($query) {
                                    $query->where(function($query) {
                                        $query->where('excused_flag', '<>', '1')
                                            ->orWhereNull('excused_flag');
                                    });
                                })
                                ->whereNull('user_demo_jr_view.date_deleted')
                                ->whereNull('goals.deleted_at')
                                ->where('goals.status','active')
                                ->where('goals.is_library','0')  
                                ->groupBy('goals.goal_type_id')
                                ->get();   
            } else if ($selected_query == 'conversation'){
                $employee_topic_query = UserDemoJrView::selectRaw("employee_id, empl_record, employee_name, 
                                organization, level1_program, level2_division,
                                level3_branch, level4,conversation_participants.role,
                                conversations.deleted_at,conversation_participants.conversation_id,
                                conversations.signoff_user_id,conversations.supervisor_signoff_id,
                                conversation_participants.participant_id,conversations.conversation_topic_id,
                        DATEDIFF ( next_conversation_date
                            , curdate() )
                    as overdue_in_days")
                ->leftJoin('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id')->where('conversation_participants.role','emp');
                })
                ->leftJoin('conversations', function($join){
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
                })        
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('due_date_paused', 'N')
                            ->orWhereNull('due_date_paused');
                    });
                })
                ->whereNull('date_deleted')
                ->whereNull('deleted_at')
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('excused_flag', '<>', '1')
                            ->orWhereNull('excused_flag');
                    });
                })
                ->get();   
            }
            
        }

        $endTime = microtime(true);

        $queryTime = $endTime - $startTime;

        // Add feedback about query time, CPU usage, etc.
        $feedback = [
            'query_time' => $queryTime,
            // Add more metrics as needed
        ];

        return response()->json($feedback);
    }
}
