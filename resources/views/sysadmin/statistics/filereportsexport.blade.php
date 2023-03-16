<x-side-layout title="{{ __('Statistic and Reports - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Performance Evaluation Reports
        </h2> 
		
    </x-slot>
    </div>
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
                        <th>Description</th>
                        <th>Measure Of Success</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                    <tr>
                        <td><?php echo $data["selected_goal"]->title; ?></td>
                        <td><?php echo $data["selected_goal"]->name; ?></td>
                        <td><?php echo $data["selected_goal"]->business_unit; ?></td>
                        <td><?php echo $data["selected_goal"]->organization; ?></td>
                        <td><?php echo $data["selected_goal"]->what; ?></td>
                        <td><?php echo $data["selected_goal"]->measure_of_success; ?></td>
                        <td><?php echo $data["selected_goal"]->start_date; ?></td>
                        <td><?php echo $data["selected_goal"]->target_date; ?></td>
                    </tr>
                </table>                
                <?php } ?>
                
                <?php if(isset($data["selected_goal_comments"])){?>
                    <?php echo $data["selected_goal_comments"];?>
                <?php } ?>
                
                <?php if(isset($data["selected_conversation"])){?>
                <table class="table">
                    <tr>
                        <th>Topic</th>
                        <th>Owner</th>
                        <th>Participants</th>
                        <th>Supervisor Sign Off</th>
                        <th>Employee Sign Off</th>
                    </tr>
                    <tr>
                        <td><?php echo $data["selected_conversation"]->topic; ?></td>
                        <td><?php echo $data["selected_conversation"]->employee_name; ?></td>
                        <td><?php echo $data["selected_conversation"]->participants; ?></td>
                        <td><?php echo $data["selected_conversation"]->sign_supervisor_name; ?></td>
                        <td><?php echo $data["selected_conversation"]->sign_employee_name; ?></td>
                    </tr>
                </table>                
                <?php } ?>
                
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
                
            </div>    
        </div>
    </div>

    
<?php 


?>
    

<x-slot name="css">
	<style>
		@media screen  {
		.chart {
			/* min-width:  180px;  */
			min-height: 480px;
		}	
	
		.print-only {
			display: none;
		}
	}	
	
	@media print {
	
		@page { size:letter } 
		body { 
			
			max-width: 800px !important;
			margin-left: 100px !important;
	
		}	 
		.no-print, .no-print *
		{
			display: none !important;
		}
		.chart {
			/* min-width:  180px;  */
			margin-left: 60px; 
		}	
	
		.row {
			display: block;
		}
		.page-break  { 
			display:block; 
			page-break-before : always ; 
	
		}
		  
	}
	</style>
</x-slot>


</x-side-layout>
<style>
    label{
        display:inline;
        margin-right: 30px;
    } 
</style> 