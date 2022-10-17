<?php

namespace App\DataTables;

use App\Models\Conversation;
use App\Models\SharedProfile;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;
use App\Models\ExcusedClassification;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SharedEmployeeDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('name', function ($row) {
                return view('my-team.partials.link-to-profile', compact(['row']));
            })
            ->addColumn('action', function ($row) {
                return view('goal.partials.action', compact(["row"])); // $row['id'];
            })->editColumn('active_goals_count', function ($row) {
                if( !$row['is_goal_shared_with_auth_user']) {
                    return "-";
                }
                $text = $row['active_goals_count'] . " Goals";
                return view('my-team.partials.link-to-profile', compact(['row', 'text']));
            })->addColumn('nextConversationDue', function ($row) {
                $jr = EmployeeDemoJunior::where('guid', $row->guid)->getQuery()->orderBy('id', 'desc')->first();
                if ($jr->excused_type == 'A') {
                    $text = 'Paused';
                    $landingPage = 'conversation.templates';
                    return view('my-team.partials.link-to-profile', compact(["row", "text", "landingPage"]));
                }
                if ($row->excused_flag) {
                    $text = 'Paused';
                    $landingPage = 'conversation.templates';
                    return view('my-team.partials.link-to-profile', compact(["row", "text", "landingPage"]));
                }
                if ($jr->next_conversation_date) {
                    $text = Carbon::parse($jr->next_conversation_date)->format('M d, Y');
                    $landingPage = 'conversation.templates';
                    return view('my-team.partials.link-to-profile', compact(["row", "text", "landingPage"]));
                }
            })
            ->addColumn('shared', function ($row) {
                $yesOrNo = ($row->is_shared) ? 'Yes' : 'No';
                return view('my-team.partials.view-btn', compact(["row", "yesOrNo"])); // $row['id'];
            })
            ->addColumn('excused_flag', function ($row) {
                $jr = EmployeeDemoJunior::where('guid', $row->guid)->getQuery()->orderBy('id', 'desc')->first();
                $excused_type = $jr->excused_type;
                $excused = json_encode([
                    'excused_flag' => $row->excused_flag,
                    'reason_id' => $row->excused_reason_id
                ]);
                if ($jr) {
                    if ($jr->excused_type) {
                        if ($jr->excused_type == 'A') {
                            $yesOrNo = 'Auto';
                            return view('my-team.partials.switch', compact(["row", "excused", "yesOrNo", "excused_type"]));
                        }
                    }
                }
                $yesOrNo = $row->excused_flag ? 'Yes' : 'No';
                return view('my-team.partials.switch', compact(["row", "excused", "yesOrNo", "excused_type"]));
            })
            ->addColumn('direct-reports', function($row) {
                return view('my-team.partials.direct-report-col', compact(["row"]));
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        return $model->newQuery()->whereIn('id', SharedProfile::where('shared_with', Auth::id())->pluck('shared_id') )
            ->withCount('activeGoals')
            ->with('upcomingConversation')
            ->with('employee_demo')
            ->with('employee_demo_jr')
            ->with('latestConversation');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('shared-employees-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('my-team.shared-employee-table'))
            ->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->searching(true)
            ->ordering(true)
            ->parameters([
                'autoWidth' => false
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
           
            new Column([
                'title' => 'Employee name',
                'data' => 'name',
                'name' => 'name'
            ]),
            new Column([
                'title' => 'Active Goals',
                'data' => 'active_goals_count',
                'name' => 'active_goals_count',
                'searchable' => false
            ]),
            Column::computed('nextConversationDue')
                ->title('Next Conversation Due')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
            Column::computed('shared')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::computed('excused_flag')
                ->title('Excused')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }
}