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
                <b>Conversation Details:</b> <p>
                <?php foreach($data as $i=>$item){?>
                    <?php if($i>0){?>
                    <div class="page-break">
                    <?php }?>    
                    <table class="table">
                        <tr>
                            <th>Topic</th>
                            <th>Participants</th>
                            <th>Supervisor Sign Off</th>
                            <th>Employee Sign Off</th>
                            <?php
                            if($item->sign_supervisor_name != '' && $item->sign_employee_name != ''){
                                echo "<th>Latest Signoff At</th>";
                            }else{    
                                echo "<th>Created At</th>";
                            }
                            ?>
                        </tr>
                        <tr>
                            <td><?php echo $item->topic; ?></td>
                            <td><?php echo $item->participants; ?></td>
                            <td><?php echo $item->sign_supervisor_name; ?> <?php if($item->supervisor_signoff_time != ''){?>[<?php echo $item->supervisor_signoff_time; ?>]<?php } ?></td>
                            <td><?php echo $item->sign_employee_name; ?>  <?php if($item->sign_off_time != ''){?>[<?php echo $item->sign_off_time; ?>]<?php } ?></td>
                            <?php
                            if($item->sign_supervisor_name != '' && $item->sign_employee_name != ''){
                                echo "<td>".$item->latest_update."</td>";
                            }else{    
                                echo "<td>".$item->created_at."</td>";
                            }
                            ?>
                        </tr>
                    </table>
                
                    <?php if ($item->topic == 'Performance Check-In') { ?>
                    <ul>
                        <li><b>Employee Comments</b>
                            <ul>
                                <li><b>Self Summary: </b><?php echo $item->info_comment4;?></li>
                                <li><b>Additional Comments: </b><?php echo $item->info_comment7;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment8;?></li>
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Employee Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->empl_agree1 != '') {?>
                                        <?php if($item->empl_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>    
                                        <?php } ?> 
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>    
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->empl_agree2 != '') {?>
                                        <?php if($item->empl_agree2 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>    
                                        <?php } ?>  
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>      
                                </li>
                                <li><b>Team member disagrees with the information contained in this performance review.  </b>
                                    <?php if($item->team_member_agreement != '') {?>
                                        <?php if($item->team_member_agreement == 1){?>
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
                                <li><b>Appreciation: </b><?php echo $item->info_comment1;?></li>
                                <li><b>Coaching: </b><?php echo $item->info_comment2;?></li>
                                <li><b>Evaluation : </b><?php echo $item->info_comment3;?></li>
                                <li><b>Additional Comments : </b><?php echo $item->info_comment5;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment6;?></li>
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Supervisor Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->supv_agree1 != '') {?>
                                        <?php if($item->supv_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>
                                        <?php } ?>    
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>     
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->supv_agree2 != '') {?>
                                        <?php if($item->supv_agree2 == 1){?>
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

                    <?php if ($item->topic == 'Goal Setting') { ?>
                    <ul>
                        <li><b>Employee Comments</b>
                            <ul>
                                <li><b>Comments: </b><?php echo $item->info_comment4;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment7;?></li>
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Employee Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->empl_agree1 != '') {?>
                                        <?php if($item->empl_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>   
                                        <?php } ?> 
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>     
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->empl_agree2 != '') {?>
                                        <?php if($item->empl_agree2 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>       
                                        <?php } ?>
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>     
                                </li>
                                <li><b>Team member disagrees with the information contained in this performance review.  </b>
                                    <?php if($item->team_member_agreement != '') {?>
                                        <?php if($item->team_member_agreement == 1){?>
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
                                <li><b>Comments : </b><?php echo $item->info_comment1;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment2;?></li>
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Supervisor Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->supv_agree1 != '') {?>
                                        <?php if($item->supv_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>    
                                        <?php } ?>  
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>      
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->supv_agree2 != '') {?>
                                        <?php if($item->supv_agree2 == 1){?>
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

                    <?php if ($item->topic == 'Career Development') { ?>
                    <ul>
                        <li><b>Employee Comments</b>
                            <ul>
                                <li><b>Career Goal Statement: </b><?php echo $item->info_comment4;?></li>
                                <li><b>Strengths: </b><?php echo $item->info_comment7;?></li>
                                <li><b>Areas for Growth: </b><?php echo $item->info_comment8;?></li>
                                <li><b>Additional Comments: </b><?php echo $item->info_comment9;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment10;?></li>
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Employee Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->empl_agree1 != '') {?>
                                        <?php if($item->empl_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>      
                                        <?php } ?>  
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>      
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->empl_agree2 != '') {?>
                                        <?php if($item->empl_agree2 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>     
                                        <?php } ?>
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>      
                                </li>
                                <li><b>Team member disagrees with the information contained in this performance review.  </b>
                                    <?php if($item->team_member_agreement != '') {?>
                                        <?php if($item->team_member_agreement == 1){?>
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
                                <li><b>Employee Strengths : </b><?php echo $item->info_comment1;?></li>
                                <li><b>Employee Growth: </b><?php echo $item->info_comment2;?></li>
                                <li><b>Additional Comments: </b><?php echo $item->info_comment3;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment5;?></li>                            
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Supervisor Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->supv_agree1 != '') {?>
                                        <?php if($item->supv_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>    
                                        <?php } ?>    
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>     
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->supv_agree2 != '') {?>
                                        <?php if($item->supv_agree2 == 1){?>
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

                    <?php if ($item->topic == 'Performance Improvement') { ?>
                    <ul>
                        <li><b>Employee Comments</b>
                            <ul>
                                <li><b>Self Summary: </b><?php echo $item->info_comment4;?></li>
                                <li><b>Additional Comments: </b><?php echo $item->info_comment7;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment8;?></li>
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Employee Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->empl_agree1 != '') {?>
                                        <?php if($item->empl_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>     
                                        <?php } ?>
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>     
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->empl_agree2 != '') {?>
                                        <?php if($item->empl_agree2 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>      
                                        <?php } ?> 
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>     
                                </li>
                                <li><b>Team member disagrees with the information contained in this performance review.  </b>
                                    <?php if($item->team_member_agreement != '') {?>
                                        <?php if($item->team_member_agreement == 1){?>
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
                                <li><b>Evaluation: </b><?php echo $item->info_comment1;?></li>
                                <li><b>What must the employee accomplish? By when? </b><?php echo $item->info_comment2;?></li>
                                <li><b>What support will the supervisor (and others) provide? By When? </b><?php echo $item->info_comment3;?></li>
                                <li><b>When will a follow up meeting occur? </b><?php echo $item->info_comment11;?></li>                            
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Supervisor Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->supv_agree1 != '') {?>
                                        <?php if($item->supv_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>       
                                        <?php } ?> 
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>     
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->supv_agree2 != '') {?>
                                        <?php if($item->supv_agree2 == 1){?>
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

                    <?php if ($item->topic == 'Onboarding') { ?>
                    <ul>
                        <li><b>Employee Comments</b>
                            <ul>
                                <li><b>Comments: </b><?php echo $item->info_comment4;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment7;?></li>
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Employee Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->empl_agree1 != '') {?>
                                        <?php if($item->empl_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>    
                                        <?php } ?>   
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>    
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if ($item->empl_agree2 != '') {?>
                                        <?php if($item->empl_agree2 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>    
                                        <?php } ?>    
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>    
                                </li>
                                <li><b>Team member disagrees with the information contained in this performance review.  </b>
                                    <?php if($item->team_member_agreement != '') {?>
                                        <?php if($item->team_member_agreement == 1){?>
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
                                <li><b>Comments: </b><?php echo $item->info_comment1;?></li>
                                <li><b>Action Items: </b><?php echo $item->info_comment2;?></li>                  
                            </ul>
                        </li>
                        <br/>
                        
                        <li><b>Supervisor Attestation</b>
                            <ul>
                                <li><b>We have reviewed goals and adjusted as necessary.  </b>
                                    <?php if($item->supv_agree1 != '') {?>
                                        <?php if($item->supv_agree1 == 1){?>
                                            <p>Yes</p>
                                        <?php }else{ ?>
                                            <p>No</p>    
                                        <?php } ?> 
                                    <?php }else{ ?> 
                                        <p>N/A</p>
                                    <?php } ?>     
                                </li>
                                <li><b>Performance expectations are clear.  </b>
                                    <?php if($item->supv_agree2 != '') {?>
                                        <?php if($item->supv_agree2 == 1){?>
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
                    <p>
                    <?php if($i>0){?>
                    </div>
                    <?php } ?>
                <?php }?> 
            </div>    
        </div>
    </div>
