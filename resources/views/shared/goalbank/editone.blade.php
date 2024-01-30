<x-side-layout title="{{ __('Goal Bank - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Goal Bank</h3>
        </div>
    </div>

	<small><a href=" {{ route(request()->segment(1).'.goalbank.manageindex') }}" class="btn btn-md btn-primary"><i class="fa fa-arrow-left"></i> Back to goals</a></small>

	<br><br>

	<h4>Edit: {{ $goaldetail->title }}</h4>

	<form id="notify-form" action="{{ route(request()->segment(1).'.goalbank.updategoalone', $request->id) }}" method="post">
		@csrf

		<div class="card">
			<div class="card-body">
				<label label="Current Audience" name="current_audience" > Current Individual Audience </label>
				@include('shared.goalbank.partials.filter')
				<div class="p-3">  
					<table class="table table-bordered currenttable table-striped" id="currenttable" style="width: 100%; overflow-x: auto; "></table>
				</div>
			</div>
		</div>

		<input type="hidden" id="selected_emp_ids" name="selected_emp_ids" value="">
		<input type="hidden" id="goal_id" name="goal_id" value={{$goaldetail->id}}>
		<input type="hidden" id="aselected_emp_ids" name="aselected_emp_ids" value="">
		<input type="hidden" id="aselected_org_nodes" name="aselected_org_nodes" value="">
		<input type="hidden" id="selected_org_nodes" name="selected_org_nodes" value="">

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
		<h6 class="text-bold">Step 1. Select additional individual audience</h6>
		<br>

		@include('shared.goalbank.partials.afilter')

        <div class="p-3">
            <nav>
                <div class="nav nav-tabs" id="anav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="anav-list-tab" data-toggle="tab" href="#anav-list" role="tab" aria-controls="anav-list" aria-selected="true">List</a>
                    <a class="nav-item nav-link" id="anav-tree-tab" data-toggle="tab" href="#anav-tree" role="tab" aria-controls="anav-tree" aria-selected="false">Tree</a>
                </div>
            </nav>
            <div class="tab-content" id="anav-tabContent">
                <div class="tab-pane fade show active" id="anav-list" role="tabpanel" aria-labelledby="anav-list-tab">
                    @include('shared.goalbank.partials.arecipient-list')
                </div>
                <div class="tab-pane fade" id="anav-tree" role="tabpanel" aria-labelledby="anav-tree-tab" loaded="">
                    {{-- <div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" id="tree-loading-spinner" role="status" style="display:none">
                        <span class="sr-only">Loading...</span>
                    </div> --}}
                </div>
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
			<button class="btn btn-secondary mt-2" id="obtn_cancel_send" name="obtn_cancel_send">Cancel</button>
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
			let g_employees_by_org = [];

			let ag_matched_employees = {!!json_encode($amatched_emp_ids)!!};
			let ag_selected_employees = {!!json_encode($aold_selected_emp_ids)!!};
			let ag_selected_orgnodes = {!!json_encode($aold_selected_org_nodes)!!};
			let ag_employees_by_org = [];

			function confirmSaveChangesModal(){
				$('#obtn_send').prop('disabled',true);
				let count = ag_selected_employees.length;
				$('#aselected_emp_ids').val(ag_selected_employees);

				if (count == 0) {
					$('#saveGoalModal .modal-body p').html('Are you sure you want to update goal without additional audience?<br><br>Only click \"Update Goal\" one time. It can take up to 30 seconds to process. Clicking multiple times will generate multiple copies of the goal and all notifications.');
				} else {
					$('#saveGoalModal .modal-body p').html('Are you sure you want to assign this goal to <b>'+count+'</b> additional employees?<br><br>Only click \"Update Goal\" one time. It can take up to 30 seconds to process. Clicking multiple times will generate multiple copies of the goal and all notifications.');
				}
				$('#saveGoalModal').modal();
			}

			$(document).ready(function(){

				$('#currenttable').DataTable ( {
					processing: true,
					serverSide: true,
					scrollX: true,
					stateSave: true,
					deferRender: true,
					searching: false,
					ajax: {
						url: "{{ route(request()->segment(1).'.goalbank.getgoalinds', $goaldetail->id) }}",
						data: function(d) {
							d.dd_level0 = $('#dd_level0').val();
							d.dd_level1 = $('#dd_level1').val();
							d.dd_level2 = $('#dd_level2').val();
							d.dd_level3 = $('#dd_level3').val();
							d.dd_level4 = $('#dd_level4').val();
							d.dd_superv = $('#dd_superv').val();
							d.criteria = $('#criteria').val();
							d.search_text = $('#search_text').val();
						}
					},
					columns: [
						{title: 'ID', ariaTitle: 'ID', target: 0, orderData: [0, 12], type: 'string', data: 'employee_id', name: 'employee_id', searchable: true, className: 'dt-nowrap'},
						{title: 'Name', ariaTitle: 'Employee Name', target: 1, orderData: [1, 12], type: 'string', data: 'employee_name', name: 'employee_name', searchable: true, className: 'dt-nowrap'},
						{title: 'Supervisor', ariaTitle: 'Supervisor', target: 2, orderData: [2, 12], type: 'string', data: 'isSupervisor', name: 'isSupervisor', searchable: true, className: 'dt-nowrap'},
						{title: 'Classification', ariaTitle: 'Classification', target: 3, orderData: [3, 12], type: 'string', data: 'jobcode_desc', name: 'jobcode_desc', searchable: true, className: 'dt-nowrap'},
						{title: 'Organization', ariaTitle: 'Organization', target: 4, orderData: [4, 12], type: 'string', data: 'organization', name: 'organization', searchable: true, className: 'dt-nowrap'},
						{title: 'Level 1', ariaTitle: 'Level 1', target: 5, orderData: [5, 12], type: 'string', data: 'level1_program', name: 'level1_program', searchable: true, className: 'dt-nowrap'},
						{title: 'Level 2', ariaTitle: 'Level 2', target: 6, orderData: [6, 12], type: 'string', data: 'level2_division', name: 'level2_division', searchable: true, className: 'dt-nowrap'},
						{title: 'Level 3', ariaTitle: 'Level 3', target: 7, orderData: [7, 12], type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true, className: 'dt-nowrap'},
						{title: 'Level 4', ariaTitle: 'Level 4', target: 8, orderData: [8, 12], type: 'string', data: 'level4', name: 'level4', searchable: true, className: 'dt-nowrap'},
						{title: 'Dept ID', ariaTitle: 'Dept ID', target: 9, orderData: [9, 12], type: 'string', data: 'deptid', name: 'deptid', searchable: true, className: 'dt-nowrap'},
						{title: 'Action', ariaTitle: 'Action', target: 10, orderData: [10, 12], type: 'string', data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-nowrap'},
						{title: 'Goal ID', ariaTitle: 'Goal ID', target: 1, orderData: [11, 12], type: 'num', data: 'goal_id', name: 'goal_id', searchable: false, visible: false, className: 'dt-nowrap'},
						{title: 'ID', ariaTitle: 'ID', target: 12, orderData: [12], type: 'num', data: 'share_id', name: 'share_id', searchable: false, visible: false, className: 'dt-nowrap'},
						]
				} );

				$( "#btn_send" ).click(function() {
					$('#saveGoalModal').modal('toggle');
				});

				$( "#btn_cancel_send" ).click(function() {
					$('#btn_send').prop('disabled',false);
					$('#obtn_send').prop('disabled',false);
				});

				$('#btn_search').click(function(e) {
					e.preventDefault();
					$('#currenttable').DataTable().rows().invalidate().draw();
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
					// console.log('Search Button Clicked');			
					// assign back the selected employees to server
					var text = JSON.stringify(ag_selected_employees);
					$('#aselected_emp_ids').val( text );
					var text2 = JSON.stringify(ag_selected_orgnodes);
					$('#aselected_org_nodes').val( text2 );
					// dd(g_selected_orgnodes);
					return true; // return false to cancel form action
				});

				// Tab  -- LIST Page  activate
				$("#anav-list-tab").on("click", function(e) {
					let ag_selected_employees = [];
					$('#aselected_emp_ids').val( null );
					table  = $('#aemployee-list-table').DataTable();
					table.rows().invalidate().draw();
				});

				// Tab  -- TREE activate
				$("#anav-tree-tab").on("click", function(e) {
					target = $('#anav-tree'); 
					let ag_selected_employees = [];
					// let aselected_emp_ids = [];
					$('#aselected_emp_ids').val( null );
                    ddnotempty = $('#add_level0').val() + $('#add_level1').val() + $('#add_level2').val() + $('#add_level3').val() + $('#add_level4').val();
                    if(ddnotempty) {
                        // To do -- ajax called to load the tree
                        if($.trim($(target).attr('loaded'))=='') {
                            $.when( 
                                $.ajax({
                					url: '{{ "/".request()->segment(1)."/goalbank/org-tree/3" }}',
                                    type: 'GET',
                                    data: $("#notify-form").serialize(),
                                    dataType: 'html',
                                    beforeSend: function() {
                                        $("#atree-loading-spinner").show();                    
                                    },
                                    success: function (result) {
                                        $(target).html(''); 
                                        $(target).html(result);

                                        $('#nav-tree').attr('loaded','loaded');
                                    },
                                    complete: function() {
                                        $("#atree-loading-spinner").hide();
                                    },
                                    error: function () {
                                        alert("error");
                                        $(target).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                    }
                                })
                                
                            ).then(function( data, textStatus, jqXHR ) {
                                anodes = $('#aaccordion-level0 input:checkbox');
                                aredrawTreeCheckboxes();	
                            }); 
                        
                        } else {
                            aredrawTreeCheckboxes();
                        }
                    } else {
						// alert("error");
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

				function aredrawTreeCheckboxes() {
					// redraw the selection 
					anodes = $('#aaccordion-level0 input:checkbox');
					$.each( anodes, function( index, chkbox ) {
						if (ag_employees_by_org.hasOwnProperty(chkbox.value)) {
							aall_emps = ag_employees_by_org[ chkbox.value ].map( function(x) {return x.employee_id} );
							b = aall_emps.every(v=> ag_selected_orgnodes.indexOf(v) !== -1);

							if (aall_emps.every(v=> ag_selected_orgnodes.indexOf(v) !== -1)) {
								$(chkbox).prop('checked', true);
								$(chkbox).prop("indeterminate", false);
							} else if (aall_emps.some(v=> ag_selected_orgnodes.indexOf(v) !== -1)) {
								$(chkbox).prop('checked', false);
								$(chkbox).prop("indeterminate", true);
							} else {
								$(chkbox).prop('checked', false);
								$(chkbox).prop("indeterminate", false);
							}
						} else {
							if ( $(chkbox).attr('name') == 'auserCheck[]') {
								if (ag_selected_orgnodes.includes(chkbox.value)) {
									$(chkbox).prop('checked', true);
								} else {
									$(chkbox).prop('checked', false);
								}
							}
						}
					});

					// reset checkbox state
					areverse_list = anodes.get().reverse();
					$.each( areverse_list, function( index, chkbox ) {
						if (ag_employees_by_org.hasOwnProperty(chkbox.value)) {
							pid = $(chkbox).attr('pid');
							do {
								value = '#aorgCheck' + pid;
								atoggle_indeterminate( value );
								pid = $('#aorgCheck' + pid).attr('pid');    
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
				function atoggle_indeterminate( prev_input ) {
					// Loop to checked the child
					var c_indeterminated = 0;
					var c_checked = 0;
					var c_unchecked = 0;
					prev_location = $(prev_input).parent().attr('href');
					anodes = $(prev_location).find("input:checkbox[name='aorgCheck[]']");
					$.each( anodes, function( index, chkbox ) {
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

				$('#abtn_search').click(function(e) {
					e.preventDefault();
					$('#aemployee-list-table').DataTable().rows().invalidate().draw();
					//Tree
					target = $('#anav-tree'); 
					ddnotempty = $('#add_level0').val() + $('#add_level1').val() + $('#add_level2').val() + $('#add_level3').val() + $('#add_level4').val();
					if(ddnotempty) {
						// To do -- ajax called to load the tree
						$.when( 
							$.ajax({
                				url: '{{ "/".request()->segment(1)."/goalbank/org-tree/3" }}',
								type: 'GET',
								data: $("#notify-form").serialize(),
								dataType: 'html',
								beforeSend: function() {
									$("#atree-loading-spinner").show();                    
								},
								success: function (result) {
									$('#anav-tree').html(''); 
									$('#anav-tree').html(result);
									$('#anav-tree').attr('loaded','loaded');
								},
								complete: function() {
									$("#atree-loading-spinner").hide();
								},
								error: function () {
									alert("error");
									$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
								}
							})
						).then(function( data, textStatus, jqXHR ) {
							//alert( jqXHR.status ); // Alerts 200
							anodes = $('#aaccordion-level0 input:checkbox');
							aredrawTreeCheckboxes();	
						}); 
					} else {
						$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Please apply the organization filter before creating a tree view.');
					}
				});

				$('#dd_level0').change(function (e){
					e.preventDefault();
				});

				$('#dd_level1').change(function (e){
					e.preventDefault();
				});

				$('#dd_level2').change(function (e){
					e.preventDefault();
				});

				$('#dd_level3').change(function (e){
					e.preventDefault();
				});

				$('#dd_level4').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});

				$('#criteria').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});

				$('#search_text').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});

				$('#search_text').keydown(function (e){
					if (e.keyCode == 13) {
						e.preventDefault();
						$('#btn_search').click();
					}
				});

				$('#dd_superv').change(function (e){
					e.preventDefault();
					$('#btn_search').click();
				});

				$('#add_level0').change(function (e) {
					e.preventDefault();
				});

				$('#add_level1').change(function (e) {
					e.preventDefault();
				});

				$('#add_level2').change(function (e) {
					e.preventDefault();
				});

				$('#add_level3').change(function (e) {
					e.preventDefault();
				});
				$('#add_level4').change(function (e) {
					e.preventDefault();
					//$('#abtn_search').click();
				});

				$('#acriteria').change(function (e){
					e.preventDefault();
					//$('#abtn_search').click();
				});

				$('#asearch_text').change(function (e){
					e.preventDefault();
					//$('#abtn_search').click();
				});

				$('#asearch_text').keydown(function (e){
					if (e.keyCode == 13) {
						e.preventDefault();
						$('#abtn_search').click();
					}
				});


				$('#add_superv').change(function (e){
					e.preventDefault();
					//$('#abtn_search').click();
				});

				$(window).on('beforeunload', function(){
					$('#pageLoader').show();
				});

				$(window).resize(function(){
					location.reload();
					return;
				}); 

			});
			// Model -- Confirmation Box

			var modalConfirm = function(callback) {
				$("#btn-confirm").on("click", function(){
					$("#mi-modal").modal('show');
				});
				$("#modal-btn-si").on("click", function(){
					callback(true);
					$("#mi-modal").modal('hide');
				});
				
				$("#modal-btn-no").on("click", function(){
					callback(false);
					$("#mi-modal").modal('hide');
				});
			};

		</script>
	</x-slot>

</x-side-layout>