<x-side-layout title="{{ __('Statistic and Reports - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Employee Record
        </h2> 
		@include('sysadmin.statistics.partials.tabs')
    </x-slot>
    </div>
    <div class="card p-3">  
	<form id="filter-form" class="no-print">
		<div class="form-row">
                    <div class="form-group col-md-4">
                        <b>Employee ID: </b><br/>
                        <input label="Employee ID" class="error-start" id="employee_id" name="employee_id" value="<?php echo $employee_id; ?>" />
                        @if(isset($data["error"]) && $data["error"]["employee_id"])
                        <small class="text-danger error-employee_id">Employee ID is required</small>
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        <b>Start Date: </b><br/>
                        <input label="Start Date" type="date" name="start_date" value="<?php echo $start_date; ?>" />
                        @if(isset($data["error"]) && $data["error"]["start_date"])
                        <small class="text-danger error-start_date">Start Date is required</small>
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        <b>End Date: </b><br/>
                        <input label="End Date" type="date" name="end_date" value="<?php echo $end_date; ?>" />
                        @if(isset($data["error"]) && $data["error"]["end_date"])
                        <small class="text-danger error-end_date">End Date is required</small>
                        @endif
                    </div>
                </div>
                <div class="form-row">
                    <label>Record Type</label> <br/>
                    <div class="col-md-6">
                        <table style="margin-left:30px">
                            <tr>
                                <td>
                                    <input class="form-check-input" type="checkbox" name="record_types[]" value="active_goals"
                                    <?php       
                                    if (is_array($record_types) && in_array("active_goals", $record_types)) {
                                        echo "checked";
                                    }
                                    ?>
                                    >
                            <label class="form-check-label">Active Goals</label>
                                </td>
                                <td>
                                    <input class="form-check-input" type="checkbox" name="record_types[]" value="open_conversations"
                                    <?php       
                                    if (is_array($record_types) && in_array("open_conversations", $record_types)) {
                                        echo "checked";
                                    }
                                    ?>       
                                    >
                            <label class="form-check-label">Open Conversations</label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input class="form-check-input" type="checkbox" name="record_types[]" value="past_goals"
                                    <?php       
                                    if (is_array($record_types) && in_array("past_goals", $record_types)) {
                                        echo "checked";
                                    }
                                    ?>        
                                    >
                            <label class="form-check-label">Past Goals</label>
                                </td>
                                <td>
                                    <input class="form-check-input" type="checkbox" name="record_types[]" value="completed_conversations"
                                    <?php       
                                    if (is_array($record_types) && in_array("completed_conversations", $record_types)) {
                                        echo "checked";
                                    }
                                    ?>        
                                           
                                    >
                            <label class="form-check-label">Completed Conversations</label>
                                </td>
                            </tr>
                            @if(isset($data["error"]) && $data["error"]["record_types"])
                            <tr>
                                <td colspan="2">
                                <small class="text-danger error-end_date">Record type is required</small>
                                </td>
                            </tr>    
                            @endif
                            
                        </table>    
                    </div> 
                </div>
                <span class="float-left" style="margin-top:10px;">
                    <button type="submit" class="btn btn-primary" name="btn_search" value="btn_search"> <i class="fas fa-search"></i> Search</button>
                    <!----<button type="button" class="btn btn-primary bulk-output"> <i class="fas fa-file-pdf"></i> Bulk Download</button>---->
                </span>
	</form>
    </div>
    
    <div class="card p-3"> 
        @if(!$submit)
        <p>To retrieve performance evaluation records, use the fields above to search for an employee, time frame, and record type.</p>        
        @else
            <div class="row">
                <div class="col-md-6">
                  <label for="input-field" class="form-label">Active Goals:</label>
                </div>
                @if(count($data["active_goals"]) > 0)   
                <div class="col-md-6">
                  <a href="/sysadmin/statistics/filereports-export?type=active_goal&employee_id={{$employee_id}}&start_date={{$start_date}}&end_date={{$end_date}}"><button type="button" class="btn btn-primary  float-right"> <i class="fas fa-file-pdf"></i> Bulk Download</button></a>
                </div>
                @endif
            </div>
            <hr/>
            @if(count($data["active_goals"]) > 0)                
                <table>            
                <tr>
                    <th width="10%">Name</th>
                    <th width="30%">Title</th>
                    <th width="10%">Business Unit</th>
                    <th width="30%">Ministry</th>
                    <th width="10%">Created At</th>
                    <th width="10%">&nbsp;</th>
                </tr>
                <?php foreach($data["active_goals"] as $item){?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->title; ?></td>
                    <td><?php echo $item->business_unit; ?></td>
                    <td><?php echo $item->organization; ?></td>
                    <td>
                        <?php 
                        $dateTime = $item->created_at;
                        if(strtotime($dateTime)){
                            $dateTime = new DateTime($dateTime);
                            $dateTime = $dateTime->format('Y-m-d');
                        }
                        echo $dateTime; 
                        ?>
                    </td>
                    <td>
                        <a href="/sysadmin/statistics/filereports-export?type=selected_goal&id=<?php echo $item->id; ?>"><button type="button" class="btn btn-primary   float-right"> <i class="fas fa-file-pdf"></i> Download</button></a>
                    </td>
                <tr/>    
                <?php }?>
                </table>
                <hr/>
            @else
                <p>There are no records for the employee ID and date range selected.</p>
                <hr/>
            @endif
            <p></p>
            <div class="row">
                <div class="col-md-6">
                  <label for="input-field" class="form-label">Past Goals:</label>
                </div>
                @if(count($data["past_goals"]) > 0)   
                <div class="col-md-6">
                  <a href="/sysadmin/statistics/filereports-export?type=past_goal&employee_id={{$employee_id}}&start_date={{$start_date}}&end_date={{$end_date}}"><button type="button" class="btn btn-primary  float-right"> <i class="fas fa-file-pdf"></i> Bulk Download</button></a>
                </div>
                @endif
            </div>
            <hr/>
            @if(count($data["past_goals"]) > 0)                
                <table>            
                <tr>
                    <th width="10%">Name</th>
                    <th width="30%">Title</th>
                    <th width="10%">Business Unit</th>
                    <th width="30%">Ministry</th>
                    <th width="10%">Created At</th>
                    <th width="10%">&nbsp;</th>
                </tr>
                <?php foreach($data["past_goals"] as $item){?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->title; ?></td>
                    <td><?php echo $item->business_unit; ?></td>
                    <td><?php echo $item->organization; ?></td>
                    <td>
                        <?php 
                        $dateTime = $item->created_at;
                        if(strtotime($dateTime)){
                            $dateTime = new DateTime($dateTime);
                            $dateTime = $dateTime->format('Y-m-d');
                        }
                        echo $dateTime; 
                        ?>
                    </td>
                    <td><a href="/sysadmin/statistics/filereports-export?type=selected_goal&id=<?php echo $item->id; ?>"><button type="button" class="btn btn-primary   float-right"> <i class="fas fa-file-pdf"></i> Download</button></a></td>
                <tr/>    
                <?php }?>
                </table>
                <hr/>
            @else
                <p>There are no records for the employee ID and date range selected.</p>
                <hr/>
            @endif
            <p></p>
            <div class="row">
                <div class="col-md-6">
                  <label for="input-field" class="form-label">Open Conversations:</label>
                </div>
                @if(count($data["open_conversations"]) > 0)   
                <div class="col-md-6">
                  <a href="/sysadmin/statistics/filereports-export?type=open_conversation&employee_id={{$employee_id}}&start_date={{$start_date}}&end_date={{$end_date}}"><button type="button" class="btn btn-primary  float-right"> <i class="fas fa-file-pdf"></i> Bulk Download</button></a>
                </div>
                @endif
            </div>
            <hr/>
            @if(count($data["open_conversations"]) > 0)                
                <table>            
                <tr>
                    <th width="10%">Name</th>
                    <th width="30%">Title</th>
                    <th width="10%">Business Unit</th>
                    <th width="30%">Ministry</th>
                    <th width="10%">Created At</th>
                    <th width="10%">&nbsp;</th>
                </tr>
                <?php foreach($data["open_conversations"] as $item){?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->topic; ?></td>
                    <td><?php echo $item->business_unit; ?></td>
                    <td><?php echo $item->organization; ?></td>
                    <td>
                        <?php 
                        $dateTime = $item->created_at;
                        if(strtotime($dateTime)){
                            $dateTime = new DateTime($dateTime);
                            $dateTime = $dateTime->format('Y-m-d');
                        }
                        echo $dateTime; 
                        ?>
                    </td>
                    <td><a href="/sysadmin/statistics/filereports-export?type=selected_conversation&id=<?php echo $item->conversation_id; ?>"><button type="button" class="btn btn-primary  float-right"> <i class="fas fa-file-pdf"></i> Download</button></a></td>
                <tr/>    
                <?php }?>
                </table>
                <hr/>
            @else
                <p>There are no records for the employee ID and date range selected.</p>
                <hr/>
            @endif
            <p></p>
            <div class="row">
                <div class="col-md-6">
                  <label for="input-field" class="form-label">Completed Conversations:</label>
                </div>
                @if(count($data["completed_conversations"]) > 0)   
                <div class="col-md-6">
                  <a href="/sysadmin/statistics/filereports-export?type=completed_conversation&employee_id={{$employee_id}}&start_date={{$start_date}}&end_date={{$end_date}}"><button type="button" class="btn btn-primary  float-right"> <i class="fas fa-file-pdf"></i> Bulk Download</button></a>
                </div>
                @endif
            </div>
            <hr/>
            @if(count($data["completed_conversations"]) > 0)                
                <table>            
                <tr>
                    <th width="10%">Name</th>
                    <th width="30%">Title</th>
                    <th width="10%">Business Unit</th>
                    <th width="30%">Ministry</th>
                    <th width="10%">Latest Signoff At</th>
                    <th width="10%">&nbsp;</th>
                </tr>
                <?php foreach($data["completed_conversations"] as $item){?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->topic; ?></td>
                    <td><?php echo $item->business_unit; ?></td>
                    <td><?php echo $item->organization; ?></td>
                    <td>
                        <?php 
                        $dateTime = $item->latest_update;
                        if(strtotime($dateTime)){
                            $dateTime = new DateTime($dateTime);
                            $dateTime = $dateTime->format('Y-m-d');
                        }
                        echo $dateTime; 
                        ?>
                    </td>
                    <td><a href="/sysadmin/statistics/filereports-export?type=selected_conversation&id=<?php echo $item->conversation_id; ?>"><button type="button" class="btn btn-primary  float-right"> <i class="fas fa-file-pdf"></i> Download</button></a></td>
                <tr/>                   
                <?php }?>
                </table>
            @else
                <p>There are no records for the employee ID and date range selected.</p>
                <hr/>
            @endif
        @endif
        
        
    </div>    

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