<?php

namespace App\Http\Controllers\SysAdmin;

use stdClass;
use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Goal;
use App\Models\GoalComment;
use App\Models\User;
use App\Models\GoalType;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\UserDemoJrView;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoTree;
use App\Models\EmployeeDemoJunior;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

use Dompdf\Dompdf;
use Dompdf\Options;

class SysadminStatisticsReportController extends Controller
{
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
        if ($goal_type_id != ''){                        
            $from_stmt .= " and goal_type_id =".  $goal_type_id ;
        }    
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
        
        $total_goals = UserDemoJrView::selectRaw('count(*) as goal_count, goals.goal_type_id')
                        ->join('goals', 'goals.user_id', 'user_demo_jr_view.user_id') 
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
                        ->whereNull('deleted_at')
                        ->where('goals.status','active')
                        ->where('goals.is_library','0')       
                        ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                        ->when( $request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                        ->when( $request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                        ->when( $request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                        ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })
                        ->groupBy('goals.goal_type_id')
                        ->get();
                        
        $total_number_query = UserDemoJrView::selectRaw('count(*) as total_emp')
                                  ->where(function($query) {
                            $query->where(function($query) {
                                $query->where('user_demo_jr_view.due_date_paused', 'N')
                                    ->orWhereNull('user_demo_jr_view.due_date_paused');
                            });
                        })
                    ->where(function($query) {
                            $query->where(function($query) {
                                $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                    ->orWhereNull('user_demo_jr_view.excused_flag');
                            });
                        }) 
                    ->whereNull('user_demo_jr_view.date_deleted')     
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); });
        $total_number_obj = $total_number_query->get();
        $total_number_emp = $total_number_obj[0]->total_emp;
        
        $goal_count_cal = UserDemoJrView::selectRaw("user_demo_jr_view.user_id, COUNT(goals.id) AS goals_count, goals.goal_type_id")
                ->leftJoin('goals', function ($join) {
                    $join->on('goals.user_id', '=', 'user_demo_jr_view.user_id');
                })
                ->where('goals.status', '=', 'active')
                ->whereNull('goals.deleted_at')
                ->where('goals.is_library', '=', 0)
                ->where(function($query) {
                            $query->where(function($query) {
                                $query->where('user_demo_jr_view.due_date_paused', 'N')
                                    ->orWhereNull('user_demo_jr_view.due_date_paused');
                            });
                        })
                    ->where(function($query) {
                            $query->where(function($query) {
                                $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                    ->orWhereNull('user_demo_jr_view.excused_flag');
                            });
                        }) 
                    ->whereNull('user_demo_jr_view.date_deleted')     
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })            
                    ->groupBy(['user_demo_jr_view.user_id', 'goals.goal_type_id']);
                            
        $goal_count_cal = $goal_count_cal->get()->toArray();
               
        $convertedArray = [];
        $groupedData = [];
        $toal_goal_counts = 0;
        foreach ($goal_count_cal as $item) {
            $user_id = $item['user_id'];
            $goals_count = $item['goals_count'];
            $goal_type_id = $item['goal_type_id'];
            
            $toal_goal_counts = $toal_goal_counts + $goals_count;
            if ($goals_count == 0) {
                $groupKey = '0';
            } elseif ($goals_count >= 1 && $goals_count <= 5) {
                $groupKey = '1-5';
            } elseif ($goals_count >= 6 && $goals_count <= 10) {
                $groupKey = '6-10';
            } else {
                $groupKey = '>10';
            }

            $key = $groupKey . '_' . $goal_type_id;

            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'group_key' => $groupKey,
                    'goals_count' => 0,
                    'goal_type_id' => $goal_type_id,
                ];
            }
            $groupedData[$key]['goals_count'] += $goals_count;
        }
        
        $groupedData[0]['goals_count'] = $total_number_emp - $toal_goal_counts;
        $groupedData[0]['goal_type_id'] = '';
        $groupedData[0]['group_key'] = 0;
        
        $goals_count_type_array = array_values($groupedData);
        
        $total_goal_counts = 0; 
        $no_type_count = array();
        $no_type_count["1-5"] = 0;
        $no_type_count["6-10"] = 0;        
        $no_type_count[">10"] = 0;
        $total_notype_count = 0;
        foreach($goals_count_type_array as $item){
            $total_goal_counts = $total_goal_counts + $item["goals_count"];
            if($item["goal_type_id"]){
                if($item["group_key"] == '1-5'){
                    $no_type_count["1-5"] =  $no_type_count["1-5"] + $item["goals_count"];
                    $total_notype_count = $total_notype_count + $item["goals_count"];
                }
                if($item["group_key"] == '6-10'){
                    $no_type_count["6-10"] =  $no_type_count["6-10"] + $item["goals_count"];
                    $total_notype_count = $total_notype_count + $item["goals_count"];
                }
                if($item["group_key"] == '>10'){
                    $no_type_count[">10"] =  $no_type_count[">10"] + $item["goals_count"];
                    $total_notype_count = $total_notype_count + $item["goals_count"];
                }
            }
        }   
        
        foreach($types as $type)
        {
            $goal_id = $type->id ? $type->id : '';
            if($goal_id != '') {
                $goals = $total_goals->filter(function ($type_goals) use($goal_id){
                    return $type_goals->goal_type_id == $goal_id;
                });
            } else {
                $goals = $total_goals;
            }
            $total_count = 0;
            $total_item = 0;
            if ($total_item != 0){
                $goals_average = $total_count / $total_item;
            }else{
                $goals_average = 0;
            }
            $data[$goal_id] = [ 
                'name' => $type->name ? ' ' . $type->name : '',
                'goal_type_id' => $goal_id,
                'average' =>  $goals_average, 
                'groups' => []
            ];
            
            $goals_count_array = array();
            $goals_count_array["0"] = 0;
            $goals_count_array["1-5"] = 0;
            $goals_count_array["6-10"] = 0;
            $goals_count_array[">10"] = 0;
            $sub_type_total = 0;
            foreach($goals_count_type_array as $item){
                if($type->id){
                    if ($item["goal_type_id"] == $type->id){
                        if($item["group_key"] == '1-5'){
                            $goals_count_array["1-5"] = $item["goals_count"]; 
                            $sub_type_total = $sub_type_total + $item["goals_count"];
                        }
                        if($item["group_key"] == '6-10'){
                            $goals_count_array["6-10"] = $item["goals_count"];
                            $sub_type_total = $sub_type_total + $item["goals_count"];
                        }
                        if($item["group_key"] == '>10'){
                            $goals_count_array[">10"] = $item["goals_count"];
                            $sub_type_total = $sub_type_total + $item["goals_count"];
                        }
                    }
                } else {
                    $goals_count_array["1-5"] = $no_type_count["1-5"]; 
                    $goals_count_array["6-10"] = $no_type_count["6-10"]; 
                    $goals_count_array[">10"] = $no_type_count[">10"]; 
                    
                    $sub_type_total = $total_notype_count;
                }
            }
            $result_count = $total_goal_counts - $sub_type_total;
            $goals_count_array["0"] = $result_count;

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
        $count_raw .= " (select count(*) from goal_tags, goals, user_demo_jr_view ";
        $count_raw .= "   where goals.id = goal_tags.goal_id "; 
	    $count_raw .= "     and tag_id = tags.id ";  
        $count_raw .= "     and user_demo_jr_view.user_id = goals.user_id ";
        if($request->dd_level0) {
            $count_raw .= " and user_demo_jr_view.organization_key = '".$request->dd_level0."'";
        }
        if($request->dd_level1) {
            $count_raw .= " and user_demo_jr_view.level1_key = '".$request->dd_level1."'";
        }
        if($request->dd_level2) {
            $count_raw .= " and user_demo_jr_view.level2_key = '".$request->dd_level2."'";
        }
        if($request->dd_level3) {
            $count_raw .= " and user_demo_jr_view.level3_key = '".$request->dd_level3."'";
        }
        if($request->dd_level4) {
            $count_raw .= " and user_demo_jr_view.level4_key = '".$request->dd_level4."'";
        }
        $count_raw .= "     and ( ";
        $count_raw .= "           user_demo_jr_view.due_date_paused = 'N' ";
        $count_raw .= "         )";
        $count_raw .= ") as count";
        
        $sql = Tag::selectRaw($count_raw);
        $sql2 = Goal::join('user_demo_jr_view', function($join) {
                    $join->on('goals.user_id', '=', 'user_demo_jr_view.user_id');
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })
                ->where(function($query) {
                            $query->where(function($query) {
                                $query->where('user_demo_jr_view.due_date_paused', 'N')
                                    ->orWhereNull('user_demo_jr_view.due_date_paused');
                            });
                        })
                ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                ->orWhereNull('user_demo_jr_view.excused_flag');
                        });
                    }) 
                ->whereNull('user_demo_jr_view.date_deleted')    
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('goal_tags')
                          ->whereColumn('goals.id', 'goal_tags.goal_id');
                })
                ->where('user_demo_jr_view.guid', '<>', '')
                ->where('goals.status', '=', 'active')
                ->whereNull('goals.deleted_at')
                ->where('goals.is_library', '=', 0);
                
        $tags = $sql->get();
        $collection = new Collection($tags);
        $sortedArray = $collection->sortBy('name')->values()->all();
        
        $blank_count = $sql2->count();
        
        $data_tag = [ 
            'name' => 'Active Goal Tags',
            'labels' => [],
            'values' => [],
        ];

        // each group 
        array_push($data_tag['labels'], '[Blank]');  
        array_push($data_tag['values'], $blank_count);
        foreach($sortedArray as $key => $tag)
        {
            array_push($data_tag['labels'], $tag->name);  
            array_push($data_tag['values'], $tag->count);
        }
        array_multisort($data_tag['labels'], $data_tag['values']);

        return view('sysadmin.statistics.goalsummary',compact('data', 'data_tag'));
    }


    public function goalSummaryExport(Request $request) {

        $total_emp_query = UserDemoJrView::selectRaw("user_demo_jr_view.user_id, 0 AS goals_count, 0 AS goal_type_id, user_demo_jr_view.employee_id,"
                . "employee_name,employee_email,organization,level1_program,level2_division,level3_branch,level4")
                    ->where(function($query) {
                            $query->where(function($query) {
                                $query->where('user_demo_jr_view.due_date_paused', 'N')
                                    ->orWhereNull('user_demo_jr_view.due_date_paused');
                            });
                        })
                    ->where(function($query) {
                            $query->where(function($query) {
                                $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                    ->orWhereNull('user_demo_jr_view.excused_flag');
                            });
                        }) 
                    ->whereNull('user_demo_jr_view.date_deleted')     
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); });
        $total_emp_obj = $total_emp_query->get()->toArray();
          
        $subquery = DB::table('goals')
            ->select('user_id', 'goal_type_id', DB::raw('COUNT(*) AS sub_count'))
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->where('is_library', 0)
            ->groupBy('user_id', 'goal_type_id');

        $goal_count_query = DB::table('user_demo_jr_view')
            ->leftJoin('goals', 'goals.user_id', '=', 'user_demo_jr_view.user_id')
            ->leftJoin('goal_types', 'goals.goal_type_id', '=', 'goal_types.id')
            ->leftJoinSub($subquery, 'subquery', function ($join) {
                $join->on('subquery.user_id', '=', 'user_demo_jr_view.user_id')
                     ->on('subquery.goal_type_id', '=', 'goals.goal_type_id');
            })
            ->select('user_demo_jr_view.user_id', DB::raw('COUNT(goals.id) AS goals_count'), 'goals.goal_type_id', 'user_demo_jr_view.employee_id', 'employee_name', 'employee_email', 'organization', 'level1_program', 'level2_division', 'level3_branch', 'level4', 'goal_types.name AS goal_type_name', 'subquery.sub_count AS sub_goals_count')
            ->where('goals.status', 'active')
            ->whereNull('goals.deleted_at')
            ->where('goals.is_library', 0)
            ->where(function ($query) {
                $query->where('user_demo_jr_view.due_date_paused', 'N')
                      ->orWhereNull('user_demo_jr_view.due_date_paused');
            })
            ->where(function ($query) {
                $query->where('user_demo_jr_view.excused_flag', '<>', 1)
                      ->orWhereNull('user_demo_jr_view.excused_flag');
            })
            ->whereNull('user_demo_jr_view.date_deleted')
            ->groupBy('user_demo_jr_view.user_id', 'goals.goal_type_id');
                    
        $goal_count_obj = $goal_count_query->get()->toArray();
                       
        foreach($total_emp_obj as $i => $emp_item){
            foreach($goal_count_obj as $goal_item){
                if($goal_item->user_id == $emp_item["user_id"]){
                    $total_emp_obj[$i]["goals_count"] = $goal_item->goals_count;
                    if($goal_item->goal_type_id != '' && $goal_item->goal_type_id != 4){
                        $total_emp_obj[$i]["goal_type_id"] = $goal_item->goal_type_id;
                        $total_emp_obj[$i]["goal_type_name"] = $goal_item->goal_type_name;
                        $total_emp_obj[$i]["sub_goals_count"] = $goal_item->sub_goals_count;
                    } else {
                        $total_emp_obj[$i]["goal_type_id"] = '';
                        $total_emp_obj[$i]["goal_type_name"] = '';
                        $total_emp_obj[$i]["sub_goals_count"] = 0;
                    }
                }
            }
        }
        
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
        
        if(!$request->goal){
            $columns = ["Employee ID", "Name", "Email", 'Total Active Goals', 
                            'Active Work Goals', 'Active Learning Goals', 'Active Career Development Goals', 'Active Private Goals',
                            "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
                        ];
        }elseif($request->goal == 1){
            $columns = ["Employee ID", "Name", "Email", 
                            'Active Work Goals', 
                            "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
                        ];
        }elseif($request->goal == 2){
            $columns = ["Employee ID", "Name", "Email", 
                            'Active Career Development Goals', 
                            "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
                        ];
        }elseif($request->goal == 3){
            $columns = ["Employee ID", "Name", "Email", 
                            'Active Learning Development Goals', 
                            "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
                        ];
        }elseif($request->goal == 4){
            $columns = ["Employee ID", "Name", "Email", 
                            'Active Private Goals', 
                            "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
                        ];
        }
        
        $callback = function() use($total_emp_obj, $columns, $request) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($total_emp_obj as $user) {
                $row['Employee ID'] = "[".$user["employee_id"]."]";
                $row['Name'] = $user["employee_name"];
                $row['Email'] = $user["employee_email"];
                if(!$request->goal){
                    $row['Total Active Goals'] = $user["goals_count"];
                }
                //Active Work Goals
                if(!$request->goal || $request->goal == 1){
                    if($user["goal_type_id"] == 1){
                        $row['Active Work Goals'] = $user["sub_goals_count"];
                    }else{
                        $row['Active Work Goals'] = 0;
                    }
                }
                //Active Learning  Goals
                if(!$request->goal || $request->goal == 3){
                    if($user["goal_type_id"] == 3){
                        $row['Active Learning Goals'] = $user["sub_goals_count"];
                    }else{
                        $row['Active Learning Goals'] = 0;
                    }
                }
                //Active Career Development Goals
                if(!$request->goal || $request->goal == 2){
                    if($user["goal_type_id"] == 2){
                        $row['Active Career Development Goals'] = $user["sub_goals_count"];
                    }else{
                        $row['Active Career Development Goals'] = 0;
                    }
                }
                //Active Private Goals
                if(!$request->goal || $request->goal == 4){
                    if($user["goal_type_id"] == 4){
                        $row['Active Private Goals'] = $user["sub_goals_count"];
                    }else{
                        $row['Active Private Goals'] = 0;
                    }
                }
                $row['Organization'] = $user["organization"];
                $row['Level 1'] = $user["level1_program"];
                $row['Level 2'] = $user["level2_division"];
                $row['Level 3'] = $user["level3_branch"];
                $row['Level 4'] = $user["level4"];
                //$row['Reporting To'] = $user->reportingManager ? $user->reportingManager->name : '';
                                
                if(!$request->goal){
                    fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Total Active Goals']
                            , $row['Active Work Goals'], $row['Active Learning Goals'], $row['Active Career Development Goals'], $row['Active Private Goals']
                            , $row['Organization'],$row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                }elseif($request->goal == 1){
                    fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email']
                            , $row['Active Work Goals']
                            , $row['Organization'],$row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                }elseif($request->goal == 2){
                    fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email']
                            ,$row['Active Career Development Goals']
                            , $row['Organization'],$row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                }elseif($request->goal == 3){
                    fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email']
                            , $row['Active Learning Goals']
                            , $row['Organization'],$row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                }elseif($request->goal == 4){
                    fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email']
                            , $row['Active Private Goals']
                            , $row['Organization'],$row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'] ));
                }
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
        $count_raw .= " employee_name, employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4";
        if (!$request->tag || $request->tag == '[Blank]') {
            $count_raw .= " ,(select count(*) from goals ";
            $count_raw .= "    where users.id = goals.user_id ";
            $count_raw .= "      and not exists (select 'x' from goal_tags ";
            $count_raw .= "                       where goals.id = goal_tags.goal_id) ";
            $count_raw .= "      and goals.deleted_at is null and goals.is_library = 0 ";            
            $count_raw .= "      and employee_demo.guid <> '' ";
            
            $count_raw .= "     and ( ";
            $count_raw .= "            users.due_date_paused = 'N'";
            $count_raw .= "         )";

            $count_raw .= " ) as 'tag_0' ";
        }
        foreach ($tags as $tag) {
            $count_raw .= " ,(select count(*) from goal_tags, goals ";
            $count_raw .= "    where goals.id = goal_tags.goal_id "; 
            $count_raw .= "      and tag_id = " . $tag->id;  
            $count_raw .= "      and users.id = goals.user_id ";

            $count_raw .= "     and ( ";
            $count_raw .= "            users.due_date_paused = 'N'";
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
                    ->where(function($query) {
                            $query->where(function($query) {
                                $query->where('users.excused_flag', '<>', '1')
                                    ->orWhereNull('users.excused_flag');
                            });
                        })         
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
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

    public function conversationSummary(Request $request)
    {

        // send back the input parameters
        $this->preservedInputParams($request);

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        
        //get all employee number
        $query = UserDemoJrView::selectRaw("employee_id, empl_record, employee_name, 
                                organization, level1_program, level2_division,
                                level3_branch, level4,conversation_participants.role,
                                conversations.deleted_at,conversation_participants.conversation_id,
                                conversations.signoff_user_id,conversations.supervisor_signoff_id,
                                conversation_participants.participant_id,conversations.conversation_topic_id,
                        DATEDIFF ( next_conversation_date
                            , curdate() )
                    as overdue_in_days")
                ->leftJoin('conversation_participants', function($join) {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id');
                })
                ->leftJoin('conversations', function($join) {
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id');
                })        
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('due_date_paused', 'N')
                            ->orWhereNull('due_date_paused');
                    });
                })
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })
                ->whereNull('date_deleted')
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('excused_flag', '<>', '1')
                            ->orWhereNull('excused_flag');
                    });
                });                
        $all_employees = $query->get();
        
        // Chart1 -- Overdue
        $data = array();
        $data['chart1']['chart_id'] = 1;
        $data['chart1']['title'] = 'Next Conversation Due';
        $data['chart1']['legend'] = array_keys($this->overdue_groups);
        $data['chart1']['groups'] = array();
        foreach($this->overdue_groups as $key => $range)
        {            
            $subset = $all_employees->whereBetween('overdue_in_days', $range );
            $subset = array_unique(array_column($subset->toArray(), 'employee_id'));
            array_push( $data['chart1']['groups'],  [ 'name' => $key, 'value' => count($subset), 
                        ]);
        }        
        $conversations = $all_employees->filter(function ($all_employee) {
            return $all_employee->role == 'emp';
        });
        $total_unique_emp = count($conversations);  
        
        // Chart4 -- Open Conversation employees
        $open_conversations = $conversations->filter(function ($conversation) {
            return $conversation->signoff_user_id === null || $conversation->supervisor_signoff_id === null;
        }); 
        $topics = ConversationTopic::select('id','name')->get();
        $data['chart4']['chart_id'] = 4;
        $data['chart4']['title'] = 'Open Conversations by Topic';
        $data['chart4']['legend'] = $topics->pluck('name')->toArray();
        $data['chart4']['groups'] = array();
        foreach($topics as $topic)
        {
            $subset =$open_conversations->filter(function ($conversation) use($topic) {
                return $conversation->conversation_topic_id == $topic->id;
            }); 
            $subset = array_unique(array_column($subset->toArray(), 'employee_id'));
            $unique_emp = count($subset);    
            $per_emp = 0;
            if($total_unique_emp > 0) {
                $per_emp = ($unique_emp / $total_unique_emp) * 100;
            }
            array_push( $data['chart4']['groups'],  [ 'name' => $topic->name, 'value' => $unique_emp,
                        'topic_id' => $topic->id, 
                        ]);
        } 
        
        
        // Chart 5 -- Completed Conversation by employees
        $completed_conversations = $conversations->filter(function ($conversation) {
            return $conversation->signoff_user_id != null && $conversation->supervisor_signoff_id != null;
        }); 
        $data['chart5']['chart_id'] = 5;
        $data['chart5']['title'] = 'Completed Conversations by Topic';
        $data['chart5']['legend'] = $topics->pluck('name')->toArray();
        $data['chart5']['groups'] = array();
        foreach($topics as $topic)
        {
            $subset =$completed_conversations->filter(function ($conversation) use($topic) {
                return $conversation->conversation_topic_id == $topic->id;
            }); 
            $subset = array_unique(array_column($subset->toArray(), 'employee_id'));
            $unique_emp = count($subset);    
            $per_emp = 0;
            if($total_unique_emp > 0) {
                $per_emp = ($unique_emp / $total_unique_emp) * 100;
            }
            array_push( $data['chart5']['groups'],  [ 'name' => $topic->name, 'value' => $unique_emp, 
                    'topic_id' => $topic->id, 
                ]);
        } 
                
        // Chart6 -- Employee Has Open Conversation
        $employees = array_unique(array_column($all_employees->toArray(), 'employee_id'));
        $employees = count($employees);
        
        //employees with conversations      
        $employee_conversations = $all_employees->filter(function ($employee) {
            // Filter out if role is 'emp', deleted_at is null, and conversation_id is not null
            return $employee->role === 'emp' && $employee->deleted_at === null && $employee->conversation_id !== null;
        });
        
        //get employees has open conversations
        $users = $employee_conversations->filter(function ($employee_conversation) {
            return $employee_conversation->signoff_user_id === null || $employee_conversation->supervisor_signoff_id === null;
        });     
        $users = array_unique(array_column($users->toArray(), 'employee_id'));
        $has_conversation = count($users);
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
        //get employees has Completed conversations
        $users2 = $employee_conversations->filter(function ($employee_conversation) {
            return $employee_conversation->signoff_user_id != null && $employee_conversation->supervisor_signoff_id != null;
        }); 
        $users2 = array_unique(array_column($users2->toArray(), 'employee_id'));
        $has_conversation =  count($users2);
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
        
        return view('sysadmin.statistics.conversationsummary',compact('data'));

    }


    public function conversationSummaryExport(Request $request) {

        // SQL - Chart 1
        $sql_chart1 = User::selectRaw("users.*, employee_name, 
            employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                        DATEDIFF ( users.next_conversation_date, curdate() ) as overdue_in_days,
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
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->whereNull('employee_demo.date_deleted')
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('users.excused_flag', '<>', '1')
                            ->orWhereNull('users.excused_flag');
                    });
                }) ;
                
        // SQL - Chart 2
        $sql_chart2 = Conversation::selectRaw("conversations.*, users.employee_id, employee_name, users.email,
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
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->where('conversation_participants.role','<>','mgr')
                ->whereNull('employee_demo.date_deleted')        
                ->with('topic:id,name')
                ->with('signoff_user:id,name')
                ->with('signoff_supervisor:id,name');

         // SQL for Chart 3
         $sql_chart3 = Conversation::selectRaw("conversations.*, users.employee_id, employee_name, users.email,
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
            ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
            ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
            ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
            ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
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
            ->whereNull('employee_demo.date_deleted')
            ->where('conversation_participants.role','<>','mgr')
            ->with('topic:id,name')
            ->with('signoff_user:id,name')
            ->with('signoff_supervisor:id,name')
            ;
            
        // SQL - Chart 4
        $sql_chart4 = ConversationParticipant::selectRaw("conversations.*, conversation_topics.name as conversation_name, users.employee_id, employee_name, users.email,
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
                ->whereNull('employee_demo.date_deleted')        
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); });
             
                
        // SQL for Chart 5
         $sql_chart5 = ConversationParticipant::selectRaw("conversations.*, conversation_topics.name as conversation_name, users.employee_id, employee_name, users.email,
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
            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
            ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
            ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
            ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
            ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
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
                                "Next Conversation Due",  'Due Date Category',
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
                        $row['Employee ID'] = "[".$conversation->employee_id."]";
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
               
            case 4:

                $filename = 'Open Conversation By Topic.csv';
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
        
                $columns = ["Employee ID", "Employee Name", "Email", "Conversation Topic", "Conversation Participant",
                        "Conversation Due Date","Employee Sign-Off", "Supervisor Sign-off", 
                                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", 
                           ];
                
                $callback = function() use($conversations_unique, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    
                    foreach ($conversations_unique as $conversation) {
                        $row['Employee ID'] = "[".$conversation->employee_id."]";
                        $row['Name'] = $conversation->employee_name;
                        $row['Email'] = $conversation->email;
                        $row['Conversation Topic'] = $conversation->conversation_name;
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
                        $row['Conversation Topic'], $row['Conversation Participant'], 
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

                $filename = 'Completed Conversation By Topic.csv';
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
        
                $columns = ["Employee ID", "Employee Name", "Email","Conversation Topic","Conversation Participant",
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
                            $row['Conversation Topic'] = $conversation->conversation_name;
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
                            $row["Conversation Topic"],$row['Conversation Participant'], 
                        $row["Employee Sign-Off"], $row["Employee Sign-Off Time"], $row["Supervisor Sign-off"],$row["Supervisor Sign-off Time"],
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
                ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('users.excused_flag', '<>', '1')
                                ->orWhereNull('users.excused_flag');
                        });
                    })         
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); });

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

        return view('sysadmin.statistics.sharedsummary',compact('data'));

    } 

    public function sharedSummaryExport(Request $request) 
    {

      $selected_ids = $request->ids ? explode(',', $request->ids) : [];

      $sql = User::selectRaw("users.*,
                employee_name, employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
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
            ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('users.excused_flag', '<>', '1')
                                ->orWhereNull('users.excused_flag');
                        });
                    })         
            ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
            ->when( $request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
            ->when( $request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
            ->when( $request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
            ->when( $request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
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
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                    ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                    ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                    ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                    ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                    ->whereNull('employee_demo.date_deleted');
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
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('employee_demo_tree.level4_key', $request->dd_level4); })
                ->whereNull('employee_demo.date_deleted')
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
    
    public function fileReports(Request $request)
    {

        $data = array();
        $submit = false;
        $employee_id = '';
        $start_date = '';
        $end_date = '';
        $record_types = array();
                
        if($request->btn_search){
            $data["error"]["employee_id"] = 0;
            $data["error"]["start_date"] = 0;
            $data["error"]["end_date"] = 0;
            $data["error"]["record_types"] = 0;
            $data["active_goals"] = array();
            $data["past_goals"] = array();
            $data["open_conversations"] = array();
            $data["completed_conversations"] = array();
            
            $employee_id = $request->employee_id;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $record_types = $request->record_types;
            
            if ($request->employee_id && $request->start_date && $request->end_date && $request->record_types) {
                $submit = true;
                if(!empty($request->record_types)){
                    foreach($request->record_types as $item){
                        if($item == "active_goals"){
                            $active_goals = Goal::selectRaw("goals.id, users.name, goals.title, goals.start_date, goals.target_date, goals.created_at, employee_demo.organization, employee_demo.business_unit")
                                    ->join('users', function($join) {
                                        $join->on('users.id', '=', 'goals.user_id');   
                                    }) 
                                    ->join('employee_demo', function($join) {
                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                    })
                                    ->whereNull('goals.deleted_at')
                                    ->where('status','=','active')
                                    ->where(function($query) use($request) {   
                                            $query->where(function($query) use($request) {   
                                                            $query ->where([
                                                                ['goals.created_at','>=',$request->start_date . ' 00:00:00'],
                                                                ['goals.created_at','<=',$request->end_date . ' 23:59:59']
                                                            ]);
                                        });
                                    })  
                                    ->where('employee_demo.employee_id', '=', $request->employee_id)  
                                    ->orderBy('goals.created_at', 'DESC')        
                                    ->get(); 
                            $data["active_goals"] = $active_goals;
                            
                        }
                        if($item == "past_goals"){
                            $past_goals = Goal::selectRaw("goals.id, users.name, goals.title, goals.start_date, goals.target_date, goals.created_at, employee_demo.organization, employee_demo.business_unit")
                                    ->join('users', function($join) {
                                        $join->on('users.id', '=', 'goals.user_id');   
                                    }) 
                                    ->join('employee_demo', function($join) {
                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                    })
                                    ->whereNull('goals.deleted_at')
                                    ->where('status','<>','active')
                                    ->where(function($query) use($request) {   
                                            $query->where(function($query) use($request) {   
                                                            $query ->where([
                                                                ['goals.created_at','>=',$request->start_date . ' 00:00:00'],
                                                                ['goals.created_at','<=',$request->end_date . ' 23:59:59']
                                                            ]);
                                        });
                                    })  
                                    ->where('employee_demo.employee_id', '=', $request->employee_id)     
                                    ->orderBy('goals.created_at', 'DESC')              
                                    ->get();
                            $data["past_goals"] = $past_goals;
                        }
                        if($item == "open_conversations"){
                            $open_conversations = ConversationParticipant::selectRaw("conversation_participants.conversation_id, users.name, conversation_topics.name as topic, employee_demo.organization, employee_demo.business_unit, conversations.created_at")
                                                  ->join('conversations', function($join) {
                                                        $join->on('conversations.id', '=', 'conversation_participants.conversation_id');   
                                                  }) 
                                                  ->join('conversation_topics', function($join) {
                                                        $join->on('conversations.conversation_topic_id', '=', 'conversation_topics.id');   
                                                  }) 
                                                  ->join('users', function($join) {
                                                        $join->on('users.id', '=', 'conversation_participants.participant_id');   
                                                  }) 
                                                  ->join('employee_demo', function($join) {
                                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                                  })
                                                  ->where([
                                                        ['conversations.created_at','>=',$request->start_date . ' 00:00:00'],
                                                        ['conversations.created_at','<=',$request->end_date . ' 23:59:59']
                                                  ])
                                                  ->where(function($query) {
                                                        $query->where(function($query) {
                                                            $query->whereNull('signoff_user_id')
                                                                ->orWhereNull('supervisor_signoff_id');
                                                        });
                                                    })
                                                  ->whereNull('conversations.deleted_at')
                                                  ->where('conversation_participants.role', '=', 'emp')          
                                                  ->where('employee_demo.employee_id', '=', $request->employee_id)   
                                                  ->orderBy('conversations.created_at', 'DESC')                
                                                  ->get();
                                $data["open_conversations"] = $open_conversations;   
                        }
                        if($item == "completed_conversations"){
                            $completed_conversations = ConversationParticipant::selectRaw("conversation_participants.conversation_id, users.name, conversation_topics.name as topic, employee_demo.organization, employee_demo.business_unit,GREATEST(conversations.sign_off_time, conversations.supervisor_signoff_time) as latest_update")
                                                  ->join('conversations', function($join) {
                                                        $join->on('conversations.id', '=', 'conversation_participants.conversation_id');   
                                                  })
                                                  ->join('conversation_topics', function($join) {
                                                        $join->on('conversations.conversation_topic_id', '=', 'conversation_topics.id');   
                                                  }) 
                                                  ->join('users', function($join) {
                                                        $join->on('users.id', '=', 'conversation_participants.participant_id');
                                                  }) 
                                                  ->join('employee_demo', function($join) {
                                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                                  })
                                                  ->where([
                                                        ['conversations.sign_off_time','>=',$request->start_date . ' 00:00:00'],
                                                        ['conversations.supervisor_signoff_time','>=',$request->start_date . ' 00:00:00'],
                                                  ])
                                                  ->where([
                                                        ['conversations.sign_off_time','<=',$request->end_date . ' 23:59:59'],
                                                        ['conversations.supervisor_signoff_time','<=',$request->end_date . ' 23:59:59'],
                                                  ])        
                                                  ->where(function($query) {
                                                        $query->where(function($query) {
                                                            $query->whereNotNull('signoff_user_id')
                                                                  ->whereNotNull('supervisor_signoff_id');
                                                        });
                                                    })
                                                  ->whereNull('conversations.deleted_at')
                                                  ->where('conversation_participants.role', '=', 'emp')             
                                                  ->where('employee_demo.employee_id', '=', $request->employee_id)  
                                                  ->orderBy('conversations.id', 'DESC')              
                                                  ->get();
                            $data["completed_conversations"] = $completed_conversations;  
                        }
                    }
                } 
            } else {
                if (!$request->employee_id){
                    $data["error"]["employee_id"] = 1;
                }
                if (!$request->start_date) {
                    $data["error"]["start_date"] = 1;
                }
                if (!$request->end_date) {
                    $data["error"]["end_date"] = 1;
                }
                if (!$request->record_types){
                    $data["error"]["record_types"] = 1;
                }
            }
        }
        //echo $start_date;exit;
        return view('sysadmin.statistics.filereports', compact('data', 'submit', 'employee_id', 'start_date', 'end_date', 'record_types'));
    }
    
    
    public function fileReportsExport(Request $request)
    {
        if($request->type == 'active_goal'){
            $data = array();
            $active_goals = Goal::selectRaw("goals.id, users.name, goals.what, goals.measure_of_success, goals.created_at, goals.title, goals.start_date, goals.target_date, employee_demo.organization, employee_demo.business_unit")
                                    ->join('users', function($join) {
                                        $join->on('users.id', '=', 'goals.user_id');   
                                    }) 
                                    ->join('employee_demo', function($join) {
                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                    })
                                    ->whereNull('goals.deleted_at')
                                    ->where('status','=','active')
                                    ->where(function($query) use($request) {   
                                            $query->where(function($query) use($request) {   
                                                            $query ->where([
                                                                ['goals.created_at','>=',$request->start_date . ' 00:00:00'],
                                                                ['goals.created_at','<=',$request->end_date . ' 23:59:59']
                                                            ]);
                                        });
                                    })
                                    ->where('employee_demo.employee_id', '=', $request->employee_id)  
                                    ->orderBy('goals.created_at', 'DESC')        
                                    ->get(); 
            foreach ($active_goals as $active_goal){
                $goal_id = $active_goal->id;
                $goal_comments = DB::table('goal_comments')
                                    ->selectRaw("goal_comments.*, users.name, employee_demo.organization, employee_demo.organization, employee_demo.business_unit")
                                    ->join('users', function($join) {
                                            $join->on('users.id', '=', 'goal_comments.user_id');   
                                        }) 
                                    ->join('employee_demo', function($join) {
                                            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                        })
                                    ->where('goal_comments.goal_id','=',$goal_id)   
                                    ->get(); 
                $comments = array();
                $i = 0;
                foreach($goal_comments as $goal_item){
                    $comments[$i]['id'] = $goal_item->id; 
                    $comments[$i]['goal_id'] = $goal_item->goal_id; 
                    $comments[$i]['user_id'] = $goal_item->user_id; 
                    $comments[$i]['comment'] = $goal_item->comment; 
                    $comments[$i]['created_at'] = $goal_item->created_at; 
                    $comments[$i]['updated_at'] = $goal_item->updated_at; 
                    $comments[$i]['deleted_at'] = $goal_item->deleted_at; 
                    $comments[$i]['parent_id'] = $goal_item->parent_id; 
                    $comments[$i]['name'] = $goal_item->name; 
                    
                    $i++;
                }               
                                        
                $comments = $this->getCommentTree($comments, '');
                $commentTree = $this->getCommentTreeHtml($comments);
                
                $item["selected_goal"] = $active_goal;
                $item["selected_goal_comments"] = $commentTree;                
                
                array_push($data, $item);
            }              
            
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $dompdf = new Dompdf($options);
            // Fetch the HTML content to be converted to PDF
            $html = view('sysadmin.statistics.filereportsbulkgoalexport', compact('data'))->render();
            // Load HTML content
            $dompdf->loadHtml($html);
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            // Render the HTML as PDF
            $dompdf->render();
            // Output the generated PDF to the browser
            return $dompdf->stream('employee_record_active_goals.pdf');                
                
            //return view('sysadmin.statistics.filereportsexport', compact('data'));
            
        } elseif($request->type == 'past_goal'){
            $data = array();
            $past_goals = Goal::selectRaw("goals.id, users.name, goals.what, goals.measure_of_success, goals.title, goals.created_at, goals.start_date, goals.target_date, employee_demo.organization, employee_demo.business_unit")
                                    ->join('users', function($join) {
                                        $join->on('users.id', '=', 'goals.user_id');   
                                    }) 
                                    ->join('employee_demo', function($join) {
                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                    })
                                    ->whereNull('goals.deleted_at')
                                    ->where('status','<>','active')
                                    ->where(function($query) use($request) {   
                                            $query->where(function($query) use($request) {   
                                                            $query ->where([
                                                                ['goals.created_at','>=',$request->start_date . ' 00:00:00'],
                                                                ['goals.created_at','<=',$request->end_date . ' 23:59:59']
                                                            ]);
                                        });
                                    })  
                                    ->where('employee_demo.employee_id', '=', $request->employee_id)     
                                    ->orderBy('goals.created_at', 'DESC')              
                                    ->get();
            foreach ($past_goals as $past_goal){
                $goal_id = $past_goal->id;
                $goal_comments = DB::table('goal_comments')
                                    ->selectRaw("goal_comments.*, users.name, employee_demo.organization, employee_demo.organization, employee_demo.business_unit")
                                    ->join('users', function($join) {
                                            $join->on('users.id', '=', 'goal_comments.user_id');   
                                        }) 
                                    ->join('employee_demo', function($join) {
                                            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                        })
                                    ->where('goal_comments.goal_id','=',$goal_id)           
                                    ->get(); 
                $comments = array();
                $i = 0;
                foreach($goal_comments as $goal_item){
                    $comments[$i]['id'] = $goal_item->id; 
                    $comments[$i]['goal_id'] = $goal_item->goal_id; 
                    $comments[$i]['user_id'] = $goal_item->user_id; 
                    $comments[$i]['comment'] = $goal_item->comment; 
                    $comments[$i]['created_at'] = $goal_item->created_at; 
                    $comments[$i]['updated_at'] = $goal_item->updated_at; 
                    $comments[$i]['deleted_at'] = $goal_item->deleted_at; 
                    $comments[$i]['parent_id'] = $goal_item->parent_id; 
                    $comments[$i]['name'] = $goal_item->name; 
                    
                    $i++;
                }               
                                        
                $comments = $this->getCommentTree($comments, '');
                $commentTree = $this->getCommentTreeHtml($comments);
                
                $item["selected_goal"] = $past_goal;
                $item["selected_goal_comments"] = $commentTree;                
                
                array_push($data, $item);
            }              
            
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $dompdf = new Dompdf($options);
            // Fetch the HTML content to be converted to PDF
            $html = view('sysadmin.statistics.filereportsbulkgoalexport', compact('data'))->render();
            // Load HTML content
            $dompdf->loadHtml($html);
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            // Render the HTML as PDF
            $dompdf->render();
            // Output the generated PDF to the browser
            return $dompdf->stream('employee_record_past_goals.pdf');                
                
            //return view('sysadmin.statistics.filereportsexport', compact('data'));
            
        }elseif($request->type == 'open_conversation'){
            $data = array();
            $open_conversations = ConversationParticipant::selectRaw("conversation_participants.conversation_id, users.name, conversation_topics.name as topic, employee_demo.organization, employee_demo.business_unit, conversations.created_at")
                                                  ->join('conversations', function($join) {
                                                        $join->on('conversations.id', '=', 'conversation_participants.conversation_id');   
                                                  }) 
                                                  ->join('conversation_topics', function($join) {
                                                        $join->on('conversations.conversation_topic_id', '=', 'conversation_topics.id');   
                                                  }) 
                                                  ->join('users', function($join) {
                                                        $join->on('users.id', '=', 'conversation_participants.participant_id');   
                                                  }) 
                                                  ->join('employee_demo', function($join) {
                                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                                  })
                                                  ->where([
                                                        ['conversations.created_at','>=',$request->start_date . ' 00:00:00'],
                                                        ['conversations.created_at','<=',$request->end_date . ' 23:59:59']
                                                  ])
                                                  ->where(function($query) {
                                                        $query->where(function($query) {
                                                            $query->whereNull('signoff_user_id')
                                                                ->orWhereNull('supervisor_signoff_id');
                                                        });
                                                    })
                                                  ->whereNull('conversations.deleted_at')
                                                  ->where('conversation_participants.role', '=', 'emp')          
                                                  ->where('employee_demo.employee_id', '=', $request->employee_id)   
                                                  ->orderBy('conversations.created_at', 'DESC')                
                                                  ->get();
            $i = 0;                                        
            foreach($open_conversations as $item){
                $conversation_id = $item->conversation_id;                
                
                $conversation = Conversation::selectRaw("conversations.*, conversation_topics.name as topic, users.employee_id, employee_demo.employee_name, users.email,
                employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                        users.next_conversation_date as next_due_date, supervisor.name as sign_supervisor_name, employee.name as sign_employee_name")
                ->whereNull('deleted_at')                        
                ->join('conversation_participants','conversations.id','conversation_participants.conversation_id')        
                ->join('users', 'users.id', 'conversation_participants.participant_id') 
                ->join('conversation_topics','conversations.conversation_topic_id','conversation_topics.id')                
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->leftJoin('users as supervisor', 'supervisor.id', '=', 'conversations.supervisor_signoff_id')
                ->leftJoin('users as employee', 'employee.id', '=', 'conversations.signoff_user_id')   
                ->where('conversation_participants.role','=','emp')
                ->where('conversations.id','=',$conversation_id)
                ->whereNull('employee_demo.date_deleted')        
                ->get();
                
                $participants = DB::table('conversation_participants')
                                        ->select('users.name')
                                        ->join('users', 'conversation_participants.participant_id', '=', 'users.id')
                                        ->where('conversation_participants.conversation_id', $conversation_id)
                                        ->get();      
                $participants_arr = array();
                foreach($participants as $participant){
                    $participants_arr[] = $participant->name;
                }
                $conversation[0]->participants = implode(', ', $participants_arr );
                
                $item = $conversation[0];
                $data[$i] = $item;
                $i++;
            }               
            
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $dompdf = new Dompdf($options);
            // Fetch the HTML content to be converted to PDF
            $html = view('sysadmin.statistics.filereportsbulkconversationexport', compact('data'))->render();
            // Load HTML content
            $dompdf->loadHtml($html);
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            // Render the HTML as PDF
            $dompdf->render();
            // Output the generated PDF to the browser
            return $dompdf->stream('employee_record_open_conversations.pdf');                
                
            //return view('sysadmin.statistics.filereportsexport', compact('data'));
            
        }elseif($request->type == 'completed_conversation'){
            $data = array();
            $completed_conversations = ConversationParticipant::selectRaw("conversation_participants.conversation_id, users.name, conversation_topics.name as topic, employee_demo.organization, employee_demo.business_unit,GREATEST(conversations.sign_off_time, conversations.supervisor_signoff_time) as latest_update")
                                                  ->join('conversations', function($join) {
                                                        $join->on('conversations.id', '=', 'conversation_participants.conversation_id');   
                                                  })
                                                  ->join('conversation_topics', function($join) {
                                                        $join->on('conversations.conversation_topic_id', '=', 'conversation_topics.id');   
                                                  }) 
                                                  ->join('users', function($join) {
                                                        $join->on('users.id', '=', 'conversation_participants.participant_id');
                                                  }) 
                                                  ->join('employee_demo', function($join) {
                                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                                  })
                                                  ->where([
                                                        ['conversations.sign_off_time','>=',$request->start_date . ' 00:00:00'],
                                                        ['conversations.supervisor_signoff_time','>=',$request->start_date . ' 00:00:00'],
                                                  ])
                                                  ->where([
                                                        ['conversations.sign_off_time','<=',$request->end_date . ' 23:59:59'],
                                                        ['conversations.supervisor_signoff_time','<=',$request->end_date . ' 23:59:59'],
                                                  ])        
                                                  ->where(function($query) {
                                                        $query->where(function($query) {
                                                            $query->whereNotNull('signoff_user_id')
                                                                  ->whereNotNull('supervisor_signoff_id');
                                                        });
                                                    })
                                                  ->whereNull('conversations.deleted_at')
                                                  ->where('conversation_participants.role', '=', 'emp')             
                                                  ->where('employee_demo.employee_id', '=', $request->employee_id)  
                                                  ->orderBy('conversations.id', 'DESC')              
                                                  ->get();
            
            $i = 0;                                        
            foreach($completed_conversations as $item){
                $conversation_id = $item->conversation_id;                
                
                $conversation = Conversation::selectRaw("conversations.*, conversation_topics.name as topic, users.employee_id, employee_demo.employee_name, users.email,
                employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                        users.next_conversation_date as next_due_date, supervisor.name as sign_supervisor_name, employee.name as sign_employee_name,GREATEST(conversations.sign_off_time, conversations.supervisor_signoff_time) as latest_update")
                ->whereNull('deleted_at')                        
                ->join('conversation_participants','conversations.id','conversation_participants.conversation_id')        
                ->join('users', 'users.id', 'conversation_participants.participant_id') 
                ->join('conversation_topics','conversations.conversation_topic_id','conversation_topics.id')                
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->leftJoin('users as supervisor', 'supervisor.id', '=', 'conversations.supervisor_signoff_id')
                ->leftJoin('users as employee', 'employee.id', '=', 'conversations.signoff_user_id')   
                ->where('conversation_participants.role','=','emp')
                ->where('conversations.id','=',$conversation_id)
                ->whereNull('employee_demo.date_deleted')        
                ->get();
                
                $participants = DB::table('conversation_participants')
                                        ->select('users.name')
                                        ->join('users', 'conversation_participants.participant_id', '=', 'users.id')
                                        ->where('conversation_participants.conversation_id', $conversation_id)
                                        ->get();      
                $participants_arr = array();
                foreach($participants as $participant){
                    $participants_arr[] = $participant->name;
                }
                $conversation[0]->participants = implode(', ', $participants_arr );
                
                $item = $conversation[0];
                $data[$i] = $item;
                $i++;
            }               
            
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $dompdf = new Dompdf($options);
            // Fetch the HTML content to be converted to PDF
            $html = view('sysadmin.statistics.filereportsbulkconversationexport', compact('data'))->render();
            // Load HTML content
            $dompdf->loadHtml($html);
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            // Render the HTML as PDF
            $dompdf->render();
            // Output the generated PDF to the browser
            return $dompdf->stream('employee_record_completed_conversations.pdf');                
                
            //return view('sysadmin.statistics.filereportsexport', compact('data'));
            
        }else {
            if($request->type == 'selected_goal'){
                $goal_id = $request->id;
                $selected_goal = Goal::selectRaw("users.name, goals.*, employee_demo.organization, employee_demo.business_unit")
                                    ->join('users', function($join) {
                                        $join->on('users.id', '=', 'goals.user_id');   
                                    }) 
                                    ->join('employee_demo', function($join) {
                                        $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                    })
                                    ->whereNull('goals.deleted_at')
                                    ->where('goals.id','=',$goal_id)           
                                    ->get();
                $goal_comments = DB::table('goal_comments')
                                    ->selectRaw("goal_comments.*, users.name, employee_demo.organization, employee_demo.organization, employee_demo.business_unit")
                                    ->join('users', function($join) {
                                            $join->on('users.id', '=', 'goal_comments.user_id');   
                                        }) 
                                    ->join('employee_demo', function($join) {
                                            $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                                        })
                                    ->where('goal_comments.goal_id','=',$goal_id)           
                                    ->get(); 
                $comments = array();
                $i = 0;
                foreach($goal_comments as $item){
                    $comments[$i]['id'] = $item->id; 
                    $comments[$i]['goal_id'] = $item->goal_id; 
                    $comments[$i]['user_id'] = $item->user_id; 
                    $comments[$i]['comment'] = $item->comment; 
                    $comments[$i]['created_at'] = $item->created_at; 
                    $comments[$i]['updated_at'] = $item->updated_at; 
                    $comments[$i]['deleted_at'] = $item->deleted_at; 
                    $comments[$i]['parent_id'] = $item->parent_id; 
                    $comments[$i]['name'] = $item->name; 
                    
                    $i++;
                }                                          
                                        
                $comments = $this->getCommentTree($comments, '');
                $commentTree = $this->getCommentTreeHtml($comments);
                                
                $data["selected_goal"] = $selected_goal[0];
                $data["selected_goal_comments"] = $commentTree;
                
                $options = new Options();
                $options->set('defaultFont', 'Arial');
                $dompdf = new Dompdf($options);
                // Fetch the HTML content to be converted to PDF
                $html = view('sysadmin.statistics.filereportsexport', compact('data'))->render();
                // Load HTML content
                $dompdf->loadHtml($html);
                // Set paper size and orientation
                $dompdf->setPaper('A4', 'portrait');
                // Render the HTML as PDF
                $dompdf->render();
                // Output the generated PDF to the browser
                return $dompdf->stream('employee_record.pdf');                
                
                //return view('sysadmin.statistics.filereportsexport', compact('data'));
            }
            if($request->type == 'selected_conversation'){
                $conversation_id = $request->id;                
                
                $conversation = Conversation::selectRaw("conversations.*, conversation_topics.name as topic, users.employee_id, employee_demo.employee_name, users.email,
                employee_demo_tree.organization, employee_demo_tree.level1_program, employee_demo_tree.level2_division, employee_demo_tree.level3_branch, employee_demo_tree.level4,
                        users.next_conversation_date as next_due_date, supervisor.name as sign_supervisor_name, employee.name as sign_employee_name,GREATEST(conversations.sign_off_time, conversations.supervisor_signoff_time) as latest_update")
                ->whereNull('deleted_at')                        
                ->join('conversation_participants','conversations.id','conversation_participants.conversation_id')        
                ->join('users', 'users.id', 'conversation_participants.participant_id') 
                ->join('conversation_topics','conversations.conversation_topic_id','conversation_topics.id')                
                ->join('employee_demo', function($join) {
                    $join->on('employee_demo.employee_id', '=', 'users.employee_id');
                })
                ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                ->leftJoin('users as supervisor', 'supervisor.id', '=', 'conversations.supervisor_signoff_id')
                ->leftJoin('users as employee', 'employee.id', '=', 'conversations.signoff_user_id')   
                ->where('conversation_participants.role','=','emp')
                ->where('conversations.id','=',$conversation_id)
                ->whereNull('employee_demo.date_deleted')        
                ->get();
                
                $participants = DB::table('conversation_participants')
                                        ->select('users.name')
                                        ->join('users', 'conversation_participants.participant_id', '=', 'users.id')
                                        ->where('conversation_participants.conversation_id', $conversation_id)
                                        ->get();      
                $participants_arr = array();
                foreach($participants as $participant){
                    $participants_arr[] = $participant->name;
                }
                $conversation[0]->participants = implode(', ', $participants_arr );
                
                $data["selected_conversation"] = $conversation[0];
                
                
                $options = new Options();
                $options->set('defaultFont', 'Arial');
                $dompdf = new Dompdf($options);
                // Fetch the HTML content to be converted to PDF
                $html = view('sysadmin.statistics.filereportsexport', compact('data'))->render();
                // Load HTML content
                $dompdf->loadHtml($html);
                // Set paper size and orientation
                $dompdf->setPaper('A4', 'portrait');
                // Render the HTML as PDF
                $dompdf->render();
                // Output the generated PDF to the browser
                return $dompdf->stream('employee_record.pdf');                
                
                //return view('sysadmin.statistics.filereportsexport', compact('data'));      
            }
        }
                
                
    }

    private function getCommentTree($input, $parentId) {
        $output = array();
        foreach ($input as $item) {
            if ($item['parent_id'] == $parentId) {
                $item['reply'] = $this->getCommentTree($input, $item['id']);
                $output[] = $item;
            }
        }
        return $output;
    }
    
    private function getCommentTreeHtml($arr, $level=0) {
        $output = '';
        $prepend = str_repeat(' ', $level);
        $output .= $prepend . '<ul>' . PHP_EOL;
        foreach($arr as $comment) {
            if($comment['deleted_at'] != ''){
                $comment_content = '<p>Comment is deleted</p>';
            }else{
                $comment_content = $comment['comment'];
            }
            $output .= $prepend . '    <li>' . $comment['name'] . ' ' . date('d/m/Y h:m:s', strtotime($comment['created_at'])) . ' ' . $comment_content . PHP_EOL;
            if (!empty($comment['reply'])) {
                $output .= $this->getCommentTreeHtml($comment['reply'], $level+1);
            }
            $output .= $prepend . '    </li>' . PHP_EOL;
        }
        $output .= $prepend . '</ul>' . PHP_EOL;
        return $output;
    }
    
    
    public function conversationStatus(Request $request)
    {

        // send back the input parameters
        $this->preservedInputParams($request);

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        
        
        // Chart6 -- Employee Has Open Conversation
         
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
                ->count();
         
        //get employees has open conversations
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
        //get employees has Completed conversations
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
        return view('sysadmin.statistics.conversationstatus',compact('data'));

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
                    $excludedIds = $users->pluck('employee_id')->toArray();
                    $sql_6_no = UserDemoJrView::selectRaw("employee_id, employee_name, employee_email, next_conversation_date, reporting_to_name,
                    organization, level1_program, level2_division, level3_branch, level4")
                            ->whereNotIn('employee_id', $excludedIds)
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
                            ->when( $request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); });
                            
                    $users_no =  $sql_6_no->get();    
                    $users = $users_no->unique('employee_id');        
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
                    $excludedIds = $users->pluck('employee_id')->toArray();
                    $sql_7_no = UserDemoJrView::selectRaw("employee_id, employee_name, employee_email, next_conversation_date, reporting_to_name,
                    organization, level1_program, level2_division, level3_branch, level4
         ")
                            ->whereNotIn('employee_id', $excludedIds)
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
                            ->get();
                    $users_no =  $sql_7_no->get();    
                    $users = $users_no->unique('employee_id');            
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
