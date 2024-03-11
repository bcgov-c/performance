<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateVideoTutorialsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('resource_content')
        ->whereRaw("category = 'videotutorials' AND question = 'Video Tutorials'")
        ->update(array(
            'answer' => '<!--Video Tutorials Section -->
            <p>
                <a href="https://youtu.be/L99OpfoHCiY" target="_blank"><i class="fab fa-youtube"></i> Overview of Performance Development</a>
            </p>
            <p>
                The BC Public Service is updating the current performance management approach to focus on meaningful career and professional development conversations 
                between employees and supervisors. This means changes to the process and culture of performance management, supported by a new Performance Development 
                Platform (PDP) replacing MyPerformance by fall 2023.
            </p>
            <p>
                <a href="https://youtu.be/s-3mR1Oni84" target="_blank"><i class="fab fa-youtube"></i> Setting a Goal in the Performance Development Platform</a>
            </p>
            <p>
                The My Goals section of the Performance Development Platform is intended to be a flexible environment that allows employees and supervisors to create 
                and track goals in a way that works best for their team. Employees and supervisors can set work, learning, or career development goals and track progress. 
                Goals stay in the system until they are completed, put on hold or withdrawn which lets you create long- and short-term goals.
            </p>
            <p>
                <a href="https://youtu.be/hv-MxU2_mKQ" target="_blank"><i class="fab fa-youtube"></i> Effective Goals in the Performance Development Platform</a>
            </p>
            <p>
                Goal setting in the PDP helps provide direction and vision for career growth and development. Watch this video to learn about why effective goals are important.
            </p>
            <p>
                <a href="https://youtu.be/TuaLsDRZJ1E" target="_blank"><i class="fab fa-youtube"></i> Performance Conversations in the Performance Development Platform</a>
            </p>
            <p>
                The Performance Development Platform supports employees and supervisors to have the right conversation at the right time. The My Conversations section 
                of the Performance Development Platform is where employees and supervisors record and sign off on performance conversations every 4 months. 
                This is also where you can find the templates to support your conversations including onboarding a new employee, goal setting, career development, 
                performance check-ins or performance improvement.
            </p>
            <p>
                <a href="https://youtu.be/EC6xgSpaNng" target="_blank"><i class="fab fa-youtube"></i> Having productive conversations in the Performance Development Platform</a>
            </p>
            <p>
                Regular performance conversations support the relationship between supervisors and employees by building trust and providing the opportunity to collaborate 
                on setting goals to determine what career growth and performance indicators look like. Watch this video to learn about why productive performance conversations 
                are important.
            </p>
            <p>
                <a href="https://youtu.be/h80op_O03AY" target="_blank"><i class="fab fa-youtube"></i> Team Goals in the Performance Development Platform</a>
            </p>
            <p>
                The Performance Development Platform is intended to be a flexible environment allowing employees and supervisors to create and track goals in a way 
                that works best for their team. Creating goals that are shared among team members allows everyone to collaborate on a common objective.
            </p>
            <p>
                <a href="https://youtu.be/rythd_z-9so" target="_blank"><i class="fab fa-youtube"></i> Setting Goals for Direct Reports in the Performance Development Platform</a>
            </p>
            <p>
                The Performance Development Platform is a flexible environment that allows employees and supervisors to create and track goals in a way that works best 
                for their team. Creating a goal for your direct report’s Goal Bank is an opportunity to create a common goal that can be personalized by employees.
            </p>
            <p>
                <a href="https://youtu.be/JFijaF1maaU" target="_blank"><i class="fab fa-youtube"></i> My Team Section of the Performance Development Platform</a>
            </p>
            <p>    
                The My Team section of the Performance Development Platform provides supervisors a dashboard of direct report details and access to their profiles 
                including a summary view of how many active goals each employee has, when their next performance conversation is due, and whether or not they have 
                been shared or excused in the PDP.
            </p>
            '
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the data (optional)
    }
}
