<?php

namespace App\Http\Controllers\SysAdmin;

use App\Models\Goal;
use App\Models\User;
use App\Models\GoalType;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\ConversationTopic;
use Illuminate\Support\Facades\DB;
use App\Exports\ConversationExport;
use App\Exports\UserGoalCountExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SharedEmployeeExport;
use App\Exports\ExcusedEmployeeExport;

class SysadminStatisticsReportController extends Controller
{
    //
    private $groups;
    private $overdue_groups;

    
    public function __construct()
    {
        $this->groups = [
            '0' => [0,0],
            '1-5' => [1,5],
            '6-10' => [6,10],
            '>10' => [11,99999],
        ];

        $this->overdue_groups = [
            'overdue' => [-999999,0],
            '< 1 week' => [1,7],
            '< 1 month' => [8,30],
            '> 1 month' => [31,999999],
        ];

        set_time_limit(120);    // 3 mins

    }

    Public function goalSummary_from_statement($goal_type_id)
    {
        $from_stmt = "(select users.employee_id, users.empl_record, (select count(*) from goals where user_id = users.id
                        and status = 'active' and deleted_at is null and is_library = 0 ";
        if ($goal_type_id)                        
            $from_stmt .= " and goal_type_id =".  $goal_type_id ;
        $from_stmt .= ") as goals_count from users ) AS A";

        return $from_stmt;
    }

    public function goalSummary(Request $request)
    {
       
        $types = GoalType::orderBy('id')->get();
        $types->prepend( new GoalType()  ) ;

        foreach($types as $type)
        {
            $goal_id = $type->id ? $type->id : '';

            $from_stmt = $this->goalSummary_from_statement($type->id);

            $sql = User::selectRaw('AVG(goals_count) as goals_average')
                          ->from(DB::raw( $from_stmt ))
                          ->join('employee_demo', function($join) {
                                $join->on('employee_demo.employee_id', '=', 'A.employee_id');
                                $join->on('employee_demo.empl_record', '=', 'A.empl_record');
                            });

            $goals_average = $sql->get()->first()->goals_average;

            $data[$goal_id] = [ 
                'name' => $type->name ? ' ' . $type->name : '',
                'goal_type_id' => $goal_id,
                'average' =>  $goals_average, 
                'groups' => []
            ];

            // each group 
            foreach($this->groups as $key => $range)
            {
                
                $sql = User::selectRaw('count(goals_count) as goals_count')
                        ->from(DB::raw( $from_stmt ))
                        ->join('employee_demo', function($join) {
                            $join->on('employee_demo.employee_id', '=', 'A.employee_id');
                            $join->on('employee_demo.empl_record', '=', 'A.empl_record');
                        })
                        // ->where('acctlock', 0)
                        ->whereBetween('goals_count', $range);

                $goals_count = $sql->get()->first()->goals_count;

                array_push( $data[$goal_id]['groups'], [ 'name' => $key, 'value' => $goals_count, 
                             'goal_id' => $goal_id, 
                             //'ids' =>  $subset ? $subset->pluck('id')->toArray() : [],
                ]);
            }

        }

        return view('sysadmin.statistics.goalsummary',compact('data'));
    }

    public function goalSummaryExport(Request $request)
    {

        $from_stmt = $this->goalSummary_from_statement($request->goal);

        $sql = User::selectRaw('A.*, goals_count, employee_name, 
                                organization, level1_program, level2_division, level3_branch, level4')
                ->from(DB::raw( $from_stmt ))                                
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'A.employee_id');
                    $join->on('employee_demo.empl_record', '=', 'A.empl_record');
                })
                ->whereNotNull('A.guid')
                // ->where('acctlock', 0)
                ->when( (array_key_exists($request->range, $this->groups)) , function($q) use($request) {
                    return $q->whereBetween('goals_count', $this->groups[$request->range]);
                });

        $users = $sql->get();

      
        // Generating Output file 
        $filename = 'Active Goals Per Employee.csv';
        if ($request->goal) {        
            $type = GoalType::where('id', $request->goal)->first();
            $filename = 'Active ' . ($type ? $type->name . ' ' : '') . 'Goals Per Employee.csv';
        }

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ["Employee ID", "Name", "Email", 'Active Goals Count', 
                        "Organization", "Level 1", "Level 2", "Level 3", "Level 4", "Reporting To",
                    ];

        $callback = function() use($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['Employee ID'] = $user->employee_id;
                $row['Name'] = $user->employee_name;
                $row['Email'] = $user->email;
                $row['Active Goals Count'] = $user->goals_count;
                $row['Organization'] = $user->organization;
                $row['Level 1'] = $user->level1_program;
                $row['Level 2'] = $user->level2_division;
                $row['Level 3'] = $user->level3_branch;
                $row['Level 4'] = $user->level4;
                $row['Reporting To'] = $user->reportingManager ? $user->reportingManager->name : '';

                fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Active Goals Count'], $row['Organization'],
                            $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], $row['Reporting To'] ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    }

    public function conversationSummary(Request $request)
    {

        // Chart1 -- Overdue
        $sql_2 = User::selectRaw("users.employee_id, users.empl_record, employee_name, 
                            organization, level1_program, level2_division, level3_branch, level4,
                        DATEDIFF (
                            COALESCE (
                                (select GREATEST( max(sign_off_time) , max(supervisor_signoff_time) )  
                                    from conversations A 
                                where A.user_id = users.id
                                    and signoff_user_id is not null      
                                    and supervisor_signoff_id is not null)
                                , (joining_date) 
                            ) 
                        , DATE_ADD( DATE_FORMAT(sysdate(), '%Y-%m-%d'), INTERVAL -122 day) )
                    as overdue_in_days")
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                    $join->on('employee_demo.empl_record', '=', 'users.empl_record');
                });
            
        $next_due_users = $sql_2->get();
        $data = array();

        // Chart1 -- Overdue
        $data['chart1']['chart_id'] = 1;
        $data['chart1']['title'] = 'Next Conversation Due';
        $data['chart1']['legend'] = array_keys($this->overdue_groups);
        $data['chart1']['groups'] = array();
        foreach($this->overdue_groups as $key => $range)
        {
            $subset = $next_due_users->whereBetween('overdue_in_days', $range );
            array_push( $data['chart1']['groups'],  [ 'name' => $key, 'value' => $subset->count(), 
                            // 'ids' => $subset ? $subset->pluck('id')->toArray() : [] 
                        ]);
        }

        // SQL for Chart 2
        $sql = Conversation::join('users', 'users.id', 'conversations.user_id') 
        ->join('employee_demo', function($join) {
            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
            $join->on('employee_demo.empl_record', '=', 'users.empl_record');
        })
        ->where(function ($query)  {
            return $query->whereNull('signoff_user_id')
                        ->orWhereNull('supervisor_signoff_id');
        })
        ->whereRaw("DATEDIFF (
                    COALESCE (
                            (select GREATEST( max(sign_off_time) , max(supervisor_signoff_time) )  
                                from conversations A 
                            where A.user_id = conversations.user_id
                                and signoff_user_id is not null      
                                and supervisor_signoff_id is not null),
                            (select joining_date from users where id = conversations.user_id)
                        ) 
                , DATE_ADD( DATE_FORMAT(sysdate(), '%Y-%m-%d'), INTERVAL -122 day) ) < 0 ");

        $conversations = $sql->get();

        // Chart2 -- Open Conversation
        $topics = ConversationTopic::select('id','name')->get();
        $data['chart2']['chart_id'] = 2;
        $data['chart2']['title'] = 'Topic: Open Conversations';
        $data['chart2']['legend'] = $topics->pluck('name')->toArray();
        $data['chart2']['groups'] = array();

        $open_conversations = $conversations;
        foreach($topics as $topic)
        {
            $subset = $open_conversations->where('conversation_topic_id', $topic->id );
            array_push( $data['chart2']['groups'],  [ 'name' => $topic->name, 'value' => $subset->count(),
                        'topic_id' => $topic->id,
                            // 'ids' => $subset ? $subset->pluck('id')->toArray() : []
                        ]);
        }    

        // Chart 3 -- Completed Conversation by Topics
        $data['chart3']['chart_id'] = 3;
        $data['chart3']['title'] = 'Topic: Completed Conversations';
        $data['chart3']['legend'] = $topics->pluck('name')->toArray();
        $data['chart3']['groups'] = array();

        // SQL for Chart 3
        $completed_conversations = Conversation::where(function ($query)  {
                return $query->whereNotNull('signoff_user_id')
                             ->whereNotNull('supervisor_signoff_id');
        })
        ->join('users', 'users.id', 'conversations.user_id') 
        ->join('employee_demo', function($join) {
            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
            $join->on('employee_demo.empl_record', '=', 'users.empl_record');
        })
        ->get();

        foreach($topics as $topic)
        {
            $subset = $completed_conversations->where('conversation_topic_id', $topic->id );
            array_push( $data['chart3']['groups'],  [ 'name' => $topic->name, 'value' => $subset->count(), 
                    'topic_id' => $topic->id,
                    // 'ids' => $subset ? $subset->pluck('id')->toArray() : []
                ]);
        }    

        return view('sysadmin.statistics.conversationsummary',compact('data'));

    }


    public function conversationSummaryExport(Request $request)
    {
       
        // SQL - Chart 1
        $sql_chart1 = User::selectRaw("users.*, employee_name, 
                        organization, level1_program, level2_division, level3_branch, level4,
                    DATEDIFF (
                            COALESCE (
                                (select GREATEST( max(sign_off_time) , max(supervisor_signoff_time) )  
                                    from conversations A 
                                where A.user_id = users.id
                                    and signoff_user_id is not null      
                                    and supervisor_signoff_id is not null)
                                , (joining_date) 
                            ) 
                    , DATE_ADD( DATE_FORMAT(sysdate(), '%Y-%m-%d'), INTERVAL -122 day) )
                    as overdue_in_days,
                    DATE_ADD( DATE_FORMAT( COALESCE (                       
                       (select GREATEST( max(sign_off_time) , max(supervisor_signoff_time) )
                            from conversations A
                        where A.user_id = users.id 
                            and signoff_user_id is not null 
                            and supervisor_signoff_id is not null)
                            ,joining_date), '%Y-%m-%d'), INTERVAL 122 day) as next_due_date")
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                    $join->on('employee_demo.empl_record', '=', 'users.empl_record');
                });
                
        // SQL - Chart 2
        $sql_chart2 = Conversation::selectRaw("conversations.*, users.employee_id, employee_name, 
                        organization, level1_program, level2_division, level3_branch, level4,
                    DATE_ADD(
                        COALESCE (
                            (select GREATEST( max(sign_off_time) , max(supervisor_signoff_time) )  
                                from conversations A 
                            where A.user_id = conversations.user_id
                                and signoff_user_id is not null      
                                and supervisor_signoff_id is not null), 
                            (select joining_date from users where id = conversations.user_id)
                        ), INTERVAL 122 day)
                    as next_due_date")
                // ->whereIn('id', $selected_ids)
                ->whereRaw("DATEDIFF (
                    COALESCE (
                            (select GREATEST( max(sign_off_time) , max(supervisor_signoff_time) )  
                                from conversations A 
                            where A.user_id = conversations.user_id
                                and signoff_user_id is not null      
                                and supervisor_signoff_id is not null),
                            (select joining_date from users where id = conversations.user_id)
                        ) 
                , DATE_ADD( DATE_FORMAT(sysdate(), '%Y-%m-%d'), INTERVAL -122 day) ) < 0 ")
                ->where(function ($query)  {
                    return $query->whereNull('signoff_user_id')
                                 ->orwhereNull('supervisor_signoff_id');
                })
                ->join('users', 'users.id', 'conversations.user_id') 
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                    $join->on('employee_demo.empl_record', '=', 'users.empl_record');
                })
                ->when( $request->topic_id, function($q) use($request) {
                    $q->where('conversations.conversation_topic_id', $request->topic_id);
                }) 
                ->with('topic:id,name')
                ->with('signoff_user:id,name')
                ->with('signoff_supervisor:id,name');

         // SQL for Chart 3
         $sql_chart3 = Conversation::selectRaw("conversations.*, users.employee_id, employee_name, 
                    organization, level1_program, level2_division, level3_branch, level4,
                    DATE_ADD(
                        COALESCE (
                            (select GREATEST( max(sign_off_time) , max(supervisor_signoff_time) )  
                                from conversations A 
                            where A.user_id = conversations.user_id
                                and signoff_user_id is not null      
                                and supervisor_signoff_id is not null), 
                            (select joining_date from users where id = conversations.user_id)
                        ), INTERVAL 122 day)
                    as next_due_date")
            ->join('users', 'users.id', 'conversations.user_id') 
            ->join('employee_demo', function($join) {
                $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                $join->on('employee_demo.empl_record', '=', 'users.empl_record');
            })
            ->where(function ($query)  {
                return $query->whereNotNull('signoff_user_id')
                             ->whereNotNull('supervisor_signoff_id');
            })
            ->when( $request->topic_id, function($q) use($request) {
                $q->where('conversations.conversation_topic_id', $request->topic_id);
            }) 
            ->with('topic:id,name')
            ->with('signoff_user:id,name')
            ->with('signoff_supervisor:id,name')
            ;


        // Generating Output file 
        $filename = 'Conversations.xlsx';
        switch ($request->chart) {
            case 1:

                $filename = 'Next Conversation Due.csv';
                // $data = $next_due_users;
                $users =  $sql_chart1->get();

                if (array_key_exists($request->range, $this->overdue_groups) ) {
                    $users = $users->whereBetween('overdue_in_days', $this->overdue_groups[$request->range]);  
                }
        
                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );
        
                $columns = ["Employee ID", "Employee Name", "Email",
                                "Next Conversation Due",
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", "Reporting To",
                           ];
        
                $callback = function() use($users, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
        
                    foreach ($users as $user) {
                        $row['Employee ID'] = $user->employee_id;
                        $row['Name'] = $user->employee_name;
                        $row['Email'] = $user->email;
                        $row['Next Conversation Due'] = $user->next_due_date;
                        $row['Organization'] = $user->organization;
                        $row['Level 1'] = $user->level1_program;
                        $row['Level 2'] = $user->level2_division;
                        $row['Level 3'] = $user->level3_branch;
                        $row['Level 4'] = $user->level4;
                        $row['Reporting To'] = $user->reportingManager ? $user->reportingManager->name : '';
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Next Conversation Due'], $row['Organization'],
                                    $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], $row['Reporting To'] ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;

            case 2:

                $filename = 'Open Conversation By Topic.csv';
                $conversations =  $sql_chart2->get();
        
                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );
        
                $columns = ["Employee ID", "Employee Name", "Email",
                        "Conversation Due Date",
                            "Conversation Participant", "Employee Sign-Off", "Supervisor Sign-off", 
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", 
                           ];
        
                $callback = function() use($conversations, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
        
                    foreach ($conversations as $conversation) {
                        $row['Employee ID'] = $conversation->employee_id;
                        $row['Name'] = $conversation->employee_name;
                        $row['Email'] = $conversation->email;
                        $row['Conversation Due Date'] = $conversation->next_due_date;
                        $row['Conversation Participant'] = implode(', ', $conversation->conversationParticipants->pluck('participant.name')->toArray() );
                        $row['Employee Sign-Off'] = $conversation->signoff_user  ? $conversation->signoff_user->name : '';
                        $row['Supervisor Sign-off'] = $conversation->signoff_supervisor ? $conversation->signoff_supervisor->name : '';
                        $row['Organization'] = $conversation->organization;
                        $row['Level 1'] = $conversation->level1_program;
                        $row['Level 2'] = $conversation->level2_division;
                        $row['Level 3'] = $conversation->level3_branch;
                        $row['Level 4'] = $conversation->level4;
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], // $row['Next Conversation Due'],
                        $row['Conversation Due Date'], $row["Conversation Participant"],
                        $row["Employee Sign-Off"], $row["Supervisor Sign-off"],
                                 $row['Organization'],
                                  $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], 
                                ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);


                break;

            case 3:

                $filename = 'Completed Conversation By Topic.csv';
                $conversations =  $sql_chart3->get();

                if (array_key_exists($request->range, $this->overdue_groups) ) {
                    $users = $users->whereBetween('overdue_in_days', $this->overdue_groups[$request->range]);  
                }
        
                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );
        
                $columns = ["Employee ID", "Employee Name", "Email",
                        "Conversation Due Date",
                            "Conversation Participant", "Employee Sign-Off", "Supervisor Sign-off", 
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", 
                           ];
        
                $callback = function() use($conversations, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
        
                    foreach ($conversations as $conversation) {
                        $row['Employee ID'] = $conversation->employee_id;
                        $row['Name'] = $conversation->employee_name;
                        $row['Email'] = $conversation->email;
                        $row['Conversation Due Date'] = $conversation->next_due_date;
                        $row['Conversation Participant'] = implode(', ', $conversation->conversationParticipants->pluck('participant.name')->toArray() );
                        $row['Employee Sign-Off'] = $conversation->signoff_user  ? $conversation->signoff_user->name : '';
                        $row['Supervisor Sign-off'] = $conversation->signoff_supervisor ? $conversation->signoff_supervisor->name : '';
                        $row['Organization'] = $conversation->organization;
                        $row['Level 1'] = $conversation->level1_program;
                        $row['Level 2'] = $conversation->level2_division;
                        $row['Level 3'] = $conversation->level3_branch;
                        $row['Level 4'] = $conversation->level4;
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], // $row['Next Conversation Due'],
                        $row['Conversation Due Date'], $row["Conversation Participant"],
                        $row["Employee Sign-Off"], $row["Supervisor Sign-off"],
                                 $row['Organization'],
                                  $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], 
                                ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;    
        }
        
    }

    public function sharedsummary(Request $request) 
    {

        $sql = User::selectRaw("users.employee_id, users.empl_record,
                case when (select count(*) from shared_profiles A where A.shared_id = users.id) > 0 then 'Yes' else 'No' end as shared")
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                    $join->on('employee_demo.empl_record', '=', 'users.empl_record');
                });

        $users = $sql->get();

        // Chart 1 
        $legends = ['Yes', 'No'];
        $data['chart1']['chart_id'] = 1;
        $data['chart1']['title'] = 'Shared Status';
        $data['chart1']['legend'] = $legends;
        $data['chart1']['groups'] = array();

        foreach($legends as $legend)
        {
            $subset = $users->where('shared', $legend);
            array_push( $data['chart1']['groups'],  [ 'name' => $legend, 'value' => $subset->count(),
                            // 'ids' => $subset ? $subset->pluck('id')->toArray() : []
                            'legend' => $legend, 
                        ]);
        }    

        return view('sysadmin.statistics.sharedsummary',compact('data'));

    } 

    public function sharedSummaryExport(Request $request) 
    {

      $selected_ids = $request->ids ? explode(',', $request->ids) : [];

      $sql = User::selectRaw("users.*,
                employee_name, organization, level1_program, level2_division, level3_branch, level4,
            case when (select count(*) from shared_profiles A where A.shared_id = users.id) > 0 then 'Yes' else 'No' end as shared")
            ->join('employee_demo', function($join) {
                $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                $join->on('employee_demo.empl_record', '=', 'users.empl_record');
            })
            ->when( $request->legend == 'Yes', function($q) use($request) {
                $q->whereRaw(" (select count(*) from shared_profiles A where A.shared_id = users.id) > 0 ");
            }) 
            ->when( $request->legend == 'No', function($q) use($request) {
                $q->whereRaw(" (select count(*) from shared_profiles A where A.shared_id = users.id) = 0 ");
            }) 
            ->with('sharedWith');

      $users = $sql->get();

      // Generating output file 
      $filename = 'Shared Employees.csv';
      
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ["Employee ID", "Name", "Email", 'Shared', 'Shared with',
                        "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
                    ];

        $callback = function() use($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['Employee ID'] = $user->employee_id;
                $row['Name'] = $user->employee_name;
                $row['Email'] = $user->email;
                $row['Shared'] = $user->shared;
                $row['Shared with'] = implode(', ', $user->sharedWith->map( function ($item, $key) { return $item ? $item->sharedWith->name : null; })->toArray() );
                $row['Organization'] = $user->organization;
                $row['Level 1'] = $user->level1_program;
                $row['Level 2'] = $user->level2_division;
                $row['Level 3'] = $user->level3_branch;
                $row['Level 4'] = $user->level4;

                fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], 
                        $row['Shared'], $row['Shared with'],
                        $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    }


    public function excusedsummary(Request $request) {

        
        $sql = User::selectRaw("users.employee_id, users.empl_record, 
                    employee_name, organization, level1_program, level2_division, level3_branch, level4,
                    case when date(SYSDATE()) between excused_start_date and excused_end_date then 'Yes' else 'No' end as excused")
                    ->join('employee_demo', function($join) {
                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                        $join->on('employee_demo.empl_record', '=', 'users.empl_record');
                    });

        $users = $sql->get();

        // Chart1 -- Excuse 
        $legends = ['Yes', 'No'];
        $data['chart1']['chart_id'] = 1;
        $data['chart1']['title'] = 'Excused Status';
        $data['chart1']['legend'] = $legends;
        $data['chart1']['groups'] = array();


        foreach($legends as $legend)
        {
            $subset = $users->where('excused', $legend);
            array_push( $data['chart1']['groups'],  [ 'name' => $legend, 'value' => $subset->count(),
                            'legend' => $legend, 
                            // 'ids' => $subset ? $subset->pluck('id')->toArray() : []
                        ]);
        }    

        return view('sysadmin.statistics.excusedsummary',compact('data'));


    } 


    public function excusedSummaryExport(Request $request) {

      $selected_ids = $request->ids ? explode(',', $request->ids) : [];

      $sql = User::selectRaw("users.*, 
                    employee_name, organization, level1_program, level2_division, level3_branch, level4,
                    case when date(SYSDATE()) between excused_start_date and excused_end_date then 'Yes' else 'No' end as excused")
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                    $join->on('employee_demo.empl_record', '=', 'users.empl_record');
                })
                ->when( $request->legend == 'Yes', function($q) use($request) {
                    $q->whereRaw(" ( date(SYSDATE()) between excused_start_date and excused_end_date) = TRUE ");
                }) 
                ->when( $request->legend == 'No', function($q) use($request) {
                    $q->whereRaw(" ( date(SYSDATE()) between excused_start_date and excused_end_date) = FALSE ");
                })
                ->with('excuseReason') ;

        $users = $sql->get();

      // Generating Output file
        $filename = 'Excused Employees.csv';
      
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ["Employee ID", "Name", "Email", 
                        "Excused", "Excused Start Date", "Excused End Date", "Excused Reason",
                        "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
                    ];

        $callback = function() use($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['Employee ID'] = $user->employee_id;
                $row['Name'] = $user->employee_name;
                $row['Email'] = $user->email;

                $row['Excused'] = $user->excused;
                $row['Excused Start Date'] = $user->excused_start_date;
                $row['Excused End Date'] = $user->excused_end_date;
                $row['Excused Reason'] = $user->excuseReason ? $user->excuseReason->name : '';
                $row['Shared'] = $user->shared;
                $row['Shared with'] = implode(', ', $user->sharedWith->map( function ($item, $key) { return $item ? $item->sharedWith->name : null; })->toArray() );
                $row['Organization'] = $user->organization;
                $row['Level 1'] = $user->level1_program;
                $row['Level 2'] = $user->level2_division;
                $row['Level 3'] = $user->level3_branch;
                $row['Level 4'] = $user->level4;

                fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], 
                        $row['Excused'], $row['Excused Start Date'], $row['Excused End Date'], $row['Excused Reason'],
                        $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    }
    
}
