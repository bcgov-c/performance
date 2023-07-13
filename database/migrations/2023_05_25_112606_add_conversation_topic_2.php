<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ConversationTopic;

class AddConversationTopic2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $topics = [
                [
                    'id' => 1,
                    'name' => 'Performance Check-In',
                    'when_to_use' => 'Use this template when performance expectations are generally being met for a given role and you want to discuss progress against goals, challenges, successes, and how to improve ways of working and future performance outcomes.',
                    'question_html' => '<p>Participants can choose some or all the questions below to help guide discussions. Significant outcomes and action items should be captured in the comment boxes throughout the template.</p>
                    <ul><li>Overall, how would you describe your work since our last check-in?</li>
                    <li>What progress have you made against your goals?</li>
                    <li>Have your goals shifted?Tell me about that.</li>
                    <li>What accomplishments are you most proud of?</li><ul>
                    <li>How would you like to celebrate success? Do you prefer one-on-one discussions or would you like more public or team-oriented recognition?</li></ul>
                    <li>How do you think your role helps the work unit succeed?</li>
                    <li>What challenges have you faced? What did you learn?</li>
                    <ul><li>What could our team or organization learn from your experience?</li></ul>
                    <li>What support do you need from me as your supervisor to perform at your best?</li>
                    <ul><li>What do I do that is most/least helpful for you when it comes to completing your work?</li></ul>
                    <li>In what areas do you need or want to improve? What would help you improve?</li>
                    <ul><li>What specific skills or competencies should we focus on moving forward?</li></ul>
                    <li>What motivates you to get your job done?</li>
                    <li>Which job responsibilities/tasks do you enjoy most? Which do you least enjoy?</li>
                    <li>What opportunities are you looking for moving forward?</li>
                    <ul><li>Which of your interests or skills could we consider integrating into your work?</li></ul>
                    <li>How do you prefer to receive feedback and/or recognition for your work?</li>
                    <li>What (if any) concerns do you have when it comes to giving me feedback? How can I alleviate those concerns?</li></ul>',
                    'preparing_for_conversation' => '<ul><li>Employees </li><ul>
                    <li>Consider areas you have excelled at, or projects you&rsquo;ve been involved in that have been great successes</li>
                    <li>Identify skills and competencies that have led to your greatest results</li>
                    <li>Determine areas you feel you could improve and what would help you improve</li>
                    <li>Identify things that got in the way that will be important to address moving forward</li></ul></ul>
                    <ul><li>Supervisors </li><ul>
                    <p>Come prepared with feedback that is:</p>
                    <ul><li>Specific</li>
                    <li>Supported by examples</li>
                    <li>Focused on behaviours, not individuals</li>
                    <li>Focused on future improvements, not past mistakes</li></ul></ul>',
                ],
            
            
                [
                    'id' => 4,
                    'name' => 'Performance Improvement',
                    'when_to_use' => 'Use this template when performance expectations are not being met for a given role. It will help define required performance improvements, support to be provided, timelines, and next steps. ',
                    'question_html' => '<p>Supervisors should summarize the high-level performance expectations identified in the performance profile as requiring further development to begin the conversation. Participants can then use the items below to guide discussion. Significant outcomes and action items should be captured in the appropriate comment boxes throughout the template.</p>
                    <ul><li>Tell me about how things have been going for you in your role.</li>
                    <ul><li>What is going well?</li>
                    <li>Where do you see opportunities for improvement?</li></ul>
                    <li>To meet these expectations, what support do you need?</li>
                    <ul><li>This could be coaching, tools, resources, additional training, etc.</li></ul>
                    <li>Between now and our next conversation, I would like you to work on 2-3 areas we&rsquo;ve discussed as requiring development. What specific steps will you take to meet these expectations?</li>
                    <li>We will follow up on these areas and discuss your progress during our next meeting. If needed, we can discuss an action plan for improvement in each of those areas when we meet again.</li>
                    <li>What else would you like to share with me?</li>
                    <li>What support would you like from me?</li></ul>',
                    'preparing_for_conversation' => 'Employees
                    <ul><li>Consider what has been going well in your role</li>
                    <li>Identify where you see opportunities for improvement</li>
                    <li>Determine what supports would help you perform at your best</li></ul>
                    <p>Supervisors</p>
                    <p>Before engaging in this conversation, you may want to reach out to an HR Specialist through MyHR for additional support in having this conversation and/or if performance improvements are not made within agreed upon timelines.</p>
                    <p>You may also consider requesting short term coaching to review how you want to show up in the conversation.</p>
                    Consider:
                    <ul><li>What are the expectations for the position? Are they consistent with the employee&rsquo;s classification, job description, and work done by other employees in similar roles? Has a copy of the job description been provided to the employee?</li>
                    <li>What assumptions are you making about the employee?</li>
                    <li>Have the expectations been clearly articulated? How have they been articulated (i.e. goals in the Performance Development app, a letter of expectations)? Does the employee understand them?</li>
                    <li>What 2-3 specific areas of performance should the employee focus in the near term?</li></ul>',
                ],
            ];
        
        foreach($topics as $topic) {
                ConversationTopic::updateOrCreate([
                    'id' => $topic['id'],
                ], $topic);
            }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
