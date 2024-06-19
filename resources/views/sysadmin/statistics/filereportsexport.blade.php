<style>
    table {
        border-collapse: collapse;
    }

    td, th {
        border: 1px solid black;
        padding:5px;
    }
    .page-break {
        page-break-before: always;
    }
</style>
<h2 class="font-semibold text-xl text-primary leading-tight">Performance Development</h2>

    <h3  role="banner">
            Employee Record
    </h3> 
    <div class="card p-3">  
        <div class="form-row">
            <div class="form-group col-md-12">                
                <?php if(isset($data["selected_goal"])){?>
                <b>Goal Details:</b> <p>
                <table class="table">
                    <tr>
                        <th width="10%">Title</th>
                        <th width="10%">Owner</th>
                        <th width="10%">Business Unit</th>
                        <th width="50%">Ministry</th>
                        <th width="10%">Start Date</th>
                        <th width="10%">End Date</th>
                        <th width="10%">Created At</th>
                    </tr>
                    <tr>
                        <td><?php echo $data["selected_goal"]->title; ?></td>
                        <td><?php echo $data["selected_goal"]->name; ?></td>
                        <td><?php echo $data["selected_goal"]->business_unit; ?></td>
                        <td><?php echo $data["selected_goal"]->organization; ?></td>
                        <td>
                        <?php 
                            $dateTime = $data["selected_goal"]->start_date;
                            if(strtotime($dateTime)){
                                $dateTime = new DateTime($dateTime);
                                $dateTime = $dateTime->format('Y-m-d');
                            }
                            echo $dateTime; 
                            ?>
                        </td>
                        <td>
                        <?php 
                            $dateTime = $data["selected_goal"]->target_date;
                            if(strtotime($dateTime)){
                                $dateTime = new DateTime($dateTime);
                                $dateTime = $dateTime->format('Y-m-d');
                            }
                            echo $dateTime; 
                            ?>
                        </td>
                        <td>
                        <?php 
                            $dateTime = $data["selected_goal"]->created_at;
                            if(strtotime($dateTime)){
                                $dateTime = new DateTime($dateTime);
                                $dateTime = $dateTime->format('Y-m-d');
                            }
                            echo $dateTime; 
                            ?>
                        </td>
                    </tr>
                </table>   
                
                <p><b>Description</b></p>
                <?php echo $data["selected_goal"]->what; ?>
                
                <p><b>Measure Of Success</b></p>
                <?php echo $data["selected_goal"]->measure_of_success; ?>
                
                <?php } ?>
                
                <?php if(isset($data["selected_goal_comments"])){?>
                    <p><b>Comments</b></p>
                    <?php echo $data["selected_goal_comments"];?>
                <?php } ?>
                
                <?php if(isset($data["selected_conversation"])){?>
                <b>Conversation Details:</b> <p>
                <table class="table">
                    <tr>
                        <th width="25%">Topic</th>
                        <th width="25%">Participants</th>
                        <th width="25%">Supervisor Sign Off</th>
                        <th width="25%">Employee Sign Off</th>
                        <?php
                            if($data["selected_conversation"]->sign_supervisor_name != '' && $data["selected_conversation"]->sign_employee_name != ''){
                                echo "<th>Latest Signoff At</th>";
                            }else{    
                                echo "<th>Created At</th>";
                            }
                        ?>
                    </tr>
                    <tr>
                        <td><?php echo $data["selected_conversation"]->topic; ?></td>
                        <td><?php echo $data["selected_conversation"]->participants; ?></td>
                        <td><?php echo $data["selected_conversation"]->sign_supervisor_name; ?> <?php if($data["selected_conversation"]->supervisor_signoff_time != ''){?>[<?php echo $data["selected_conversation"]->supervisor_signoff_time; ?>]<?php } ?></td>
                        <td><?php echo $data["selected_conversation"]->sign_employee_name; ?>  <?php if($data["selected_conversation"]->sign_off_time != ''){?>[<?php echo $data["selected_conversation"]->sign_off_time; ?>]<?php } ?></td>
                        <?php
                            if($data["selected_conversation"]->sign_supervisor_name != '' && $data["selected_conversation"]->sign_employee_name != ''){
                                echo "<td>".$data["selected_conversation"]->latest_update."</td>";
                            }else{    
                                echo "<td>".$data["selected_conversation"]->created_at."</td>";
                            }
                        ?>
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
                    <br/>
                    
                    <li><b>Employee Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?>    
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>      
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?>
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>      
                            </li>
                        </ul>
                    </li>
                    <br/>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Appreciation: </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>Coaching: </b><?php echo $data["selected_conversation"]->info_comment2;?></li>
                            <li><b>Evaluation : </b><?php echo $data["selected_conversation"]->info_comment3;?></li>
                            <li><b>Additional Comments : </b><?php echo $data["selected_conversation"]->info_comment5;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment6;?></li>
                        </ul>
                    </li>
                    <br/>
                    
                    <li><b>Supervisor Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>
                                <?php } ?>    
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>
                                <?php } ?>    
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
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
                    <br/>
                    
                    <li><b>Employee Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>   
                                <?php } ?> 
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>       
                                <?php } ?>   
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>      
                            </li>
                        </ul>
                    </li>
                    <br/>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Comments : </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment2;?></li>
                        </ul>
                    </li>
                    <br/>
                    
                    <li><b>Supervisor Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?>    
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>      
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>       
                                <?php } ?>   
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>      
                            </li>
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
                    <br/>
                    
                    <li><b>Employee Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>      
                                <?php } ?> 
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>     
                                <?php } ?>    
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                        </ul>
                    </li>
                    <br/>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Employee Strengths : </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>Employee Growth: </b><?php echo $data["selected_conversation"]->info_comment2;?></li>
                            <li><b>Additional Comments: </b><?php echo $data["selected_conversation"]->info_comment3;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment5;?></li>                            
                        </ul>
                    </li>
                    <br/>
                    
                    <li><b>Supervisor Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?> 
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>    
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?>
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>    
                            </li>
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
                    <br/>
                    
                    <li><b>Employee Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>     
                                <?php } ?>    
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>      
                                <?php } ?>
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                        </ul>
                    </li>
                    <br/>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Evaluation: </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>What must the employee accomplish? By when? </b><?php echo $data["selected_conversation"]->info_comment2;?></li>
                            <li><b>What support will the supervisor (and others) provide? By When? </b><?php echo $data["selected_conversation"]->info_comment3;?></li>
                            <li><b>When will a follow up meeting occur? </b><?php echo $data["selected_conversation"]->info_comment11;?></li>                            
                        </ul>
                    </li>
                    <br/>
                    
                    <li><b>Supervisor Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>       
                                <?php } ?>  
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>       
                                <?php } ?>   
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
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
                    <br/>
                    
                    <li><b>Employee Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?>  
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>      
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->empl_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->empl_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?>
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                        </ul>
                    </li>
                    <br/>
                    <li><b>Supervisor Comments</b>
                        <ul>
                            <li><b>Comments: </b><?php echo $data["selected_conversation"]->info_comment1;?></li>
                            <li><b>Action Items: </b><?php echo $data["selected_conversation"]->info_comment2;?></li>                  
                        </ul>
                    </li>
                    <br/>
                    
                    <li><b>Supervisor Attestation</b>
                        <ul>
                            <li><b>We have reviewed progress of goals and adjusted as necessary.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree1 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree1 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?> 
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                            <li><b>Performance expectations have been clearly communicated.  </b>
                                <?php if ($data["selected_conversation"]->supv_agree2 != '') {?>
                                <?php if($data["selected_conversation"]->supv_agree2 == 1){?>
                                    <p>Yes</p>
                                <?php }else{ ?>
                                    <p>No</p>    
                                <?php } ?>
                                <?php }else{ ?> 
                                    <p>N/A</p>
                                <?php } ?>     
                            </li>
                        </ul>
                    </li>
                </ul>
                <?php } ?>
                
                <?php } ?>
                
                
                
            </div>    
        </div>
    </div>
