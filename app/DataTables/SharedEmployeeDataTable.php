<?php

namespace App\DataTables;

use App\Models\Conversation;
use App\Models\SharedProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

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
                $text = Conversation::nextConversationDue(User::find($row["id"]));
                $landingPage = 'conversation.templates';
                return view('my-team.partials.link-to-profile', compact(["row", "text", "landingPage"]));
            })/* ->addColumn('latestConversation', function ($row) {
                if( !$row['is_conversation_shared_with_auth_user']) {
                    return "-";
                }
                $conversation = $row->latestConversation[0] ?? null;
                return view('my-team.partials.conversation', compact(["row", "conversation"]));
            })->addColumn('upcomingConversation', function ($row) {
                if( !$row['is_conversation_shared_with_auth_user']) {
                    return "-";
                }
                $removeBlankLink = true;
                $conversation = $row->upcomingConversation[0] ?? null;
                return view('my-team.partials.conversation', compact(["row", "conversation", 'removeBlankLink']));
            }) */
            ->addColumn('shared', function ($row) {
                $yesOrNo = ($row->is_shared) ? 'Yes' : 'No';
                return view('my-team.partials.view-btn', compact(["row", "yesOrNo"])); // $row['id'];
            })
            ->addColumn('excused', function ($row) {
                // $yesOrNo = ($row->id % 2 !== 0) ? 'Yes' : 'No';
                $yesOrNo = ($row->excused_start_date !== null) ? 'Yes' : 'No';

                $excused = json_encode([
                    'start_date' => $row->excused_start_date,
                    'end_date' => $row->excused_end_date,
                    'reason_id' => $row->excused_reason_id
                ]);
                // return view('my-team.partials.switch', compact(["yesOrNo"])); // $row['id'];
                // return $row;
                return view('my-team.partials.switch', compact(["row", "excused", "yesOrNo"]));
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
            /* Column::computed('upcomingConversation')
                ->title('Upcoming Conversation')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
            Column::computed('latestConversation')
                ->title('Last Conversation')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'), */
            Column::computed('shared')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::computed('excused')
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