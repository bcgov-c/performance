<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\GenericTemplate;
use Carbon\Carbon;

class AddNewTemplate1 extends Migration
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
            'template' => 'GOAL_COMMENT_SHARED',
        ],[
            'description' =>  'Send out email notification to shared_with when a comment is added',
            'instructional_text' => 'You can add parameters',
            'sender' => '2',
            'subject' => 'PDP - %2 Added a Comment on One of the Shared Goals',
            'body' => "<p>Hello %1,</p><p>%2 added a comment on one of the shared goals in the Performance Development Platform.</p><p>Goal title: %3</p><p>Comment added:<br />%4</p><p>Log in to performance.gov.bc.ca to view the details.</p><p>Thanks!</p>",
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
            'description' => 'Person who added the comment',
        ]);        
        $template->binds()->create([
            'seqno' => 2,
            'bind' => '%3', 
            'description' => 'Goal title',
        ]); 
        $template->binds()->create([
            'seqno' => 3,
            'bind' => '%4', 
            'description' => 'Comment that was added',
        ]);      
        
        //
        // Template for shared goals
        //
        $template = GenericTemplate::updateOrCreate([
            'template' => 'GOAL_SHARED',
        ],[
            'description' =>  'Send out email notification when a goal was shared',
            'instructional_text' => 'N/A',
            'sender' => '2',
            'subject' => 'PDP - %2 Has Shared a Goal with You',
            'body' => "<p>Hello %1,</p><p>%2 has shared a&nbsp;Performance Development Platform goal with you.</p><p>Goal Title: %3.</p><p>Thank you!</p><p>https://www.performance.gov.bc.ca<br />&nbsp;</p>",
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
            'description' => 'Person who shared the goal',
        ]);        
        $template->binds()->create([
            'seqno' => 2,
            'bind' => '%3', 
            'description' => 'Goal title',
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
        $template = GenericTemplate::whereRaw("template = 'GOAL_COMMENT_SHARED'")
            ->first();
        if($template) {
            $template->binds()->delete();
            $template->delete();
        } 
        $template = GenericTemplate::whereRaw("template = 'GOAL_SHARED'")
            ->first();
        if($template) {
            $template->binds()->delete();
            $template->delete();
        } 
    }

}
