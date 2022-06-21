<?php

namespace App\Exports;

use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\ExcusedReason;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ExcusedEmployeeExport implements FromCollection,WithHeadings, WithMapping, WithStrictNullComparison

{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $users;

    function __construct($users) {
        // $this->id = $id;

        $this->users = $users;
        
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->users;
    }

      // map what a single member row should look like
    // this method will iterate over each collection item
    public function map($user): array
    {

        $emp = EmployeeDemo::where('guid', $user->guid)->first();
        // $reason = ExcusedReason::where('id', $user->excused_reason_id)->first();
        // $reason_name = $reason ? $reason->name : '';

        return [
            $emp ? $emp->employee_id : '',
            $emp ? $emp->employee_name : '',
            $user->email,
            $user->excused,
            $user->excused_start_date,
            $user->excused_end_date,
            $user->excuseReason ? $user->excuseReason->name : '',
            $emp ? $emp->organization : '',
            $emp ? $emp->level1_program : '',
            $emp ? $emp->level2_division : '',
            $emp ? $emp->level3_branch : '',
            $emp ? $emp->level4 : '',
            
        ];
        
    }


    public function headings(): array
    {
        return ["Employee ID", "Employee Name", "Email",
            "Excused", "Excused Start Date", "Excused End Date", "Excused Reason",
            "Organization", "Level 1", "Level 2", "Level 3", "Level 4", "Reporting To",
        ];
        
    }
}
