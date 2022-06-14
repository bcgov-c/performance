<?php

namespace App\Exports;

use App\Models\User;
use App\Models\EmployeeDemo;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class UserGoalCountExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison
{

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
        // return User::select('id','name','email','reporting_to')->get();
        
        return $this->users;

    }

     // map what a single member row should look like
    // this method will iterate over each collection item
    public function map($user): array
    {

        $emp = EmployeeDemo::where('guid', $user->guid)->first();

        return [
            $emp ? $emp->employee_id : '',
            $emp ? $emp->employee_name : '',
            $user->email,
            $user->goals_count,
            $emp ? $emp->organization : '',
            $emp ? $emp->level1_program : '',
            $emp ? $emp->level2_division : '',
            $emp ? $emp->level3_branch : '',
            $emp ? $emp->level4 : '',
            $user->reportingManager ? $user->reportingManager->name : '',
        ];
    }


    public function headings(): array
    {
        return ["Employee ID", "Name", "Email", 'Active Goals Count', 
                "Organziation", "Level 1", "Level 2", "Level 3", "Level 4", "Reporting To",
            ];
    }
}
