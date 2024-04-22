<x-side-layout title="{{ __('Goal Bank - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Goal Bank</h3>
        </div>
    </div>

	<small><a href=" {{ route(request()->segment(1).'.goalbank.manageindex') }}" class="btn btn-md btn-primary"><i class="fa fa-arrow-left"></i> Back to goals</a></small>

	<br><br>

	<h4>Edit: {{ $goaldetail->title }}</h4>

	<form id="notify-form" action="{{ route(request()->segment(1).'.goalbank.updategoal', $request->id) }}" method="post">
		@csrf

		<div class="card">
			<div class="card-body">
				<label label="Current Audience" name="current_audience" > Current Organizational Audience </label>
				@include('shared.goalbank.partials.filter')
				<div class="p-3">  
					<table class="table table-bordered currenttable table-striped" id="currenttable" style="width: 100%; overflow-x: auto; "></table>
				</div>
			</div>
		</div>

		<input type="hidden" id="selected_emp_ids" name="selected_emp_ids" value="">
		<input type="hidden" id="goal_id" name="goal_id" value={{$goaldetail->id}}>

		<!----modal starts here--->
		<div id="saveGoalModal" class="modal" role='dialog'>
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
						<button class="btn btn-primary mt-2" type="submit" id="btn_send" name="btn_send" value="btn_send">Update Goal</button>
						<button class="btn btn-secondary" type="button" id="btn_cancel_send" name="btn_cancel_send" data-dismiss="modal">Cancel</button>
					</div>
					
				</div>
			</div>
		</div>
		<!--Modal ends here--->	

		<br>
		<h6 class="text-bold">Step 1. Select additional organizational audience</h6>
		<br>

		<input type="hidden" id="selected_org_nodes" name="selected_org_nodes" value="">
		<input type="hidden" id="selected_inherited" name="selected_inherited" value="">

		@include('shared.goalbank.partials.filter2')

		<div id="enav-tree" aria-labelledby="enav-tree-tab" loaded="loaded">
			<div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" id="etree-loading-spinner" role="status" style="display:none">
				<span class="sr-only">Loading...</span>
			</div>
		</div>

		<br>
		<h6 class="text-bold">Step 2. Finish</h6>
		<div class="card col-md-12">
			<div class="card-body">
				<div class="row">
					<div class="col">
						<i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="All employees will receive a notification on their PDP homepage about the goal. By selecting &quot;Yes&quot; to this question, you will also send an alert to their email prompting them to log in and view the new goal."> </i>
						<b>Send an email notification to new employees about this existing goal?</b>
					</div>
				</div>
				<div class="row">		
					<div class="col-md-12 mb-12">		
						<div class="form-check">
							<input class="form-check-input" type="radio" name="emailit" id="emailit1" value="1">
							<label class="form-check-label" for="emailit1">
								Yes
							</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="emailit" id="emailit2" value="0" checked>
							<label class="form-check-label" for="emailit2">
								No
							</label>
						</div>				
					</div>
				</div>
			</div>
		</div>	
		<br>
		<div class="col-md-3 mb-2">
			<button class="btn btn-primary mt-2" type="button" onclick="confirmSaveChangesModal()" id="obtn_send" name="obtn_send" value="btn_send">Save Changes</button>
			<button class="btn btn-secondary mt-2" type="button" id="obtn_cancel_send" name="obtn_cancel_send" onClick="window.location='{{ URL::previous() }}'">Cancel</button>
		</div>

	</form>

	<h6 class="m-20">&nbsp;</h6>
	<h6 class="m-20">&nbsp;</h6>
	<h6 class="m-20">&nbsp;</h6>


    @push('css')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
    @endpush

	<x-slot name="css">
		<style>

            #currenttable_filter label {
                text-align: right !important;
            }

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
		<script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
		{{-- <script src="//cdn.ckeditor.com/4.17.2/standard/ckeditor.js"></script> --}}

		<script>				
				$('body').popover({
					selector: '[data-toggle]',
					trigger: 'click',
				});
                
				$('.modal').popover({
					selector: '[data-toggle-select]',
					trigger: 'click',
				});

				$(".tags").multiselect({
                	enableFiltering: true,
                	enableCaseInsensitiveFiltering: true,
					nonSelectedText: null,
            	});

				$('body').on('click', function (e) {
                $('[data-toggle=popover]').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    	if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    	}
                	});
				});		
		</script>

		<script>
			let g_matched_employees = {!!json_encode($matched_emp_ids)!!};
			let g_selected_employees = {!!json_encode($old_selected_emp_ids)!!};
			let g_selected_orgnodes = {!!json_encode($old_selected_org_nodes)!!};
			let g_selected_inherited = {!! json_encode($old_selected_inherited) !!};
			let eg_selected_orgnodes = {!!json_encode($eold_selected_org_nodes)!!};
			let eg_selected_inherited = {!!json_encode($eold_selected_inherited)!!};
			let g_employees_by_org = [];

			function confirmSaveChangesModal(){
				$('#obtn_send').prop('disabled',true);
				count = g_selected_orgnodes.length + eg_selected_inherited.length;
				if (count == 0) {
					$('#saveGoalModal .modal-body p').html('Are you sure you want to update goal without additional audience?<br><br>Only click \"Update Goal\" one time. It can take up to 30 seconds to process. Clicking multiple times will generate multiple copies of the goal and all notifications.');
				} else {
					$('#saveGoalModal .modal-body p').html('Are you sure you want to add <b>'+count+'</b> business units to the audience?<br><br>Only click \"Update Goal\" one time. It can take up to 30 seconds to process. Clicking multiple times will generate multiple copies of the goal and all notifications.');
				}
				$('#saveGoalModal').modal();
			}

			$(document).ready(function(){

				$('#blank5th').hide();
				$('#criteria_group').hide();
				$('#search_text_group').hide();
				$('#eblank5th').hide();
				$('#ecriteria_group').hide();
				$('#esearch_text_group').hide();

				var table = $('#currenttable').DataTable
				(
					{
						processing: true,
						serverSide: true,
						scrollX: true,
						stateSave: true,
						deferRender: true,
						searching: false,
						ajax: {
							url: "{{ route(request()->segment(1).'.goalbank.getgoalorgs', $goaldetail->id) }}",
							data: function(d) {
								d.dd_level0 = $('#dd_level0').val();
								d.dd_level1 = $('#dd_level1').val();
								d.dd_level2 = $('#dd_level2').val();
								d.dd_level3 = $('#dd_level3').val();
								d.dd_level4 = $('#dd_level4').val();
								d.criteria = $('#criteria').val();
								d.search_text = $('#search_text').val();
							}
						},
						columns: [
							{title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', searchable: true, className: 'dt-nowrap'},
							{title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', searchable: true, className: 'dt-nowrap'},
							{title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', searchable: true, className: 'dt-nowrap'},
							{title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true, className: 'dt-nowrap'},
							{title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', searchable: true, className: 'dt-nowrap'},
							{title: 'Inherited', ariaTitle: 'Inherited', target: 0, type: 'string', data: 'inherited', name: 'inherited', searchable: false},
							{title: 'Action', ariaTitle: 'Action', target: 0, type: 'string', data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-nowrap'},
							{title: 'Goal ID', ariaTitle: 'Goal ID', target: 0, type: 'num', data: 'goal_id', name: 'goal_id', searchable: false, visible: false, className: 'dt-nowrap'},
							{title: 'ID', ariaTitle: 'ID', target: 0, type: 'num', data: 'id', name: 'id', searchable: false, visible: false, className: 'dt-nowrap'},
						]
					}
				);

				$( "#btn_send" ).click(function() {
					$('#saveGoalModal').modal('toggle');
				});

				$( "#btn_cancel_send" ).click(function() {
					$('#btn_send').prop('disabled',false);
					$('#obtn_send').prop('disabled',false);
				});

				$('#delete_org').click(function(e) {
					e.preventDefault();
					$('#btn_search').hide();
				});

				$('#btn_search').click(function(e) {
					e.preventDefault();
					$('#currenttable').DataTable().destroy();
					$('#currenttable').empty();
					$('#currenttable').DataTable(
						{
							processing: true,
							serverSide: true,
							scrollX: true,
							stateSave: true,
							deferRender: true,
							ajax: {
								url: "{{ route(request()->segment(1).'.goalbank.getgoalorgs', $goaldetail->id) }}",
								type: 'GET',
								data: function(d) {
									d.dd_level0 = $('#dd_level0').val();
									d.dd_level1 = $('#dd_level1').val();
									d.dd_level2 = $('#dd_level2').val();
									d.dd_level3 = $('#dd_level3').val();
									d.dd_level4 = $('#dd_level4').val();
									d.criteria = $('#criteria').val();
									d.search_text = $('#search_text').val();
								}
							},
							columns: [
								{title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', searchable: true},
								{title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', searchable: true},
								{title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', searchable: true},
								{title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true},
								{title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', searchable: true},
								{title: 'Inherited', ariaTitle: 'Inherited', target: 0, type: 'string', data: 'inherited', name: 'inherited', searchable: false},
								{title: 'Action', ariaTitle: 'Action', target: 0, type: 'string', data: 'action', name: 'action', orderable: false, searchable: false},
								{title: 'Goal ID', ariaTitle: 'Goal ID', target: 0, type: 'num', data: 'goal_id', name: 'goal_id', searchable: false, visible: false},
							]
						}
					);
				});

				$('#dd_level0').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});

				$('#dd_level1').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});

				$('#dd_level2').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});

				$('#dd_level3').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});
				$('#dd_level4').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});

				$('#btn_search_reset').click(function(e) {
					e.preventDefault();
					$('#criteria').val('all');
					$('#search_text').val(null);
					$('#dd_level0').val(null);
					$('#dd_level1').val(null);
					$('#dd_level2').val(null);
					$('#dd_level3').val(null);
					$('#dd_level4').val(null);
					$('#btn_search').click();
        		});

				$(".tags").multiselect({
                	enableFiltering: true,
                	enableCaseInsensitiveFiltering: true
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
					var text3 = JSON.stringify(eg_selected_inherited);
					$('#selected_inherited').val( text3 );
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
                					url: '{{ "/".request()->segment(1)."/goalbank/org-tree/1" }}',
                                    type: 'GET',
                                    data: $("#notify-form").serialize(),
                                    dataType: 'html',
                                    beforeSend: function() {
                                        $("#tree-loading-spinner").show();                    
                                    },
                                    success: function (result) {
                                        $(target).html(''); 
                                        $(target).html(result);

                                        $('#nav-tree').attr('loaded', 'loaded');
                                    },
                                    complete: function() {
                                        $(".tree-loading-spinner").hide();
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
                            redrawTreeCheckboxes();
                        }
                    } else {
						$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Please apply the organization filter before creating a tree view.');
					}
				});

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

				$('#edd_level0').change(function (e){
					e.preventDefault();
				});

				$('#edd_level1').change(function (e){
					e.preventDefault();
				});

				$('#edd_level2').change(function (e){
					e.preventDefault();
				});

				$('#edd_level3').change(function (e){
					e.preventDefault();
				});
				$('#edd_level4').change(function (e){
					e.preventDefault();
					$('#ebtn_search').click();
				});

				$('#ebtn_search_reset').click(function(e) {
					e.preventDefault();
					$('#ecriteria').val('all');
					$('#esearch_text').val(null);
					$('#edd_level0').val(null);
					$('#edd_level1').val(null);
					$('#edd_level2').val(null);
					$('#edd_level3').val(null);
					$('#edd_level4').val(null);
       			});

				$('#ebtn_search').click(function(e) {
					target = $('#enav-tree'); 
					ddnotempty = $('#edd_level0').val() + $('#edd_level1').val() + $('#edd_level2').val() + $('#edd_level3').val() + $('#edd_level4').val();
					if(ddnotempty) {
						// To do -- ajax called to load the tree
						$.when( 
							$.ajax({
								url: '{{ "/".request()->segment(1)."/goalbank/org-tree/2" }}',
                				// url: '{{ "/".request()->segment(1)."/goalbank/eorg-tree" }}',
								type: 'GET',
								data: $("#notify-form").serialize(),
								dataType: 'html',

								beforeSend: function() {
									$("#etree-loading-spinner").show();                    
								},
								success: function (result) {
									$('#enav-tree').html(''); 
									$('#enav-tree').html(result);
									$('#enav-tree').attr('loaded','loaded');
								},
								complete: function() {
									$("#etree-loading-spinner").hide();
								},
								error: function () {
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
					}
				});
			});

			$(window).on('beforeunload', function(){
				$('#pageLoader').show();
			});

		</script>
	</x-slot>

</x-side-layout>