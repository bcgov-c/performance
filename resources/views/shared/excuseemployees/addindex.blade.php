<x-side-layout title="{{ __('Excuse Employees - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Excuse Employees</h3>
            @include('shared.excuseemployees.partials.tabs')
        </div>
    </div>

    @include('shared/excuseemployees/partials/excused-edit-modal')

	<p class="px-3">Follow the steps below to select an employee and excuse them from the Performance Development process. This will remove the employee from any reporting and will pause the employee’s conversation deadlines.</p>

        @if(Session::has('message'))
            <div class="col-12">                    
                <div class="alert alert-danger" style="display:">
                    <i class="fa fa-info-circle"></i> {{ Session::get('message') }}
                </div>
            </div>
        @endif

	<br>
	<h6 class="text-bold">Step 1. Select employee(s) to excuse</h6>
	<br>

	<form id="notify-form" action="{{ route(request()->segment(1).'.excuseemployees.saveexcuse') }}" method="post">
		@csrf
		<input type="hidden" id="selected_emp_ids" name="selected_emp_ids" value="">

		<!----modal starts here--->
		<div id="saveExcuseModal" class="modal" role='dialog'>
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><i class="fa fa-exclamation-triangle fa-2x" style="color:red"></i>&nbsp &nbsp Confirmation</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						    <span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>Are you sure to send out this message ?</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-primary mt-2" type="submit" id="modal_btn_send" name="modal_btn_send" value="modal_btn_send">Excuse</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					</div>
					
				</div>
			</div>
		</div>
		<!--Modal ends here--->	
	
		<div class="m-n2 pb-n3 pl-2">
			@include('shared.excuseemployees.partials.filter')
		</div>

        <div class="m-2 mt-n2">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-list-tab" data-toggle="tab" href="#nav-list" role="tab" aria-controls="nav-list" aria-selected="true">List</a>
                    <a class="nav-item nav-link" id="nav-tree-tab" data-toggle="tab" href="#nav-tree" role="tab" aria-controls="nav-tree" aria-selected="false">Tree</a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
                    @include('shared.excuseemployees.partials.recipient-list')
                </div>
                <div class="tab-pane fade" id="nav-tree" role="tabpanel" aria-labelledby="nav-tree-tab" loaded="">
                    <div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" id="tree-loading-spinner" role="status" style="display:none">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>


        <div class="container-fluid">
			<br>
			<h6 class="text-bold">Step 2. Select reason for excusing selected employee(s)</h6> 
			<br>
			<div class="card col-md-6" >
				<div class="card-body">
					<div class="row">
						{{-- <div class="col">
							<x-input label="Start Date " class="error-start" type="date" id="start_date" name="start_date" value="{{ Request::old('start_date') }}" />
						</div>
						<div class="col">
							<x-input label="End Date " class="error-target" type="date" id="target_date" name="target_date" value="{{ Request::old('target_date') }}" />
						</div> --}}
                        {{-- <div class="col-2 mt-1"> --}}
                            {{-- <x-dropdown :list="$yesOrNo" label="Excused" name="excused_flag" value=""/> --}}
                        {{-- </div> --}}
						<div class="col">
							<input type="hidden" id="excused_flag" name="excused_flag" value=1>
							<label for='excuse_reason'>Reason
								{{-- <x-dropdown :list="$reasons" class="multiple" id="excuse_reason" name="excuse_reason" :selected="request()->excused_reason"></x-dropdown> --}}
								<select id="excused_reason" name="excused_reason" class="form-control">
									@foreach($reasons as $reason)
										<option value="{{ $reason->id }}" {{ old('excused_reason') == '$reason->id' ? "selected" : "" }}>{{ $reason->name }}</option>
									@endforeach
								</select>
							</label>
						</div>
					</div>
				</div>
			</div>

			<div class="container-fluid">
			<br>
			<h6 class="text-bold">Step 3. Declaration</h6>
			<br>
			<div class="card col-md-12" >
				<div class="card-body">
					{{-- <h6 class="text-bold mt-1">Target Audience</h6> --}}
					<div class="row">
						<input class="" type="checkbox"  id="chkbox_declare" name="chkbox_declare" value="">
						<p class="px-3">I wish to excuse the selected employees from the Performance Development process.</p>
					</div>
					<div class="row">
						<div class="alert alert-warning alert-dismissible no-border"  style="border-color:#d5e6f6; background-color:#d5e6f6" role="alert">
							<span class="h6" aria-hidden="true"><i class="icon fa fa-exclamation-triangle  "></i><b>Note: By doing so, these employees will not show up in PDP reports.</b></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<br>
		<h6 class="text-bold">Step 4. Excuse selected employee(s)</h6>
		<br>
		<div class="col-md-4 mb-2">
			<button class="btn btn-primary mt-2" type="button" onclick="confirmSaveExcuseModal()" id="btn_send" name="btn_send" value="btn_send">Excuse Employee(s)</button>
			<button class="btn btn-secondary mt-2">Cancel</button>
		</div>

	</form>

	<h6 class="m-20">&nbsp;</h6>
	<h6 class="m-20">&nbsp;</h6>
	<h6 class="m-20">&nbsp;</h6>


	<x-slot name="css">
		<style>

			.select2-container .select2-selection--single {
				height: 38px !important;
			}EmployeeID

			.select2-container--default .select2-selection--single .select2-selection__arrow {
				height: 38px !important;
			}

			.pageLoader{
				/* background: url(../images/loader.gif) no-repeat center center; */
				position: fixed;
				top: 0;
				left: 0;
				height: 100%;
				width: 100%;
				z-index: 9999999;
				background-color: #ffffff8c;

			}

			.pageLoader .spinner {
				/* background: url(../images/loader.gif) no-repeat center center; */
				position: fixed;
				top: 25%;
				left: 47%;
				width: 10em;
				height: 10em;
				z-index: 9000000;
			}

		</style>
	</x-slot>

	<x-slot name="js">

		<script>
			let g_matched_employees = {!!json_encode($matched_emp_ids)!!};
			let g_selected_employees = {!!json_encode($old_selected_emp_ids)!!};
			let g_selected_orgnodes = {!!json_encode($old_selected_org_nodes)!!};
			let g_employees_by_org = [];

			function confirmSaveExcuseModal(){
				count = g_selected_employees.length;
				if (count == 0) {
					$('#modal_btn_send').attr('disabled',true);
					$('#saveExcuseModal .modal-body p').html('<p>There are no employees selected.</p>');
				} else {
					$('#modal_btn_send').removeAttr('disabled');
					$('#saveExcuseModal .modal-body p').html('<p>Are you sure you want to excuse the <b>' + count + '</b> selected employee(s)? <br>This will pause their next conversation due date and remove them from PDP reporting.</p><p>Note: Excuse status may not update until the next day.</p>');
				}
				$('#saveExcuseModal').modal();
			}

			$(document).ready(function(){

				confirmSwitch();

				function navTreeActive() {
					return $('#nav-tree').attr('class').search(/active/i) > 0 ?? false;
				}

				function navListActive() {
					return $('#nav-list').attr('class').search(/active/i) > 0 ?? false;
				}

				function confirmSwitch(){
					if($('#chkbox_declare').prop('checked')) {
						$('#btn_send').removeAttr('disabled');
					} else {
						$('#btn_send').attr('disabled',true);
					}
				}

				$("#chkbox_declare").change(function (e){
					e.preventDefault();
					confirmSwitch();
				});

				$('#pageLoader').hide();

				$('#notify-form').keydown(function (e) {
					if (e.keyCode == 13) {
						e.preventDefault();
						return false;
					}
				});

				$('#notify-form').submit(function() {
					// assign back the selected employees to server
					var text = JSON.stringify(g_selected_employees);
					$('#selected_emp_ids').val( text );
					var text2 = JSON.stringify(g_selected_orgnodes);
					$('#selected_org_nodes').val( text2 );
					return true; // return false to cancel form action
				});


				// Tab  -- LIST Page  activate
				$("#nav-list-tab").on("click", function(e) {
					table  = $('#employee-list-table').DataTable();
					table.rows().invalidate().draw();
				});

				// Tab  -- TREE activate
				$("#nav-tree-tab").on("click", function(e) {
					target = $('#nav-tree'); 
                    ddnotempty = $('#dd_level0').val() + $('#dd_level1').val() + $('#dd_level2').val() + $('#dd_level3').val() + $('#dd_level4').val();
                    if(ddnotempty) {
                        // To do -- ajax called to load the tree
                        if($.trim($(target).attr('loaded'))=='') {
                           	$.when( 
                                $.ajax({
                					url: '{{ "/".request()->segment(1)."/excuseemployees/org-tree/1" }}',
                                    type: 'GET',
									data: $("#notify-form").serialize(),
									dataType: 'html',
									beforeSend: function() {
                                        $("#tree-loading-spinner").show();                    
                                    },
									success: function (result) {
                                        $(target).html(''); 
                                        $(target).html(result);

                                        $('#nav-tree').attr('loaded','loaded');
                                    },
									complete: function() {
                                        $("#tree-loading-spinner").hide();
                                    },
									error: function () {
                                        alert("error");
                                        $(target).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                    }
                                })
                            ).then(function( data, textStatus, jqXHR ) {
                                nodes = $('#accordion-level0 input:checkbox');
                                redrawTreeCheckboxes();	
                            }); 
                        } else {
							$(target).removeAttr('loaded');
							$("#nav-tree-tab").click();
                        }
                    } else {
						$(target).removeAttr('loaded');
						$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Please apply the organization filter before creating a tree view.');
					}
				});

				$('#btn_search').click(function(e) {
					e.preventDefault();
					if (navListActive()) {
						$('#employee-list-table').DataTable().rows().invalidate().draw();
					}
					if (navTreeActive()) {
						$("#nav-tree-tab").click();
					}
				} );

				function redrawTreeCheckboxes() {
					// redraw the selection 
					nodes = $('#accordion-level0 input:checkbox');
					$.each( nodes, function( index, chkbox ) {
						if (g_employees_by_org.hasOwnProperty(chkbox.value)) {
							all_emps = g_employees_by_org[ chkbox.value ].map( function(x) {return x.employee_id} );
							b = all_emps.every(v=> g_selected_employees.indexOf(v) !== -1);
							if (all_emps.every(v=> g_selected_employees.indexOf(v) !== -1)) {
								$(chkbox).prop('checked', true);
								$(chkbox).prop("indeterminate", false);
							} else if (all_emps.some(v=> g_selected_employees.indexOf(v) !== -1)) {
								$(chkbox).prop('checked', false);
								$(chkbox).prop("indeterminate", true);
							} else {
								$(chkbox).prop('checked', false);
								$(chkbox).prop("indeterminate", false);
							}
						} 
					});

					// reset checkbox state
					reverse_list = nodes.get().reverse();
					$.each( reverse_list, function( index, chkbox ) {
						if (g_employees_by_org.hasOwnProperty(chkbox.value)) {
							pid = $(chkbox).attr('pid');
							do {
								value = '#orgCheck' + pid;
								toggle_indeterminate( value );
								pid = $('#orgCheck' + pid).attr('pid');    
							} 
							while (pid);
						}
					});

                    // update the check all checkbox status 
                    if (g_selected_employees.length == 0) {
                        $('#employee-list-select-all').prop("checked", false);
                        $('#employee-list-select-all').prop("indeterminate", false);   
                    } else if (g_selected_employees.length == g_matched_employees.length) {
                        $('#employee-list-select-all').prop("checked", true);
                        $('#employee-list-select-all').prop("indeterminate", false);   
                    } else {
                        $('#employee-list-select-all').prop("checked", false);
                        $('#employee-list-select-all').prop("indeterminate", true);    
                    }

				}

				function eredrawTreeCheckboxes() {
					// redraw the selection 
					enodes = $('#eaccordion-level0 input:checkbox');
					$.each( enodes, function( index, chkbox ) {
						if (eg_employees_by_org.hasOwnProperty(chkbox.value)) {
							eall_emps = eg_employees_by_org[ chkbox.value ].map( function(x) {return x.employee_id} );
							b = eall_emps.every(v=> g_selected_orgnodes.indexOf(v) !== -1);

							if (eall_emps.every(v=> g_selected_orgnodes.indexOf(v) !== -1)) {
								$(chkbox).prop('checked', true);
								$(chkbox).prop("indeterminate", false);
							} else if (eall_emps.some(v=> g_selected_orgnodes.indexOf(v) !== -1)) {
								$(chkbox).prop('checked', false);
								$(chkbox).prop("indeterminate", true);
							} else {
								$(chkbox).prop('checked', false);
								$(chkbox).prop("indeterminate", false);
							}
						} else {
							if ( $(chkbox).attr('name') == 'userCheck[]') {
								if (g_selected_orgnodes.includes(chkbox.value)) {
									$(chkbox).prop('checked', true);
								} else {
									$(chkbox).prop('checked', false);
								}
							}
						}
					});

					// reset checkbox state
					ereverse_list = enodes.get().reverse();
					$.each( ereverse_list, function( index, chkbox ) {
						if (eg_employees_by_org.hasOwnProperty(chkbox.value)) {
							pid = $(chkbox).attr('pid');
							do {
								value = '#eorgCheck' + pid;
								etoggle_indeterminate( value );
								pid = $('#eorgCheck' + pid).attr('pid');    
							} 
							while (pid);
						}
					});

				}

				// Set parent checkbox
				function toggle_indeterminate( prev_input ) {
					// Loop to checked the child
					var c_indeterminated = 0;
					var c_checked = 0;
					var c_unchecked = 0;
					prev_location = $(prev_input).parent().attr('href');
					nodes = $(prev_location).find("input:checkbox[name='orgCheck[]']");
					$.each( nodes, function( index, chkbox ) {
						if (chkbox.checked) {
							c_checked++;
						} else if ( chkbox.indeterminate ) {
							c_indeterminated++;
						} else {
							c_unchecked++;
						}
					});
					
					if (c_indeterminated > 0) {
						$(prev_input).prop('checked', false);
						$(prev_input).prop("indeterminate", true);
					} else if (c_checked > 0 && c_unchecked > 0) {
						$(prev_input).prop('checked', false);
						$(prev_input).prop("indeterminate", true);
					} else if (c_checked > 0 && c_unchecked == 0 ) {
						$(prev_input).prop('checked', true);
						$(prev_input).prop("indeterminate", false);
					} else {
						$(prev_input).prop('checked', false);
						$(prev_input).prop("indeterminate", false);
					}
				}

				// Set parent checkbox
				function etoggle_indeterminate( prev_input ) {
					// Loop to checked the child
					var c_indeterminated = 0;
					var c_checked = 0;
					var c_unchecked = 0;
					prev_location = $(prev_input).parent().attr('href');
					nodes = $(prev_location).find("input:checkbox[name='eorgCheck[]']");
					$.each( nodes, function( index, chkbox ) {
						if (chkbox.checked) {
							c_checked++;
						} else if ( chkbox.indeterminate ) {
							c_indeterminated++;
						} else {
							c_unchecked++;
						}
					});
					
					if (c_indeterminated > 0) {
						$(prev_input).prop('checked', false);
						$(prev_input).prop("indeterminate", true);
					} else if (c_checked > 0 && c_unchecked > 0) {
						$(prev_input).prop('checked', false);
						$(prev_input).prop("indeterminate", true);
					} else if (c_checked > 0 && c_unchecked == 0 ) {
						$(prev_input).prop('checked', true);
						$(prev_input).prop("indeterminate", false);
					} else {
						$(prev_input).prop('checked', false);
						$(prev_input).prop("indeterminate", false);
					}
				}

				$('#ebtn_search').click(function(e) {
					target = $('#enav-tree'); 
					ddnotempty = $('#edd_level0').val() + $('#edd_level1').val() + $('#edd_level2').val() + $('#edd_level3').val() + $('#edd_level4').val();
                    if(ddnotempty) {
						// To do -- ajax called to load the tree
						$.when( 
							$.ajax({
                				url: '{{ "/" . request()->segment(1) . "/excuseemployees/org-tree/2" }}'
								, type: 'GET'
								, data: $("#notify-form").serialize()
								, dataType: 'html'
								, beforeSend: function() {
									$("#etree-loading-spinner").show();                    
								}
								, success: function (result) {
									$('#enav-tree').html(''); 
									$('#enav-tree').html(result);
									$('#enav-tree').attr('loaded','loaded');
								}
								, complete: function() {
									$("#etree-loading-spinner").hide();
								}
								, error: function () {
									alert("error");
									$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
								}
							})
						).then(function( data, textStatus, jqXHR ) {
							enodes = $('#eaccordion-level0 input:checkbox');
							eredrawTreeCheckboxes();	
						}); 
					} else {
						$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Please apply the organization filter before creating a tree view.');
					};
				});

			});

            $('#editModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var excused_flag = button.data('excused').excused_flag;
                var excused_reason_id = button.data('excused').reason_id;
                var excused_reason_id2 = button.data('excused').reason_id;
                var employee_name = button.data('employee_name');
                var excused_type = button.data('excused-type');
                var user_id = button.data('user-id');
                var current_status = button.data('current_status');
                $('#excusedDetailLabel').text('Edit Employee Excuse:  '+employee_name);
                $("#editModal").find(".employee_name").html(employee_name);
                $("#editModal").find("input[name=id]").val(user_id);
                $("#editModal").find("input[name=user_id]").val(user_id);
                $("#editModal").find("select[name=excused_flag]").val(excused_flag ?? 0);
                $("#editModal").find("select[name=excused_flag2]").val(1);
                $("#editModal").find("select[name=excused_reason_id]").val(excused_reason_id ? (excused_reason_id < 3 ? 3 : excused_reason_id) : 3);
                $("#editModal").find("select[name=excused_flag2]").attr('disabled', true);
                $("#editModal").find("select[name=excused_reason_id2]").attr('disabled', true);
                if (excused_type == 'A') {
                    $("#editModal").find("select[name=excused_reason_id2]").val(current_status == 'A' ? excused_reason_id2 : 1);
                    $("#editModal").find("select[name=excused_reason_id]").attr('disabled', true);
                    $("#divReason1").hide();
                    $("#divReason2").show();
                    $("#divExcuse1").hide();
                    $("#divExcuse2").show();
                    $("#editModal").find("button[name=saveExcuseButton]").attr('disabled', true);
                } else {
                    $("#editModal").find("select[name=excused_reason_id]").attr('disabled', false);
                    $("#divReason1").show();
                    $("#divReason2").hide();
                    $("#divExcuse1").show();
                    $("#divExcuse2").hide();
                    $("#editModal").find("button[name=saveExcuseButton]").attr('disabled', false);
                }
            });
		</script>
	</x-slot>

</x-side-layout>

<style>
    .alert-danger {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    
</style> 