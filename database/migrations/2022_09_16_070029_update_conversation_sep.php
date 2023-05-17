<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ConversationTopic;

class UpdateConversationSep extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conversations = [
                [
                    'id' => 2,
                    'name' => 'Goal Setting',
                    'when_to_use' => 'Use this template when you need to focus on establishing initial goals or revising existing goals in response to shifting or new priorities in the organization. It will help to align individual goals with organizational strategies and create connections to individual strengths and opportunities for growth.',
                    'question_html' => '<p>Supervisors can share relevant team goals and priorities to begin the conversation. This helps employees understand the bigger picture and how their goals contribute. Participants can choose some or all the questions below to help guide discussion. Significant outcomes and action items should be captured in the comment boxes throughout the template.</p>
                     <ul><li>What goals can you add to your plan to help achieve team priorities?</li>
                     <li>What competencies and values will you focus on to achieve your goals?</li>
                     <li>What do you need to learn to achieve your goals? What gaps do you want to address?</li>
                     <li>What are the barriers to your success? How will you overcome them?</li>
                     <li>Imagine what success will look like: this can inform your personal performance measures (remember to pick results that are within your control and/or influence)</li>
                     <li>What personal goals would you like to include in your profile?</li>
                     <ul><li>What are your greatest growth opportunities?</li></ul>
                     <ul><li>What strengths do you have that you want to use more of? (Think about what you do effortlessly. What are you doing when you are at your best?)</li></ul>
                     <ul><li>How do your personal goals align with our team goals or those of the organization?</li></ul>
                     <li>What support do you need to meet your goals?</li></ul>',
                    'preparing_for_conversation' => 'Employees
                     <ul><li>Consider how your work connects to broader team, organizational, or corporate objectives</li>
                     <li>Reflect on personal strengths and skills that could support the work of the team</li>
                     <li>Identify any potential barriers to success that will be important to address when setting new goals</li>
                     <li>Come prepared to discuss goals that focus on both business results (what we accomplish) and/or behavioural competencies (how we accomplish things)</li></ul>
                     Supervisors
                     <ul><li>Provide copies of relevant corporate plans, organizational plans, and job profiles to the employee to help focus discussions</li>
                     <li>Come prepared to discuss goals that focus on both business results (what we accomplish) and/or behavioural competencies (how we accomplish things)</li>
                     <li>Consider adding suggested or mandated goals for employees through the Goal Bank in this platform as a way to provide common language and a starting point for customization</li></ul>'
                ],
            ];

            foreach($conversations as $conversation) {
                ConversationTopic::updateOrCreate([
                    'id' => $conversation['id'],
                ], $conversation);
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
