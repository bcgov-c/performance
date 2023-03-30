<h2 class="font-semibold text-xl text-primary leading-tight">Performance Development</h2>

    <h3  role="banner">
            Employee Record
    </h3> 
    <div class="card p-3">  
        <div class="form-row">
            <div class="form-group col-md-12">
                <b>Goal Details:</b> <p>
                <?php if(isset($data["selected_goal"])){?>
                <table class="table">
                    <tr>
                        <th>Title</th>
                        <th>Owner</th>
                        <th>Business Unit</th>
                        <th>Ministry</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                    <tr>
                        <td><?php echo $data["selected_goal"]->title; ?></td>
                        <td><?php echo $data["selected_goal"]->name; ?></td>
                        <td><?php echo $data["selected_goal"]->business_unit; ?></td>
                        <td><?php echo $data["selected_goal"]->organization; ?></td>
                        <td><?php echo $data["selected_goal"]->start_date; ?></td>
                        <td><?php echo $data["selected_goal"]->target_date; ?></td>
                    </tr>
                </table>   
                
                <p><b>Description</b></p>
                <?php echo $data["selected_goal"]->what; ?>
                
                <p><b>Measure Of Success</b></p>
                <?php echo $data["selected_goal"]->measure_of_success; ?>
                
                <?php } ?>
                
                <?php if(isset($data["selected_goal_comments"])){?>
                    <?php echo $data["selected_goal_comments"];?>
                <?php } ?>
                
                <?php if(isset($data["selected_conversation"])){?>
                <table class="table">
                    <tr>
                        <th>Topic</th>
                        <th>Participants</th>
                        <th>Supervisor Sign Off</th>
                        <th>Employee Sign Off</th>
                    </tr>
                    <tr>
                        <td><?php echo $data["selected_conversation"]->topic; ?></td>
                        <td><?php echo $data["selected_conversation"]->participants; ?></td>
                        <td><?php echo $data["selected_conversation"]->sign_supervisor_name; ?> <?php if($data["selected_conversation"]->supervisor_signoff_time != ''){?>[<?php echo $data["selected_conversation"]->supervisor_signoff_time; ?>]<?php } ?></td>
                        <td><?php echo $data["selected_conversation"]->sign_employee_name; ?>  <?php if($data["selected_conversation"]->sign_off_time != ''){?>[<?php echo $data["selected_conversation"]->sign_off_time; ?>]<?php } ?></td>
                    </tr>
                </table>  
                
                <?php if ($data["selected_conversation"]->topic == 'Performance Check-In') { ?>
                <ul>
                    <li><b>Employee Comments</b>
                        <ul>
                            <li><b>Self Summary: </b><?php echo $data["selected_conversation"]->info_comment4;?></li>
                            <li><b>Additional Comments: </b><?php echo $data["selected_conversation"]->info_comment7;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment8;?></li>
                        </ul>
                    </li>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Appreciation: </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>Coaching: </b><?php echo $data["selected_conversation"]->info_comment2;?></li>
                            <li><b>Evaluation : </b><?php echo $data["selected_conversation"]->info_comment3;?></li>
                            <li><b>Additional Comments : </b><?php echo $data["selected_conversation"]->info_comment5;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment6;?></li>
                        </ul>
                    </li>
                </ul>
                <?php } ?>
                
                <?php if ($data["selected_conversation"]->topic == 'Goal Setting') { ?>
                <ul>
                    <li><b>Employee Comments</b>
                        <ul>
                            <li><b>Comments: </b><?php echo $data["selected_conversation"]->info_comment4;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment7;?></li>
                        </ul>
                    </li>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Comments : </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment2;?></li>
                        </ul>
                    </li>
                </ul>
                <?php } ?>
                
                <?php if ($data["selected_conversation"]->topic == 'Career Development') { ?>
                <ul>
                    <li><b>Employee Comments</b>
                        <ul>
                            <li><b>Career Goal Statement: </b><?php echo $data["selected_conversation"]->info_comment4;?></li>
                            <li><b>Strengths: </b><?php echo $data["selected_conversation"]->info_comment7;?></li>
                            <li><b>Areas for Growth: </b><?php echo $data["selected_conversation"]->info_comment8;?></li>
                            <li><b>Additional Comments: </b><?php echo $data["selected_conversation"]->info_comment9;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment10;?></li>
                        </ul>
                    </li>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Employee Strengths : </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>Employee Growth: </b><?php echo $data["selected_conversation"]->info_comment2;?></li>
                            <li><b>Additional Comments: </b><?php echo $data["selected_conversation"]->info_comment3;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment5;?></li>                            
                        </ul>
                    </li>
                </ul>
                <?php } ?>
                
                <?php if ($data["selected_conversation"]->topic == 'Performance Improvement') { ?>
                <ul>
                    <li><b>Employee Comments</b>
                        <ul>
                            <li><b>Self Summary: </b><?php echo $data["selected_conversation"]->info_comment4;?></li>
                            <li><b>Additional Comments: </b><?php echo $data["selected_conversation"]->info_comment7;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment8;?></li>
                        </ul>
                    </li>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Evaluation: </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>What must the employee accomplish? By when? </b><?php echo $data["selected_conversation"]->info_comment2;?></li>
                            <li><b>What support will the supervisor (and others) provide? By When? </b><?php echo $data["selected_conversation"]->info_comment3;?></li>
                            <li><b>When will a follow up meeting occur? </b><?php echo $data["selected_conversation"]->info_comment11;?></li>                            
                        </ul>
                    </li>
                </ul>
                <?php } ?>
                
                <?php if ($data["selected_conversation"]->topic == 'Onboarding') { ?>
                <ul>
                    <li><b>Employee Comments</b>
                        <ul>
                            <li><b>Comments: </b><?php echo $data["selected_conversation"]->info_comment4;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment7;?></li>
                        </ul>
                    </li>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Comments: </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment2;?></li>                  
                        </ul>
                    </li>
                </ul>
                <?php } ?>
                
                <?php } ?>
                
                
                
            </div>    
        </div>
    </div>
