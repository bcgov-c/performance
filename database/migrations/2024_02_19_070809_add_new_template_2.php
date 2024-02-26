<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\GenericTemplate;
use Carbon\Carbon;

class AddNewTemplate2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $this->down();

        //
        // Template for shared goal comments
        //
        $template = GenericTemplate::updateOrCreate([
            'template' => 'CONVERSATION_CHANGE_NOTIFY',
        ],[
            'description' =>  'Send out email notification to other participants when a conversation is updated',
            'instructional_text' => 'You can add parameters',
            'sender' => '2',
            'subject' => 'PDP - %2 Has Updates on Your %3 Conversation',
            'body' => "<p>Hello %1,</p><p>%2 has made updates on your %3 conversation. Please visit https://performance.gov.bc.ca to view the details.</p>",
        ]);
    
        $template->binds()->delete();
    
        $template->binds()->create([
            'seqno' => 0,
            'bind' => '%1', 
            'description' => 'Recipient of the email',
        ]);        
        $template->binds()->create([
            'seqno' => 1,
            'bind' => '%2', 
            'description' => 'Person who made the update',
        ]);        
        $template->binds()->create([
            'seqno' => 2,
            'bind' => '%3', 
            'description' => 'Conversation topic',
        ]); 
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove Templates
        $template = GenericTemplate::whereRaw("template = 'CONVERSATION_CHANGE_NOTIFY'")->first();
        if($template) {
            $template->binds()->delete();
            $template->delete();
        } 
    }

}
