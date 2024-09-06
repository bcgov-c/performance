<?php

namespace App\DataTables;

use App\Models\Conversation;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;
use App\Models\ExcusedReason;
use App\Models\ExcusedClassification;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MyEmployeesDataTable extends DataTable
{
    protected $id;
    protected $route;

    public function __construct($id = null) {
        $this->id = $id;
        if ($this->id == null) {
            $this->id = Auth::id();
        }
        $this->route = null;
    }

    public function setRoute($route) {
        $this->route = $route;
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn(
                'name',
                function ($row) {
                    return view(
                        'my-team.partials.link-to-profile',
                        compact(['row'])
                    );
                }
            )
            ->addColumn('action', function ($row) {
                return view('goal.partials.action', compact(["row"])); // $row['id'];
            })->editColumn('active_goals_count', function ($row) {
                $text = $row['active_goals_count'] . " Goals";
                $landingPage = 'goal.current';
                return view('my-team.partials.link-to-profile', compact(['row', 'text', 'landingPage']));
            })->addColumn('nextConversationDue', function ($row) {
                $jr = EmployeeDemoJunior::where('employee_id', $row->employee_id)->getQuery()->orderBy('id', 'desc')->first();
                if (isset($jr->excused_type) && $jr->excused_type) {
                    if ($jr->excused_type == 'A') {
                        $text = 'Paused';
                        $landingPage = 'conversation.templates';
                        return view('my-team.partials.link-to-profile', compact(["row", "text", "landingPage"]));
                    }
                }
                if ($row->excused_flag) {
                    $text = 'Paused';
                    $landingPage = 'conversation.templates';
                    return view('my-team.partials.link-to-profile', compact(["row", "text", "landingPage"]));
                }
                if (isset($jr->next_conversation_date) && $jr->next_conversation_date) {
                    if ($jr->next_conversation_date) {
                        $text = Carbon::parse($jr->next_conversation_date)->format('M d, Y');
                        $landingPage = 'conversation.templates';
                        return view('my-team.partials.link-to-profile', compact(["row", "text", "landingPage"]));
                    }
                }
                return '';
            })
            ->addColumn('shared', function ($row) {
                $yesOrNo = $row->is_shared ? "Yes" : "No";
                return view('my-team.partials.view-btn', compact(["row", "yesOrNo"])); // $row['id'];
            })
            ->addColumn('excused_flag', function ($row) {
                $jr = EmployeeDemoJunior::where('employee_id', $row->employee_id)->getQuery()->orderBy('id', 'desc')->first();
                $excused_type = '';
                $current_status = '';
                $excused = json_encode([
                    'excused_flag' => $row->excused_flag,
                    'reason_id' => $row->excused_reason_id
                ]);
                if ($jr) {
                    $current_status = $jr->current_employee_status;
                    if (isset($jr->excused_type) && $jr->excused_type) {
                        $excused_type = $jr->excused_type;
                        if ($jr->excused_type == 'A') {
                            $yesOrNo = 'Auto';
                            return view('my-team.partials.switch', compact(["row", "excused", "yesOrNo", "excused_type", "current_status"]));
                        }
                    }
                }
                $yesOrNo = $row->excused_flag ? 'Yes' : 'No';
                return view('my-team.partials.switch', compact(["row", "excused", "yesOrNo", "excused_type", "current_status"]));
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
        $reporting_users = User::find($this->id)->getAvaliableReportingUserIds();
        return $model->newQuery()->whereIn('id', $reporting_users)
            ->withCount('activeGoals')
            ->with('upcomingConversation')
            ->with('latestConversation')
            ->with('employee_demo')
            ->with('employee_demo_jr')
            ->withCount('reportees');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $route = $this->route;
        if (!$route) {
            $route = route('my-team.my-employee-table');
        }
        return $this->builder()
            ->setTableId('my-employees-table')
            ->columns($this->getColumns())
            ->minifiedAjax($route)
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
            Column::computed('direct-reports')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
        ];
    }
}
