<?php

namespace App\DataTables;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;

class MyEmployeesDataTable extends DataTable
{
    protected $id;
    protected $route;

    public function __construct($id = null) {
        $this->id = $id;
        if($this->id == null) {
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
            ->editColumn('name', function ($row) {
                return view('my-team.partials.link-to-profile', compact(['row']));
            })
            ->addColumn('action', function ($row) {
                return view('goal.partials.action', compact(["row"])); // $row['id'];
            })->editColumn('active_goals_count', function ($row) {
                $text = $row['active_goals_count'] . " Goals";
                $landingPage = 'goal.current';
                return view('my-team.partials.link-to-profile', compact(['row', 'text', 'landingPage']));
            })->addColumn('nextConversationDue', function ($row) {
                $text = Conversation::nextConversationDue(User::find($row["id"]));
                $landingPage = 'conversation.templates';
                return view('my-team.partials.link-to-profile', compact(["row", "text", "landingPage"]));
            })/* ->addColumn('latestConversation', function ($row) {
                $conversation = $row->latestConversation[0] ?? null;
                return view('my-team.partials.conversation', compact(["row", "conversation"]));
            })->addColumn('upcomingConversation', function ($row) {
                $removeBlankLink = true;
                $conversation = $row->upcomingConversation[0] ?? null;
                return view('my-team.partials.conversation', compact(["row", "conversation", 'removeBlankLink']));
            }) */
            ->addColumn('shared', function ($row) {
                $yesOrNo = $row->is_shared ? "Yes" : "No";
                return view('my-team.partials.view-btn', compact(["row", "yesOrNo"])); // $row['id'];
            })
            ->addColumn('excused', function ($row) {
                $yesOrNo = ($row->excused_start_date !== null) ? 'Yes' : 'No';

                $excused = json_encode([
                    'start_date' => $row->excused_start_date,
                    'end_date' => $row->excused_end_date,
                    'reason_id' => $row->excused_reason_id
                ]);
                // return view('my-team.partials.switch', compact(["yesOrNo"])); // $row['id'];
                // return $row;
                return view('my-team.partials.switch', compact(["row", "excused", "yesOrNo"]));
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
        $reporting_users = User::find($this->id)->getReportingUserIds();
        // dd($reporting_users);
        return $model->newQuery()->whereIn('id', $reporting_users)
            ->withCount('activeGoals')
            ->with('upcomingConversation')
            ->with('latestConversation')
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
                ->addClass('text-center')
            /* ,
            Column::computed('upcomingConversation')
                ->title('Upcoming Conversation')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
            Column::computed('latestConversation')
                ->title('Last Conversation')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center') */,
            Column::computed('shared')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::computed('excused')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::computed('direct-reports')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
            /* new Column([
                'title' => 'Type',
                'data' => 'goal_type.name',
                'name' => 'goalType.name'
            ]),
            'start_date',
            'target_date', */
        ];
    }
}
