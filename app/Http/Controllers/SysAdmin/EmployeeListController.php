<?php

namespace App\Http\Controllers\SysAdmin;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Goal;
use App\Models\EmployeeDemoJunior;
use App\Models\EmployeeDemoTree;
use App\Models\SharedProfile;
use App\Models\UserDemoJrView;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;


class EmployeeListController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function currentList(Request $request) {
        $errors = session('errors');
        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->criteria = isset($old['criteria']) ? $old['criteria'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
        } 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
            ]);
        }
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $criteriaList = $this->search_criteria_list();
        return view('shared.employeelists.currentlist', compact ('request', 'criteriaList'));
    }

    public function pastList(Request $request) {
        $errors = session('errors');
        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->criteria = isset($old['criteria']) ? $old['criteria'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
        } 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
            ]);
        }
        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $criteriaList = $this->search_criteria_list();
        return view('shared.employeelists.pastlist', compact ('request', 'criteriaList'));
    }

    public function getCurrentList(Request $request) {
        if ($request->ajax()) {
            $query = UserDemoJrView::from('user_demo_jr_view AS u')
                ->whereNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text, function($q) use ($request) { return $q->whereRaw("{$request->criteria} like '%{$request->search_text}%'"); })
                ->orderBy('u.employee_id')
                ->selectRaw ("
                    u.user_id AS id,
                    u.guid,
                    u.user_name,
                    u.excused_flag,
                    u.employee_id,
                    u.employee_name, 
                    u.employee_email, 
                    u.position_number,
                    u.empl_record,
                    u.jobcode_desc,
                    u.organization,
                    u.level1_program,
                    u.level2_division,
                    u.level3_branch,
                    u.level4,
                    u.deptid,
                    u.date_deleted,
                    u.employee_status,
                    u.supervisor_name,
                    u.supervisor_position_number,
                    u.reporting_to_employee_id,
                    u.reporting_to_name,
                    u.reporting_to_email,
                    u.due_date_paused,
                    u.next_conversation_date,
                    u.excusedtype AS excused,
                    '' AS nextConversationDue,
                    '' AS shared,
                    '' AS reportees,
                    '' AS activeGoals
                ");
            return Datatables::of($query)->addIndexColumn()
                ->editColumn('activeGoals', function($row) {
                    return (User::where('id', $row->id)->first()->activeGoals()->count() ?? '0').' Goals';
                })
                ->editColumn('nextConversationDue', function ($row) {
                    if ($row->excused_flag) {
                        return 'Paused';
                    } 
                    if ($row->due_date_paused != 'Y') {
                        $text = Carbon::parse($row->next_conversation_date)->format('M d, Y');
                        return $text;
                    } else {
                        return 'Paused';
                    }
                    return '';
                })
                ->editColumn('shared', function ($row) {
                    return SharedProfile::where('shared_id', $row->id)->count() > 0 ? "Yes" : "No";
                })
                ->editColumn('reportees', function($row) {
                    return User::where('id', $row->id)->first()->reporteesCount() ?? '0';
                })
                ->make(true);
        }
    }

    public function exportCurrent(Request $request) {
        $query = UserDemoJrView::from('user_demo_jr_view AS u')
            ->whereNull('u.date_deleted')
            ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
            ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
            ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
            ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
            ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
            ->when($request->search_text, function($q) use ($request) { return $q->whereRaw("{$request->criteria} like '%{$request->search_text}%'"); })
            ->orderBy('u.employee_id')
            ->selectRaw ("
                u.user_id AS id,
                u.guid,
                u.user_name,
                u.excused_flag,
                u.employee_id,
                u.employee_name, 
                u.employee_email, 
                u.position_number,
                u.empl_record,
                u.jobcode_desc,
                u.organization,
                u.level1_program,
                u.level2_division,
                u.level3_branch,
                u.level4,
                u.deptid,
                u.date_deleted,
                u.employee_status,
                u.supervisor_name,
                u.supervisor_position_number,
                u.reporting_to_employee_id,
                u.reporting_to_name,
                u.reporting_to_email,
                u.due_date_paused,
                u.next_conversation_date,
                u.excusedtype AS excused,
                CASE WHEN (u.due_date_paused != 'Y' AND u.excused_flag <> 1) THEN u.next_conversation_date ELSE 'Paused' END AS nextConversationDue,
                CASE WHEN (SELECT COUNT(sp.id) FROM shared_profiles AS sp WHERE sp.shared_id = u.user_id) > 0 THEN 'Yes' ELSE 'No' END AS shared,
                (SELECT COUNT(DISTINCT rep.id) FROM users AS rep WHERE rep.reporting_to = u.employee_id) AS reportees,
                (SELECT COUNT(DISTINCT g.id) FROM goals as g WHERE g.user_id = u.user_id AND g.status = 'active') AS activeGoals
            ");
        $records = $query->get();
        // Generating Output file
        $filename = 'Current_Employees_'. Carbon::now()->format('YmdHis') . '.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = [
            'Employee ID',  
            'Name', 
            'Email', 
            'Position #',  
            'Reports To Name', 
            'Reports To Position #',  
            'Status', 
            'Record #', 
            'Classification', 
            'Organization', 
            'Level 1',
            'Level 2',
            'Level 3',
            'Level 4',
            'Dept ID',
            'Active Goals',
            'Next Conversation',
            'Excused',
            'Shared',
            'Direct Reports',
        ];
        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($records as $rec) {
                $row['Employee ID'] = $rec->id;
                $row['Name'] = $rec->employee_name;
                $row['Email'] = $rec->employee_email;
                $row['Position #'] = $rec->position_number;
                $row['Reports To Name'] = $rec->reporting_to_name;
                $row['Reports To Position #'] = $rec->supervisor_position_number;
                $row['Status'] = $rec->employee_status;
                $row['Record #'] = $rec->empl_record;
                $row['Classification'] = $rec->jobcode_desc;
                $row['Organization'] = $rec->organization;
                $row['Level 1'] = $rec->level1_program;
                $row['Level 2'] = $rec->level2_division;
                $row['Level 3'] = $rec->level3_branch;
                $row['Level 4'] = $rec->level4;
                $row['Dept ID'] = $rec->deptid;
                $row['Active Goals'] = $rec->activeGoals;
                $row['Next Conversation'] = $rec->nextConversationDue;
                $row['Excused'] = $rec->excused;
                $row['Shared'] = $rec->shared;
                $row['Direct Reports'] = $rec->reportees;
                fputcsv($file, array(
                    $row['Employee ID'], 
                    $row['Name'], 
                    $row['Email'], 
                    $row['Position #'],  
                    $row['Reports To Name'], 
                    $row['Reports To Position #'],
                    $row['Status'], 
                    $row['Record #'], 
                    $row['Classification'], 
                    $row['Organization'], 
                    $row['Level 1'], 
                    $row['Level 2'], 
                    $row['Level 3'], 
                    $row['Level 4'], 
                    $row['Dept ID'], 
                    $row['Active Goals'], 
                    $row['Next Conversation'], 
                    $row['Excused'], 
                    $row['Shared'], 
                    $row['Direct Reports'] 
                ));
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function getPastList(Request $request) {
        if ($request->ajax()) {
            $query = UserDemoJrView::from('user_demo_jr_view AS u')
                ->whereNotNull('u.date_deleted')
                ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
                ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
                ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
                ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
                ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
                ->when($request->search_text, function($q) use ($request) { return $q->whereRaw("{$request->criteria} like '%{$request->search_text}%'"); })
                ->orderBy('u.employee_id')
                ->selectRaw ("
                    u.user_id AS id,
                    u.guid,
                    u.user_name,
                    u.excused_flag,
                    u.employee_id,
                    u.employee_name, 
                    u.employee_email, 
                    u.position_number,
                    u.empl_record,
                    u.jobcode_desc,
                    u.organization,
                    u.level1_program,
                    u.level2_division,
                    u.level3_branch,
                    u.level4,
                    u.deptid,
                    u.date_deleted,
                    u.employee_status,
                    u.supervisor_name,
                    u.supervisor_position_number,
                    u.reporting_to_employee_id,
                    u.reporting_to_name,
                    u.reporting_to_email,
                    u.due_date_paused,
                    u.next_conversation_date,
                    u.excusedtype AS excused,
                    '' AS nextConversationDue,
                    '' AS shared,
                    '' AS reportees,
                    '' AS activeGoals
                ");
            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('activeGoals', function($row) {
                    return (User::where('id', $row->id)->first()->activeGoals()->count() ?? '0').' Goals';
                })
                ->editColumn('nextConversationDue', function ($row) {
                    if ($row->excused_flag) {
                        return 'Paused';
                    } 
                    if ($row->due_date_paused != 'Y') {
                        $text = Carbon::parse($row->next_conversation_date)->format('M d, Y');
                        return $text;
                    } else {
                        return 'Paused';
                    }
                    return '';
                })
                ->editColumn('shared', function ($row) {
                    return SharedProfile::where('shared_id', $row->id)->count() > 0 ? "Yes" : "No";
                })
                ->editColumn('reportees', function($row) {
                    return User::where('id', $row->id)->first()->reporteesCount() ?? '0';
                })
                ->editColumn('date_deleted', function ($row) {
                    if ($row->date_deleted) {
                        $text = Carbon::parse($row->date_deleted)->format('M d, Y');
                    } else {
                        $text = '';
                    }
                    return $row->date_deleted ? $text : null;
                })
                ->rawColumns(['date_deleted', 'nextConversation'])
                ->make(true);
        }
    }

    public function exportPast(Request $request) {
        $query = UserDemoJrView::from('user_demo_jr_view AS u')
            ->whereNotNull('u.date_deleted')
            ->when($request->dd_level0, function($q) use($request) { return $q->where('u.organization_key', $request->dd_level0); })
            ->when($request->dd_level1, function($q) use($request) { return $q->where('u.level1_key', $request->dd_level1); })
            ->when($request->dd_level2, function($q) use($request) { return $q->where('u.level2_key', $request->dd_level2); })
            ->when($request->dd_level3, function($q) use($request) { return $q->where('u.level3_key', $request->dd_level3); })
            ->when($request->dd_level4, function($q) use($request) { return $q->where('u.level4_key', $request->dd_level4); })
            ->when($request->search_text, function($q) use ($request) { return $q->whereRaw("{$request->criteria} like '%{$request->search_text}%'"); })
            ->orderBy('u.employee_id')
            ->selectRaw ("
                u.user_id AS id,
                u.guid,
                u.user_name,
                u.excused_flag,
                u.employee_id,
                u.employee_name, 
                u.employee_email, 
                u.position_number,
                u.empl_record,
                u.jobcode_desc,
                u.organization,
                u.level1_program,
                u.level2_division,
                u.level3_branch,
                u.level4,
                u.deptid,
                u.date_deleted,
                u.employee_status,
                u.supervisor_name,
                u.supervisor_position_number,
                u.reporting_to_employee_id,
                u.reporting_to_name,
                u.reporting_to_email,
                u.due_date_paused,
                u.next_conversation_date,
                u.excusedtype AS excused,
                CASE WHEN (u.due_date_paused != 'Y' AND u.excused_flag <> 1) THEN u.next_conversation_date ELSE 'Paused' END AS nextConversationDue,
                CASE WHEN (SELECT COUNT(sp.id) FROM shared_profiles AS sp WHERE sp.shared_id = u.user_id) > 0 THEN 'Yes' ELSE 'No' END AS shared,
                (SELECT COUNT(DISTINCT rep.id) FROM users AS rep WHERE rep.reporting_to = u.employee_id) AS reportees,
                (SELECT COUNT(DISTINCT g.id) FROM goals as g WHERE g.user_id = u.user_id AND g.status = 'active') AS activeGoals
            ");
        $records = $query->get();
        // Generating Output file
        $filename = 'Past_Employees_'. Carbon::now()->format('YmdHis') . '.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = [
            'Employee ID',  
            'Name', 
            'Email', 
            'Position #',  
            'Reports To Name', 
            'Reports To Position #',  
            'Status', 
            'Record #', 
            'Classification', 
            'Organization', 
            'Level 1',
            'Level 2',
            'Level 3',
            'Level 4',
            'Dept ID',
            'Active Goals',
            'Next Conversation',
            'Excused',
            'Shared',
            'Direct Reports',
            'Date Deleted',
        ];
        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($records as $rec) {
                $row['Employee ID'] = $rec->id;
                $row['Name'] = $rec->employee_name;
                $row['Email'] = $rec->employee_email;
                $row['Position #'] = $rec->position_number;
                $row['Reports To Name'] = $rec->reporting_to_name;
                $row['Reports To Position #'] = $rec->supervisor_position_number;
                $row['Status'] = $rec->employee_status;
                $row['Record #'] = $rec->empl_record;
                $row['Classification'] = $rec->jobcode_desc;
                $row['Organization'] = $rec->organization;
                $row['Level 1'] = $rec->level1_program;
                $row['Level 2'] = $rec->level2_division;
                $row['Level 3'] = $rec->level3_branch;
                $row['Level 4'] = $rec->level4;
                $row['Dept ID'] = $rec->deptid;
                $row['Active Goals'] = $rec->activeGoals;
                $row['Next Conversation'] = $rec->nextConversationDue;
                $row['Excused'] = $rec->excused;
                $row['Shared'] = $rec->shared;
                $row['Direct Reports'] = $rec->reportees;
                $row['Date Deleted'] = $rec->date_deleted;
                fputcsv($file, array(
                    $row['Employee ID'], 
                    $row['Name'], 
                    $row['Email'], 
                    $row['Position #'],  
                    $row['Reports To Name'], 
                    $row['Reports To Position #'],
                    $row['Status'], 
                    $row['Record #'], 
                    $row['Classification'], 
                    $row['Organization'], 
                    $row['Level 1'], 
                    $row['Level 2'], 
                    $row['Level 3'], 
                    $row['Level 4'], 
                    $row['Dept ID'], 
                    $row['Active Goals'], 
                    $row['Next Conversation'], 
                    $row['Excused'], 
                    $row['Shared'], 
                    $row['Direct Reports'], 
                    $row['Date Deleted'] 
                ));
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    protected function search_criteria_list() {
        return [
            'u.employee_name'=> 'Name',
            'u.employee_id' => 'Employee ID', 
            'u.employee_email' => 'Email', 
            'u.position_number' => 'Position #',
            'u.reporting_to_name' => 'Reports To Name',
            'u.supervisor_position_number' => 'Reports to Position #',
            'u.jobcode_desc' => 'Classification',
            'u.deptid' => 'Dept ID'
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
