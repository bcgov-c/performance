<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InsertResourceContentData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('resource_content')->insert([
            [
                'category' => 'userguide',
                'question' => 'Welcome!',
                'answer' => '<p>The Performance Development Platform (PDP) is a new tool to support you to set effective goals and have meaningful performance conversations. This guide will outline some of the basic functions of the PDP and should help set you up for success.</p>
                    <p>In addition to the guide, there are <a href="/resources/video-tutorials" target="_blank">tutorial videos</a> available to walk you through some common scenarios. There are also specific resources for <a href="/resources/goal-setting" target="_blank">Goal Setting</a> and <a href="/resources/conversations" target="_blank">Performance Conversations</a> that go into greater detail about best practice in each area, and a <a href="/resources/faq" target="_blank">Frequently Asked Questions</a> area with information about both the performance development approach and the PDP tool.</p>
                    <p>Finally, there are helpful tips like this one <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="top" data-html="true" data-content="See the sections below for more info on the basic functions of the PDP."> </i> throughout the app. Make sure to click on them if you are looking for more information.
                    <p>If you can\'t find the information you are looking for, please feel free to <a href="/resources/contact" target="_blank">Contact Us</a> and we will do our best to help.</p>',
                'answer_file' => '5',    
            ],
            [
                'category' => 'userguide',
                'question' => 'My Goals Section',
                'answer' => '<!-- My Team Section (supervisors only) -->
                <p>The My Team section displays a dashboard of your direct reports and any employees that have been shared with you. For each team member you can see their number of active goals, their next performance conversation due date, their shared status, and their excused status.</p>
                <br>
                <u>Shared Status</u>
                <br><br>
                <p>Clicking on the Yes/No below the “Shared” column header launches the employee share window. This provides details of anyone that currently has supervisor-level access to the employee&rsquo;s profile and allows you to add additional shared access if required.</p>
                <p>Supervisors may share an employee&rsquo;s PDP profile with another supervisor or staff only for a legitimate business reason. The profile should only be shared with people who normally handle employees&rsquo; permanent personnel records (i.e. Public Service Agency or co-supervisors).</p>
                <p>An employee may also wish to share their profile with someone other than a direct supervisor (for example, a hiring manager). In order to do this - the employee\'s consent is required.</p>
                <br>

                <u>Excused Status</u>
                <br><br>
                <p>Clicking on the Yes/No/Auto below the “Excuse” column header launches the employee excused window. This provides details on the employee&rsquo;s current excused status. The PDP automatically excuses employees who are on leave or covered by another performance review process (i.e. ADMs, DMs) and activates them again if their status changes.</p>
                <p>All employees are required to complete at least one performance conversation every four months unless they are excused. Supervisors may excuse employees from completing a conversation only if they fit into one of the categories listed in the dropdown box provided. Excusing an employee will remove them from any reporting and will pause the employee&rsquo;s conversation deadlines.</p>
                <br>

                <u>Viewing Employee Profiles</u>
                <br><br>
                <p>Clicking on an employee name will take you to the supervisor view of their profile. Here you can see the details of their current and past goals. Clicking on a goal in their profile allows you to review the details of the goal and provide feedback in the comments section. The employee will receive a notification when a new comment is added to one of their goals.</p>
                <p>You can also view the employee&rsquo;s upcoming and completed conversations. If you are a participant in the conversation, you can add your comments and sign-off directly from this view. If the conversation is between the employee and another supervisor you will be able to view the details but not contribute or sign-off.</p>',
                'answer_file' => '2',
            ],
            [
                'category' => 'userguide',
                'question' => 'My Conversations Section',
                'answer' => '<!-- My Conversations Section -->
                <!-- <p>The My Conversations section is designed to support employees and supervisors to have the right conversation at the right time. Visit the <a href=\'/resource/conversations\' target=\'_blank\'>Performance Conversations</a> resource for more information on best practices.</p> -->
                <p>The My Conversations section is designed to support employees and supervisors to have the right conversation at the right time.</p>
                <!-- <p>Every employee must record at least one performance conversation every four months (three per year). However, the topic and nature of each performance conversation does not have to be the same. The PDP has templates to support conversations focused on onboarding a new employee, goal setting, performance check-ins, performance improvement, and career development. Each of these count as a performance conversation. Check out the resource on <a href=\'/resource/conversations?t=5\' target=\'_blank\'>Performance Conversation Templates</a> to see all the options and suggestions for when to use each one.</p> -->
                <p>Every employee must record at least one performance conversation every four months (three per year). However, the topic and nature of each performance conversation does not have to be the same. The PDP has templates to support conversations focused on onboarding a new employee, goal setting, performance check-ins, career development, and performance improvement. Each of these count as a performance conversation. Check out the resource on <a href=\'/resources/conversations?t=5\' target=\'_blank\'>Performance Conversation Templates</a> to see all the options and suggestions for when to use each one.</p>
                <p>Furthermore, each employee has their own unique performance conversation deadline in the PDP. Not all conversations happen across the organization at the same time. Once you complete a performance conversation, your personal deadline becomes the date you completed the conversation plus four months. For example, if my supervisor and I sign-off on a performance check-in conversation on February 10, my next conversation will be due on June 10 (Feb 10 plus four months). This provides greater flexibility for when performance conversations occur.</p>
                <br>
                <u>Conversation Templates</u>
                <br><br>
                <!-- <p>This area lists all the performance conversation templates available for use. Each templates includes suggestions for when to use it, tips on how employees and supervisors can prepare for the conversation, and a list of questions to consider in advance or during the conversation to guide discussion. You may also want to check out the resource on <a href=\'/resource/conversations?t=5\' target=\'_blank\'>Performance Conversation Templates</a> to see more details on all the options and examples for when to use each one.</p> -->
                <p>This area lists all the performance conversation templates available for use. Each templates includes suggestions for when to use it, tips on how employees and supervisors can prepare for the conversation, and a list of questions to consider in advance or during the conversation to guide discussion. You may also want to check out the resource on <a href=\'/resources/conversations\' target=\'_blank\'>Performance Conversation Templates</a> to see more details on all the options and examples for when to use each one.</p>
                <p>Once you\'ve decided on the right template, use the participants dropdown menu to select which team member or supervisor you want to connect with and hit "Start Conversation" to alert them that you want to meet. An email will be sent but conversations will still need to be scheduled independently in your Outlook calendar.</p>
                <br>
                
                <u>Open Conversations</u>
                <br><br>
                <p>This area lists all planned or in progress conversations that have yet to be signed-off by both employee and supervisor. Each open conversations includes an optional list of questions to help guide the conversation, text boxes to capture major conversation outcomes, and an attestation and sign-off area to formalize the results.</p>
                <p>Note that as soon as either employee or supervisor signs off on the open conversation, the content is locked and no additional comments can be made. Both participants should add all comments before either sign.</p>
                <p>Once a conversation has been signed-off by both participants, it moves to the Completed Conversations tab and becomes an official performance development record for the employee. Their next performance conversation deadline will be set for the sign-off date plus four months.</p>
                <br>
                
                <u>Completed Conversations</u>
                <br><br>
                <p>This area contains all conversations that have been signed by both employee and supervisor. There is a two-week period from the date of sign-off when either participant can un-sign the conversation to return it to the Open Conversations tab for further edits. Conversations that have passed the two-week period require approval and assistance to re-open. If you need to unlock a conversation, submit an <a target="blank" href="https://www2.gov.bc.ca/gov/content/careers-myhr">AskMyHR</a> service request to Myself > HR Software Systems Support > Performance Development Platform.</p>
                    ',
                'answer_file' => '3',    
            ],
            [
                'category' => 'userguide',
                'question' => 'My Team Section (supervisors only)',
                'answer' => '<!-- My Team Section (supervisors only) -->
                <p>The My Team section displays a dashboard of your direct reports and any employees that have been shared with you. For each team member you can see their number of active goals, their next performance conversation due date, their shared status, and their excused status.</p>
                <br>
                <u>Shared Status</u>
                <br><br>
                <p>Clicking on the Yes/No below the “Shared” column header launches the employee share window. This provides details of anyone that currently has supervisor-level access to the employee&rsquo;s profile and allows you to add additional shared access if required.</p>
                <p>Supervisors may share an employee&rsquo;s PDP profile with another supervisor or staff only for a legitimate business reason. The profile should only be shared with people who normally handle employees&rsquo; permanent personnel records (i.e. Public Service Agency or co-supervisors).</p>
                <p>An employee may also wish to share their profile with someone other than a direct supervisor (for example, a hiring manager). In order to do this - the employee\'s consent is required.</p>
                <br>
                
                <u>Excused Status</u>
                <br><br>
                <p>Clicking on the Yes/No/Auto below the “Excuse” column header launches the employee excused window. This provides details on the employee&rsquo;s current excused status. The PDP automatically excuses employees who are on leave or covered by another performance review process (i.e. ADMs, DMs) and activates them again if their status changes.</p>
                <p>All employees are required to complete at least one performance conversation every four months unless they are excused. Supervisors may excuse employees from completing a conversation only if they fit into one of the categories listed in the dropdown box provided. Excusing an employee will remove them from any reporting and will pause the employee&rsquo;s conversation deadlines.</p>
                <br>
                
                <u>Viewing Employee Profiles</u>
                <br><br>
                <p>Clicking on an employee name will take you to the supervisor view of their profile. Here you can see the details of their current and past goals. Clicking on a goal in their profile allows you to review the details of the goal and provide feedback in the comments section. The employee will receive a notification when a new comment is added to one of their goals.</p>
                <p>You can also view the employee&rsquo;s upcoming and completed conversations. If you are a participant in the conversation, you can add your comments and sign-off directly from this view. If the conversation is between the employee and another supervisor you will be able to view the details but not contribute or sign-off.</p>
                
                
                ',
                'answer_file' => '4',
            ],

            [
                'category' => 'videotutorials',
                'question' => 'Video Tutorials',
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
                    <a href="https://youtu.be/TuaLsDRZJ1E" target="_blank"><i class="fab fa-youtube"></i> Performance Conversations in the Performance Development Platform</a>
                </p>
                <p>
                    The Performance Development Platform supports employees and supervisors to have the right conversation at the right time. The My Conversations section 
                    of the Performance Development Platform is where employees and supervisors record and sign off on performance conversations every 4 months. 
                    This is also where you can find the templates to support your conversations including onboarding a new employee, goal setting, career development, 
                    performance check-ins or performance improvement.
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
                ',
                'answer_file' => '1',
            ],

            [
                'category' => 'goalsetting',
                'question' => 'What is goal setting?',
                'answer' => 'Goal setting is a process of working towards what we want to do or who we want to be. Employees and supervisors should collaborate and communicate openly on what goals should be and how they can be achieved.
                ',
                'answer_file' => '',
            ],
            [
                'category' => 'goalsetting',
                'question' => 'Why are goals important?',
                'answer' => 'Goals help us:
                <ul>    
                    <li>Focus our energy and resources</li>
                    <li>Increase our performance and our organization&rsquo;s performance</li>
                    <li>Feel more engaged and invested in our roles</li>
                    <li>Give us a sense of accomplishment and satisfaction</li>
                </ul>
                ',
                'answer_file' => '2',
            ],
            [
                'category' => 'goalsetting',
                'question' => 'SMART and HARD goal setting frameworks',
                'answer' => '<!-- <p><strong>Elements of effective goals(the Five C&rsquo;s)</strong></p> -->
                <!-- <p>The types of goals we set and how we frame them influence how well they motivate us and how likely we are to achieve them. To maximize effectiveness, we should <strong>c</strong>ollaborate with our supervisor to set <strong>c</strong>lear, <strong>c</strong>hallenging goals and <strong>c</strong>ommit ourselves to following through on them while recognizing when a <strong>c</strong>omplex goal may need to be broken it into smaller more manageable chunks. Details of this &ldquo;Five Cs&rdquo; approach are shown below.</p> -->
                <p>Creating S-M-A-R-T goals help ensure that our objectives are clearly defined and attainable within an agreed upon timeframe. Making those goals H-A-R-D increases our level of focus and motivation.</p>
                SMART
                <table cellspacing="0" cellpadding="0" class="table table-sm table-bordered">
                <tbody>
                <!-- <tr> -->
                <!-- <td colspan="2"> -->
                <!-- <p><strong>The Five Cs</strong></p> -->
                <!-- </td> -->
                <!-- </tr> -->
                <tr>
                <td>
                <!-- <p><strong>Clarity</strong></p> -->
                <p>Specific</p>
                </td>
                <td>
                <p>Define exactly what is expected, who is responsible for it, and what steps need to be taken to achieve it.</p>
                <!-- <ul>
                <li><strong>S</strong>pecific: well-defined and easily understood</li>
                <li><strong>M</strong>easurable: associated with objective measures that define what success looks like</li>
                <li><strong>A</strong>chievable: attainable with realistic effort and available resources</li>
                <li><strong>R</strong>elevant: beneficial/valuable and connected to your organization&rsquo;s strategy</li>
                <li><strong>T</strong>ime-bound: driven by ambitious but realistic timelines</li>
                </ul> -->
                </td>
                </tr>
                <tr>
                <td>
                <p>Measurable</p>
                </td>
                <td>
                <!-- <p>Achievable does not mean easy. Be willing to challenge yourself and leave your comfort zone. Lean in to being uncomfortable; that&rsquo;s where the magic happens.</p> -->
                <p>Provide milestones to track progress, increase motivation, and know when to celebrate successes.</p>
                </td>
                </tr>
                <tr>
                <td>
                <p>Attainable</p>
                </td>
                <td>
                <!-- <p>You need to be committed to the goal for it to be effective. Try to find a way to connect with it emotionally &ndash; what does it mean to succeed? What is the impact? If you can visualize success, you will feel more committed to achieving your goal.</p> -->
                <p>Check to make sure goals are realistic and can reasonably be achieved; identify any known roadblocks up front and don&rsquo;t be afraid to break larger goals down into multiple sub-goals to stay motivated and on track.</p>
                </td>
                </tr>
                <tr>
                <td>
                <p>Relevant</p>
                </td>
                <td>
                <p>Connect individual goals to the bigger picture and focus on goals with the greatest impact to overall career, team, or organizational strategy.</p>
                </td>
                </tr>
                <tr>
                <td>
                <p>Time-bound</p>
                </td>
                <td>
                <p>Allow enough time to achieve the goal, but not too much time to undermine performance. Goals without deadlines tend to be overtaken by day-to-day events.
                </p>
                </td>
                </tr>
                </tbody>
                </table>
                
                <br>
                HARD
                <table cellspacing="0" cellpadding="0" class="table table-sm table-bordered">
                <tbody>
                <tr>
                <td>
                <p>Heartfelt</p>
                </td>
                <td>
                <p>Highlight an individual or emotional attachment to the goal; does it move you toward a desired future state?</p>
                </td>
                </tr>
                <tr>
                <td>
                <p>Animated</p>
                </td>
                <td>
                <p>Visualize the goal and focus on what success will look and feel like; what actions do you see yourself taking to get there?</p>
                </td>
                </tr>
                <tr>
                <td>
                <p>Required</p>
                </td>
                <td>
                <p>Focus on goals that are necessary for your own or your organization&rsquo;s success.</p>
                </td>
                </tr>
                <tr>
                <td>
                <p>Difficult</p>
                </td>
                <td>
                <p>Embrace goals that require learning new skills and that potentially move you out of an established comfort zone.</p>
                </td>
                </tr>
                </tbody>
                </table>
                ',
                'answer_file' => '3',
            ],
            [
                'category' => 'goalsetting',
                'question' => 'What does a good goal statement look like?',
                'answer' => '<div class="table-responsive">
                <table cellspacing="0" cellpadding="0" class="table table-condensed table-bordered">
                    <tbody>
                        <tr>
                            <td colspan="2">
                                <p><strong>Goal Element</strong></p>
                            </td>
                            <td>
                                <p><strong>SMART Framework</strong></p>
                            </td>
                            <td>
                                <p><strong>HARD Framework</strong></p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><em>What</em></p>
                            </td>
                            <td>
                                <p>A clear, concise opening statement for your goal. Anyone that reads this should understand what the goal is.</p>
                            </td>
                            <td>
                                <!-- <p>Clarity, Challenge &amp; Collaboration</p> -->
                                <p>Specific</p>
                            </td>
                            <td>                    
                                Animated
                                <br>
                                Difficult
                            </td>                
                        </tr>
                        <tr>
                            <td>
                                <p><em>Why</em></p>
                            </td>
                            <td>
                                <p>Explanation of why this goal is important to you and the organization.</p>
                            </td>
                            <td>
                                <!-- <p>Clarity (R in SMART) &amp; Commitment</p> -->
                                <p>Relevant</p>
                            </td>
                            <td>                    
                                Required
                                <br>
                                Heartfelt
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><em>How</em></p>
                            </td>
                            <td>
                                <p>The plan or steps you will take to achieve your goal.</p>
                            </td>
                            <td>
                                <!-- <p>Clarity, Challenge &amp; Complexity</p> -->
                                Specific
                                <br>
                                Attainable
                            </td>
                            <td>                    
                                Animated                    
                            </td>                
                        </tr>
                        <tr>
                            <td>
                                <p><em>Measure</em></p>
                            </td>
                            <td>
                                <p>Outlines how you and your supervisor will objectively evaluate if you have accomplished your goal and whether you have accomplished it well.</p>
                            </td>
                            <td>
                                Measurable
                                <br>
                                Time-bound
                            </td>
                            <td>
                                Difficult
                            </td>
                        </tr>
                        <!-- <tr>
                            <td>
                                <p><em>Time-limit</em></p>
                            </td>
                            <td>
                                <p>Gives a realistic but challenging time limit to accomplish the goal.</p>
                            </td>
                            <td>
                                <p>Clarity (A &amp; T in SMART) &amp; Challenge</p>
                            </td>
                        </tr> -->
                    </tbody>
                </table>
            </div>
                ',
                'answer_file' => '4',
            ],
            [
                'category' => 'goalsetting',
                'question' => 'Tips on how to get started',
                'answer' => '<ul>    
                <li>Review the Goal Setting Conversation Template for examples of questions to reflect on that may influence your goals.</li>
                <li>Set up a meeting with your supervisor to discuss what your goals should include.</li>
                <li>Review your goals in light of the resource materials on this page. What can you do to make your goals more effective?</li>
                <li>Review your goals with your supervisor.</li>
                <ul>
                    <li>Are you focusing on the right things?</li>
                    <li>Is anything missing?</li>
                    <li>Are there any subjective or vague words that you need to further define or clarify?</li>
                    <li>How will you define success? Share your perspective of what success looks like and make sure you have a clear understanding of what your supervisor&rsquo;s expectations are.</li>
                </ul>
            </ul>
                ',
                'answer_file' => '9',
            ],
            [
                'category' => 'goalsetting',
                'question' => 'Examples of Work Goals',
                'answer' => '<ul>
                <li>(<em>What</em>) My goal is to improve the accuracy and timeliness of my responses to AskMyHR inquiries <em>(Why)</em> to ensure the best possible outcomes for clients as they conduct their work in the BCPS. (<em>How</em>) I will do this by sorting the requests I receive each day into categories by content, priority, and complexity so that I gain efficiencies when responding. <em>(Measure)</em> I will know I have been successful if I reduce my average response time per request by 10% and increase the average number of client requests I respond to per month by 15%.</li>
                <br>
                Tags: clerical, client service
                </ul>
                <ul>
                <li><em>(What)</em> I will make my workplace more inclusive. <em>(Why)</em> When employees feel as though they belong, retention improves and work productivity increases. <em>(How)</em> I will host an Inclusion Discussion with my team, using the Meeting in a Box resources on the Equity, Diversity & Inclusion Resource Centre.</li>
                <br>
                Tag: diversity and inclusion
                </ul>
                <ul>
                <li><em>(What)</em> My goal is to shift to a coaching leadership style, <em>(Why)</em> so that I can bring out the best in my team. <em>(How)</em> I will do this by completing the Supervising in the BC Public Service (SBCPS) program, signing up for coaching, attending coaching courses through the Learning Centre, and by having ongoing conversations with my team members about their work. <em>(Measure)</em> Achievement will be measured through ongoing annual improvement in my 360-degree feedback scores and the extent to which my team members are able to achieve their goals.</li>
                <br>
                Tags: talent management
                </ul>
                <ul>
                <li><em>(What)</em> When setting up a meeting with someone for the first time, I will invite them to advise if I could remove any barriers <em>(Why)</em> to normalize conversations about accessibility and help grow a culture of disability inclusion. <em>(Measure)</em> I will know I succeeded when it becomes habit.</li>
                <br>
                Tags: accessibility, diversity and inclusion
                </ul>
                
                ',
                'answer_file' => '5',
            ],
            [
                'category' => 'goalsetting',
                'question' => 'Examples of Learning Goals',
                'answer' => '<ul>
                <li><em>(What)</em> I will develop my competencies related to inclusive hiring. <em>(Why)</em> To make the BC Public Service more effective by attracting candidates of all backgrounds and experiences. <em>(How)</em> I will take Hiring Certification Training Part I & Part II (when available).</li>
                <br>
                Tags: diversity and inclusion, human resources
                </ul>
                <ul>
                <li><em>(What)</em> My goal is to develop facilitation skills (<em>Why</em>) to support future stakeholder engagement. (<em>How</em>) To do this, I will complete the first two Facilitating Results Online courses (FRO 101 & 102) offered through the Learning Centre. (<em>Measure</em>) I will be successful if I complete both courses in the next four to eight months and practice my skills in a work-related environment before year end.</li>
                <br>
                Tags: communication
                </ul>
                <ul>
                <li><em>(What)</em> I will deepen my understanding of the shared history of Indigenous Peoples and British Columbians <em>(Why)</em> to meet government commitments to build a meaningful foundation for reconciliation <em>(How)</em> by completing Indigenous and Canadian Histories 101 through the House of Indigenous Learning on the PSA Learning System.</li>
                <br>
                Tags: reconciliation and decolonization
                </ul>
                <ul>
                <li><em>(What)</em> I will set up a monthly check-in with a colleague on the Labour Relations team <em>(Why)</em> so that I better understand how collective agreements impact my day-to-day work. <em>(How)</em> We will create an informal agenda that allows half the time for my colleague to answer my questions about labour relations and the other half for me to answer my colleague&rsquo;s questions about my area. <em>(Measure)</em> I will know I have been successful if I can better respond to simple client requests related to labour relations and better understand the supports available to me to answer more complicated questions.</li>
                <br>
                Tags: client service, human resources
                </ul>
                <ul>
                <li><em>(What)</em> I will develop my competencies related to accessibility. <em>(Why)</em> To identify and minimize the impact of barriers on the services I provide the public. <em>(How)</em> I will complete two NAAW learning engagements: “Digital Accessibility” and “Working with Co-Workers who are Deaf or Hard of Hearing.” </li>
                <br>
                Tags: accessibility
                </ul>
                
                ',
                'answer_file' => '6',
            ],
            [
                'category' => 'goalsetting',
                'question' => 'Examples of Career Goals',
                'answer' => '<ul>
                <li><em>(What)</em> I want to move into a role focused on facilitating engagement with remote communities throughout BC <em>(Why)</em> to ensure every citizen in BC has an opportunity to be heard. <em>(How)</em> I will do this by developing and practicing my facilitation skills with various groups, learning about the relevant interests and issues of remote communities in BC, and applying for related facilitation positions as they arise. <em>(Measure)</em> I will be successful if I attain a Community Facilitator role within four years and increase the degree of consultation with members in remote communities on future government-related projects.</li>
                </ul>
                <ul>
                <li><em>(What)</em> I want to move into a position more focused on policy creation <em>(Why)</em> so that I can take advantage of my formal education and interest in the area. <em>(How)</em> I will reach out to managers in policy shops across the BCPS to introduce myself, request meetings to discuss my interests, explore opportunities to join their team, and I will apply for policy-related temporary assignments that could help me increase my government-specific experience. <em>(Measure)</em> I will be successful if I have participated in a policy project by end of year and if I have moved into a permanent policy position within three years.</li>
                </ul>
                ',
                'answer_file' => '7',
            ],

            [
                'category' => 'conversations',
                'question' => 'What is a performance development conversation?',
                'answer' => 'Any conversation about an employee and their work can be considered a performance development conversation. They can be informal check-ins, regular 1-on-1\'s, recognition for a job well done, feedback, or more formal conversations when trying to modify behaviour.
                ',
                'answer_file' => '',
            ],
            [
                'category' => 'conversations',
                'question' => 'How to use the conversation templates',
                'answer' => '<!--How to use the conversation templates -->
                <p>All of us are already having different types of performance conversations. Some may be focused on recognition, others on goal setting or performance improvement, while most conversations touch on multiple aspects of our performance. So why do we need a conversation template?</p>
                <p>The conversation templates are provided to support you to have the conversation you need to have when you need to have it. They include suggestions for questions that can help guide discussions and a place for you to record observations and action items. See below for examples of when the different conversation templates might be used or review the specific conversation templates for more information.</p>
                <br>
                <table class="table table-bordered">
                    <tr>
                        <th>Conversation Template Options</th>
                        <th>When To Use This Template</th>
                    </tr>
                    <tr>
                        <td style="width:200px;">Performance Check-In</td>
                        <td>Performance expectations are generally being met or exceeded and you want to discuss a little bit of everything: progress, challenges, successes, how to improve, etc. This will likely be the template you use in most situations.
                            <br><br>
                            <b>Example scenarios:</b>
                            <ul>
                                <li>You regularly check in with your team member about expectations and performance and just need a place to quickly capture a status update.</li>
                                <li>You haven&rsquo;t had a performance discussion with your team member in a while but don&rsquo;t have any specific concerns. You plan to discuss elements of recognition, evaluation and/or coaching.</li>
                                <li>Your team member has been performing at a very high level or recently completed a significant project. You plan to use the “Appreciation” portion of the template to focus on celebrating this achievement and setting up for continued success.</li>
                                <li>You have some minor performance concerns with a team member that you need to address to ensure they don&rsquo;t worsen. You plan use the “Coaching” portion of the template to ensure that expectations and supports provided are being documented.</li>
                            </ul>
                            <b>Example Questions:</b>
                            <ul>
                                <li>Overall, how would you describe your work since our last check-in?</li>
                                <li>What progress have you made against your goals?</li>
                                <li>Have your goals shifted? Tell me about that.</li>
                                <li>What accomplishments are you most proud of?</li>
                                <li>What challenges have you faced? What did you learn?</li>
                                <li>What support do you need from me as your supervisor to perform at your best?</li>
                                <li>In what areas do you need or want to improve? What would help you improve?</li>
                                <li>What motivates you to get your job done?</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>Goal Setting</td>
                        <td>You want to focus your conversation on establishing goals or revising existing goals due to changes in circumstances.
                            <br><br>
                            <b>Example scenarios:</b>
                            <ul>
                                <li>You have a new team member or a team member whose responsibilities have shifted.</li>
                                <li>You have a team member whom you are trying to motivate. You plan on discussing ties between their work and how they contribute to the larger purpose, and how they can use more of their strengths.</li>
                                <li>A new priority has come up and projects need to shift. You plan to sit down with your team member and figure out how this will impact their work.</li>
                            </ul>
                            <b>Example Questions:</b>
                            <ul>
                                <li>What goals can you add to your plan to help achieve team priorities?</li>
                                <li>What competencies and values will you focus on to achieve your goals?</li>
                                <li>What do you need to learn to achieve your goals? What gaps do you want to address?</li>
                                <li>What are the barriers to your success? How will you overcome them?</li>
                                <li>Imagine what success will look like: this can inform your personal performance measures (remember to pick results that are within your control and/or influence)</li>
                                <li>What personal goals would you like to include in your profile?</li>
                                <li>What support do you need to meet your goals?</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>Career Development</td>
                        <td>You want to focus on a team member&rsquo;s career development. This could include long-term goals that involve developing key skills or learnings. Career development conversations should attempt to embrace the public service “one employer” experience and include an understanding that achieving career goals may eventually involve a team member moving on to another team or organization.
                            <br><br>
                            <b>Example scenarios:</b>
                            <ul>
                                <li>One of your team members has approached you wanting to know more about career opportunities that are open to them based on their current role.</li>
                                <li>You like to take the time to sit down with each of your team members to learn more about their interests and long-term goals are. What can you do to support them? Are there any key experiences you can provide to help them achieve their career goals?</li>
                                <li>Your team member has been excelling in their current role and you&rsquo;d like to help prepare them for the next step in their public service journey.</li>
                            </ul>
                            <b>Example Questions:</b>
                            <ul>
                                <li>What are your career goals? What do you think you need to get there?
                                <li>What specific goals would you like to achieve in the next year, two years, longer?
                                <li>What positions or opportunities are you interested in exploring within the BCPS?
                                <li>What do you want your next position to be?
                                <li>How would you define “success” for your career?
                                <li>What positive impact do you want your career to have on the Public Service (both short term and long term)?
                                <li>What is one thing I can do to support your career development?
                                <li>What is one thing you can do to support your own career development?
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>Onboarding</td>
                        <td>You want to welcome a new employee to the public service or to their new position on your team. You plan to clarify expectations for the role, provide organizational context, define short-term goals, and set the team member up for success.
                            <br><br>
                            <b>Example scenarios:</b>
                            <ul>
                                <li>You have a team member that is new to the public service and you want to be sure they have been provided with the supports needed to transition into their new career. You also want to understand more about their strengths, interests, and any potential areas that you could provide additional support.</li>
                                <li>A long-time public servant has recently transferred to your team. You want to discuss specific expectations of the new role and find out more about communication and work styles that have worked well for the team member in the past.</li>
                            </ul>
                            <b>Example Questions:</b>
                            <ul>
                                <li>Do you have a clear understanding of the expectations for this role?
                                <li>Have you received access to all of the information, tools, and resources you need to complete your responsibilities?
                                <li>What support do you need from me as your supervisor?
                                <li>Are there any specific tools or training sessions that would help you be more successful?
                                <li>Which aspects of the job are you excited / worried about?
                                <li>How do you prefer to receive feedback and/or recognition for your work?
                                <li>What would help you feel connected to the rest of the team?
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>Performance Improvement</td>
                        <td>Performance expectations are <b>not</b> being met for a given role and you need to identify specific performance improvements, supports and deadlines. You have likely already used the Performance Check-In template to document earlier performance concerns and coaching provided. 
                            <br><br>
                            If supervisors use or are considering using this template, they are encouraged to reach out to an HR Specialist through AskMyHR.
                            <br><br>
                            <b>Example scenarios:</b>
                            <ul>
                                <li>You&rsquo;ve provided feedback and coaching to a team member but are concerned that their performance is still falling short compared to acceptable standards.</li>
                                <li>You have a team member that is meeting minimum performance standards based on metrics, but they aren&rsquo;t behaving consistently with some of the corporate competencies or values.</li>
                            </ul>
                            <b>Example Questions:</b>
                            <ul>
                                <li>Tell me about how things have been going for you in your role. What is going well? Where do you see opportunities for improvement?</li>
                                <li>To meet expectations, what support do you need? This could be coaching, tools, resources, additional training, etc.</li>
                                <li>Between now and our next conversation, I would like you to work on 2-3 areas we&rsquo;ve discussed as requiring development. What specific steps will you take to meet these expectations?</li>
                                <li>We will follow up on these areas and discuss your progress during our next meeting. If needed, we can discuss an action plan for improvement in each of those areas when we meet again.</li>
                                <li>What else would you like to share with me?</li>
                                <li>What support would you like from me?</li>
                            </ul>
                        </td>
                    </tr>
                </table>
                ',
                'answer_file' => '0',
            ],
            [
                'category' => 'conversations',
                'question' => 'Why are performance conversations important?',
                'answer' => '<p>Meaningful conversations about performance and development help:</p>
                <br>
                <ul>
                    <li>Build the relationship between supervisor and employee, creating trust.</li>
                    <li>Provide clarity to the employee - What&rsquo;s my job? How am I doing so far? What do I need to improve?</li>
                    <li>Provide insight to the supervisor about what&rsquo;s important to each employee and what motivates them.</li>
                    <li>Make the process meaningful to employees. A supervisor can offer feedback and suggestions based on where the employee wants to go in their career.</li>
                    <li>Create an opportunity to discuss working styles.</li>
                    <li>Build engagement and improve results.</li>
                </ul>
                
                ',
                'answer_file' => '2',
            ],
            [
                'category' => 'conversations',
                'question' => 'What makes a conversation effective?',
                'answer' => '<p>Conversations are effective when:</p>
                <ul>
                <li>They are authentic and genuine.</li>
                <li>They come from a place of wanting to improve and develop.</li>
                <li>The feedback is trusted and delivered in a safe environment.</li>
                <li>They include elements of evaluation, recognition and coaching as required.</li>
                <li>Participants come open to learn and listen.</li>
                <li>Participants are prepared in advance.</li>
                </ul>
                
                ',
                'answer_file' => '3',
            ],
            [
                'category' => 'conversations',
                'question' => 'Elements of a meaningful conversation',
                'answer' => '<ul>
                <li>Prepare in advance. Be clear on your intent, purpose and what you hope to achieve. Participants should know ahead of time when the conversation will occur and what will be discussed.</li>
                <li>Take some time to reflect prior to the conversation. Review the conversation template ahead of time to help you prepare as it includes sample questions you might discuss.</li>
                <li>Remember that feedback is more likely to be appreciated and integrated when it is designed to help improve and develop. Check in with yourself and make sure your feedback will achieve this.</li>
                <li>Ask open-ended questions that encourage reflection and learning. Plan your questions ahead of time. Each conversation template includes sample questions and other supports.</li>
                <li>Check to ensure all participants have a common understanding of key messages and future actions. Document significant commitments in the conversation template.</li>
                <li>Recognize and acknowledge the efforts, perspective, and circumstances of the person you are speaking with. We all want to feel seen, heard and understood.</li>
                <li>Ask for feedback to learn more about what you can do to improve the quality and content of future conversations. Then apply that feedback in your future conversations.</li>
                </ul>
                
                ',
                'answer_file' => '4',
            ],
            [
                'category' => 'conversations',
                'question' => 'Elements of effective feedback',
                'answer' => '<p>Effective feedback is:</p>
                <ul>
                <li>Timely, regardless of whether it is complimentary or corrective.</li>
                <li>Specific and supported by examples to ensure the feedback is well understood.</li>
                <li>Focused on observable behaviours that have been seen or heard rather than broad comments about a person. For example: “I saw that you were 15 minutes late to work on Monday and Thursday of this week” rather than “you are not a punctual person” or “you are always late for things”.</li>
                <li>Supported by an impact statement. Who and what was impacted, for better or worse, by the behaviour?</li>
                <li>Focused on future improvements, not past mistakes. What can and will we do next time?</li>
                </ul>
               
                ',
                'answer_file' => '5',
            ],
            [
                'category' => 'conversations',
                'question' => 'Asking for feedback or inquiring into someone else\'s perspective',
                'answer' => 'For conversations to be meaningful and to build a trusting relationship, you want to understand what is happening from the other’s perspective. By inquiring into the other person’s views you gain an opportunity to really listen for the benefit of your own learning. Supervisors may find it helpful to ask questions to encourage employees to self-evaluate:
                    <ul>
                        <li>What&rsquo;s your perspective on this? Or, What are your thoughts?</li>
                        <li>Overall, how do you feel your performance has been?</li>
                        <li>Tell me about some of your successes this year.</li>
                        <li>What areas do you feel you could most improve in?</li> 
                        <li>What would help you improve in those areas?</li> 
                        <li>What&rsquo;s getting in the way of your success?</li> 
                        <li>What do you need from me?</li> 
                    </ul>
                    <br>
                    Your goal is to encourage the other person to share as much as possible from their point of view. Stay open and curious, listen and let them talk until they are finished. Resist the temptation to interject your opinion about their statements. Listen and learn all you can about the situation. You may be surprised, at times, to learn that how the other person views the subject is different than what you expected. By allowing them to share their thoughts, you will gain a keener insight as to how to approach your points.
               
                ',
                'answer_file' => '6',
            ],
            [
                'category' => 'conversations',
                'question' => 'Addressing a performance issue',
                'answer' => 'Addressing performance issues are some of the more awkward conversations for many of us. One way supervisors can err is by being so vague that an employee doesn&rsquo;t understand what changes are expected. Conversations can also be abrupt or come from a place of frustration/anger. First, ask yourself if this is a good time to give feedback. Generally, it&rsquo;s best to have discussions about an issue closely following the behaviour. However, you could notice that either you or the other individual isn&rsquo;t in the right frame of mind for a productive conversation. If that&rsquo;s the case, find another moment, but don&rsquo;t postpone the conversation for long. Also consider your responsibilities as a supervisor and whether you may have contributed to the situation (for example, perhaps by giving unclear instructions or not providing necessary training). Be prepared to provide what&rsquo;s necessary if anything has been missed. 
                <br><br>
                Addressing issues with an employee in the early stages includes the following: 
                <ul>
                    <li>Being able to clearly state the issue.</li>
                    <li>Basing comments on specific situations and being prepared to share an example or two (what was the actual behaviour noticed?). </li>
                    <li>Explaining the impact of the behaviour (why this is important?).</li> 
                    <li>Asking the individual for their perspective (and following that by really listening). </li>
                    <li>Involving the individual in determining next steps. </li>
                </ul>
                <p>If you are working on building your skill in having these kinds of conversations, consider accessing the coaching services through MyHR. Repetitive or complex performance issues may require additional steps. Please reach out to HR Specialist Services through MyHR.</p>
                <p>To see how the PDP can further support you further in these conversations, check out the FAQ section on what to do if an employe is not meeting expectations.</p>
                ',
                'answer_file' => '7',
            ],

            [
                'category' => 'contact',
                'question' => 'Agriculture and Food',
                'answer' => '<p>
                People and Workplace Strategies: <a href="mailto:PAWS.NRM@gov.bc.ca" target="_blank">PAWS.NRM@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '0',
            ],
            [
                'category' => 'contact',
                'question' => 'Attorney General',
                'answer' => '<p>
                People and Workplace Strategies: <a href="mailto:PAWS.NRM@gov.bc.ca" target="_blank">PAWS.NRM@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '1',
            ],
            [
                'category' => 'contact',
                'question' => 'BC Public Service Agency',
                'answer' => '<p>
                Emma Gillespie: <a href="mailto:emma.gillespie@gov.bc.ca" target="_blank">emma.gillespie@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '2',
            ],
            [
                'category' => 'contact',
                'question' => 'Children and Family Development',
                'answer' => '<p>
                Dale Fudge: <a href="mailto:dale.fudge@gov.bc.ca" target="_blank">dale.fudge@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '3',
            ],
            [
                'category' => 'contact',
                'question' => 'Citizens’ Services',
                'answer' => '<p>
                Ministry Strategic Human Resources: <a href="mailto:CITZ.Strategichumanresources@gov.bc.ca" target="_blank">CITZ.Strategichumanresources@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '4',
            ],
            [
                'category' => 'contact',
                'question' => 'Education and Child Care',
                'answer' => '<p>
                Ministry Strategic Human Resources: <a href="mailto:educshr@gov.bc.ca" target="_blank">educshr@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '5',
            ],
            [
                'category' => 'contact',
                'question' => 'Emergency Management and Climate Readiness',
                'answer' => '<p>
                Ministry Strategic Human Resources: <a href="mailto:EMBC.HR@gov.bc.ca" target="_blank">EMBC.HR@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '6',
            ],
            [
                'category' => 'contact',
                'question' => 'Energy, Mines and Low Carbon Innovation',
                'answer' => '<p>
                People and Workplace Strategies: <a href="mailto:PAWS.NRM@gov.bc.ca" target="_blank">PAWS.NRM@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '7',
            ],
            [
                'category' => 'contact',
                'question' => 'Environment and Climate Change Strategy',
                'answer' => '<p>
                People and Workplace Strategies: <a href="mailto:PAWS.NRM@gov.bc.ca" target="_blank">PAWS.NRM@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '8',
            ],
            [
                'category' => 'contact',
                'question' => 'Finance',
                'answer' => '<p>
                Ministry Strategic Human Resources: <a href="mailto:shr.programs@gov.bc.ca" target="_blank">shr.programs@gov.bc.ca</a>
                </p>
                 
                ',
                'answer_file' => '9',
            ],
            [
                'category' => 'contact',
                'question' => 'Forests',
                'answer' => '<p>
                People and Workplace Strategies: <a href="mailto:PeopleandWorkplaceStrategies@gov.bc.ca" target="_blank">PeopleandWorkplaceStrategies@gov.bc.ca</a>
                </p>
                
                ',
                'answer_file' => '10',
            ],
            [
                'category' => 'contact',
                'question' => 'Government Communications & Public Engagement',
                'answer' => '<p>
                Ministry Human Resources: <a href="mailto:gcpehr@gov.bc.ca" target="_blank">gcpehr@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '11',
            ],
            [
                'category' => 'contact',
                'question' => 'Health',
                'answer' => '<p>
                Sara Weeks: <a href="mailto:sara.weeks@gov.bc.ca" target="_blank">sara.weeks@gov.bc.ca</a>
                </p>                
                ',
                'answer_file' => '12',
            ],
            [
                'category' => 'contact',
                'question' => 'Housing',
                'answer' => '<p>
                AG PSSG Corporate Initiatives: <a href="mailto:agpssg.corporateinitiatives@gov.bc.ca" target="_blank">agpssg.corporateinitiatives@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '13',
            ],
            [
                'category' => 'contact',
                'question' => 'Indigenous Relations and Reconciliation',
                'answer' => '<p>
                People and Workplace Strategies: <a href="mailto:PAWS.NRM@gov.bc.ca" target="_blank">PAWS.NRM@gov.bc.ca</a>
                </p>                
                ',
                'answer_file' => '14',
            ],
            [
                'category' => 'contact',
                'question' => 'Jobs, Economic Development and Innovation',
                'answer' => '<p>
                Economy Sector Strategic Human Resources: <a href="mailto:SHRinformation@gov.bc.ca" target="_blank">SHRinformation@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '15',
            ],
            [
                'category' => 'contact',
                'question' => 'Labour',
                'answer' => '<p>
                Economy Sector Strategic Human Resources: <a href="mailto:SHRinformation@gov.bc.ca" target="_blank">SHRinformation@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '16',
            ],
            [
                'category' => 'contact',
                'question' => 'Mental Health and Addictions',
                'answer' => '<p>
                Ministry Strategic Human Resources: <a href="mailto:mmha.strategichumanresource@gov.bc.ca" target="_blank">mmha.strategichumanresource@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '17',
            ],
            [
                'category' => 'contact',
                'question' => 'Municipal Affairs',
                'answer' => '<p>
                Economy Sector Strategic Human Resources: <a href="mailto:SHRinformation@gov.bc.ca" target="_blank">SHRinformation@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '18',
            ],
            [
                'category' => 'contact',
                'question' => 'Post-Secondary Education and Future Skills',
                'answer' => '<p>
                Ministry Strategic Human Resources: <a href="mailto:PSFS.PeopleandWorkplaceStrategies@gov.bc.ca" target="_blank">PSFS.PeopleandWorkplaceStrategies@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '19',
            ],
            [
                'category' => 'contact',
                'question' => 'Premier’s Office',
                'answer' => '<p>
                HR Support Services: <a href="mailto:premhrsupport@gov.bc.ca" target="_blank">premhrsupport@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '20',
            ],
            [
                'category' => 'contact',
                'question' => 'Public Safety and Solicitor General',
                'answer' => '<p>
                AG PSSG Corporate Initiatives: <a href="mailto:agpssg.corporateinitiatives@gov.bc.ca" target="_blank">agpssg.corporateinitiatives@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '21',
            ],
            [
                'category' => 'contact',
                'question' => 'Royal BC Museum',
                'answer' => '<p>
                RBCM People and Development: <a href="mailto:peopleanddevelopment@royalbcmuseum.bc.ca" target="_blank">peopleanddevelopment@royalbcmuseum.bc.ca</a>
                </p>
                ',
                'answer_file' => '22',
            ],
            [
                'category' => 'contact',
                'question' => 'Social Development and Poverty Reduction',
                'answer' => '<p>
                Ministry Strategic Human Resources: <a href="mailto:SDPR.StrategicHumanResources@gov.bc.ca" target="_blank">SDPR.StrategicHumanResources@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '23',
            ],
            [
                'category' => 'contact',
                'question' => 'Tourism, Arts, Culture and Sport',
                'answer' => '<p>
                Economy Sector Strategic Human Resources: <a href="mailto:SHRinformation@gov.bc.ca" target="_blank">SHRinformation@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '24',
            ],
            [
                'category' => 'contact',
                'question' => 'Transportation and Infrastructure',
                'answer' => '<p>
                People and Workplace Initiatives: <a href="mailto:MOTI.PWI@gov.bc.ca" target="_blank">MOTI.PWI@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '25',
            ],
            [
                'category' => 'contact',
                'question' => 'Water, Land and Resource Stewardship',
                'answer' => '<p>
                People and Workplace Strategies: <a href="mailto:PeopleandWorkplaceStrategies@gov.bc.ca" target="_blank">PeopleandWorkplaceStrategies@gov.bc.ca</a>
                </p>
                ',
                'answer_file' => '26',
            ],

            [
                'category' => 'faq',
                'question' => 'Why is performance development important?',
                'answer' => '<p>
                Performance development serves a range of functions that promote employee performance and career growth and organizational competitiveness, including:
                </p>
                
                <ul>
                    <li>Supporting skill development and career planning</li>
                    <li>Aligning individual goals with organizational goals</li>
                    <li>Increasing individual and organizational accountability</li>
                    <li>Recognizing and rewarding good performance and managing underperformance</li>
                    <li>Identifying and developing the necessary capabilities for an effective workforce</li>
                    <li>Increasing productivity</li>
                    <li>Building employee engagement.</li>
                </ul>',
                'answer_file' => '0',
            ],
            [
                'category' => 'faq',
                'question' => 'How often do I need to have performance conversations?',
                'answer' => '<p>
                The Performance Development process requires employees to have one conversation with their supervisor every four months (three per year). 
                The old MyPerformance system also required three conversations per year (Planning, Focusing and Sign-Off). There has been no change to this requirement. 
                However, now the nature, topic and timing of the performance conversation is more flexible and can reflect the current needs of employee and supervisor.
                </p>
                ',
                'answer_file' => '1',
            ],
            [
                'category' => 'faq',
                'question' => 'How do I add a goal to my PDP profile?',
                'answer' => '<p>
                Check out the this <a href="https://youtu.be/s-3mR1Oni84" target="_blank">video tutorial</a> for an overview of this topic or continue reading below.
                </p>
                <p>
                There are two ways to include a goal in your PDP profile. You can:
                </p>
                <ul>
                    <li>Create a new goal using the “Create New Goal” function; or</li>
                    <li>Import a goal that has been assigned to you by your supervisor or organization using the “Add Goal from Goal Bank” function.</li>
                </ul>
                <p>
                When you launch the “Create New Goal” screen, you will be asked to select a goal type, goal title, and goal tags to help you identify and sort your goals. 
                All three sections are mandatory. You can access more detailed information on these sections by clicking on the information “i” icon next to each heading in the app. 
                There are best practice tips to set effective goals available in the <a href="/resources/goal-setting" target="_blank" >goal setting section</a> of the app.
                </p>
                <p>
                The second way to include a goal in your PDP profile is to import a goal from your Goal Bank. 
                This is a list of goals created for you by your supervisor or organization. Some goals will be marked as mandatory, 
                but most are suggested starting points for you to consider. You can click on a goal to view the details and add it to your own profile. 
                If needed, you can edit the goal to personalize it once it is in your profile.
                </p>',
                'answer_file' => '2',
            ],
            [
                'category' => 'faq',
                'question' => 'How do I share a goal with a colleague?',
                'answer' => '<p>
                Check out the this <a href="https://youtu.be/h80op_O03AY" target="_blank" >video tutorial</a> for an overview of this topic or continue reading below.
                </p>
                <p>
                Sharing a goal with a colleague is easy to do. Navigate to your My Current Goals page and locate the goal that you want to share. 
                Click on the drop-down list under the “Shared With” heading, type in the name of your colleague, and save your selection. 
                You can repeat this process to add additional colleagues to the goal.
                </p>
                <p>
                The colleagues you selected will now see your goal on the tab “Goals Shared with Me”. They will be able to access the goal and add comments.
                </p>
                <p>
                If needed, you can remove a colleague from your goal by going back to the “Shared With” drop-down and clicking “x” next to their name.
                </p>
                <p>
                The person that originally created the goal will be able to change the goal status and mark it as achieved or archived on behalf of the group. 
                This will move the goal to the Past Goals tab for all users with access to the goal.
                </p>',
                'answer_file' => '3',
            ],
            [
                'category' => 'faq',
                'question' => 'How do I set up a performance conversation with my employee / supervisor?',
                'answer' => '<p>
                Check out the this <a href="https://www.youtube.com/watch?v=TuaLsDRZJ1E&feature=youtu.be" target="_blank" >video tutorial</a> for an overview of this topic or continue reading below.
                </p>
                <p>
                The My Conversations section is designed to support employees and supervisors to have the right conversation at the right time. 
                The topic of each performance conversation does not have to be the same. The PDP has templates to support conversations focused on onboarding a new employee, 
                goal setting, performance check-ins, career development, and performance improvement. Each of these count as a performance conversation.
                </p>
                <p>
                To request a conversation with your employee / supervisor, navigate to My Conversations > Conversation Templates. 
                Select the template that best matches the conversation you’d like to have, select the appropriate person from the participants drop-down menu, 
                and click “Start Conversation”. This will send a notification to the other person that you would like to meet and will create a conversation file 
                on your “Open Conversations” tab under either the “Open Conversations with my Supervisor” or “Open Conversations with my Team” heading.
                </p>
                <p>
                Note that the conversation will still need to be scheduled independently in your Outlook calendar at a time that works for both you and your supervisor. 
                There is no direct connection between PDP and Outlook calendar.
                </p>',
                'answer_file' => '4',
            ],
            [
                'category' => 'faq',
                'question' => 'I am a supervisor. How do I create a goal in my team’s Goal Bank?',
                'answer' => '<p>
                Check out the this <a href="https://www.youtube.com/watch?v=rythd_z-9so" target="_blank" >video tutorial</a> for an overview of this topic or continue reading below.
                </p>
                <p>
                Navigate to My Goals > Goal Bank > Team Goal Bank and click the button to “Add Goal to Bank”. You create a goal here just as you would for yourself 
                and then select team members to push the goal out to in the “Audience” drop-down. You can include yourself in the audience if you’d also like to see 
                the goal appear in your own goal bank.
                </p>
                <p>
                This option creates a separate copy of the goal for each user that copies it from the goal bank. This means that you can add different comments 
                to each team member’s version of the goal, and the goal status can be changed to achieved or archived for each team member individually without 
                impacting the other users.
                </p>
                <p>
                You can remove a goal from your team’s goal bank by navigating to My Goals > Goal Bank > Team Goal Bank and deleting the entry. 
                This will remove it from all team members Goal Banks as well. Note that, if someone has already copied the goal into their own profile, 
                they will keep their version of the goal even if you delete the original from the Goal Bank.
                </p>',
                'answer_file' => '5',
            ],
            [
                'category' => 'faq',
                'question' => 'I’m a supervisor. I want to view my employee’s goals and upcoming conversation deadlines. How do I do this?',
                'answer' => '<p>
                Check out the this <a href="https://youtu.be/h80op_O03AY" target="_blank" >video tutorial</a> for an overview of this topic or continue reading below.
                </p>
                <p>
                As a supervisor, you have access to a My Team page that has info about your direct and shared reports. 
                Here you can find a summary view of how many active goals each employee has, when their next performance conversation is due, 
                and whether or not they have been shared or excused in the PDP.
                </p>
                <p>
                Please note that the next conversation due date is calculated by the PDP as part of an overnight background process. 
                That means that an employee’s due date will not be updated to reflect a signed-off conversation until the day after sign-off is complete.
                </p>
                <p>
                Clicking on an employee’s name will take you to their profile where you can view the details of their goals and conversations. 
                You can add comments to their goals or any open conversations right in their profile.
                </p>
                <p>
                You will see a banner across the top of the page letting you know when you are viewing an employee’s profile. 
                To return to your own profile at any time, click the “Return to my profile” button in the top right corner. 
                Clicking “back” in your web browser could cause log-in issues, so please use the “Return to my profile” option instead.
                </p>',
                'answer_file' => '6',
            ],
            [
                'category' => 'faq',
                'question' => 'I do not see the correct supervisor in the PDP / I do not see the correct direct reports in the PDP. What do I do?',
                'answer' => '<p>
                Reporting relationship data is pulled from PeopleSoft (our HR system of record) once every 24 hours. This data cannot be modified in the PDP.
                </p>
                <p>
                If you want to add a shared supervisor for an employee in the PDP, you can have the current supervisor share the employee by clicking on the 
                Yes / No under the “Shared” column on their My Team page.
                </p>
                <p>
                If the data in PeopleSoft is incorrect, you will need to submit a service request through AskMyHR using the category 
                HR Software Systems Support > Position / Reporting Updates.
                </p>
                <p>
                <b>Note:</b> Employees that report to a position number in PeopleSoft that is vacant are automatically assigned one level higher in the organization hierarchy. 
                If that next level is also vacant, the employee will see on their home page that their current supervisor is “No supervisor”.
                </p>
                <p>
                <b>Note:</b> Employees that report to a position number in PeopleSoft that has more than one active employee associated with it 
                (“double-bunked” positions) will see a dropdown menu on their homepage that allows them to select which of the supervisors associated with the position number 
                is correct for them.
                </p>
                <p>
                If a supervisor shares a position number with one or more additional employees in PeopleSoft (i.e. the supervisor is “double-bunked” in their position) 
                they may not see the correct employees by default. In this case, their employees will be able to select the correct supervisor from a dropdown menu 
                on the homepage under the “Current supervisor” heading. Once the employee has made the selection, the supervisor will gain access to that employee in the PDP.
                </p>',
                'answer_file' => '7',
            ],
            [
                'category' => 'faq',
                'question' => 'Is there an autosave function on the application?',
                'answer' => '<p>
                Yes, currently 20 minutes. After 20 minutes of not saving your work, the PDP will autosave any open windows and launch a pop-up window to 
                let you know what happened and why.
                </p>',
                'answer_file' => '8',
            ],
            [
                'category' => 'faq',
                'question' => 'Is there a timeout on the application?',
                'answer' => '<p>
                Yes, currently 120 minutes. This timeout supports the corporate IM/IT Security Policy. To get back into PDP, simply click on the link to return to the application.
                </p>',
                'answer_file' => '9',
            ],
            [
                'category' => 'faq',
                'question' => 'Can I access the PDP from home?',
                'answer' => '<p>
                Yes, you just need to have the tool’s website URL. You can type that into the browser and you will be prompted for your IDIR login information.
                </p>',
                'answer_file' => '10',
            ],
            [
                'category' => 'faq',
                'question' => 'What notifications and reminders will the PDP send me? Can I choose how often to receive an email from the PDP?',
                'answer' => '<p>
                By default the PDP will generate in-app notifications on your homepage and send an email whenever someone:
                </p>
                
                <ul>
                <li>Makes a comment on one of your goals</li>
                <li>Adds a new goal to your goal bank</li>
                <li>Shares your profile with another supervisor</li>
                <li>Wants to set up a performance conversation with you</li>
                <li>Signs-off on a performance conversation with you</li>
                <li>Disagrees with the content of a performance conversation with you</li>
                </ul>
                
                <p>
                And also:
                </p>
                
                <ul>
                <li>One month before your next conversation due date</li>
                <li>One week before your next conversation due date</li>
                <li>When your conversation is past due</li>
                <li>One month before your team member\'s next conversation due date (supervisors only)</li>
                <li>One week before your team member\'s next conversation due date (supervisors only)</li>
                <li>When your team member\'s conversation is past due (supervisors only)</li>
                </ul>
                
                <p>
                Users have the option of opting out of receiving some of these emails. Notification options can be set on the <a href="/user-preference" target="_blank">Account Preferences page</a>. 
                You can access this page by clicking on your username in the top right corner of the app.
                </p>
                
                ',
                'answer_file' => '11',
            ],
            [
                'category' => 'faq',
                'question' => 'Who can assist me if I need help with the PDP?',
                'answer' => '<p>
                Supervisors are supposed to help employees with their PDP profiles whenever possible. All supervisors and employees are encouraged to 
                review the Resources Section of the PDP for a helpful user guide, tutorial videos, 
                and best practice tips for setting effective goals and having effective conversations.
                </p>
                
                <p>
                If you can’t find the answer after reviewing this material, you can escalate your request to your ministry contact listed in the Contacts section of the PDP.
                </p>
                
                <p>
                If you are experiencing a technical issue, first check to see if the answer is in the PDP User Guide. If you cannot find the answer, 
                please submit a service request through AskMyHR to My Team or Organization > HR Software Systems Support > Performance Development Platform.
                </p>',
                'answer_file' => '12',
            ],
            [
                'category' => 'faq',
                'question' => 'How do I access past MyPerformance files?',
                'answer' => '<p>
                You will not lose access to your past MyPerformance files. The MyPerformance system will remain online during the transition to 
                performance development and the PDP. Once that transition is complete, the MyPerformance files will be made available to you in a secure archive. 
                You cannot copy MyPerformance profiles directly into the PDP, though you can copy and paste any relevant text into the new format.
                </p>
                
                <p>
                Sign-off by both parties in MyPerformance is required for the profile to be archived and made available.
                </p>',
                'answer_file' => '13',
            ],
            [
                'category' => 'faq',
                'question' => 'Why are there no ratings in the new approach to performance development?',
                'answer' => '<ul>
                <li>
                    During consultations, we heard from employees and supervisors that ratings were a major source of stress and not helpful in 
                    improving performance or guiding career development.
                </li>
                <li>
                    Performance development is focused on looking forward to improve future outcomes rather than looking backward to grade past accomplishments.
                </li>
                <li>
                    Performance development is designed to encourage regular conversations between an employee and supervisor rather than focus 
                    on an end-of-year rating conversation.
                </li>
                <li>
                    The use of templates to support conversations focused on appreciation, coaching and evaluation allow us to recognize strong 
                    performance and support performance improvement without the use of ratings.
                </li>
                <li>
                    The BC Public Service did not regularly use information MyPerformance ratings to guide talent and compensation decisions.
                    <ul>
                        <li>
                            Ratings were not a valid source of evidence to inform these decisions as they were distributed unequally and inequitably 
                            across organizations, job types, and employee groups.
                        </li>
                        <li>
                            There was always an additional step outside of the MyPerformance rating in the talent or compensation process used to evaluate candidates.
                        </li>
                    </ul>
                </li>
                
                </ul>',
                'answer_file' => '14',
            ],
            [
                'category' => 'faq',
                'question' => 'How can I qualify for a Pacific Leaders Scholarship if there are no ratings as part of performance development?',
                'answer' => '<p>
                Requirements for the Pacific Leaders Scholarship will not change. To be eligible, your supervisor must agree that you are, at minimum, 
                achieving expectations in your current role and that your career goals are consistent with the current or future needs of government. 
                This information is collected in the Pacific Leaders form that your supervisor must submit as part of the application process. 
                Your study plans must be described in your in the PDP.
                
                </p>',
                'answer_file' => '15',
            ],
            [
                'category' => 'faq',
                'question' => 'How will I qualify for MCCF in-range compensation movement if there are no ratings as part of performance development?',
                'answer' => '<p>
                MyPerformance ratings were not the deciding factor in determining in-range movement. A separate process was conducted each year to identify 
                successful candidates for in-range movement depending on the criteria and quotas identified. This separate process will proceed as usual.
                
                </p>',
                'answer_file' => '16',
            ],
            [
                'category' => 'faq',
                'question' => 'What do I do if one of my employees is not performing up to expectations?',
                'answer' => '<p>
                There are supports offered in the PDP to help address different stages of performance concerns through targeted conversations and goal setting. 
                At any stage, you can reach out to an HR Specialist through AskMyHR for additional support in having these conversations. 
                You may also consider requesting short term coaching to review how you want to show up in the conversation.
                </p>
                
                <p>
                If performance concerns are just starting to emerge or are not deemed significant, you will want to use the “Coaching” and “Action Items” 
                areas of the Performance Check-In template. This documents that you began the conversation with the employee, offered support and coaching, 
                and agreed on relevant follow-up actions. You are still free to use other sections of the template to celebrate successes or 
                document additional aspects of performance discussions.
                </p>
                
                <p>
                If performance concerns are significant or persist over time, you will want to progress to the Performance Improvement template. 
                This template is more explicit in identifying performance issues, supports to be provided, and timelines for meeting agreed improvement measures.
                </p>
                
                <p>
                If performance improvements are not made within agreed upon timelines, you should reach out to an HR Specialist through AskMyHR. The information that you 
                have recorded in the PDP will provide important context and evidence to support the process as it is escalated.
                </p>
                
                <p>
                You can also check out the section on “Addressing a Performance Issue” in the <a href="/resources/conversations" target="_blank">Conversations Resource section</a> of the PDP for additional assistance.
                </p>',
                'answer_file' => '17',
            ],
            [
                'category' => 'faq',
                'question' => 'What happens when an employee clicks “I disagree with the information contained in this performance review”?',
                'answer' => '<p>
                The supervisor will be notified by email and a note will be added to their PDP homepage. The disagreement is registered in the system and 
                will be visible when a report is run by the employee’s ministry or PSA. In addition to clicking the “disagree” option, the employee must still 
                sign-off the conversation using their employee ID to complete the process.
                </p>',
                'answer_file' => '18',
            ],
            [
                'category' => 'faq',
                'question' => 'I am a supervisor and my employee clicked “I disagree with the information contained in this performance review”. What do I do?',
                'answer' => '<p>
                When an employee disagrees with the supervisor’s comments and/or assessment, the employee has the option to check off this box when 
                signing off on the conversation.
                </p>
                <p>
                This is another opportunity for the supervisor and employee to discuss the employee’s perspective so that the supervisor can seek to understand and 
                consider the employee’s concerns and reasons for disagreeing with the conversation.
                </p>
                <p>
                The <a href="/resources/conversations" target="_blank">Conversations Resources</a> area of the PDP can support you in this discussion. You may find the sections on “Asking for Feedback/Inquiring Into the 
                Other’s Perspective” and “Addressing a Performance Issue” particularly helpful.
                </p>
                <p>
                If you would like to speak with someone for guidance and advice, we could forward your Service Request to an HR Specialist. You may also want to partner with a Coach by signing up for individual Coaching Services. 
                Please visit MyHR for more information on Coaching and requesting Coaching.
                </p>',
                'answer_file' => '19',
            ],
            [
                'category' => 'faq',
                'question' => 'I am a supervisor and I don’t have enough time in my schedule to go through this process with my employees. Why do we need to do this?',
                'answer' => '<p>
                The <a href="https://www2.gov.bc.ca/gov/content/careers-myhr/managers-supervisors/employee-labour-relations/conditions-agreements/accountability-framework" target="_blank">Accountability Framework for Human Resource Management</a> requires that all supervisors in the BC Public Service:
                </p>
                
                <ul>
                <li>Organize, direct and manage the performance of staff to meet operational requirements; and</li>
                <li>Provide regular on-going feedback to their employees on their performance and support employees’ career paths.</li>
                </ul>
                
                <p>
                The performance development process requires employees to have one conversation with their supervisor every four 
                months (three per year) to meet these supervisor accountabilities.
                </p>
                
                <p>
                In addition, performance conversations are a rewarding investment of time.
                </p>
                
                <ul>
                <li>They are foundational to the strength of employee-supervisor relationships. These relationships are integral to attracting, developing, and retaining talent.</li>
                <li>They provide clarity to the employee on what is expected of them and insights to the supervisor about what’s important to each employee and what motivates them.</li>
                </ul>
                ',
                'answer_file' => '20',
            ],
            [
                'category' => 'faq',
                'question' => 'I support a ministry and need to have HR Administrator access for the PDP. How do I receive this status?',
                'answer' => '<p>
                Please submit a request for PDP Administrator Access through AskMyHR. Choose "I am submitting this request as or on behalf of - Myself" 
                and select the "HR Software Systems Support>Performance Development Platform" category. 
                You will be provided with a request form that will need to be completed and signed off by your Executive Director or equivalent.
                </p>
                
                <p>
                Once you have this access you can do the following:
                </p>
                
                <ul>
                <li>View the employee list for your organization (including reporting relationships)</li>
                <li>Run reports for your ministry</li>
                <li>Excuse and share employees</li>
                <li>Create goals for your organization’s goal bank</li>
                </ul>
                ',
                'answer_file' => '21',
            ],

        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the data (optional)
        DB::table('resource_content')->whereIn('question', ['Welcome!', 'My Goals Section'])->delete();
    }
}
