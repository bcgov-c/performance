<?php

namespace App\Exports;

use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\SharedProfile;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class SharedEmployeeExport implements FromCollection,WithHeadings, WithMapping, WithStrictNullComparison

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
        $shared_profiles = SharedProfile::where('shared_id', $user->id)->get();

        $shared_with = implode(', ', $shared_profiles->map( function ($item, $key) { return $item ? $item->sharedWith->name : null; })->toArray() );

        return [
            $emp ? $emp->employee_id : '',
            $emp ? $emp->employee_name : '',
            $user->email,
            $user->shared,
            $shared_with,
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
            "Shared", "Shared with",
            "Organization", "Level 1", "Level 2", "Level 3", "Level 4", "Reporting To",
        ];
        
    }
}
