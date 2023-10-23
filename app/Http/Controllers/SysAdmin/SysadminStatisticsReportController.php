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
use App\Models\OrganizationTree;
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
        $from_stmt = "(select user_demo_jr_view.user_id, user_demo_jr_view.employee_email, user_demo_jr_view.employee_id, user_demo_jr_view.empl_record
                    , user_demo_jr_view.guid, user_demo_jr_view.reporting_to, 
                        (select count(*) from goals where goals.user_id = user_demo_jr_view.user_id
                        and goals.status = 'active' and goals.deleted_at is null and goals.is_library = 0 ";
        if ($goal_type_id != ''){                        
            $from_stmt .= " and goals.goal_type_id =".  $goal_type_id ;
        }    
        $from_stmt .= ") as goals_count from user_demo_jr_view WHERE 
        (user_demo_jr_view.excused_flag IS NULL OR user_demo_jr_view.excused_flag <> 1) 
        AND 
        (user_demo_jr_view.due_date_paused = 'N' OR user_demo_jr_view.due_date_paused IS NULL) 
        AND
        user_demo_jr_view.date_deleted IS NULL
        ) AS A";

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

        $level0 = '';
        $level1 = '';
        $level2 = '';
        $level3 = '';
        $level4 = '';

        if($request->dd_level0) {
            $level0 = $this->getOrgLevel($request->dd_level0);
            $level0_name = $level0->name;
            $request->session()->flash('dd_level0_name', $level0_name);
        }

        if($request->dd_level1) {
            $level1 = $this->getOrgLevel($request->dd_level1);
            $level1_name = $level1->name;
            $request->session()->flash('dd_level1_name', $level1_name);
        }

        if($request->dd_level2) {
            $level2 = $this->getOrgLevel($request->dd_level2);
            $level2_name = $level2->name;
            $request->session()->flash('dd_level2_name', $level2_name);
        }

        if($request->dd_level3) {
            $level3 = $this->getOrgLevel($request->dd_level3);
            $level3_name = $level3->name;
            $request->session()->flash('dd_level3_name', $level3_name);
        }

        if($request->dd_level4) {
            $level4 = $this->getOrgLevel($request->dd_level4);
            $level4_name = $level4->name;
            $request->session()->flash('dd_level4_name', $level4_name);
        }


        $types = GoalType::orderBy('id')->get();

        $types->prepend( new GoalType()  ) ;

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
        //->where('goal_types.name','<>', 'Private')     
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

        $goal_count_cal = Goal::selectRaw("user_demo_jr_view.employee_id, COUNT(goals.id) AS goals_count, goals.goal_type_id")
        ->join('user_demo_jr_view', 'goals.user_id', 'user_demo_jr_view.user_id')
        ->join('goal_types', 'goals.goal_type_id', 'goal_types.id')
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
            //->where('goal_types.name','<>', 'Private')    
            ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
            ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
            ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
            ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
            ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })            
            ->groupBy(['user_demo_jr_view.employee_id', 'goals.goal_type_id']);
                    
        $goal_count_cal = $goal_count_cal->get()->toArray();

        $convertedArray = [];
        $groupedData = [];
        $toal_goal_counts = 0;
        $sub_users = 0;
        foreach ($goal_count_cal as $item) {
            $employee_id = $item['employee_id'];

            $sub_users++;

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
            $groupedData[$key]['goals_count'] ++;
        }

        $groupedData[0]['goals_count'] = $total_number_emp - $sub_users;
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
            $goal_name = $type->name ? $type->name : '';
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
            $data[$goal_name] = [ 
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

                    array_push( $data[$goal_name]['groups'], [ 'name' => $key, 'value' => $goals_count, 
                        'goal_id' => $goal_id, 
                    ]);
            }

        }
        
        $notype_data["groups"][0]["name"] = 0;    
        $notype_data["groups"][0]["value"] = 0;   
        $notype_data["groups"][0]["goal_id"] = '';
        
        $result_1 = DB::table(function ($query) use($request){
            $query->select(
                    DB::raw('user_demo_jr_view.employee_id, goal_type_id, COUNT(goals.id) AS goals_count')
                )
                ->from('goals')
                ->join('user_demo_jr_view', 'goals.user_id', '=', 'user_demo_jr_view.user_id')
                ->join('goal_types', 'goals.goal_type_id', '=', 'goal_types.id')
                ->where('goals.status', '=', 'active')
                ->whereNull('goals.deleted_at')
                ->where('goals.is_library', '=', 0)
                ->where(function ($query) {
                    $query->where('user_demo_jr_view.due_date_paused', '=', 'N')
                        ->orWhereNull('user_demo_jr_view.due_date_paused');
                })
                ->where(function ($query) {
                    $query->where('user_demo_jr_view.excused_flag', '<>', 1)
                        ->orWhereNull('user_demo_jr_view.excused_flag');
                })
                ->whereNull('user_demo_jr_view.date_deleted')
                //->where('goal_types.name', '<>', 'Private')
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); }) 
                ->groupBy(['user_demo_jr_view.employee_id', 'goal_type_id'])
                ->havingRaw('goals_count BETWEEN 1 AND 5');
            }, 't')
            ->select('t.employee_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('t.employee_id')
            ->get()->toArray();

        $notype_count_1 = count($result_1);

        $result_2 = DB::table(function ($query) use($request){
            $query->select(
                    DB::raw('user_demo_jr_view.employee_id, goal_type_id, COUNT(goals.id) AS goals_count')
                )
                ->from('goals')
                ->join('user_demo_jr_view', 'goals.user_id', '=', 'user_demo_jr_view.user_id')
                ->join('goal_types', 'goals.goal_type_id', '=', 'goal_types.id')
                ->where('goals.status', '=', 'active')
                ->whereNull('goals.deleted_at')
                ->where('goals.is_library', '=', 0)
                ->where(function ($query) {
                    $query->where('user_demo_jr_view.due_date_paused', '=', 'N')
                        ->orWhereNull('user_demo_jr_view.due_date_paused');
                })
                ->where(function ($query) {
                    $query->where('user_demo_jr_view.excused_flag', '<>', 1)
                        ->orWhereNull('user_demo_jr_view.excused_flag');
                })
                ->whereNull('user_demo_jr_view.date_deleted')
                //->where('goal_types.name', '<>', 'Private')
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); }) 
                ->groupBy(['user_demo_jr_view.employee_id', 'goal_type_id'])
                ->havingRaw('goals_count BETWEEN 6 AND 10');
            }, 't')
            ->select('t.employee_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('t.employee_id')
            ->get()->toArray();

        $notype_count_2 = count($result_2);

        $result_3 = DB::table(function ($query) use($request){
            $query->select(
                    DB::raw('user_demo_jr_view.employee_id, goal_type_id, COUNT(goals.id) AS goals_count')
                )
                ->from('goals')
                ->join('user_demo_jr_view', 'goals.user_id', '=', 'user_demo_jr_view.user_id')
                ->join('goal_types', 'goals.goal_type_id', '=', 'goal_types.id')
                ->where('goals.status', '=', 'active')
                ->whereNull('goals.deleted_at')
                ->where('goals.is_library', '=', 0)
                ->where(function ($query) {
                    $query->where('user_demo_jr_view.due_date_paused', '=', 'N')
                        ->orWhereNull('user_demo_jr_view.due_date_paused');
                })
                ->where(function ($query) {
                    $query->where('user_demo_jr_view.excused_flag', '<>', 1)
                        ->orWhereNull('user_demo_jr_view.excused_flag');
                })
                ->whereNull('user_demo_jr_view.date_deleted')
                //->where('goal_types.name', '<>', 'Private')
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); }) 
                ->groupBy(['user_demo_jr_view.employee_id', 'goal_type_id'])
                ->havingRaw('goals_count > 10');
            }, 't')
            ->select('t.employee_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('t.employee_id')
            ->get()->toArray();

        $notype_count_3 = count($result_3);
        

        $notype_data["groups"][1]["name"] = '1-5';    
        $notype_data["groups"][1]["value"] = $notype_count_1;   
        $notype_data["groups"][1]["goal_id"] = '';

        $notype_data["groups"][2]["name"] = '6-10';    
        $notype_data["groups"][2]["value"] = $notype_count_2;   
        $notype_data["groups"][2]["goal_id"] = '';

        $notype_data["groups"][3]["name"] = '>10';    
        $notype_data["groups"][3]["value"] = $notype_count_3;   
        $notype_data["groups"][3]["goal_id"] = '';

        $notype_employee_hasgoal = $notype_count_1 + $notype_count_2 + $notype_count_3;
        $notype_employee_hasnogoal = $total_number_emp - $notype_employee_hasgoal;
        $notype_data["groups"][0]["value"] = $notype_employee_hasnogoal; 
        

        // Goal Tag count 
        $count_raw = "id, name, ";
        $count_raw .= " (select count(*) from goal_tags, goals, user_demo_jr_view, goal_types ";
        $count_raw .= "   where goals.id = goal_tags.goal_id "; 
	    $count_raw .= "     and tag_id = tags.id ";  
        $count_raw .= "     and user_demo_jr_view.user_id = goals.user_id ";
        $count_raw .= "     and goal_types.id = goals.goal_type_id ";

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
        $count_raw .= "   and  (user_demo_jr_view.excused_flag IS NULL OR user_demo_jr_view.excused_flag <> 1) 
                            AND 
                            (user_demo_jr_view.due_date_paused = 'N' OR user_demo_jr_view.due_date_paused IS NULL) ";
        $count_raw .= "  and user_demo_jr_view.date_deleted is null ";
        $count_raw .= "     and goals.deleted_at is null   ";
        $count_raw .= "     and goals.is_library = 0   ";
        $count_raw .= "     and goals.status = 'active'   ";
        //$count_raw .= "     and goal_types.name <> 'Private'   ";
        $count_raw .= ") as count";
        
        $sql = Tag::selectRaw($count_raw);
        $sql2 = Goal::join('user_demo_jr_view', function($join) {
                    $join->on('goals.user_id', '=', 'user_demo_jr_view.user_id');
                })
                ->join('goal_types', 'goals.goal_type_id', 'goal_types.id')
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
                //->where('goal_types.name','<>', 'Private')  
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('goal_tags')
                          ->whereColumn('goals.id', 'goal_tags.goal_id');
                })
                ->where('user_demo_jr_view.guid', '<>', '')
                ->where('goals.status', '=', 'active')
                ->whereNull('goals.deleted_at')
                //->where('goals.goal_type_id', '<>', 4)
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
        //array_push($data_tag['labels'], '[Blank]');  
        //array_push($data_tag['values'], $blank_count);
        foreach($sortedArray as $key => $tag)
        {
            array_push($data_tag['labels'], $tag->name);  
            array_push($data_tag['values'], $tag->count);
        }
        array_multisort($data_tag['labels'], $data_tag['values']);

        //average goals
        $all_goals = Goal::selectRaw('count(goals.id) as num, goal_types.name')
                            ->join('user_demo_jr_view', 'goals.user_id', 'user_demo_jr_view.user_id')
                            ->join('goal_types', 'goals.goal_type_id', 'goal_types.id')
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
                            ->where('user_demo_jr_view.guid', '<>', '')
                            ->where('goals.status', '=', 'active')
                            ->whereNull('goals.deleted_at')
                            ->where('goals.is_library', '=', 0)
                            ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                            ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                            ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                            ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                            ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })
                            ->groupBy('goal_types.name')->get()->toArray();                   
        
        $total_works = 0;
        $total_learning = 0;
        $total_career = 0;
        $total_private = 0;
        $total_all = 0;
        foreach($all_goals as $all_goal){
            if($all_goal['name'] == 'Work') {
                $total_works = $all_goal['num'];
            }
            if($all_goal['name'] == 'Learning') {
                $total_learning = $all_goal['num'];
            }
            if($all_goal['name'] == 'Career Development') {
                $total_career = $all_goal['num'];
            }
            if($all_goal['name'] == 'Private') {
                $total_private = $all_goal['num'];
            }
            $total_all = $total_works + $total_learning + $total_career + $total_private;
        }                    
                 
        $average_all = $total_all / $total_number_emp;
        $average_works = $total_works / $total_number_emp;
        $average_learning = $total_learning / $total_number_emp;
        $average_career = $total_career / $total_number_emp;
        $average_private = $total_private / $total_number_emp;

        $average = array();
        $average[''] = $average_all;
        $average['Work'] = $average_works;
        $average['Learning'] = $average_learning;
        $average['Career Development'] = $average_career;
        $average['Private'] = $average_private;
      
        return view('sysadmin.statistics.goalsummary',compact('data', 'notype_data', 'average', 'data_tag'));
    }


    public function goalSummaryExport(Request $request)
    {

        $level0 = $request->dd_level0 ? EmployeeDemoTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? EmployeeDemoTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? EmployeeDemoTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? EmployeeDemoTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? EmployeeDemoTree::where('id', $request->dd_level4)->first() : null;

        if($request->goal) {
            $from_stmt = $this->goalSummary_from_statement($request->goal);
            $sql = UserDemoJrView::selectRaw('A.*, goals_count, user_demo_jr_view.employee_name, 
            user_demo_jr_view.organization, user_demo_jr_view.level1_program, user_demo_jr_view.level2_division, user_demo_jr_view.level3_branch, user_demo_jr_view.level4, user_demo_jr_view.reporting_to_name')
                    ->from(DB::raw( $from_stmt ))                                
                    ->join('user_demo_jr_view', function($join) {
                        $join->on('user_demo_jr_view.employee_id', '=', 'A.employee_id');
                        //$join->on('employee_demo.empl_record', '=', 'A.empl_record');
                    })
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                ->orWhereNull('user_demo_jr_view.excused_flag');
                        });
                    }) 
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.due_date_paused', 'N')
                                ->orWhereNull('user_demo_jr_view.due_date_paused');
                        });
                    })
                    ->whereNull('user_demo_jr_view.date_deleted')
                    // ->join('employee_demo_jr as j', 'employee_demo.guid', 'j.guid')
                    // ->whereRaw("j.id = (select max(j1.id) from employee_demo_jr as j1 where j1.guid = j.guid) and (j.due_date_paused = 'N') ")
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })
                    // ->where('acctlock', 0)
                    ->when( (array_key_exists($request->range, $this->groups)) , function($q) use($request) {
                        return $q->whereBetween('goals_count', $this->groups[$request->range]);
                    });
                    // ->where( function($query) {
                    //     $query->whereRaw('date(SYSDATE()) not between IFNULL(A.excused_start_date,"1900-01-01") and IFNULL(A.excused_end_date,"1900-01-01") ')
                    //           ->where('employee_demo.employee_status', 'A');
                    // });
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
                    $row['Email'] = $user->employee_email;
                    $row['Active Goals Count'] = $user->goals_count;
                    $row['Organization'] = $user->organization;
                    $row['Level 1'] = $user->level1_program;
                    $row['Level 2'] = $user->level2_division;
                    $row['Level 3'] = $user->level3_branch;
                    $row['Level 4'] = $user->level4;
                    $row['Reporting To'] = $user->reporting_to_name ? $user->reporting_to_name : '';

                    fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Active Goals Count'], $row['Organization'],
                                $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], $row['Reporting To'] ));
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);            
        } else {
            $from_stmt_1 = $this->goalSummary_from_statement(1);
            $sql_1 = UserDemoJrView::selectRaw('A.*, goals_count, user_demo_jr_view.employee_name, 
                    user_demo_jr_view.organization, user_demo_jr_view.level1_program, user_demo_jr_view.level2_division, user_demo_jr_view.level3_branch, user_demo_jr_view.level4, user_demo_jr_view.reporting_to_name')
                    ->from(DB::raw( $from_stmt_1 ))                                
                    ->join('user_demo_jr_view', function($join) {
                        $join->on('user_demo_jr_view.employee_id', '=', 'A.employee_id');
                        //$join->on('employee_demo.empl_record', '=', 'A.empl_record');
                    })
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                ->orWhereNull('user_demo_jr_view.excused_flag');
                        });
                    }) 
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.due_date_paused', 'N')
                                ->orWhereNull('user_demo_jr_view.due_date_paused');
                        });
                    })
                    ->whereNull('user_demo_jr_view.date_deleted')
                    // ->join('employee_demo_jr as j', 'employee_demo.guid', 'j.guid')
                    // ->whereRaw("j.id = (select max(j1.id) from employee_demo_jr as j1 where j1.guid = j.guid) and (j.due_date_paused = 'N') ")
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })
                    // ->where('acctlock', 0)
                    ->when( (array_key_exists($request->range, $this->groups)) , function($q) use($request) {
                        return $q->whereBetween('goals_count', $this->groups[$request->range]);
                    });
                    // ->where( function($query) {
                    //     $query->whereRaw('date(SYSDATE()) not between IFNULL(A.excused_start_date,"1900-01-01") and IFNULL(A.excused_end_date,"1900-01-01") ')
                    //           ->where('employee_demo.employee_status', 'A');
                    // });
            $users_1 = $sql_1->get();

            $from_stmt_2 = $this->goalSummary_from_statement(2);
            $sql_2 = UserDemoJrView::selectRaw('A.*, goals_count, user_demo_jr_view.employee_name, 
                    user_demo_jr_view.organization, user_demo_jr_view.level1_program, user_demo_jr_view.level2_division, user_demo_jr_view.level3_branch, user_demo_jr_view.level4, user_demo_jr_view.reporting_to_name')
                    ->from(DB::raw( $from_stmt_2 ))                                
                    ->join('user_demo_jr_view', function($join) {
                        $join->on('user_demo_jr_view.employee_id', '=', 'A.employee_id');
                        //$join->on('employee_demo.empl_record', '=', 'A.empl_record');
                    })
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                ->orWhereNull('user_demo_jr_view.excused_flag');
                        });
                    }) 
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.due_date_paused', 'N')
                                ->orWhereNull('user_demo_jr_view.due_date_paused');
                        });
                    })
                    ->whereNull('user_demo_jr_view.date_deleted')
                    // ->join('employee_demo_jr as j', 'employee_demo.guid', 'j.guid')
                    // ->whereRaw("j.id = (select max(j1.id) from employee_demo_jr as j1 where j1.guid = j.guid) and (j.due_date_paused = 'N') ")
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })
                    // ->where('acctlock', 0)
                    ->when( (array_key_exists($request->range, $this->groups)) , function($q) use($request) {
                        return $q->whereBetween('goals_count', $this->groups[$request->range]);
                    });
                    // ->where( function($query) {
                    //     $query->whereRaw('date(SYSDATE()) not between IFNULL(A.excused_start_date,"1900-01-01") and IFNULL(A.excused_end_date,"1900-01-01") ')
                    //           ->where('employee_demo.employee_status', 'A');
                    // });
            $users_2 = $sql_2->get();

            $from_stmt_3 = $this->goalSummary_from_statement(3);
            $sql_3 = UserDemoJrView::selectRaw('A.*, goals_count, user_demo_jr_view.employee_name, 
                    user_demo_jr_view.organization, user_demo_jr_view.level1_program, user_demo_jr_view.level2_division, user_demo_jr_view.level3_branch, user_demo_jr_view.level4, user_demo_jr_view.reporting_to_name')
                    ->from(DB::raw( $from_stmt_3 ))                                
                    ->join('user_demo_jr_view', function($join) {
                        $join->on('user_demo_jr_view.employee_id', '=', 'A.employee_id');
                        //$join->on('employee_demo.empl_record', '=', 'A.empl_record');
                    })
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                ->orWhereNull('user_demo_jr_view.excused_flag');
                        });
                    }) 
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.due_date_paused', 'N')
                                ->orWhereNull('user_demo_jr_view.due_date_paused');
                        });
                    })
                    ->whereNull('user_demo_jr_view.date_deleted')
                    // ->join('employee_demo_jr as j', 'employee_demo.guid', 'j.guid')
                    // ->whereRaw("j.id = (select max(j1.id) from employee_demo_jr as j1 where j1.guid = j.guid) and (j.due_date_paused = 'N') ")
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })
                    // ->where('acctlock', 0)
                    ->when( (array_key_exists($request->range, $this->groups)) , function($q) use($request) {
                        return $q->whereBetween('goals_count', $this->groups[$request->range]);
                    });
                    // ->where( function($query) {
                    //     $query->whereRaw('date(SYSDATE()) not between IFNULL(A.excused_start_date,"1900-01-01") and IFNULL(A.excused_end_date,"1900-01-01") ')
                    //           ->where('employee_demo.employee_status', 'A');
                    // });
            $users_3 = $sql_3->get();

            $from_stmt_4 = $this->goalSummary_from_statement(4);
            $sql_4 = UserDemoJrView::selectRaw('A.*, goals_count, user_demo_jr_view.employee_name, 
                    user_demo_jr_view.organization, user_demo_jr_view.level1_program, user_demo_jr_view.level2_division, user_demo_jr_view.level3_branch, user_demo_jr_view.level4, user_demo_jr_view.reporting_to_name')
                    ->from(DB::raw( $from_stmt_4 ))                                
                    ->join('user_demo_jr_view', function($join) {
                        $join->on('user_demo_jr_view.employee_id', '=', 'A.employee_id');
                        //$join->on('employee_demo.empl_record', '=', 'A.empl_record');
                    })
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.excused_flag', '<>', '1')
                                ->orWhereNull('user_demo_jr_view.excused_flag');
                        });
                    }) 
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('user_demo_jr_view.due_date_paused', 'N')
                                ->orWhereNull('user_demo_jr_view.due_date_paused');
                        });
                    })
                    ->whereNull('user_demo_jr_view.date_deleted')
                    // ->join('employee_demo_jr as j', 'employee_demo.guid', 'j.guid')
                    // ->whereRaw("j.id = (select max(j1.id) from employee_demo_jr as j1 where j1.guid = j.guid) and (j.due_date_paused = 'N') ")
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('user_demo_jr_view.organization_key', $request->dd_level0); })
                    ->when( $request->dd_level1, function ($q) use($request) { return $q->where('user_demo_jr_view.level1_key', $request->dd_level1); })
                    ->when( $request->dd_level2, function ($q) use($request) { return $q->where('user_demo_jr_view.level2_key', $request->dd_level2); })
                    ->when( $request->dd_level3, function ($q) use($request) { return $q->where('user_demo_jr_view.level3_key', $request->dd_level3); })
                    ->when( $request->dd_level4, function ($q) use($request) { return $q->where('user_demo_jr_view.level4_key', $request->dd_level4); })
                    // ->where('acctlock', 0)
                    ->when( (array_key_exists($request->range, $this->groups)) , function($q) use($request) {
                        return $q->whereBetween('goals_count', $this->groups[$request->range]);
                    });
                    // ->where( function($query) {
                    //     $query->whereRaw('date(SYSDATE()) not between IFNULL(A.excused_start_date,"1900-01-01") and IFNULL(A.excused_end_date,"1900-01-01") ')
                    //           ->where('employee_demo.employee_status', 'A');
                    // });
            $users_4 = $sql_4->get();

            $users = array();
            foreach($users_1 as $user){
                if(isset($users[$user->user_id])) {
                    $users[$user->user_id]["Active Work Goals Count"] = $user->goals_count;
                } else {
                    $users[$user->user_id]["employee_id"] = $user->employee_id;
                    $users[$user->user_id]["employee_name"] = $user->employee_name;
                    $users[$user->user_id]["employee_email"] = $user->employee_email;
                    $users[$user->user_id]["Active Work Goals Count"] = $user->goals_count;
                    $users[$user->user_id]["Active Career Development Goals Count"] = 0;
                    $users[$user->user_id]["Active Learning Goals Count"] = 0;
                    $users[$user->user_id]["Active Private Goals Count"] = 0;
                    $users[$user->user_id]["organization"] = $user->organization;
                    $users[$user->user_id]["level1_program"] = $user->level1_program;
                    $users[$user->user_id]["level2_division"] = $user->level2_division;
                    $users[$user->user_id]["level3_branch"] = $user->level3_branch;
                    $users[$user->user_id]["level4"] = $user->level4;
                    $users[$user->user_id]["Reporting To"] = $user->reporting_to_name ? $user->reporting_to_name : '';
                }
            }

            foreach($users_2 as $user){
                if(isset($users[$user->user_id])) {
                    $users[$user->user_id]["Active Career Development Goals Count"] = $user->goals_count;
                } else {
                    $users[$user->user_id]["employee_id"] = $user->employee_id;
                    $users[$user->user_id]["employee_name"] = $user->employee_name;
                    $users[$user->user_id]["employee_email"] = $user->employee_email;
                    $users[$user->user_id]["Active Work Goals Count"] = 0;
                    $users[$user->user_id]["Active Career Development Goals Count"] = $user->goals_count;
                    $users[$user->user_id]["Active Learning Goals Count"] = 0;
                    $users[$user->user_id]["Active Private Goals Count"] = 0;
                    $users[$user->user_id]["organization"] = $user->organization;
                    $users[$user->user_id]["level1_program"] = $user->level1_program;
                    $users[$user->user_id]["level2_division"] = $user->level2_division;
                    $users[$user->user_id]["level3_branch"] = $user->level3_branch;
                    $users[$user->user_id]["level4"] = $user->level4;
                    $users[$user->user_id]["Reporting To"] = $user->reporting_to_name ? $user->reporting_to_name : '';
                }
                
            }

            foreach($users_3 as $user){
                if(isset($users[$user->user_id])) {
                    $users[$user->user_id]["Active Learning Goals Count"] = $user->goals_count;
                } else {
                    $users[$user->user_id]["employee_id"] = $user->employee_id;
                    $users[$user->user_id]["employee_name"] = $user->employee_name;
                    $users[$user->user_id]["employee_email"] = $user->employee_email;
                    $users[$user->user_id]["Active Work Goals Count"] = 0;
                    $users[$user->user_id]["Active Career Development Goals Count"] = 0;
                    $users[$user->user_id]["Active Learning Goals Count"] = $user->goals_count;
                    $users[$user->user_id]["Active Private Goals Count"] = 0;
                    $users[$user->user_id]["organization"] = $user->organization;
                    $users[$user->user_id]["level1_program"] = $user->level1_program;
                    $users[$user->user_id]["level2_division"] = $user->level2_division;
                    $users[$user->user_id]["level3_branch"] = $user->level3_branch;
                    $users[$user->user_id]["level4"] = $user->level4;
                    $users[$user->user_id]["Reporting To"] = $user->reporting_to_name ? $user->reporting_to_name : '';
                }
                
            }

            foreach($users_4 as $user){
                if(isset($users[$user->user_id])) {
                    $users[$user->user_id]["Active Private Goals Count"] = $user->goals_count;
                } else {
                    $users[$user->user_id]["employee_id"] = $user->employee_id;
                    $users[$user->user_id]["employee_name"] = $user->employee_name;
                    $users[$user->user_id]["employee_email"] = $user->employee_email;
                    $users[$user->user_id]["Active Work Goals Count"] = 0;
                    $users[$user->user_id]["Active Career Development Goals Count"] = 0;
                    $users[$user->user_id]["Active Learning Goals Count"] = 0;
                    $users[$user->user_id]["Active Private Goals Count"] = $user->goals_count;
                    $users[$user->user_id]["organization"] = $user->organization;
                    $users[$user->user_id]["level1_program"] = $user->level1_program;
                    $users[$user->user_id]["level2_division"] = $user->level2_division;
                    $users[$user->user_id]["level3_branch"] = $user->level3_branch;
                    $users[$user->user_id]["level4"] = $user->level4;
                    $users[$user->user_id]["Reporting To"] = $user->reporting_to_name ? $user->reporting_to_name : '';
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

            $columns = ["Employee ID", "Name", "Email", 'Active Goals Count', 'Active Work Goals Count', 'Active Career Development Goals Count', 'Active Learning Goals Count', 'Active Private Goals Count', 
                            "Organization", "Level 1", "Level 2", "Level 3", "Level 4", "Reporting To",
                        ];

            $callback = function() use($users, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($users as $user) {
                    $row['Employee ID'] = $user["employee_id"];
                    $row['Name'] = $user["employee_name"];
                    $row['Email'] = $user["employee_email"];
                    $goals_count = $user["Active Work Goals Count"] + $user["Active Career Development Goals Count"] + $user["Active Learning Goals Count"] + $user["Active Private Goals Count"];
                    $row['Active Goals Count'] = $goals_count;
                    $row['Active Work Goals Count'] = $user["Active Work Goals Count"];
                    $row['Active Career Development Goals Count'] = $user["Active Career Development Goals Count"];
                    $row['Active Learning Goals Count'] = $user["Active Learning Goals Count"];
                    $row['Active Private Goals Count'] = $user["Active Private Goals Count"];
                    $row['Organization'] = $user["organization"];
                    $row['Level 1'] = $user["level1_program"];
                    $row['Level 2'] = $user["level2_division"];
                    $row['Level 3'] = $user["level3_branch"];
                    $row['Level 4'] = $user["level4"];
                    $row['Reporting To'] = $user["Reporting To"];

                    fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], 
                                $row['Active Goals Count'], $row['Active Work Goals Count'], $row['Active Career Development Goals Count'], $row['Active Learning Goals Count'], $row['Active Private Goals Count'], 
                                $row['Organization'], $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], $row['Reporting To'] ));
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);   
        }
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
            $count_raw .= "      and goals.deleted_at is null and goals.is_library = 0 and goals.status = 'active' ";                
            $count_raw .= "      and employee_demo.guid <> '' ";
            
            $count_raw .= "   and  (users.excused_flag IS NULL OR users.excused_flag <> 1) 
                            AND 
                            (users.due_date_paused = 'N' OR users.due_date_paused IS NULL) ";
            //$count_raw .= "     and goals.goal_type_id <> 4    ";

            $count_raw .= " ) as 'tag_0' ";
        }
        foreach ($tags as $tag) {
            $count_raw .= " ,(select count(*) from goal_tags, goals ";
            $count_raw .= "    where goals.id = goal_tags.goal_id "; 
            $count_raw .= "      and tag_id = " . $tag->id;  
            $count_raw .= "      and goals.deleted_at is null and goals.is_library = 0 and goals.status = 'active' ";  
            $count_raw .= "      and users.id = goals.user_id ";

            $count_raw .= "   and  (users.excused_flag IS NULL OR users.excused_flag <> 1) 
                            AND 
                            (users.due_date_paused = 'N' OR users.due_date_paused IS NULL) ";
            //$count_raw .= "     and goals.goal_type_id <> 4    ";

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
                    ->where(function($query) {
                        $query->where(function($query) {
                            $query->where('users.due_date_paused', 'N')
                                ->orWhereNull('users.due_date_paused');
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
                                ->whereNull('goals.deleted_at')
                                ->where('goals.is_library', 0)
                                ->where('goals.status', 'active')
                                //->where('goals.goal_type_id', '<>', 4)
                                ->whereColumn('goals.user_id',  'users.id');
                    })
                    // To show the tag == selected tag name
                    ->when( ($request->tag && $request->tag <> '[Blank]' ), function ($q) use ($request) {
                        $q->whereExists(function ($query) use ($request) {
                              return $query->select(DB::raw(1))
                                        ->from('goals')
                                        ->join('goal_tags', 'goals.id', '=', 'goal_tags.goal_id')
                                        ->join('tags', 'goal_tags.tag_id', '=', 'tags.id')
                                        ->join('goal_types', 'goal_types.id', '=', 'goals.goal_type_id')
                                        ->whereNull('goals.deleted_at')
                                        ->where('goals.is_library', 0)
                                        ->where('goals.status', 'active')
                                        //->where('goal_types.name', '<>', 'Private')
                                        ->whereColumn('goals.user_id',  'users.id')
                                        ->where('tags.name', $request->tag);
                            });
                    })  
                    // To show the  tag == '[blank]'
                    ->when( ($request->tag && $request->tag == '[Blank]' ), function ($q) {
                        $q->whereNotExists(function ($query) {
                              return $query->select(DB::raw(1))
                                        ->from('goals')
                                        ->join('goal_tags', 'goals.id', '=', 'goal_tags.goal_id')
                                        ->join('goal_types', 'goal_types.id', '=', 'goals.goal_type_id')
                                        ->whereNull('goals.deleted_at')
                                        ->where('goals.is_library', 0)
                                        ->where('goals.status', 'active')
                                        //->where('goal_types.name', '<>', 'Private')
                                        ->whereColumn('goals.user_id',  'users.user_id');
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

        if($request->dd_level0) {
            $level0 = $this->getOrgLevel($request->dd_level0);
            $level0_name = $level0->name;
            $request->session()->flash('dd_level0_name', $level0_name);
        }

        if($request->dd_level1) {
            $level1 = $this->getOrgLevel($request->dd_level1);
            $level1_name = $level1->name;
            $request->session()->flash('dd_level1_name', $level1_name);
        }

        if($request->dd_level2) {
            $level2 = $this->getOrgLevel($request->dd_level2);
            $level2_name = $level2->name;
            $request->session()->flash('dd_level2_name', $level2_name);
        }

        if($request->dd_level3) {
            $level3 = $this->getOrgLevel($request->dd_level3);
            $level3_name = $level3->name;
            $request->session()->flash('dd_level3_name', $level3_name);
        }

        if($request->dd_level4) {
            $level4 = $this->getOrgLevel($request->dd_level4);
            $level4_name = $level4->name;
            $request->session()->flash('dd_level4_name', $level4_name);
        }

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
            $employee_topic_query = UserDemoJrView::selectRaw("employee_id, empl_record, employee_name, 
                                organization, level1_program, level2_division,
                                level3_branch, level4,conversation_participants.role,
                                conversations.deleted_at,conversation_participants.conversation_id,
                                conversations.signoff_user_id,conversations.supervisor_signoff_id,
                                conversation_participants.participant_id,conversations.conversation_topic_id,
                        DATEDIFF ( next_conversation_date
                            , curdate() )
                    as overdue_in_days")
                ->leftJoin('conversation_participants', function($join)  {
                    $join->on('conversation_participants.participant_id', '=', 'user_demo_jr_view.user_id')->where('conversation_participants.role','emp');
                })
                ->leftJoin('conversations', function($join) use($topic) {
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id')->where('conversations.conversation_topic_id', $topic->id);
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
                ->whereNull('deleted_at')
                ->where('conversations.conversation_topic_id', $topic->id)
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('excused_flag', '<>', '1')
                            ->orWhereNull('excused_flag');
                    });
                });  

            $topic_employees = $employee_topic_query->get();              
            
            
            $open_conversations = $conversations->filter(function ($conversation) {
                return $conversation->signoff_user_id === null || $conversation->supervisor_signoff_id === null;
            }); 
                        
            $subset =$open_conversations->filter(function ($conversation) use($topic) {
                return $conversation->conversation_topic_id == $topic->id;
            }); 
            $subset = $subset->toArray();
            foreach($subset as $index=>$value){
                if($value['deleted_at'] != ''){
                    unset($subset[$index]);
                }
            }
            //$subset = array_unique(array_column($subset, 'employee_id')); 
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
                ->leftJoin('conversations', function($join)use($topic){
                    $join->on('conversations.id', '=', 'conversation_participants.conversation_id')->where('conversations.conversation_topic_id', $topic->id);
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
                ->whereNull('deleted_at')
                ->where('conversations.conversation_topic_id', $topic->id)
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('excused_flag', '<>', '1')
                            ->orWhereNull('excused_flag');
                    });
                });  
            $topic_employees = $employee_topic_query->get();              
            
            $conversations = $topic_employees->filter(function ($topic_employees) {
                return $topic_employees->role == 'emp';
            });
            $complete_conversations = $conversations->filter(function ($conversation) {
                return $conversation->signoff_user_id != null && $conversation->supervisor_signoff_id != null;
            }); 
                        
            $subset =$complete_conversations->filter(function ($conversation) use($topic) {
                return $conversation->conversation_topic_id == $topic->id;
            }); 
            $subset = $subset->toArray();
            foreach($subset as $index=>$value){
                if($value['deleted_at'] != ''){
                    unset($subset[$index]);
                }
            }
            //$subset = array_unique(array_column($subset, 'employee_id')); 
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
        $data['chart6']['title'] = 'User Has Open Conversation';
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
        $data['chart7']['title'] = 'User Has Completed Conversation';
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
                        //$unique_subset = $subset->unique('employee_id');
                        foreach($subset as $item) {
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
                                        ->select('users.name', 'conversation_participants.role')
                                        ->join('users', 'conversation_participants.participant_id', '=', 'users.id')
                                        ->where('conversation_participants.conversation_id', $conversation->id)
                                        ->get();              
                        $participants_arr = array();
                        foreach($participants as $participant){
                            if($participant->role == 'mgr') {
                                $participants_arr[] = $participant->name;
                            }
                        }
                        foreach($participants as $participant){
                            if($participant->role == 'emp') {
                                $participants_arr[] = $participant->name;
                            }
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
                        //$unique_subset = $subset->unique('employee_id');
                        foreach($subset as $item) {
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
                                        ->select('users.name', 'conversation_participants.role')
                                        ->join('users', 'conversation_participants.participant_id', '=', 'users.id')
                                        ->where('conversation_participants.conversation_id', $conversation->id)
                                        ->get();      
                            $participants_arr = array();
                            foreach($participants as $participant){
                                if($participant->role == 'mgr') {
                                    $participants_arr[] = $participant->name;
                                }
                            }
                            foreach($participants as $participant){
                                if($participant->role == 'emp') {
                                    $participants_arr[] = $participant->name;
                                }
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

        if($request->dd_level0) {
            $level0 = $this->getOrgLevel($request->dd_level0);
            $level0_name = $level0->name;
            $request->session()->flash('dd_level0_name', $level0_name);
        }

        if($request->dd_level1) {
            $level1 = $this->getOrgLevel($request->dd_level1);
            $level1_name = $level1->name;
            $request->session()->flash('dd_level1_name', $level1_name);
        }

        if($request->dd_level2) {
            $level2 = $this->getOrgLevel($request->dd_level2);
            $level2_name = $level2->name;
            $request->session()->flash('dd_level2_name', $level2_name);
        }

        if($request->dd_level3) {
            $level3 = $this->getOrgLevel($request->dd_level3);
            $level3_name = $level3->name;
            $request->session()->flash('dd_level3_name', $level3_name);
        }

        if($request->dd_level4) {
            $level4 = $this->getOrgLevel($request->dd_level4);
            $level4_name = $level4->name;
            $request->session()->flash('dd_level4_name', $level4_name);
        }

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

        if($request->dd_level0) {
            $level0 = $this->getOrgLevel($request->dd_level0);
            $level0_name = $level0->name;
            $request->session()->flash('dd_level0_name', $level0_name);
        }

        if($request->dd_level1) {
            $level1 = $this->getOrgLevel($request->dd_level1);
            $level1_name = $level1->name;
            $request->session()->flash('dd_level1_name', $level1_name);
        }

        if($request->dd_level2) {
            $level2 = $this->getOrgLevel($request->dd_level2);
            $level2_name = $level2->name;
            $request->session()->flash('dd_level2_name', $level2_name);
        }

        if($request->dd_level3) {
            $level3 = $this->getOrgLevel($request->dd_level3);
            $level3_name = $level3->name;
            $request->session()->flash('dd_level3_name', $level3_name);
        }

        if($request->dd_level4) {
            $level4 = $this->getOrgLevel($request->dd_level4);
            $level4_name = $level4->name;
            $request->session()->flash('dd_level4_name', $level4_name);
        }

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);

        $sql = UserDemoJrView::selectRaw("employee_id, 
                                excused_reason_id, excusedtype, reason_name, excused_by_name, organization, level1_program, level2_division, level3_branch, level4,
                                (CASE WHEN reason_name <> ''
                                    THEN 'Yes' ELSE 'No' END) AS excused")
                    ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                    ->when($request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                    ->when($request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                    ->when($request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                    ->when($request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })
                    ->whereNull('date_deleted');
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

      $sql = UserDemoJrView::selectRaw("employee_id, employee_name, employee_email,
      excused_reason_id, excusedtype, reason_name, excused_by_name, created_at_string, organization, level1_program, level2_division, level3_branch, level4,
      (CASE WHEN reason_name <> '' THEN 'Yes' ELSE 'No' END) AS excused")
                ->when($request->dd_level0, function ($q) use($request) { return $q->where('organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function ($q) use($request) { return $q->where('level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function ($q) use($request) { return $q->where('level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function ($q) use($request) { return $q->where('level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function ($q) use($request) { return $q->where('level4_key', $request->dd_level4); })
                ->when($request->legend === 'Yes', function ($q) { return $q->having('excused', 'Yes'); }) // Condition for legend 'Yes'
                ->when($request->legend === 'No', function ($q) { return $q->having('excused', 'No'); }) // Condition for legend 'No'
                ->whereNull('date_deleted');

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
                        "Excused", "Reason", "Excused By", "Excused At", 
                        "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
                    ];

        $callback = function() use($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row['Employee ID'] = $user->employee_id;
                $row['Name'] = $user->employee_name;
                $row['Email'] = $user->employee_email;

                $row['Excused'] = $user->excusedtype ? $user->excusedtype : '';
                $row['Reason'] = $user->reason_name ? $user->reason_name : '';
                $row['Excused By'] = $user->excused_by_name ? $user->excused_by_name : '';
                $row['Excused At'] = $user->created_at_string ? $user->created_at_string : '';

                $row['Organization'] = $user->organization;
                $row['Level 1'] = $user->level1_program;
                $row['Level 2'] = $user->level2_division;
                $row['Level 3'] = $user->level3_branch;
                $row['Level 4'] = $user->level4;

                fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], 
                        $row['Excused'], $row['Reason'], $row['Excused By'], $row['Excused At'], 
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
                                    ->whereNull('employee_demo.date_deleted')
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
                                    ->whereNull('employee_demo.date_deleted')
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
                                                  ->whereNull('employee_demo.date_deleted')
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
                                                  ->whereNull('employee_demo.date_deleted')
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
                                    ->whereNull('employee_demo.date_deleted')
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
                                    ->whereNull('employee_demo.date_deleted')
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
                                                  ->whereNull('employee_demo.date_deleted')
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
                                                  ->whereNull('employee_demo.date_deleted')
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
                                    ->whereNull('employee_demo.date_deleted')
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

                $filename = 'User Has Open Conversation.csv';
                $users =  $sql_6->get();
                $users = $users->unique('employee_id');
                //get has conversation users employee_id list
                $excludedIds = $users->pluck('employee_id')->toArray();
                if($request->legend == 'No' || !$request->legend){                    
                    $sql_6_all = UserDemoJrView::selectRaw("employee_id, employee_name, employee_email, next_conversation_date, reporting_to_name,
                    organization, level1_program, level2_division, level3_branch, level4")
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
                            
                    $users_all =  $sql_6_all->get(); 
                    if($request->legend == 'No' ) {
                        foreach($users_all as $index=>$user){
                            if(in_array($user->employee_id, $excludedIds)){
                                unset($users_all[$index]);
                            }
                        }
                    }  
                    
                    
                    $users = $users_all->unique('employee_id');  
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
                                "Level 1", "Level 2", "Level 3", "Level 4", 'Have Conversation'
                           ];
        
                $callback = function() use($users, $excludedIds, $columns) {
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
                        
                        if(in_array($user->employee_id, $excludedIds)){
                            $row['Have Conversation'] = 'Yes';
                        } else {
                            $row['Have Conversation'] = 'No';
                        }

        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Organization'],
                                    $row['next_conversation_date'],$row['reporting_to_name'],
                                    $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], $row['Have Conversation'] ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;    
            
            case 7:

                $filename = 'User Has Complete Conversation.csv';
                $users =  $sql_7->get();
                $users = $users->unique('employee_id');
                //get has conversation users employee_id list
                $excludedIds = $users->pluck('employee_id')->toArray();
                
                if($request->legend == 'No' || !$request->legend){                 
                    $sql_7_all = UserDemoJrView::selectRaw("employee_id, employee_name, employee_email, next_conversation_date, reporting_to_name,
                    organization, level1_program, level2_division, level3_branch, level4")
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
                            
                    $users_all =  $sql_7_all->get();   
                    if($request->legend == 'No' ) {
                        foreach($users_all as $index=>$user){
                            if(in_array($user->employee_id, $excludedIds)){
                                unset($users_all[$index]);
                            }
                        }
                    } 
                            
                    $users = $users_all->unique('employee_id');            
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
                                "Level 1", "Level 2", "Level 3", "Level 4",  'Have Conversation',
                           ];
        
                $callback = function() use($users, $excludedIds, $columns) {
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

                        if(in_array($user->employee_id, $excludedIds)){
                            $row['Have Conversation'] = 'Yes';
                        } else {
                            $row['Have Conversation'] = 'No';
                        }
        
                        fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Email'], $row['Organization'],
                                    $row['next_conversation_date'],$row['reporting_to_name'],
                                    $row['Level 1'], $row['Level 2'], $row['Level 3'], $row['Level 4'], $row['Have Conversation'] ));
                    }
        
                    fclose($file);
                };
        
                return response()->stream($callback, 200, $headers);

                break;  
                
        }
        
    }


    public function getOrgLevel($id)
    {
        $query = DB::table('employee_demo_tree')
                    ->select('name')
                    ->where('id', $id)
                    ->first();
   

        return $query;
    }

}
