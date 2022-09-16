<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\GenericTemplate;
use Carbon\Carbon;

class GenericTemplateSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    //
    // 1. Template for Converstion Sign off
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'CONVERSATION_SIGN_OFF',
    ],[
      'description' =>  'Send out an email notification when a conversation has been signed',
      'instructional_text' => 'N/A',
      'sender' => '2',
      'subject' => 'PDP - %2 Signed-off on Your %3 Conversation',
      // 'body' => "<p>Hello %1,</p><p>%2 just signed-off on your %3 conversation. Please visit www.performance.gov.bc.ca to view the details.</p><p>Thank you!</p>",
      'body' => "<p>Hello %1,</p><p>%2 just signed-off on your %3 conversation. Please visit <a href='https://www.performance.gov.bc.ca'>www.performance.gov.bc.ca</a> to view the details.</p><p>Thank you!</p>",
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
      'description' => 'Person who signed the conversation',
    ]);        
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'Conversation topic',
    ]); 

    //
    // 2. New Goal in Goal Bank
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'NEW_GOAL_IN_GOAL_BANK',
    ],[
      'description' =>  'Send out email notification when a new goal is added to an employee\'s goal bank',
      'instructional_text' => 'N/A',
      'sender' => '2',
      'subject' => 'PDP - A New Goal Has Been Added to Your Goal Bank',
      // 'body' => "<p>Hello %1,</p><p>%2 has added a %4 goal to your goal bank. The goal is called: %3.</p><p>Please log in to https://performance.gov.bc.ca to view more details and add the goal to your profile as needed.</p><p>Thanks!</p>",
      'body' => "<p>Hello %1,</p><p>%2 has added a %4 goal to your goal bank. The goal is called: %3.</p><p>Please log in to <a href='https://www.performance.gov.bc.ca'>https://performance.gov.bc.ca</a> to view more details and add the goal to your profile as needed.</p><p>Thanks!</p>",
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
      'description' => 'Person who added goal to goal bank',
    ]);        
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'Goal title',
    ]); 
    $template->binds()->create([
      'seqno' => 3,
      'bind' => '%4', 
      'description' => 'Mandatory or suggested status',
    ]); 


    //
    // 3. Supervisor Add a new Comment 
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'SUPERVISOR_COMMENT_MY_GOAL',
    ], [
      'description' =>  '** Not in Use **',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'PDP - %2 Added a Comment on One of Your Goals',
      'body' => "<p>Hello %1,</p><p>%2 added a comment on one of your goals in the Performance Development Platform.</p><p>Goal title: %3</p><p>Comment added:<br />%4</p><p>Log in to https://performance.gov.bc.ca for details.</p><p>Thanks!</p>",
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
      'description' => 'User who made the comment',
    ]);        
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'Goal title',
    ]);      
    $template->binds()->create([
      'seqno' => 3,
      'bind' => '%4', 
      'description' => 'Comment added',
    ]);      
    
    //
    // 4. Employee Add a new Comment 
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'EMPLOYEE_COMMENT_THE_GOAL',
    ], [
      'description' =>  'Send out email notification when employee adds a comment to supervisor\'s goal',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'PDP - %2 Added a Comment on One of Your Goals',
      // 'body' => "<p>Hello %1,</p><p>%2 added a comment on one of your goals in the Performance Development Platform.</p><p>Goal title: %3</p><p>Comment added:<br />%4</p><p>Log in to performance.gov.bc.ca to view the details.</p><p>Thanks!</p>",
      'body' => "<p>Hello %1,</p><p>%2 added a comment on one of your goals in the Performance Development Platform.</p><p>Goal title: %3</p><p>Comment added:<br />%4</p><p>Log in to <a href='https://performance.gov.bc.ca'>performance.gov.bc.ca</a> to view the details.</p><p>Thanks!</p>",
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
    // 5. Advice Schedule Conversation
    //
    
    $template = GenericTemplate::updateOrCreate([
      'template' => 'ADVICE_SCHEDULE_CONVERSATION',
    ], [
      'description' =>  'Send out email notification to schedule a conversation',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'PDP - %2 Would Like to Have a %3 Conversation With You',
      // 'body' => "<p>Hi %1,</p><p>%2 would like to have a %3 conversation with you in the Performance Development Platform. Please work with %2 to schedule a time in your Outlook calendar.</p><p>The deadline to complete your next performance conversation is %4.</p><p>Thank you!</p><p>https://www.performance.gov.bc.ca</p><p>&nbsp;</p>",
      'body' => "<p>Hi %1,</p><p>%2 would like to have a %3 conversation with you in the Performance Development Platform. Please work with %2 to schedule a time in your Outlook calendar.</p><p>The deadline to complete your next performance conversation is %4.</p><p>Thank you!</p><p><a href='https://www.performance.gov.bc.ca'>https://www.performance.gov.bc.ca</a></p><p>&nbsp;</p>",
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
      'description' => 'Person who created the conversation',
    ]);        
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'Topic of the conversation',
    ]);      
    $template->binds()->create([
      'seqno' => 3,
      'bind' => '%4', 
      'description' => 'Due date for recipient\'s next conversation',
    ]);         

    //
    // Template 6 : WEEKLY_OVERDUE_SUMMARY
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'WEEKLY_OVERDUE_SUMMARY',
    ], [
      'description' =>  ' Send out email notification to HR Administrator with a list of employees who are overdue for a conversation',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'PDP - Past Due Performance Conversations',
      'body' => "<p>Hello %1,</p><p>The following employees are overdue for a conversation in the Performance Development Platform:</p><p>%2</p><p>&nbsp;</p>",
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
      'description' => 'List of overdue employees: table with ID, name, email, organization, level 1, level 2, level 3, level 4, supervisor',
    ]);        

    //
    // 7. CONVERSATION_DISAGREED
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'CONVERSATION_DISAGREED',
    ],[
      'description' =>  'Send out an email notification when someone selects "disagree" on a performance conversation',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'PDP - %2 Has Selected "Disagree" on Your %3 Conversation',
      // 'body' => "<p>Hello %1,</p><p>%2 just selected &quot;disagree&quot; on your %3 conversation. Please visit https://performance.gov.bc.ca to view the details.</p><p>Thank you!</p>",
      'body' => "<p>Hello %1,</p><p>%2 just selected &quot;disagree&quot; on your %3 conversation. Please visit <a href='https://www.performance.gov.bc.ca'>https://performance.gov.bc.ca</a> to view the details.</p><p>Thank you!</p>",
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
      'description' => 'Person who disagree the conversation',
    ]);        
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'Conversation topic',
    ]); 


    //
    // 8. CONVERSATION_DUE
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'CONVERSATION_DUE',
    ],[
      'description' =>  'Send out email notification when conversation is overdue',
      'instructional_text' => 'N/A',
      'sender' => '2',
      'subject' => 'PDP - Your Performance Conversation is Past Due',
      // 'body' => "<p>Hello %1,</p><p>Your next performance conversation was due on %2. Please work with your supervisor to schedule and complete this conversation as soon as possible.</p><p>Thank you!</p><p>https://www.performance.gov.bc.ca</p>",
      'body' => "<p>Hello %1,</p><p>Your next performance conversation was due on %2. Please work with your supervisor to schedule and complete this conversation as soon as possible.</p><p>Thank you!</p><p><a href='https://www.performance.gov.bc.ca'>https://www.performance.gov.bc.ca</a></p>",
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
      'description' => 'Next conversation due date',
    ]);     

    //
    // 9. CONVERSATION_REMINDER
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'CONVERSATION_REMINDER',
    ],[
      'description' =>  'Send out email notification when conversation will be due in 1 week or 1 month',
      'instructional_text' => 'N/A',
      'sender' => '2',
      'subject' => 'PDP - Your Next Performance Conversation is Due by %2',
      // 'body' => "<p>Hello %1,</p><p>A reminder that your next conversation in the Performance Development Platform is due by %2. Please work with your supervisor to schedule this conversation at your earliest convenience.</p><p>Thank you!</p><p>https://www.performance.gov.bc.ca</p>",
      'body' => "<p>Hello %1,</p><p>A reminder that your next conversation in the Performance Development Platform is due by %2. Please work with your supervisor to schedule this conversation at your earliest convenience.</p><p>Thank you!</p><p><a href='https://www.performance.gov.bc.ca'>https://www.performance.gov.bc.ca</a></p>",
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
      'description' => 'Next conversation due date',
    ]);   
    
    //
    // 10. PROFILE_SHARED
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'PROFILE_SHARED',
    ],[
      'description' =>  'Send out email notification when your profile was shared',
      'instructional_text' => 'N/A',
      'sender' => '2',
      'subject' => 'PDP - Your Profile Has Been Shared with %2',
      // 'body' => "<p>Hello %1,</p><p>Your Performance Development Platform profile has been shared with %2.</p><p>Element(s) that have been shared: %3.</p><p>Thank you!</p><p>https://www.performance.gov.bc.ca</p>",
      'body' => "<p>Hello %1,</p><p>Your Performance Development Platform profile has been shared with %2.</p><p>Element(s) that have been shared: %3.</p><p>Thank you!</p><p><a href='https://www.performance.gov.bc.ca'>https://www.performance.gov.bc.ca</a></p>",
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
      'description' => 'Delegated supervisor',
    ]); 
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'Shared element',
    ]); 
    $template->binds()->create([
      'seqno' => 3,
      'bind' => '%4', 
      'description' => 'Comment',
    ]); 


    //
    // 11. SUPV_CONV_REMINDER
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'SUPV_CONV_REMINDER',
    ],[
      'description' =>  'Send out an email notification to the supervisor when one of their employee\'s has an upcoming conversation deadline',
      'instructional_text' => 'N/A',
      'sender' => '2',
      'subject' => 'PDP - %2\'s Next Conversation Due Date is %3',
      // 'body' => "<p>Hello %1,</p><p>A reminder that %2&#39;s next conversation in the Performance Development Platform is due by %3. Please work with %2 to schedule and complete this conversation at your earliest convenience.</p><p>Thank you!</p><p>https://www.performance.gov.bc.ca</p>",
      'body' => "<p>Hello %1,</p><p>A reminder that %2&#39;s next conversation in the Performance Development Platform is due by %3. Please work with %2 to schedule and complete this conversation at your earliest convenience.</p><p>Thank you!</p><p><a href='https://www.performance.gov.bc.ca'>https://www.performance.gov.bc.ca</a></p>",
    ]);

    $template->binds()->delete();

    $template->binds()->create([
      'seqno' => 0,
      'bind' => '%1', 
      'description' => 'Supervisor who is receiving the email',
    ]);   
    $template->binds()->create([
      'seqno' => 1,
      'bind' => '%2', 
      'description' => 'Employee who has an upcoming conversation due',
    ]); 
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'Next conversation due date for the employee',
    ]); 


    //
    // 12. SUPV_CONV_REMINDER
    //
    $template = GenericTemplate::updateOrCreate([
      'template' => 'SUPV_CONV_DUE',
    ],[
      'description' =>  'Send out email notification to supervisor when an employee\'s conversation is past due',
      'instructional_text' => 'N/A',
      'sender' => '2',
      'subject' => 'PDP - %2\'s Conversation is Past Due',
      // 'body' => "<p>Hello %1,</p><p>%2&#39;s next conversation in the Performance Development Platform was due by %3. Please work with %2 to schedule and complete this conversation as soon as possible.</p><p>Thank you!</p><p>https://www.performance.gov.bc.ca</p>",
      'body' => "<p>Hello %1,</p><p>%2&#39;s next conversation in the Performance Development Platform was due by %3. Please work with %2 to schedule and complete this conversation as soon as possible.</p><p>Thank you!</p><p><a href='https://www.performance.gov.bc.ca'>https://www.performance.gov.bc.ca</a></p>",
    ]);

    $template->binds()->delete();

    $template->binds()->create([
      'seqno' => 0,
      'bind' => '%1', 
      'description' => 'Supervisor who is receiving the email',
    ]);   
    $template->binds()->create([
      'seqno' => 1,
      'bind' => '%2', 
      'description' => 'Employee who has an upcoming conversation due',
    ]); 
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'Next conversation due date for the employee',
    ]); 
   

  }
}
