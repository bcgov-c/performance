<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeConversationStatusView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS employee_conversation_status_view;');

        \DB::statement("
            CREATE VIEW employee_conversation_status_view AS
            select user_demo_jr_view.*, 
            case when conversation_id IS NULL then 0 else 1 end as has_conversation,  
            case when (signoff_user_id is not null and supervisor_signoff_id is not null) then 1 else 0 end as conversation_completed    
            from `user_demo_jr_view` 
            left join `conversation_participants` on `conversation_participants`.`participant_id` = `user_demo_jr_view`.`user_id` 
            left join `conversations` on `conversation_participants`.`conversation_id` = `conversations`.`id` 
            where ((`due_date_paused` = 'N' or `due_date_paused` is null))
            and `date_deleted` is null
            ORDER BY employee_id; 
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("
            DROP VIEW employee_conversation_status_view
        ");
    }
}
