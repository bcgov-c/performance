<?php

namespace App\DataTables;

use App\Models\Goal;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Auth;

class GoalsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                return view('goal.partials.action', compact(["row"]));// $row['id'];
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Goal $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Goal $model)
    {
        return $model->newQuery()->where('user_id', Auth::id())->with('goalType');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('goals-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->searching(false)
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
            'id',
            'title',
            new Column([
                'title' => 'Type',
                'data' => 'goal_type.name',
                'name' => 'goalType.name'
            ]),
            'start_date',
            'target_date',
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Goals_' . date('YmdHis');
    }
}