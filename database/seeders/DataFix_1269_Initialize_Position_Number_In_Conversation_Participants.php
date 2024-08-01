<?php

// NOT INTENDED FOR PRODUCTION
// ONLY FOR TEST AND TRAINING

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DataFix_1269_Initialize_Position_Number_In_Conversation_Participants extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
        $this->command->info(Carbon::now()." - Begin setup initial position number in conversation participants table.");
        \DB::statement("
            UPDATE conversation_participants 
                SET conversation_participants.position_number =
                    (SELECT ods_employee_demo.position_number
                    FROM users, ods_employee_demo 
                    WHERE conversation_participants.participant_id = users.id 
                    AND users.employee_id = ods_employee_demo.employee_id 
                    AND ods_employee_demo.date_deleted IS NULL LIMIT 1)
            WHERE conversation_participants.position_number IS NULL AND conversation_participants.role = 'emp'
        ");
        \DB::statement("
          UPDATE conversation_participants 
              SET conversation_participants.position_number =
                  (SELECT employee_demo.position_number
                  FROM users, employee_demo 
                  WHERE conversation_participants.participant_id = users.id 
                  AND users.employee_id = employee_demo.employee_id 
                  AND employee_demo.date_deleted IS NULL LIMIT 1)
          WHERE conversation_participants.position_number IS NULL AND conversation_participants.role = 'emp'
        ");
        $this->command->info(Carbon::now()." - End setup initial position number in conversation participants table.");
  }
}
