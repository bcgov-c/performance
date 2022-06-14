<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Conversation;
use App\Models\EmployeeDemo;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;


class ConversationExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison
{
    
    protected $chart;        
    protected $data;

    function __construct($chart, $data) {
        // $this->id = $id;

        $this->chart = $chart;
        $this->data = $data;
        
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->data;
    }

      // map what a single member row should look like
    // this method will iterate over each collection item
    public function map($data): array
    {

        if ($this->chart == 1) {

            $emp = EmployeeDemo::where('guid', $data->guid)->first();

            return [
                $emp ? $emp->employee_id : '',
                $emp ? $emp->employee_name : '',
                $data->email,
                $data->next_due_date,
                $emp ? $emp->organization : '',
                $emp ? $emp->level1_program : '',
                $emp ? $emp->level2_division : '',
                $emp ? $emp->level3_branch : '',
                $emp ? $emp->level4 : '',
                $data->reportingManager ? $data->reportingManager->name : '',
            ];


        } else {

            $emp = EmployeeDemo::where('guid', $data->user->guid)->first();

            // $signoff_user = User::where('id', $data->signoff_user_id)->first();
            // $signoff_supervisor = User::where('id', $data->supervisor_signoff_id)->first();

                return [
                    
                    $data->id,
                    $data->topic->name,
                    // $conversation->user->name,
                    $emp ? $emp->employee_id : '',
                    $emp ? $emp->employee_name : '',
                    $data->user->email,
                    $data->next_due_date,

                    implode(', ', $data->conversationParticipants->pluck('participant.name')->toArray() ),
                    $data->signoff_user ? $data->signoff_user->name : '',
                    $data->signoff_supervisor ? $data->signoff_supervisor->name : '',

                    $emp ? $emp->organization : '',
                    $emp ? $emp->level1_program : '',
                    $emp ? $emp->level2_division : '',
                    $emp ? $emp->level3_branch : '',
                    $emp ? $emp->level4 : '',
                  
                ];
        }
    }


    public function headings(): array
    {

        if ($this->chart == 1) {
            return ["Employee ID", "Employee Name", "Email",
                "Next Conversation Due",
                "Organization", "Level 1", "Level 2", "Level 3", "Level 4", "Reporting To",
            ];
        } else {
            return ["id", "Topic", "Employee ID", "Employee Name", "Email",
                "Conversation Due Date", "Conversation Participant",
                "Employee Sign-Off", "Supervisor Sign-off", 
                "Organization", "Level 1", "Level 2", "Level 3", "Level 4",
            ];
        }
    }
}
