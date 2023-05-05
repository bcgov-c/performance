<?php

namespace App\Http\Controllers\HRAdmin;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Goal;
use App\Models\User;
use App\Models\GoalType;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\UserDemoJrView;
use Illuminate\Http\Request;
use App\Models\OrganizationTree;
use App\Models\ConversationTopic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\ConversationExport;
use App\Exports\UserGoalCountExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SharedEmployeeExport;
use App\Exports\ExcusedEmployeeExport;

class StatisticsReportController extends Controller
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
            '1 week to 1 month' => [8,30],
            '> 1 month' => [31,999999],
        ];

        set_time_limit(120);    // 3 mins

    }

    Public function goalSummary_from_statement($goal_type_id)
    {
        $from_stmt = "(select users.id, users.email, users.employee_id, users.empl_record, users.guid, users.reporting_to, 
                        users.excused_start_date, users.excused_end_date, users.due_date_paused,
                        (select count(*) from goals where user_id = users.id
                        and status = 'active' and deleted_at is null and is_library = 0 ";
        if ($goal_type_id)                        
            $from_stmt .= " and goal_type_id =".  $goal_type_id ;
        $from_stmt .= ") as goals_count from users ) AS A";

        return $from_stmt;
    }

    public function goalSummary(Request $request) {
       
        // send back the input parameters
        $this->preservedInputParams($request);

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);

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
                        })
                        ->where('A.due_date_paused', 'N')
                        ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                        ->whereNull('employee_demo.date_deleted')        
                        ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                        ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                        ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                        ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                        ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                        ->whereExists(function ($query) {
                            return $query->select(DB::raw(1))
                                    ->from('admin_org_users')
                                    ->whereColumn('admin_org_users.allowed_user_id', 'A.id')
                                    ->whereIn('admin_org_users.access_type', [0, 1])
                                    ->where('admin_org_users.granted_to_id', '=', Auth::id());
                        });

            $goals_average = $sql->get()->first()->goals_average;

            $data[$goal_id] = [ 
                'name' => $type->name ? ' ' . $type->name : '',
                'goal_type_id' => $goal_id,
                'average' =>  $goals_average, 
                'groups' => []
            ];

            $sql = User::selectRaw("case when goals_count between 0 and 0  then '0'  
                                    when goals_count between 1 and 5  then '1-5'
                                    when goals_count between 6 and 10 then '6-10'
                                    when goals_count  > 10            then '>10'
                            end AS group_key, count(*) as goals_count")
                ->from(DB::raw( $from_stmt ))
                ->groupBy('group_key')
                    ->from(DB::raw( $from_stmt ))
                    ->join('employee_demo', function($join) {
                        $join->on('employee_demo.employee_id', '=', 'A.employee_id');
                    })
                    ->where('A.due_date_paused', 'N')
                    ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                    ->whereNull('employee_demo.date_deleted')        
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                    ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                    ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                    ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                    ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                    ->whereExists(function ($query) {
                        return $query->select(DB::raw(1))
                                ->from('admin_org_users')
                                ->whereColumn('admin_org_users.allowed_user_id', 'A.id')
                                ->whereIn('admin_org_users.access_type', [0, 1])
                                ->where('admin_org_users.granted_to_id', '=', Auth::id());
                    });

                $goals_count_array = $sql->pluck( 'goals_count','group_key' )->toArray();

                foreach($this->groups as $key => $range) {
                    $goals_count = 0;
                    if (array_key_exists( $key, $goals_count_array)) {
                            $goals_count = $goals_count_array[$key];
                    }
    
                    array_push( $data[$goal_id]['groups'], [ 'name' => $key, 'value' => $goals_count, 
                        'goal_id' => $goal_id, 
                                                                                            
                    ]);
                }
        }

        // Goal Tag count 
        $count_raw = "id, name, ";
        $count_raw .= " (select count(*) from goal_tags, goals, users, employee_demo, employee_demo_tree ";
        $count_raw .= "   where goals.id = goal_tags.goal_id "; 
	    $count_raw .= "     and tag_id = tags.id ";  
        $count_raw .= "     and users.id = goals.user_id ";
        $count_raw .= "     and users.employee_id = employee_demo.employee_id ";
        $count_raw .= "     and employee_demo.orgid = employee_demo_tree.id ";
        $count_raw .= $request->dd_level0 ? "     and employee_demo_tree.organization_key = '{ $request->dd_level0 }'" : '';
        $count_raw .= $request->dd_level1 ? "     and employee_demo_tree.level1_key = '{ $request->dd_level1 }'" : '';
        $count_raw .= $request->dd_level2 ? "     and employee_demo_tree.level2_key = '{ $request->dd_level2 }'" : '';
        $count_raw .= $request->dd_level3 ? "     and employee_demo_tree.level3_key = '{ $request->dd_level3 }'" : '';
        $count_raw .= $request->dd_level4 ? "     and employee_demo_tree.level4_key = '{ $request->dd_level4 }'" : '';
        $count_raw .= "     and ( ";
        $count_raw .= "           users.due_date_paused = 'N' ";
        $count_raw .= "         )";
        $count_raw .= "     and exists (select 1 from admin_org_users ";
        $count_raw .= "                  where admin_org_users.allowed_user_id = users.id ";
        $count_raw .= "                    and admin_org_users.access_type in (0,1) ";
        $count_raw .= "                    and admin_org_users.granted_to_id = ".  Auth::id()  .") ";
        $count_raw .= ") as count";

        $sql = Tag::selectRaw($count_raw);
        $sql2 = Goal::join('users', function($join) {
                    $join->on('goals.user_id', '=', 'users.id');
                })
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->where('users.due_date_paused', 'N')                
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->whereNull('employee_demo.date_deleted')        
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('goal_tags')
                          ->whereColumn('goals.id', 'goal_tags.goal_id');
                })
                ->where('employee_demo.guid', '<>', '')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('admin_org_users')
                            ->whereColumn('admin_org_users.allowed_user_id', 'users.id')
                            ->whereIn('admin_org_users.access_type', [0,1])
                            ->where('admin_org_users.granted_to_id', '=', Auth::id());
                });

        $tags = $sql->get();
        $blank_count = $sql2->count();
        
        $data_tag = [ 
            'name' => 'Active Goal Tags',
            'labels' => [],
            'values' => [],
        ];

        // each group 
        array_push($data_tag['labels'], '[Blank]');  
        array_push($data_tag['values'], $blank_count);
        foreach($tags as $key => $tag)
        {
            array_push($data_tag['labels'], $tag->name);  
            array_push($data_tag['values'], $tag->count);
        }

        return view('hradmin.statistics.goalsummary',compact('data','data_tag'));
    }

    public function goalSummaryExport(Request $request) {

        $from_stmt = $this->goalSummary_from_statement($request->goal);

        $sql = User::selectRaw('A.*, goals_count, employee_demo.employee_name, 
        employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4')
                ->from(DB::raw( $from_stmt ))                                
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'A.employee_id');
                })
                ->where('A.due_date_paused', 'N')
                ->whereNotNull('A.guid')
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->whereNull('employee_demo.date_deleted')        
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->when( (array_key_exists($request->range, $this->groups)) , function($q) use($request) {
                    return $q->whereBetween('goals_count', $this->groups[$request->range]);
                })
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('admin_org_users')
                            ->whereColumn('admin_org_users.allowed_user_id', 'A.id')
                            ->whereIn('admin_org_users.access_type', [0,1])
                            ->where('admin_org_users.granted_to_id', '=', Auth::id());
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

    public function goalSummaryTagExport(Request $request) {

        $tags = Tag::when($request->tag, function ($q) use($request) {
                        return $q->where('name', $request->tag);
                    })
                    ->orderBy('name')->get();

        $count_raw = "users.*, ";
        $count_raw .= " employee_demo.employee_name, employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4";
        if (!$request->tag || $request->tag == '[Blank]') {
            $count_raw .= " ,(select count(*) from goals ";
            $count_raw .= "    where users.id = goals.user_id ";
            $count_raw .= "      and not exists (select 'x' from goal_tags ";
            $count_raw .= "                       where goals.id = goal_tags.goal_id) ";
            $count_raw .= "      and goals.deleted_at is null and goals.is_library = 0 ";            
            $count_raw .= "      and employee_demo.guid <> '' ";
            
            $count_raw .= "     and ( ";
            $count_raw .= "            users.due_date_paused = 'N' ";
            $count_raw .= "         )";
            
            $count_raw .= " ) as 'tag_0' ";
        }
        foreach ($tags as $tag) {
            $count_raw .= " ,(select count(*) from goal_tags, goals ";
            $count_raw .= "    where goals.id = goal_tags.goal_id "; 
            $count_raw .= "      and tag_id = " . $tag->id;  
            $count_raw .= "      and users.id = goals.user_id ";
            
            $count_raw .= "     and ( ";
            $count_raw .= "            users.due_date_paused = 'N' ";
            $count_raw .= "         )";
            
            $count_raw .= ") as 'tag_". $tag->id ."'";
        }

        $sql = User::selectRaw($count_raw)
                    ->join('employee_demo', function($join) {
                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                    })
                    ->where('users.due_date_paused', 'N')
                    ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                    ->whereNull('employee_demo.date_deleted')        
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                    ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                    ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                    ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                    ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('goals')
                                ->whereColumn('goals.user_id',  'users.id');
                    })
                    // To show the tag == selected tag name
                    ->when( ($request->tag && $request->tag <> '[Blank]' ), function ($q) use ($request) {
                        $q->whereExists(function ($query) use ($request) {
                              return $query->select(DB::raw(1))
                                        ->from('goals')
                                        ->join('goal_tags', 'goals.id', '=', 'goal_tags.goal_id')
                                        ->join('tags', 'goal_tags.tag_id', '=', 'tags.id')
                                        ->whereColumn('goals.user_id',  'users.id')
                                        ->where('name', $request->tag);
                            });
                    })  
                    // To show the  tag == '[blank]'
                    ->when( ($request->tag && $request->tag == '[Blank]' ), function ($q) {
                        $q->whereNotExists(function ($query) {
                              return $query->select(DB::raw(1))
                                        ->from('goals')
                                        ->join('goal_tags', 'goals.id', '=', 'goal_tags.goal_id')
                                        ->whereColumn('goals.user_id',  'users.id');
                                });
                    })
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('admin_org_users')
                                ->whereColumn('admin_org_users.allowed_user_id', 'users.id')
                                ->whereIn('admin_org_users.access_type', [0,1])
                                ->where('admin_org_users.granted_to_id', '=', Auth::id());
                    });
        
        $users = $sql->get();

        // Generating Output file 
        $filename = 'Active Goal Tags Per Employee.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ["Employee ID", "Name", "Email", 
                    "Organization", "Level 1", "Level 2", "Level 3", "Level 4", 
                    ];
        if (!$request->tag || $request->tag == '[Blank]') {
            array_push($columns, 'Blank' );
        }
        foreach ($tags as $tag)                    
        {
            array_push($columns, $tag->name); 
        }

        $callback = function() use($users, $columns, $tags, $request) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['Employee ID'] = $user->employee_id;
                $row['Name'] = $user->employee_name;
                $row['Email'] = $user->email;
                $row['Organization'] = $user->organization;
                $row['Level 1'] = $user->level1_program;
                $row['Level 2'] = $user->level2_division;
                $row['Level 3'] = $user->level3_branch;
                $row['Level 4'] = $user->level4;

                $row_data = array( $row['Employee ID'], $row['Name'], $row['Email'], $row['Organization'],
                                    $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'],
                                );

                if (!$request->tag || $request->tag == '[Blank]') {
                    array_push($row_data, $user->getAttribute('tag_0') );
                }

                foreach ($tags as $tag) 
                {
                    array_push($row_data, $user->getAttribute('tag_'.$tag->id) );
                }

                fputcsv($file, $row_data );

            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
        
    }

    public function conversationSummary(Request $request) {

        // send back the input parameters
        $this->preservedInputParams($request);

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);

        // Chart1 -- Overdue
        $sql_2 = User::selectRaw("users.employee_id, users.empl_record, employee_name, 
                                employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division,
                                employee_demo_tree.level3_branch, employee_demo_tree.level4,
                        DATEDIFF ( users.next_conversation_date
                            , curdate() )
                    as overdue_in_days")
                ->leftJoin('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.due_date_paused', 'N')
                            ->orWhereNull('users.due_date_paused');
                    });
                })        
                ->leftJoin('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->whereNull('employee_demo.date_deleted')
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.excused_flag', '<>', '1')
                            ->orWhereNull('users.excused_flag');
                    });
                }) 
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'users.id')
                            ->where('auth_users.auth_id', '=', Auth::id());
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
            $subset = $subset->unique('employee_id');
            array_push( $data['chart1']['groups'],  [ 'name' => $key, 'value' => $subset->count(), 
                        ]);
        }
        
        
        // SQL for Chart 4
        $sql = ConversationParticipant::join('users', 'users.id', 'conversation_participants.participant_id') 
        ->join('employee_demo', function($join) {
            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
        })
        ->join('conversations', function($join) {
            $join->on('conversations.id', '=', 'conversation_participants.conversation_id');  
        })
        ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.due_date_paused', 'N')
                            ->orWhereNull('users.due_date_paused');
                    });
                })
        ->where('conversation_participants.role', 'emp')
        ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
        ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
        ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
        ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
        ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
        ->where(function($query) {
            $query->where(function($query) {
                $query->whereNull('signoff_user_id')
                    ->orWhereNull('supervisor_signoff_id');
            });
        })
        ->whereNull('employee_demo.date_deleted')
        ->whereNull('conversations.deleted_at')
        ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                });
        
        $emp_conversations = $sql->get();
        
        // Chart4 -- Open Conversation for employees
        $topics = ConversationTopic::select('id','name')->get();
        $data['chart4']['chart_id'] = 4;
        $data['chart4']['title'] = 'Employees: Open Conversations';
        $data['chart4']['legend'] = $topics->pluck('name')->toArray();
        $data['chart4']['groups'] = array();        
        
        $total_unique_emp = 0;
        foreach($topics as $topic)
        {
            $subset = $emp_conversations->where('conversation_topic_id', $topic->id );
            $unique_emp = $subset->unique('participant_id')->count();            
            $total_unique_emp = $total_unique_emp + $unique_emp;
        } 
        
        
        foreach($topics as $topic)
        {
            $subset = $emp_conversations->where('conversation_topic_id', $topic->id );
            $unique_emp = $subset->unique('participant_id')->count();
            $per_emp = 0;
            if($total_unique_emp > 0) {
                $per_emp = ($unique_emp / $total_unique_emp) * 100;
            }
            array_push( $data['chart4']['groups'],  [ 'name' => $topic->name, 'value' => $unique_emp,
                        'topic_id' => $topic->id,
                            // 'ids' => $subset ? $subset->pluck('id')->toArray() : []
                        ]);
        } 
        
        // Chart 5 -- Completed Conversation by Employees
        $data['chart5']['chart_id'] = 5;
        $data['chart5']['title'] = 'Employees: Completed Conversations';
        $data['chart5']['legend'] = $topics->pluck('name')->toArray();
        $data['chart5']['groups'] = array();

        // SQL for Chart 5
        $completed_conversations = ConversationParticipant::join('users', 'users.id', 'conversation_participants.participant_id') 
        ->join('employee_demo', function($join) {
            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
        })
        ->join('conversations', function($join) {
            $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
        })
        ->where(function($query) {
            $query->where(function($query) {
                $query->whereNotNull('signoff_user_id')
                      ->whereNotNull('supervisor_signoff_id');                          
            });
        })
        ->whereNull('conversations.deleted_at')   
        ->whereNull('employee_demo.date_deleted')
        ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.due_date_paused', 'N')
                            ->orWhereNull('users.due_date_paused');
                    });
                })
        ->where('conversation_participants.role', 'emp')     
        ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        ->whereNull('employee_demo.date_deleted')                
        ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
        ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
        ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
        ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
        ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
        ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                })
        ->get();
        
        $total_unique_emp = 0;
        foreach($topics as $topic)
        {
            $subset = $completed_conversations->where('conversation_topic_id', $topic->id );
            $unique_emp = $subset->unique('participant_id')->count();            
            $total_unique_emp = $total_unique_emp + $unique_emp;
        } 
        

        foreach($topics as $topic)
        {
            $subset = $completed_conversations->where('conversation_topic_id', $topic->id );
            $unique_emp = $subset->unique('participant_id')->count();
            $per_emp = 0;
            if($total_unique_emp > 0) {
                $per_emp = ($unique_emp / $total_unique_emp) * 100;
            }
            
            array_push( $data['chart5']['groups'],  [ 'name' => $topic->name, 'value' => $unique_emp, 
                    'topic_id' => $topic->id, 
                ]);
        }
        
        //get all employee number
        $employees = UserDemoJrView::distinct('employee_id')
                ->where(function($query) {
                                $query->where(function($query) {
                                    $query->where('excused_flag', '<>', '1')
                                        ->orWhereNull('excused_flag');
                                });
                            })
                ->whereNull('date_deleted')
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('excused_flag', '<>', '1')
                            ->orWhereNull('excused_flag');
                    });
                })
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('due_date_paused', 'N')
                            ->orWhereNull('due_date_paused');
                    });
                })              
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                                  
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'user_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                })        
                ->count();

        // Chart6 -- Employee Has Open Conversation
        $sql_6 = UserDemoJrView::selectRaw("employee_id, employee_name, 
                            organization, level1_program, level2_division, level3_branch, level4
                 ")
                ->join('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->join('conversations', function($join) {
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                      
                ->where('conversation_participants.role','emp')   
                ->whereNull('conversations.deleted_at')           
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
                ->whereNull('date_deleted')
                ->whereNotNull('conversation_id')      
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNull('signoff_user_id')
                              ->orWhereNull('supervisor_signoff_id');
                    });
                }) 
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                });      
        
        $users = $sql_6->get();
        $users = $users->unique('employee_id');
        
        $has_conversation = $users->count();
        $no_conversation = $employees - $has_conversation;
        // Chart 6 
        $legends = ['Yes', 'No'];
        $data['chart6']['chart_id'] = 6;
        $data['chart6']['title'] = 'Employee Has Open Conversation';
        $data['chart6']['legend'] = $legends;
        $data['chart6']['groups'] = array();
        
        foreach($legends as $legend)
        {            
            if($legend == 'No') {
                $subset = $no_conversation;
            } else {
                $subset = $has_conversation;
            }
            
            array_push( $data['chart6']['groups'],  [ 'name' => $legend, 'value' => $subset,
                            'legend' => $legend, 
                        ]);
        } 
        
        
        // Chart7 -- Employee Has Completed Conversation
        $sql_7 = UserDemoJrView::selectRaw("employee_id, employee_name, 
                            organization, level1_program, level2_division, level3_branch, level4
                 ")
                ->join('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->join('conversations', function($join) {
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                      
                ->where('conversation_participants.role','emp') 
                ->whereNull('conversations.deleted_at')          
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
                ->whereNull('date_deleted')
                ->whereNotNull('conversation_id')       
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNotNull('signoff_user_id')
                              ->WhereNotNull('supervisor_signoff_id');
                    });
                }) 
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                });
                            
        $users = $sql_7->get();
        $users = $users->unique('employee_id');
        
        $has_conversation = $users->count();
        $no_conversation = $employees - $has_conversation;
        // Chart 7 
        $legends = ['Yes', 'No'];
        $data['chart7']['chart_id'] = 7;
        $data['chart7']['title'] = 'Employee Has Completed Conversation';
        $data['chart7']['legend'] = $legends;
        $data['chart7']['groups'] = array();

        foreach($legends as $legend)
        {
            if($legend == 'No') {
                $subset = $no_conversation;
            } else {
                $subset = $has_conversation;
            }
            array_push( $data['chart7']['groups'],  [ 'name' => $legend, 'value' => $subset,
                            'legend' => $legend, 
                        ]);
        } 
               

        return view('hradmin.statistics.conversationsummary',compact('data'));

    }


    public function conversationSummaryExport(Request $request) {
        // SQL - Chart 1
        $sql_chart1 = User::selectRaw("users.*, employee_demo.employee_name, 
        employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                    DATEDIFF ( users.next_conversation_date, curdate() )  as overdue_in_days,
                    users.next_conversation_date as next_due_date")
                ->leftJoin('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.due_date_paused', 'N')
                            ->orWhereNull('users.due_date_paused');
                    });
                })
                ->leftJoin('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')  
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.excused_flag', '<>', '1')
                            ->orWhereNull('users.excused_flag');
                    });
                }) 
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'users.id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                });
                
        // SQL - Chart 2
        $sql_chart2 = Conversation::selectRaw("conversations.*, users.employee_id, employee_demo.employee_name, users.email,
        employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                        users.next_conversation_date as next_due_date")
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNull('signoff_user_id')
                            ->orWhereNull('supervisor_signoff_id');
                    });
                })
                ->whereNull('deleted_at')                        
                ->join('conversation_participants','conversations.id','conversation_participants.conversation_id')        
                ->join('users', 'users.id', 'conversation_participants.participant_id') 
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.due_date_paused', 'N')
                            ->orWhereNull('users.due_date_paused');
                    });
                })
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'users.id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                })
                ->where('conversation_participants.role','<>','mgr')
                ->whereNull('employee_demo.date_deleted')        
                ->with('topic:id,name')
                ->with('signoff_user:id,name')
                ->with('signoff_supervisor:id,name');

         // SQL for Chart 3
         $sql_chart3 = Conversation::selectRaw("conversations.*, users.employee_id, employee_demo.employee_name, users.email,
         employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                    users.next_conversation_date as next_due_date")
            ->join('conversation_participants','conversations.id','conversation_participants.conversation_id')        
            ->join('users', 'users.id', 'conversation_participants.participant_id')
            ->join('employee_demo', function($join) {
                $join->on('employee_demo.employee_id', '=', 'users.employee_id');
            })
            ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.due_date_paused', 'N')
                            ->orWhereNull('users.due_date_paused');
                    });
                })

            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
            ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
            ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
            ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
            ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNotNull('signoff_user_id')
                          ->whereNotNull('supervisor_signoff_id');
                });
            })
            ->whereNull('deleted_at')   
            ->whereNull('employee_demo.date_deleted')        
            ->when( $request->topic_id, function($q) use($request) {
                $q->where('conversations.conversation_topic_id', $request->topic_id);
            })
            ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'users.id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                })
            ->with('topic:id,name')
            ->with('signoff_user:id,name')
            ->with('signoff_supervisor:id,name')
            ;
            
            
        // SQL - Chart 4
        $sql_chart4 = ConversationParticipant::selectRaw("conversations.*, conversation_topics.name as conversation_name, users.employee_id, employee_demo.employee_name, users.email,
        employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                        users.next_conversation_date as next_due_date, supervisor.name as sign_supervisor_name, employee.name as sign_employee_name")
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNull('signoff_user_id')
                            ->orWhereNull('supervisor_signoff_id');
                    });
                })
                ->whereNull('deleted_at')                
                ->join('users', 'users.id', 'conversation_participants.participant_id') 
                ->join('conversations','conversations.id','conversation_participants.conversation_id')
                ->join('conversation_topics','conversations.conversation_topic_id','conversation_topics.id')            
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->leftJoin('users as supervisor', 'supervisor.id', '=', 'conversations.supervisor_signoff_id')
                ->leftJoin('users as employee', 'employee.id', '=', 'conversations.signoff_user_id')   
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.due_date_paused', 'N')
                            ->orWhereNull('users.due_date_paused');
                    });
                })

                ->where('conversation_participants.role','emp')
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->whereNull('employee_demo.date_deleted')        
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->when( $request->topic_id, function($q) use($request) {
                    $q->where('conversations.conversation_topic_id', $request->topic_id);
                })
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                });
                
        // SQL for Chart 5
         $sql_chart5 = ConversationParticipant::selectRaw("conversations.*, conversation_topics.name as conversation_name, users.employee_id, employee_demo.employee_name, users.email,
         employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                    users.next_conversation_date as next_due_date, supervisor.name as sign_supervisor_name, employee.name as sign_employee_name")
            ->join('users', 'users.id', 'conversation_participants.participant_id') 
            ->join('conversations','conversations.id','conversation_participants.conversation_id')
            ->join('conversation_topics','conversations.conversation_topic_id','conversation_topics.id')         
            ->join('employee_demo', function($join) {
                $join->on('employee_demo.employee_id', '=', 'users.employee_id');
            })
            ->leftJoin('users as supervisor', 'supervisor.id', '=', 'conversations.supervisor_signoff_id')
            ->leftJoin('users as employee', 'employee.id', '=', 'conversations.signoff_user_id')   
            ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.due_date_paused', 'N')
                            ->orWhereNull('users.due_date_paused');
                    });
                }) 
            ->where('conversation_participants.role','emp')       
            ->whereNull('employee_demo.date_deleted')            
            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
            ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
            ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
            ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
            ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNotNull('signoff_user_id')
                          ->whereNotNull('supervisor_signoff_id');
                });
            })
            ->whereNull('deleted_at')              
            ->when( $request->topic_id, function($q) use($request) {
                $q->where('conversations.conversation_topic_id', $request->topic_id);
            })
            ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                });
            
        // sql6 -- Employee Has Open Conversation
        $sql_6 = UserDemoJrView::selectRaw("employee_id, employee_name, 
                            organization, level1_program, level2_division, level3_branch, level4,
                case when conversation_id IS NULL then 'No' else 'Yes' end as has_conversation            
                ")
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('due_date_paused', 'N')
                            ->orWhereNull('due_date_paused');
                    });
                })
                ->leftJoin('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->leftJoin('conversations', function($join) {
                    $join->on('conversation_participants.conversation_id', '=', 'conversations.id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })      
                ->whereNull('date_deleted')     
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNull('conversations.id')
                            ->orwhere(function($query) {
                                $query->where(function($query) {
                                    $query->whereNull('signoff_user_id')
                                        ->orWhereNull('supervisor_signoff_id');
                                });
                            });
                        });
                })        
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                });    
                
                
        // sql7 -- Employee Has Completed Conversation
        $sql_7 = UserDemoJrView::selectRaw("employee_id, employee_name, 
                            organization, level1_program, level2_division, level3_branch, level4,
                case when conversation_id IS NULL then 'No' else 'Yes' end as has_conversation            
                ")
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('due_date_paused', 'N')
                            ->orWhereNull('due_date_paused');
                    });
                })
                ->leftJoin('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->leftJoin('conversations', function($join) {
                    $join->on('conversation_participants.conversation_id', '=', 'conversations.id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })      
                ->whereNull('date_deleted')     
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNull('conversations.id')
                            ->orwhere(function($query) {
                                $query->where(function($query) {
                                    $query->whereNotNull('signoff_user_id')
                                        ->WhereNotNull('supervisor_signoff_id');
                                });
                            });
                        });
                })           
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                });       
            
        // Generating Output file 
        $filename = 'Conversations.xlsx';
        switch ($request->chart) {
            case 1:

                $filename = 'Next Conversation Due.csv';
                $users =  $sql_chart1->get();
                $users = $users->unique('employee_id');
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
                                "Next Conversation Due", 'Due Date Category',
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", "Reporting To",
                           ];
        
                $callback = function() use($users, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);

                    foreach ($users as $user) {

                        $group_name = '';
                        foreach ($this->overdue_groups as $key => $range) {
                            if (($user->overdue_in_days >= $range[0] ) && ( $user->overdue_in_days <= $range[1])) {
                                $group_name = $key;
                            }
                        }

                        $row['Employee ID'] = "[".$user->employee_id."]";
                        $row['Name'] = $user->employee_name;
                        $row['Email'] = $user->email;
                        $row['Next Conversation Due'] = $user->next_due_date;
                        $row['Due Date Category'] = $group_name;
                        $row['Organization'] = $user->organization;
                        $row['Level 1'] = $user->level1_program;
                        $row['Level 2'] = $user->level2_division;
                        $row['Level 3'] = $user->level3_branch;
                        $row['Level 4'] = $user->level4;
                        $row['Reporting To'] = $user->reportingManager ? $user->reportingManager->name : '';
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Next Conversation Due'], 
                                    $row['Due Date Category'], $row['Organization'],
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
                        $row['Employee ID'] = "[".$conversation->employee_id."]";
                        $row['Name'] = $conversation->employee_name;
                        $row['Email'] = $conversation->user->email;
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
                        $row['Employee ID'] = "[".$conversation->employee_id."]";
                        $row['Name'] = $conversation->employee_name;
                        $row['Email'] = $conversation->user->email;
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
                
            case 4:

                $filename = 'Open Conversations by Employee.csv.csv';
                $conversations =  $sql_chart4->get();
                $conversations_unique = array();
                $topics = ConversationTopic::select('id','name')->get();
                foreach($topics as $topic){
                        $subset = $conversations->where('conversation_topic_id', $topic->id );
                        $unique_subset = $subset->unique('employee_id');
                        foreach($unique_subset as $item) {
                            array_push($conversations_unique,$item);
                        }                        
                }
                
                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );
        
                $columns = ["Employee ID", "Employee Name", "Email", "Conversation Name", "Conversation Participant",
                        "Conversation Due Date", "Employee Sign-Off", "Supervisor Sign-off", 
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", 
                           ];
        
                $callback = function() use($conversations_unique, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    
                    foreach ($conversations_unique as $conversation) {
                        $row['Employee ID'] = "[".$conversation->employee_id."]";
                        $row['Name'] = $conversation->employee_name;
                        $row['Email'] = $conversation->email;
                        $row['Conversation Name'] = $conversation->conversation_name;    
                        $participants = DB::table('conversation_participants')
                                        ->select('users.name')
                                        ->join('users', 'conversation_participants.participant_id', '=', 'users.id')
                                        ->where('conversation_participants.conversation_id', $conversation->id)
                                        ->get();      
                        $participants_arr = array();
                        foreach($participants as $participant){
                            $participants_arr[] = $participant->name;
                        }
                        $row['Conversation Participant'] = implode(', ', $participants_arr );
                        $row['Conversation Due Date'] = $conversation->next_due_date;
                        $row['Employee Sign-Off'] = $conversation->sign_employee_name;
                        $row['Supervisor Sign-off'] = $conversation->sign_supervisor_name;
                        $row['Organization'] = $conversation->organization;
                        $row['Level 1'] = $conversation->level1_program;
                        $row['Level 2'] = $conversation->level2_division;
                        $row['Level 3'] = $conversation->level3_branch;
                        $row['Level 4'] = $conversation->level4;
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], // $row['Next Conversation Due'],
                        $row['Conversation Name'],$row['Conversation Participant'],
                            $row['Conversation Due Date'],
                        $row["Employee Sign-Off"], $row["Supervisor Sign-off"],
                                 $row['Organization'],
                                  $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], 
                                ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);


                break;       
            
            case 5:

                $filename = 'Completed Conversations by Employee.csv.csv';
                $conversations =  $sql_chart5->get();
                $conversations_unique = array();
                $topics = ConversationTopic::select('id','name')->get();
                foreach($topics as $topic){
                        $subset = $conversations->where('conversation_topic_id', $topic->id );
                        $unique_subset = $subset->unique('employee_id');
                        foreach($unique_subset as $item) {
                            array_push($conversations_unique,$item);
                        }                        
                }

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
        
                $columns = ["Employee ID", "Employee Name", "Email","Conversation Name","Conversation Participant",
                        "Employee Sign-Off", "Employee Sign-Off Time", "Supervisor Sign-off", "Supervisor Sign-off Time", 
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", 
                           ];
        
                $callback = function() use($conversations_unique, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    
                    foreach ($conversations_unique as $conversation) {
                            $row['Employee ID'] = "[".$conversation->employee_id."]";
                            $row['Name'] = $conversation->employee_name;
                            $row['Email'] = $conversation->email;
                            $row['Conversation Name'] = $conversation->conversation_name;
                            $participants = DB::table('conversation_participants')
                                        ->select('users.name')
                                        ->join('users', 'conversation_participants.participant_id', '=', 'users.id')
                                        ->where('conversation_participants.conversation_id', $conversation->id)
                                        ->get();  
                            $participants_arr = array();
                            foreach($participants as $participant){
                                $participants_arr[] = $participant->name;
                            }
                            $row['Conversation Participant'] = implode(', ', $participants_arr );
                            //$row['Conversation Due Date'] = $conversation->next_due_date;
                            $row['Employee Sign-Off'] = $conversation->sign_employee_name;
                            $row['Employee Sign-Off Time'] = $conversation->sign_off_time;
                            $row['Supervisor Sign-off'] = $conversation->sign_supervisor_name;
                            $row['Supervisor Sign-off Time'] = $conversation->supervisor_signoff_time;
                            $row['Organization'] = $conversation->organization;
                            $row['Level 1'] = $conversation->level1_program;
                            $row['Level 2'] = $conversation->level2_division;
                            $row['Level 3'] = $conversation->level3_branch;
                            $row['Level 4'] = $conversation->level4;
                            fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], // $row['Next Conversation Due'],
                            $row["Conversation Name"],$row['Conversation Participant'],
                            $row["Employee Sign-Off"], $row["Employee Sign-Off Time"], $row["Supervisor Sign-off"],$row["Supervisor Sign-off Time"],
                                     $row['Organization'],
                                      $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], 
                                    ));
                        }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;   
                
                
            case 6:

                $filename = 'Employee Has Open Conversation.csv';
                $users =  $sql_6->get();
                $users = $users->unique('employee_id');
                $users = $users->where('has_conversation', $request->legend);  

                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );
        
                $columns = ["Employee ID", "Employee Name", "Email",
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", 
                           ];
        
                $callback = function() use($users, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
        
                    foreach ($users as $user) {
                        $row['Employee ID'] = "[".$user->employee_id."]";
                        $row['Name'] = $user->employee_name;
                        $row['Email'] = $user->email;
                        $row['Organization'] = $user->organization;
                        $row['Level 1'] = $user->level1_program;
                        $row['Level 2'] = $user->level2_division;
                        $row['Level 3'] = $user->level3_branch;
                        $row['Level 4'] = $user->level4;
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Organization'],
                                    $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;    
            
            case 7:

                $filename = 'Employee Has Complete Conversation.csv';
                $users =  $sql_7->get();
                $users = $users->unique('employee_id');
                $users = $users->where('has_conversation', $request->legend);  

                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );
        
                $columns = ["Employee ID", "Employee Name", "Email",
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", 
                           ];
        
                $callback = function() use($users, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
        
                    foreach ($users as $user) {
                        $row['Employee ID'] = "[".$user->employee_id."]";
                        $row['Name'] = $user->employee_name;
                        $row['Email'] = $user->email;
                        $row['Organization'] = $user->organization;
                        $row['Level 1'] = $user->level1_program;
                        $row['Level 2'] = $user->level2_division;
                        $row['Level 3'] = $user->level3_branch;
                        $row['Level 4'] = $user->level4;
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Organization'],
                                    $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;      
                
        }
        
    }

    public function sharedsummary(Request $request) {

        // send back the input parameters
        $this->preservedInputParams($request);

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);

        $sql = User::selectRaw("users.employee_id, users.empl_record,
                case when (select count(*) from shared_profiles A where A.shared_id = users.id) > 0 then 'Yes' else 'No' end as shared")
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->where('users.due_date_paused', 'N')
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->whereNull('employee_demo.date_deleted')         
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'users.id')
                            ->where('auth_users.auth_id', '=', Auth::id());
                })
                ;

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
                            'legend' => $legend, 
                        ]);
        }    

        return view('hradmin.statistics.sharedsummary',compact('data'));

    } 

    public function sharedSummaryExport(Request $request) {

      $selected_ids = $request->ids ? explode(',', $request->ids) : [];

      $sql = User::selectRaw("users.*,
                employee_name, employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division,
                employee_demo_tree.level3_branch, employee_demo_tree.level4,
            case when (select count(*) from shared_profiles A where A.shared_id = users.id) > 0 then 'Yes' else 'No' end as shared")
            ->join('employee_demo', function($join) {
                $join->on('employee_demo.employee_id', '=', 'users.employee_id');
            })
            ->where('users.due_date_paused', 'N')            
            ->when( $request->legend == 'Yes', function($q) use($request) {
                $q->whereRaw(" (select count(*) from shared_profiles A where A.shared_id = users.id) > 0 ");
            }) 
            ->when( $request->legend == 'No', function($q) use($request) {
                $q->whereRaw(" (select count(*) from shared_profiles A where A.shared_id = users.id) = 0 ");
            }) 
            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->whereNull('employee_demo.date_deleted')         
            ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
            ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
            ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
            ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
            ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
            ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                            ->from('auth_users')
                            ->whereColumn('auth_users.user_id', 'users.id')
                            ->where('auth_users.auth_id', '=', Auth::id());
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
                        $row['Organization'],
                        $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    }


    public function excusedsummary(Request $request) {

        // send back the input parameters
        $this->preservedInputParams($request);

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);

        $sql = User::selectRaw("users.employee_id, users.empl_record, 
                    employee_name, employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division,
                    employee_demo_tree.level3_branch, employee_demo_tree.level4,
                    case when users.due_date_paused = 'N'
                        then 'No' else 'Yes' end as excused")
                    ->join('employee_demo', function($join) {
                         $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                    })
                    ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                    ->whereNull('employee_demo.date_deleted')         
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                    ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                    ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                    ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                    ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                    ->whereNull('employee_demo.date_deleted')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('auth_users')
                                ->whereColumn('auth_users.user_id', 'users.id')
                                ->where('auth_users.auth_id', '=', Auth::id());
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

        return view('hradmin.statistics.excusedsummary',compact('data'));

    } 


    public function excusedSummaryExport(Request $request) {

      $selected_ids = $request->ids ? explode(',', $request->ids) : [];

      $sql = User::selectRaw("users.employee_id, users.email, users.excused_start_date, users.excused_end_date,
                            users.excused_reason_id, users.reporting_to,
                    employee_demo.employee_name, employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                    case when users.due_date_paused = 'N'
                        then 'No' else 'Yes' end as excused")
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->when( $request->legend == 'Yes', function($q) use($request) {
                    $q->whereRaw(" users.due_date_paused = 'Y' ");
                }) 
                ->when( $request->legend == 'No', function($q) use($request) {
                    $q->whereRaw(" users.due_date_paused = 'N' ");
                })
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->whereNull('employee_demo.date_deleted')         
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->whereNull('employee_demo.date_deleted')
                ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('auth_users')
                                ->whereColumn('auth_users.user_id', 'users.id')
                                ->where('auth_users.auth_id', '=', Auth::id());
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
                $row['Organization'] = $user->organization;
                $row['Level 1'] = $user->level1_program;
                $row['Level 2'] = $user->level2_division;
                $row['Level 3'] = $user->level3_branch;
                $row['Level 4'] = $user->level4;

                fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], 
                        $row['Excused'], $row['Excused Start Date'], $row['Excused End Date'], $row['Excused Reason'],
                        $row['Organization'], $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    }

    public function preservedInputParams(Request $request) 
    {
        $errors = session('errors');
        if ($errors) {
            $old = session()->getOldInput();

            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
    
        } 

        // no validation and move filter variable to old 
        if ($request->btn_search) {

            $filter = '&dd_level0='.$request->dd_level0. 
                        '&dd_level1='.$request->dd_level1. 
                        '&dd_level2='.$request->dd_level2. 
                        '&dd_level3='.$request->dd_level3. 
                        '&dd_level4='.$request->dd_level4; 

            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'filter' => $filter, 
            ]);
        } else {

            session()->put('_old_input', [
                'filter' => '', 
            ]);

        }

    }
    
    
    public function conversationStatus(Request $request) {

        // send back the input parameters
        $this->preservedInputParams($request);

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        
        //get all employee number
        $employees = UserDemoJrView::distinct('employee_id')
                ->whereNull('date_deleted')
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('excused_flag', '<>', '1')
                            ->orWhereNull('excused_flag');
                    });
                })
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('due_date_paused', 'N')
                            ->orWhereNull('due_date_paused');
                    });
                })  
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                                  
                ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('auth_users')
                                ->whereColumn('auth_users.user_id', 'users.id')
                                ->where('auth_users.auth_id', '=', Auth::id());
                    })->count();

        // Chart6 -- Employee Has Open Conversation
        $sql_6 = UserDemoJrView::selectRaw("employee_id, employee_name, 
                            organization, level1_program, level2_division, level3_branch, level4
                 ")
                ->join('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->join('conversations', function($join) {
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                      
                ->where('conversation_participants.role','emp')   
                ->whereNull('conversations.deleted_at')           
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
                ->whereNull('date_deleted')
                ->whereNotNull('conversation_id')      
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNull('signoff_user_id')
                              ->orWhereNull('supervisor_signoff_id');
                    });
                }) 
                ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('auth_users')
                                ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                                ->where('auth_users.auth_id', '=', Auth::id());
                    });      
        
        $users = $sql_6->get();
        $users = $users->unique('employee_id');
        
        $has_conversation = $users->count();
        $no_conversation = $employees - $has_conversation;
        // Chart 6 
        $legends = ['Yes', 'No'];
        $data['chart6']['chart_id'] = 6;
        $data['chart6']['title'] = 'Employee Has Open Conversation';
        $data['chart6']['legend'] = $legends;
        $data['chart6']['groups'] = array();
        
        foreach($legends as $legend)
        {            
            if($legend == 'No') {
                $subset = $no_conversation;
            } else {
                $subset = $has_conversation;
            }
            
            array_push( $data['chart6']['groups'],  [ 'name' => $legend, 'value' => $subset,
                            'legend' => $legend, 
                        ]);
        } 
        
        
        // Chart7 -- Employee Has Completed Conversation
        $sql_7 = UserDemoJrView::selectRaw("employee_id, employee_name, 
                            organization, level1_program, level2_division, level3_branch, level4
                 ")
                ->join('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->join('conversations', function($join) {
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                      
                ->where('conversation_participants.role','emp') 
                ->whereNull('conversations.deleted_at')          
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
                ->whereNull('date_deleted')
                ->whereNotNull('conversation_id')       
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNotNull('signoff_user_id')
                              ->WhereNotNull('supervisor_signoff_id');
                    });
                }) 
                ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('auth_users')
                                ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                                ->where('auth_users.auth_id', '=', Auth::id());
                    });
                            
        $users = $sql_7->get();
        $users = $users->unique('employee_id');
        
        $has_conversation = $users->count();
        $no_conversation = $employees - $has_conversation;
        // Chart 7 
        $legends = ['Yes', 'No'];
        $data['chart7']['chart_id'] = 7;
        $data['chart7']['title'] = 'Employee Has Completed Conversation';
        $data['chart7']['legend'] = $legends;
        $data['chart7']['groups'] = array();

        foreach($legends as $legend)
        {
            if($legend == 'No') {
                $subset = $no_conversation;
            } else {
                $subset = $has_conversation;
            }
            array_push( $data['chart7']['groups'],  [ 'name' => $legend, 'value' => $subset,
                            'legend' => $legend, 
                        ]);
        } 
               

        return view('hradmin.statistics.conversationstatus',compact('data'));

    }
    
    
    public function conversationStatusExport(Request $request) {        
        // sql6 -- Employee Has Open Conversation
        $sql_6 = UserDemoJrView::selectRaw("employee_id, employee_name, employee_email, next_conversation_date, reporting_to_name,
                            organization, level1_program, level2_division, level3_branch, level4
                 ")
                ->join('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->join('conversations', function($join) {
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                      
                ->where('conversation_participants.role','emp')  
                ->whereNull('conversations.deleted_at')          
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
                ->whereNull('date_deleted')
                ->whereNotNull('conversation_id')           
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNull('signoff_user_id')
                              ->orWhereNull('supervisor_signoff_id');
                    });
                })                 
                ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('auth_users')
                                ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                                ->where('auth_users.auth_id', '=', Auth::id());
                    });
                
                
        // sql7 -- Employee Has Completed Conversation
        $sql_7 = UserDemoJrView::selectRaw("employee_id, employee_name, employee_email, next_conversation_date, reporting_to_name,
                            organization, level1_program, level2_division, level3_branch, level4
                 ")
                ->join('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->join('conversations', function($join) {
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                      
                ->where('conversation_participants.role','emp')   
                ->whereNull('conversations.deleted_at')          
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
                ->whereNull('date_deleted')
                ->whereNotNull('conversation_id')            
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->whereNotNull('signoff_user_id')
                              ->WhereNotNull('supervisor_signoff_id');
                    });
                }) 
                ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                                ->from('auth_users')
                                ->whereColumn('auth_users.user_id', 'conversation_participants.participant_id')
                                ->where('auth_users.auth_id', '=', Auth::id());
                    });      
            

        // Generating Output file 
        $filename = 'Conversations.xlsx';
        switch ($request->chart) {
                
            case 6:

                $filename = 'Employee Has Open Conversation.csv';
                $users =  $sql_6->get();
                $users = $users->unique('employee_id');
                
                if($request->legend == 'No'){
                    //get has conversation users employee_id list
                    $excludedIds = $users->pluck('employee_id');
                    $sql = UserDemoJrView::whereNotIn('employee_id', $excludedIds)
                            ->whereNull('date_deleted')
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
                            ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                            ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                            ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                            ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                            ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                        ->from('auth_users')
                                        ->whereColumn('auth_users.user_id', 'user_id')
                                        ->where('auth_users.auth_id', '=', Auth::id());
                            });
                    
                    $users = $sql->get();
                    $users = $users->unique('employee_id');               
                }  

                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );
        
                $columns = ["Employee ID", "Employee Name", "Email",
                                "Organization","Next Conversation Due","Reporting To",
                                "Level 1", "Level 2", "Level 3", "Level 4",
                           ];
        
                $callback = function() use($users, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
        
                    foreach ($users as $user) {
                        $row['Employee ID'] = "[".$user->employee_id."]";
                        $row['Name'] = $user->employee_name;
                        $row['Email'] = $user->employee_email;
                        $row['Organization'] = $user->organization;
                        $row['next_conversation_date'] = $user->next_conversation_date;
                        $row['reporting_to_name'] = $user->reporting_to_name;
                        $row['Level 1'] = $user->level1_program;
                        $row['Level 2'] = $user->level2_division;
                        $row['Level 3'] = $user->level3_branch;
                        $row['Level 4'] = $user->level4;
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Organization'],
                                    $row['next_conversation_date'],$row['reporting_to_name'],
                                    $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;    
            
            case 7:

                $filename = 'Employee Has Complete Conversation.csv';
                $users =  $sql_7->get();
                $users = $users->unique('employee_id');
                
                if($request->legend == 'No'){
                    //get has conversation users employee_id list
                    $excludedIds = $users->pluck('employee_id');
                    $users = UserDemoJrView::whereNotIn('employee_id', $excludedIds)
                            ->whereNull('date_deleted')
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
                            ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                            ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                            ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                            ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                            ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })                            
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                        ->from('auth_users')
                                        ->whereColumn('auth_users.user_id', 'user_id')
                                        ->where('auth_users.auth_id', '=', Auth::id());
                            })
                            ->get();
                    $users = $users->unique('employee_id');       
                }  

                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );
        
                $columns = ["Employee ID", "Employee Name", "Email",
                                "Organization","Next Conversation Due","Reporting To",
                                "Level 1", "Level 2", "Level 3", "Level 4",
                           ];
        
                $callback = function() use($users, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
        
                    foreach ($users as $user) {
                        $row['Employee ID'] = "[".$user->employee_id."]";
                        $row['Name'] = $user->employee_name;
                        $row['Email'] = $user->employee_email;
                        $row['Organization'] = $user->organization;
                        $row['next_conversation_date'] = $user->next_conversation_date;
                        $row['reporting_to_name'] = $user->reporting_to_name;
                        $row['Level 1'] = $user->level1_program;
                        $row['Level 2'] = $user->level2_division;
                        $row['Level 3'] = $user->level3_branch;
                        $row['Level 4'] = $user->level4;
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Organization'],
                                    $row['next_conversation_date'],$row['reporting_to_name'],
                                    $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;  
                
        }
        
    }
    
}
