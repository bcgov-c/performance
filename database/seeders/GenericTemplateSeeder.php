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
    // Generic Template Creation

    $body = <<<'EOD'
<p>Hello %1</p>

<p>Your goal: <b>%2</b></p>

<p>The following comment was added on the above goal:</p>
    
<blockquote><q>%3</q></blockquote>
EOD;

$body2 = <<<'EOD'
<p>Hi all,</p>

<p>New Conversation template %1 was created and the meeting will be scheduled soon.</p>

<p>Thank you.</p>

EOD;

$body3 = <<<'EOD'
<p>Hi Administrator,</p><p>The following are the listing of the overdue users:</p><p>%1</p><p>&nbsp;</p>
EOD;


    $template = GenericTemplate::updateOrCreate([
      'template' => 'SUPERVISOR_COMMENT_MY_GOAL',
    ], [
      'description' =>  'send out email notificatioin when supervisor added comment on the gaol',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'Your supervisor added a new comment on your goal.',
      'body' => $body,
    ]);

    foreach ($template->binds as $bind) {
      $bind->delete();
    }

    $template->binds()->create([
      'seqno' => 0,
      'bind' => '%1', 
      'description' => 'Name recipient',
    ]);        
    $template->binds()->create([
      'seqno' => 1,
      'bind' => '%2', 
      'description' => 'The Goal',
    ]);        
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'The new comment',
    ]);      
    
    
    $template = GenericTemplate::updateOrCreate([
      'template' => 'EMPLOYEE_COMMENT_THE_GOAL',
    ], [
      'description' =>  'send out email notificatioin when employee added comment on the gaol',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'Your employee added a new comment on the goal.',
      'body' => $body,
    ]);

    foreach ($template->binds as $bind) {
      $bind->delete();
    }

    $template->binds()->create([
      'seqno' => 0,
      'bind' => '%1', 
      'description' => 'Name recipient',
    ]);        
    $template->binds()->create([
      'seqno' => 1,
      'bind' => '%2', 
      'description' => 'The Goal',
    ]);        
    $template->binds()->create([
      'seqno' => 2,
      'bind' => '%3', 
      'description' => 'The new comment',
    ]); 



    // Template 2
    $template = GenericTemplate::updateOrCreate([
      'template' => 'ADVICE_SCHEDULE_CONVERSATION',
    ], [
      'description' =>  'Send out email notification to all participants that you would like to schedule a conversation',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'New conversation template added, the schedule meeting will come soon',
      'body' => $body2,
    ]);

    foreach ($template->binds as $bind) {
      $bind->delete();
    }

    $template->binds()->create([
      'seqno' => 0,
      'bind' => '%1', 
      'description' => 'Conversation Template',
    ]);        


  // Template 3
    $template = GenericTemplate::updateOrCreate([
      'template' => 'WEEKLY_OVERDUE_SUMMARY',
    ], [
      'description' =>  'Send out email notification to HR Administrator about the list of conversation is overdue',
      'instructional_text' => 'You can add parameters',
      'sender' => '2',
      'subject' => 'Weekly overdue notification',
      'body' => $body3,
    ]);

    foreach ($template->binds as $bind) {
      $bind->delete();
    }

    $template->binds()->create([
      'seqno' => 0,
      'bind' => '%1', 
      'description' => 'Overdue listing',
    ]);        

  }
}
