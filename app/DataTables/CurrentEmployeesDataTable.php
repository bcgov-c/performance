<?php

namespace App\DataTables;

use App\Models\EmployeeDemo;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CurrentEmployeesDataTable extends DataTable
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
            ->addColumn('action', 'currentemployeesdatatable.action');
    }

    public function ajax()
    {
        return $this->datatables
        ->eloquent($this->query())
        ->make(true);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\CurrentEmployeesDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function query(CurrentEmployeesDataTable $model)
    public function query()
    {
        $query = DB::table('employee_demo')
        ->whereNull('date_deleted')
        ->select('employee_id', 'guid', 'employee_name', 'jobcode_desc', 'organization','level1_program', 'level2_division', 'level3_branch', 'level4', 'effdt', 'hire_dt')
        ->wherein('employee_status', ['A', 'L', 'P', 'S']);
        // return $model->newQuery();
        return $query->get();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('currentemployeesdatatable-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
                    ->buttons(
                        Button::make('create'),
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
            Column::make('id'),
            Column::make('add your columns'),
            Column::make('created_at'),
            Column::make('updated_at'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'CurrentEmployees_' . date('YmdHis');
    }
}
