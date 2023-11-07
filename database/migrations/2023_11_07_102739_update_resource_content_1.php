<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateResourceContent1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            UPDATE `resource_content` SET
            `question` = 'HR Admin Access in the PDP',
            `answer` = '<p><b>Overview:</b></p>
                        <p>
                            In order to protect user privacy, HR Administrator Access will be granted at the lowest organizational level possible 
                            that still enables an individual to fulfill their role and accomplish designated tasks. 
                            All requests for access will be reviewed by the employee\'s supervisor, executive director, and ministry HR team 
                            and ultimately be approved or denied by the Performance Development Platform team.
                        </p>   
                            <li>
                                Within their authorized area, HR Administrators will be able to:
                                <ul>
                                    <li>View basic employee data and reporting relationships.</li>
                                    <li>Update shared supervisor relationships in the PDP.</li>
                                    <li>Edit the excused status of employees in the PDP.</li>
                                    <li>Run reports and export excel files that show number and type of active goals created as well as the number, type, and due dates of conversations captured in the PDP.</li>
                                </ul>
                            </li>
                            <li>
                                HR Administrators will NOT be able to:
                                <ul>
                                    <li>Access individual user profiles (other than their own).</li>
                                    <li>See the content of goals, comments, or conversations for any employee (other than themselves).</li>
                                </ul>
                            </li>

                        <p><b>Training:</b></p>
                        <p>
                            HR Administrators should carefully review the two documents below and reach out to their ministry contact if they have additional questions.
                        </p>   
                            <ul>
                                <li><a href=\"/storage/HR Administrator Guide for PDP.pdf\" target=\"_blank\">HR Administrator Guide for PDP.pdf</a></li>
                                <li><a href=\"/storage/Guide to Creating Org Goals in PDP.pdf\" target=\"_blank\">Guide to Creating Org Goals in PDP.pdf</a></li>
                            </ul>

                        <p><b>Request Access:</b></p>
                        <p>
                            Please complete the form below with required approvals and submit a MyHR service request with subject line \"PDP HR Admin Access - YOUR NAME\" using the routing: HR Software System Support > Performance Development Platform.
                        </p>
                            <ul>
                                <li><a href=\"/storage/BCPSA DATA ACCESS AGREEMENT 2023_open with Adobe Reader.pdf\" target=\"_blank\">BCPSA Data Access Agreement.pdf</a></li>
                            </ul>'
            where question = 'HR Admin Access in the PDP';
        ");

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
