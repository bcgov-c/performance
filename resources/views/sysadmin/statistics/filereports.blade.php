<x-side-layout title="{{ __('Statistic and Reports - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Performance Evaluation Reports
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
                        <b>Completion Date (Start): </b><br/>
                        <input label="Completion Date (Start)" type="date" name="start_date" value="<?php echo $start_date; ?>" />
                        @if(isset($data["error"]) && $data["error"]["start_date"])
                        <small class="text-danger error-start_date">Completion Date (Start) is required</small>
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        <b>Completion Date ((End): </b><br/>
                        <input label="Completion Date ((End)" type="date" name="end_date" value="<?php echo $end_date; ?>" />
                        @if(isset($data["error"]) && $data["error"]["end_date"])
                        <small class="text-danger error-end_date">Completion Date (End) is required</small>
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
                                    if (in_array("active_goals", $record_types)) {
                                        echo "checked";
                                    }
                                    ?>
                                    >
                            <label class="form-check-label">Active Goals</label>
                                </td>
                                <td>
                                    <input class="form-check-input" type="checkbox" name="record_types[]" value="open_conversations"
                                    <?php       
                                    if (in_array("open_conversations", $record_types)) {
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
                                    if (in_array("past_goals", $record_types)) {
                                        echo "checked";
                                    }
                                    ?>        
                                    >
                            <label class="form-check-label">Past Goals</label>
                                </td>
                                <td>
                                    <input class="form-check-input" type="checkbox" name="record_types[]" value="completed_conversations"
                                    <?php       
                                    if (in_array("completed_conversations", $record_types)) {
                                        echo "checked";
                                    }
                                    ?>        
                                           
                                    >
                            <label class="form-check-label">Completed Conversations</label>
                                </td>
                            </tr>
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
            @if(count($data["active_goals"]) > 0)
                <b>Active Goals</b><hr/>
                <table>            
                <tr>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Business Unit</th>
                    <th>Ministry</th>
                    <th>&nbsp;</th>
                </tr>
                <?php foreach($data["active_goals"] as $item){?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->title; ?></td>
                    <td><?php echo $item->business_unit; ?></td>
                    <td><?php echo $item->organization; ?></td>
                    <td>
                        <a href="/sysadmin/statistics/filereports-export?type=selected_goal&id=<?php echo $item->id; ?>"><button type="button" class="btn btn-primary"> <i class="fas fa-file-pdf"></i> Download</button></a>
                    </td>
                <tr/>    
                <?php }?>
                </table>
                <hr/>
            @endif
            <p></p>
            @if(count($data["past_goals"]) > 0)
                <b>Past Goals</b><hr/>
                <table>            
                <tr>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Business Unit</th>
                    <th>Ministry</th>
                    <th>&nbsp;</th>
                </tr>
                <?php foreach($data["past_goals"] as $item){?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->title; ?></td>
                    <td><?php echo $item->business_unit; ?></td>
                    <td><?php echo $item->organization; ?></td>
                    <td><a href="/sysadmin/statistics/filereports-export?type=selected_goal&id=<?php echo $item->id; ?>"><button type="button" class="btn btn-primary"> <i class="fas fa-file-pdf"></i> Download</button></a></td>
                <tr/>    
                <?php }?>
                </table>
                <hr/>
            @endif
            <p></p>
            @if(count($data["open_conversations"]) > 0)
                <b>Open Conversations</b><hr/>
                <table>            
                <tr>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Business Unit</th>
                    <th>Ministry</th>
                    <th>&nbsp;</th>
                </tr>
                <?php foreach($data["open_conversations"] as $item){?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->topic; ?></td>
                    <td><?php echo $item->business_unit; ?></td>
                    <td><?php echo $item->organization; ?></td>
                    <td><a href="/sysadmin/statistics/filereports-export?type=selected_conversation&id=<?php echo $item->conversation_id; ?>"><button type="button" class="btn btn-primary"> <i class="fas fa-file-pdf"></i> Download</button></a></td>
                <tr/>    
                <?php }?>
                </table>
                <hr/>
            @endif
            <p></p>
            @if(count($data["completed_conversations"]) > 0)
                <b>Completed Conversations</b><hr/>
                <table>            
                <tr>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Business Unit</th>
                    <th>Ministry</th>
                    <th>&nbsp;</th>
                </tr>
                <?php foreach($data["completed_conversations"] as $item){?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->topic; ?></td>
                    <td><?php echo $item->business_unit; ?></td>
                    <td><?php echo $item->organization; ?></td>
                    <td><a href="/sysadmin/statistics/filereports-export?type=selected_conversation&id=<?php echo $item->conversation_id; ?>"><button type="button" class="btn btn-primary"> <i class="fas fa-file-pdf"></i> Download</button></a></td>
                <tr/>    
                <?php }?>
                </table>
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