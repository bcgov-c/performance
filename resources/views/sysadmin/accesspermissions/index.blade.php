<x-side-layout title="{{ __('Access and Permissions - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Access and Permissions</h3>
            @include('sysadmin.accesspermissions.partials.tabs')
        </div>
    </div>

	<p class="px-3">Follow the steps below to select an employee and assign them additional functions within the Performance platform.</p>


	<br>
	<h6 class="text-bold">Step 1. Select employees to assign</h6>
	<br>

	<form id="notify-form" action="{{ route('sysadmin.accesspermissions.saveaccess') }}" method="post">
		@csrf
		<input type="hidden" id="selected_emp_ids" name="selected_emp_ids" value="">
		{{-- <input type="hidden" id="selected_org_nodes" name="selected_org_nodes" value=""> --}}


		<!----modal starts here--->
		<div id="saveAccessModal" class="modal" role='dialog'>
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
						<button class="btn btn-primary mt-2" type="submit" name="btn_send" value="btn_send">Grant Access</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					</div>
					
				</div>
			</div>
		</div>
		<!--Modal ends here--->	
	
		@include('sysadmin.accesspermissions.partials.filter')

        <div class="p-3">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-list-tab" data-toggle="tab" href="#nav-list" role="tab" aria-controls="nav-list" aria-selected="true">List</a>
                    <a class="nav-item nav-link" id="nav-tree-tab" data-toggle="tab" href="#nav-tree" role="tab" aria-controls="nav-tree" aria-selected="false">Tree</a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
                    @include('sysadmin.accesspermissions.partials.recipient-list')
                </div>
                <div class="tab-pane fade" id="nav-tree" role="tabpanel" aria-labelledby="nav-tree-tab" loaded="">
                    <div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" id="tree-loading-spinner" role="status" style="display:none">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
		<br>
		<h6 class="text-bold">Step 2. Select access level and access description</h6> 
		<br>
		<div class="row  p-3">
			<div class="col col-4">
				<label for='accessselect' title='Access Level Tooltip'>Access Level
					<select name="accessselect" class="form-control" id="accessselect">
						@foreach($roles as $rid => $desc)
							<option value = {{ $rid }} > {{ $desc }} </option>
						@endforeach
					</select>
				</label>
			</div>
			<div class="col col-8">
				<b> Access Description </b>
				<i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="List org levels (i.e. BCPSA - All; or SDPR - Corporate Services) followed by the role of the individual (i.e. SHR, Ambassador, Reporting). Full example: BCPSA - Corporate Workforce Strategies: Ambassador."> </i>
				<x-input id="reason" name="reason"/>
			</div>
		</div>

		<br>
		<h6 class="text-bold">Step 3. Select ministries to assign to (for HR Administrator only)</h6>
		<br>

		<input type="hidden" id="selected_org_nodes" name="selected_org_nodes" value="">
		<input type="hidden" id="selected_inherited" name="selected_inherited" value="">

		@include('sysadmin.accesspermissions.partials.filter2')

		<div id="enav-tree" aria-labelledby="enav-tree-tab" loaded="loaded">
			<div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" id="etree-loading-spinner" role="status" style="display:none">
				<span class="sr-only">Loading...</span>
			</div>
		</div>

		<br>
		<h6 class="text-bold">Step 4. Assign selected employees</h6>
		<br>
		<div class="col-md-3 mb-2">
			<button class="btn btn-primary mt-2" type="button" onclick="confirmSaveAccessModal()" name="btn_send" value="btn_send">Assign Employees</button>
			<button class="btn btn-secondary mt-2" type="button" onclick="window.location.reload()">Cancel</button>
		</div>

	</form>

	<h6 class="m-20">&nbsp;</h6>
	<h6 class="m-20">&nbsp;</h6>
	<h6 class="m-20">&nbsp;</h6>


	<x-slot name="css">
		<style>

			.select2-container .select2-selection--single {
				height: 38px !important;
			}

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

			$('body').popover({
				selector: '[data-toggle]',
				trigger: 'click',
			});
			
			$('.modal').popover({
				selector: '[data-toggle-select]',
				trigger: 'click',
			});

			$('body').on('click', function (e) {
			$('[data-toggle=popover]').each(function () {
				// hide any open popovers when the anywhere else in the body is clicked
				if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
					$(this).popover('hide');
				}
				});
			});	
			$('body').on('click', function (e) {
			$('[data-toggle=dropdown]').each(function () {
				// hide any open popovers when the anywhere else in the body is clicked
				if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
					$(this).popover('hide');
				}
				});
			});	

		</script>

		<script>

			g_matched_employees = {!! json_encode($matched_emp_ids) !!};
			g_selected_employees = {!! json_encode($old_selected_emp_ids) !!};
			g_selected_orgnodes = {!! json_encode($old_selected_org_nodes) !!};
			g_selected_inherited = {!! json_encode($old_selected_inherited) !!};
			g_employees_by_org = [];

			function confirmSaveAccessModal(){
				count = g_selected_employees.length;
				if (count == 0) {
					$('#saveAccessModal .modal-body p').html('Are you sure you want to grant administrator access ?');
				} else {
					$('#saveAccessModal .modal-body p').html('Are you sure you want to grant administrator access to <b>' + count + '</b> selected employee(s)?');
				}
				$('#saveAccessModal').modal();
			}

			$(document).ready(function(){

				$('#pageLoader').hide();

				function navTreeActive() {
					return $('#nav-tree').attr('class').search(/active/i) > 0 ?? false;
				}

				function navListActive() {
					return $('#nav-list').attr('class').search(/active/i) > 0 ?? false;
				}

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
					var text3 = JSON.stringify(g_selected_inherited);
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
                                    url: '/sysadmin/accesspermissions/org-tree/1',
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
							if ( $(chkbox).attr('name') == 'euserCheck[]') {
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
					g_selected_orgnodes = [];
					g_selected_inherited = [];
					target = $('#enav-tree'); 
					ddnotempty = $('#edd_level0').val() + $('#edd_level1').val() + $('#edd_level2').val() + $('#edd_level3').val() + $('#edd_level4').val();
					if(ddnotempty) {
						// To do -- ajax called to load the tree
						$.when( 
							$.ajax({
								url: '/sysadmin/accesspermissions/org-tree/2',
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
									// Create New Access Page, Step 3
									$(target).removeAttr('loaded');
									alert("error");
									$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
								}
							})
							
						).then(function( data, textStatus, jqXHR ) {
							enodes = $('#eaccordion-level0 input:checkbox');
							eredrawTreeCheckboxes();	
						}); 
					} else {
						$(target).removeAttr('loaded');
						$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Please apply the organization filter before creating a tree view.');
					};
				});

				$(window).on('beforeunload', function(){
					$('#pageLoader').show();
				});

			});

		</script>
	</x-slot>

</x-side-layout>