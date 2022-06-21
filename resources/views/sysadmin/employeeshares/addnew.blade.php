<x-side-layout title="{{ __('Dashboard') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Shared Employees</h3>
            @include('sysadmin.employeeshares.partials.tabs')
        </div>
    </div>
	<p class="px-3">Supervisors and Ministry Administrators may share an employee's PDP profile with another supervisor, 
		or staff who normally handle employees' permanent personnel records (ie. Public Service Agency) for a legitimate 
		business reason; such as shared supervisory duties.  An employee may wish to share their profile with someone 
		other than a direct supervisor (for example, a hiring manager).  In order to do this - <b>the employee's consent 
		is required</b>.</p>
	<p class="px-3">To continue, please use the functions below to select the employee profiles that you would like to share,
		the supervisor you would like to share the profiles with, which elements you would like to share, and your reason 
		for sharing the profile.</p>

    <form id="share-form" action="{{ route('sysadmin.employeeshares.saveall') }}" method="post">
        @csrf

        <div class="container-fluid">
            <br>
            <h6 class="text-bold">Step 1. Select employees to share</h6>
            <br>
            <input type="hidden" id="selected_org_nodes" name="selected_org_nodes" value="">
            <input type="hidden" id="selected_emp_ids" name="selected_emp_ids" value="">
            @include('sysadmin.employeeshares.partials.loader')

            <div class="p-3">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-list-tab" data-toggle="tab" href="#nav-list" role="tab" aria-controls="nav-list" aria-selected="true">List</a>
                        <a class="nav-item nav-link" id="nav-tree-tab" data-toggle="tab" href="#nav-tree" role="tab" aria-controls="nav-tree" aria-selected="false">Tree</a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
                        @include('sysadmin.employeeshares.partials.recipient-list')
                    </div>
                    <div class="tab-pane fade" id="nav-tree" role="tabpanel" aria-labelledby="nav-tree-tab" loaded="">

                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <br>
            <h6 class="text-bold">Step 2. Select who you would like to share the selected employee(s) with</h6> 
            <br>
            <input type="hidden" id="eselected_org_nodes" name="eselected_org_nodes" value="">
            <input type="hidden" id="eselected_emp_ids" name="eselected_emp_ids" value="">

            @include('sysadmin.employeeshares.partials.loader2')

            <div class="p-3">
                <nav>
                    <div class="nav nav-tabs" id="enav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="enav-list-tab" data-toggle="tab" href="#enav-list" role="tab" aria-controls="enav-list" aria-selected="true">List</a>
                        <a class="nav-item nav-link" id="enav-tree-tab" data-toggle="tab" href="#enav-tree" role="tab" aria-controls="enav-tree" aria-selected="false">Tree</a>
                    </div>
                </nav>
                <div class="tab-content" id="enav-tabContent">
                    <div class="tab-pane fade show active" id="enav-list" role="tabpanel" aria-labelledby="enav-list-tab">
                        @include('sysadmin.employeeshares.partials.erecipient-list')
                    </div>
                    <div class="tab-pane fade" id="enav-tree" role="tabpanel" aria-labelledby="enav-tree-tab" loaded="">
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <br>
            <h6 class="text-bold">Step 3. Enter sharing details</h6> 
            <br>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col col-2">
                           <label for='elements' title='Items to Share Tooltip'>Items to Share
                                <select name="input_elements" class="form-control" id="input_elements" >
                                    <option value = 0 > Both </option>
                                    <option value = 1 > Goal </option>
                                    <option value = 2 > Conversation </option>
                                </select>
 					            <small  class="text-danger error-target_date"></small>
                            </label>
                       </div>
                        <div class="col col-10">
                            <x-input id="reason" name="input_reason" label="Reason for sharing" data-toggle="tooltip" data-placement="top" data-trigger="manual" tooltip="Reason tooltip"/>
					        <small  class="text-danger error-target_date"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <br>
            <h6 class="text-bold">Step 4. Select selected profile(s)</h6>
            <br>
            <div class="col-md-3 mb-2">
                <button class="btn btn-primary mt-2" type="submit" onclick="confirmSaveAllModal()" name="btn_send" value="btn_send">Share</button>
                <button class="btn btn-secondary mt-2">Cancel</button>
            </div>
        </div>

        <!----modal starts here--->
        <div id="saveAllModal" class="modal" role='dialog'>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure to send out this message ?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary mt-2" type="submit" name="btn_send" value="btn_send">Share</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                    
                </div>
            </div>
        </div>
        <!--Modal ends here--->	

    </form>

    <h6 class="m-20">&nbsp;</h6>
    <h6 class="m-20">&nbsp;</h6>
    <h6 class="m-20">&nbsp;</h6>

    <x-slot name="css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        {{-- <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}"> --}}
    </x-slot>

    <x-slot name="js">
	
        <script src="//cdn.ckeditor.com/4.17.2/standard/ckeditor.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
        <script>
            let g_matched_employees = {!!json_encode($matched_emp_ids)!!};
            let eg_matched_employees = {!!json_encode($ematched_emp_ids)!!};
            let g_selected_employees = {!!json_encode($old_selected_emp_ids)!!};
            let eg_selected_employees = {!!json_encode($eold_selected_emp_ids)!!};
            let g_employees_by_org = [];
            let eg_employees_by_org = [];
            let g_selected_orgnodes = [];
            let eg_selected_orgnodes = [];

            function confirmSaveAllModal(){
				$('#saveAllModal .modal-body p').html('Are you sure to share ?');
				$('#saveAllModal').modal();
			}



            $(document).ready(function(){

                // $(".items-to-share-edit").multiselect({
                //     allSelectedText: 'All',
                //     selectAllText: 'All',
                //     includeSelectAllOption: true
                // });

                $('#employee-list-table').DataTable( {
                    scrollX: true,
                    retrieve: true,
                    searching: false,
                    processing: true,
                    serverSide: true,
                    select: true,
                    order: [[1, 'asc']],
                    ajax: {
                        url: '{{ route('sysadmin.employeeshares.employee.list') }}',
                        data: function (d) {
                            d.dd_level0 = $('#dd_level0').val();
                            d.dd_level1 = $('#dd_level1').val();
                            d.dd_level2 = $('#dd_level2').val();
                            d.dd_level3 = $('#dd_level3').val();
                            d.dd_level4 = $('#dd_level4').val();
                            d.criteria = $('#criteria').val();
                            d.search_text = $('#search_text').val();
                        }
                    },
                    "fnDrawCallback": function() {
                        list = ( $('#employee-list-table input:checkbox') );
                        $.each(list, function( index, item ) {
                            var index = $.inArray( item.value , g_selected_employees);
                            if ( index === -1 ) {
                                $(item).prop('checked', false); // unchecked
                            } else {
                                $(item).prop('checked', true);  // checked 
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
                    },
                    "rowCallback": function( row, data ) {
                    },
                    columns: [
                        {title: '<input name="select_all" value="1" id="employee-list-select-all" type="checkbox" />', ariaTitle: 'employee-list-select-all', target: 0, type: 'string', data: 'select_users', name: 'select_users', orderable: false, searchable: false},
                        {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_id', className: 'dt-nowrap'},
                        {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_name', className: 'dt-nowrap'},
                        {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc', name: 'jobcode_desc', className: 'dt-nowrap'},
                        // {title: 'Email', ariaTitle: 'Email', target: 0, type: 'string', data: 'employee_email', name: 'employee_email', className: 'dt-nowrap'},
                        {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', className: 'dt-nowrap'},
                        {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', className: 'dt-nowrap'},
                        {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', className: 'dt-nowrap'},
                        {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', className: 'dt-nowrap'},
                        {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', className: 'dt-nowrap'},
                        {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'deptid', className: 'dt-nowrap'},
                    ],
                });

                $('#eemployee-list-table').DataTable( {
                    scrollX: true,
                    retrieve: true,
                    searching: false,
                    processing: true,
                    serverSide: true,
                    select: true,
                    order: [[1, 'asc']],
                    ajax: {
                        url: '{{ route('sysadmin.employeeshares.eemployee.list') }}',
                        data: function (d) {
                            d.edd_level0 = $('#edd_level0').val();
                            d.edd_level1 = $('#edd_level1').val();
                            d.edd_level2 = $('#edd_level2').val();
                            d.edd_level3 = $('e#dd_level3').val();
                            d.edd_level4 = $('#edd_level4').val();
                            d.ecriteria = $('#ecriteria').val();
                            d.esearch_text = $('#esearch_text').val();
                        }
                    },
                    "fnDrawCallback": function() {
                        list = ( $('#eemployee-list-table input:checkbox') );
                        $.each(list, function( index, item ) {
                            var index = $.inArray( item.value , eg_selected_employees);
                            if ( index === -1 ) {
                                $(item).prop('checked', false); // unchecked
                            } else {
                                $(item).prop('checked', true);  // checked 
                            }
                        });
                        // update the check all checkbox status 
                        if (eg_selected_employees.length == 0) {
                            $('#eemployee-list-select-all').prop("checked", false);
                            $('#eemployee-list-select-all').prop("indeterminate", false);   
                        } else if (eg_selected_employees.length == eg_matched_employees.length) {
                            $('#eemployee-list-select-all').prop("checked", true);
                            $('#eemployee-list-select-all').prop("indeterminate", false);   
                        } else {
                            $('#eemployee-list-select-all').prop("checked", false);
                            $('#eemployee-list-select-all').prop("indeterminate", true);    
                        }
                    },
                    "rowCallback": function( row, data ) {
                    },
                    columns: [
                        {title: '<input name="eselect_all" value="1" id="eemployee-list-select-all" type="checkbox" />', ariaTitle: 'eemployee-list-select-all', target: 0, type: 'string', data: 'eselect_users', name: 'eselect_users', orderable: false, searchable: false},
                        {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'eemployee_id', name: 'eemployee_id', className: 'dt-nowrap'},
                        {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'eemployee_name', name: 'eemployee_name', className: 'dt-nowrap'},
                        {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'ejobcode_desc', name: 'ejobcode_desc', className: 'dt-nowrap'},
                        // {title: 'Email', ariaTitle: 'Email', target: 0, type: 'string', data: 'eemployee_email', name: 'eemployee_email', className: 'dt-nowrap'},
                        {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'eorganization', name: 'eorganization', className: 'dt-nowrap'},
                        {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'elevel1_program', name: 'elevel1_program', className: 'dt-nowrap'},
                        {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'elevel2_division', name: 'elevel2_division', className: 'dt-nowrap'},
                        {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'elevel3_branch', name: 'elevel3_branch', className: 'dt-nowrap'},
                        {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'elevel4', name: 'elevel4', className: 'dt-nowrap'},
                        {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'edeptid', name: 'edeptid', className: 'dt-nowrap'},
                    ],
                });

                // Handle click on "Select all" control
                $('#employee-list-select-all').on('click', function() {
                    // Check/uncheck all checkboxes in the table
                    $('#employee-list-table tbody input:checkbox').prop('checked', this.checked);
                    if (this.checked) {
                        g_selected_employees = g_matched_employees.map((x) => x);
                        $('#employee-list-select-all').prop("checked", true);
                        $('#employee-list-select-all').prop("indeterminate", false);    
                    } else {
                        g_selected_employees = [];
                        $('#employee-list-select-all').prop("checked", false);
                        $('#employee-list-select-all').prop("indeterminate", false);    
                    }    
                });

                // Handle click on "Select all" control
                $('#eemployee-list-select-all').on('click', function() {
                    // Check/uncheck all checkboxes in the table
                    $('#eemployee-list-table tbody input:checkbox').prop('checked', this.checked);
                    if (this.checked) {
                        eg_selected_employees = eg_matched_employees.map((x) => x);
                        $('#eemployee-list-select-all').prop("checked", true);
                        $('#eemployee-list-select-all').prop("indeterminate", false);    
                    } else {
                        eg_selected_employees = [];
                        $('#eemployee-list-select-all').prop("checked", false);
                        $('#eemployee-list-select-all').prop("indeterminate", false);    
                    }    
                });

                $('#employee-list-table tbody').on( 'click', 'input:checkbox', function () {
                    // if the input checkbox is selected 
                    var id = this.value;
                    var index = $.inArray(id, g_selected_employees);
                    if(this.checked) {
                        g_selected_employees.push( id );
                    } else {
                        g_selected_employees.splice( index, 1 );
                    }

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
                });

                $('#eemployee-list-table tbody').on( 'click', 'input:checkbox', function () {
                    // if the input checkbox is selected 
                    var id = this.value;
                    var index = $.inArray(id, eg_selected_employees);
                    if(this.checked) {
                        eg_selected_employees.push( id );
                    } else {
                        eg_selected_employees.splice( index, 1 );
                    }

                    // update the check all checkbox status 
                    if (eg_selected_employees.length == 0) {
                        $('#eemployee-list-select-all').prop("checked", false);
                        $('#eemployee-list-select-all').prop("indeterminate", false);   
                    } else if (eg_selected_employees.length == eg_matched_employees.length) {
                        $('#eemployee-list-select-all').prop("checked", true);
                        $('#eemployee-list-select-all').prop("indeterminate", false);   
                    } else {
                        $('#eemployee-list-select-all').prop("checked", false);
                        $('#eemployee-list-select-all').prop("indeterminate", true);    
                    }
                });

                $('#btn_search').click(function(e) {
                    // console.log('sysadmin - employeeshares - addindex.blade.php - #btn_search.click');
                    e.preventDefault();
                    // var user_selected = [];
                    $('#employee-list-table').DataTable().rows().invalidate().draw();
                });


                $('#ebtn_search').click(function(e) {
                    // console.log('sysadmin - employeeshares - addindex.blade.php - #btn_search.click');
                    e.preventDefault();
                    // var user_selected = [];
                    $('#eemployee-list-table').DataTable().rows().invalidate().draw();
                });

				$('#share-form').submit(function() {
					// console.log('Search Button Clicked');			

					// assign back the selected employees to server
					var text = JSON.stringify(g_selected_employees);
					$('#selected_emp_ids').val( text );
					var text2 = JSON.stringify(g_selected_orgnodes);
					$('#selected_org_nodes').val( text2 );
					var text = JSON.stringify(eg_selected_employees);
					$('#eselected_emp_ids').val( text );
					var text2 = JSON.stringify(eg_selected_orgnodes);
					$('#eselected_org_nodes').val( text2 );
					return true; // return false to cancel form action
				});

                // Tab  -- LIST Page  activate
                $("#nav-list-tab").on("click", function(e) {
                    table  = $('#employee-list-table').DataTable();
                    table.rows().invalidate().draw();
                });

                $("#enav-list-tab").on("click", function(e) {
                    table  = $('#eemployee-list-table').DataTable();
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
                                    url: '/hradmin/employeeshares/org-tree',
                                    type: 'GET',
                                    data: $("#share-form").serialize(),
                                    dataType: 'html',
                                    // beforeSend: function() {
                                    //     $("#tree-loading-spinner").show();                    
                                    // },
                                    success: function (result) {
                                        $(target).html(''); 
                                        $(target).html(result);

                                        $('#nav-tree').attr('loaded','loaded');
                                    },
                                    // complete: function() {
                                    //     $(".tree-loading-spinner").hide();
                                    // },
                                    error: function () {
                                        alert("error");
                                        $(target).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                    }
                                })
                                
                            ).then(function( data, textStatus, jqXHR ) {
                                //alert( jqXHR.status ); // Alerts 200
                                nodes = $('#accordion-level0 input:checkbox');
                                redrawTreeCheckboxes();	
                            }); 
                        
                        } else {
                            redrawTreeCheckboxes();
                        }
                    } else {
						$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Tree result is too big.  Please apply organization filter before clicking on Tree.');
					}
				});

				$("#enav-tree-tab").on("click", function(e) {
					etarget = $('#enav-tree'); 
                    ddnotempty = $('#edd_level0').val() + $('#edd_level1').val() + $('#edd_level2').val() + $('#edd_level3').val() + $('#edd_level4').val();
                    if(ddnotempty) {
                        // To do -- ajax called to load the tree
                        if($.trim($(etarget).attr('loaded'))=='') {
                            $.when( 
                                $.ajax({
                                    url: '/hradmin/employeeshares/org-tree',
                                    type: 'GET',
                                    data: $("share-form").serialize(),
                                    dataType: 'html',
                                    // beforeSend: function() {
                                    //     $("#etree-loading-spinner").show();                    
                                    // },
                                    success: function (result) {
                                        $(etarget).html(''); 
                                        $(etarget).html(result);

                                        $('#enav-tree').attr('loaded','loaded');
                                    },
                                    // complete: function() {
                                    //     $(".etree-loading-spinner").hide();
                                    // },
                                    error: function () {
                                        alert("error");
                                        $(etarget).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                    }
                                })
                                
                            ).then(function( data, textStatus, jqXHR ) {
                                //alert( jqXHR.status ); // Alerts 200
                                nodes = $('#eaccordion-level0 input:checkbox');
                                eredrawTreeCheckboxes();	
                            }); 
                        
                        } else {
                            eredrawTreeCheckboxes();
                        }
                    } else {
						$(etarget).html('<i class="glyphicon glyphicon-info-sign"></i> Tree result is too big.  Please apply organization filter before clicking on Tree.');
					}
				});


                function redrawTreeCheckboxes() {
                    // redraw the selection 
                    //console.log('redraw triggered');
                    nodes = $('#accordion-level0 input:checkbox');
                    $.each( nodes, function( index, chkbox ) {
                        if (g_employees_by_org.hasOwnProperty(chkbox.value)) {
                            // console.log( 'org checkbox ' + chkbox.value);

                            all_emps = g_employees_by_org[ chkbox.value ].map( function(x) {return x.employee_id} );

                            // console.log(all_emps);
                            // console.log(g_selected_employees);
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
                            
                        } else {
                            if ( $(chkbox).attr('name') == 'userCheck[]') {
                                if (g_selected_employees.includes(chkbox.value)) {
                                    $(chkbox).prop('checked', true);
                                } else {
                                    $(chkbox).prop('checked', false);
                                }
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
                                //console.log(  value );
                                toggle_indeterminate( value );
                                //console.log("parent : " + pid);                
                                pid = $('#orgCheck' + pid).attr('pid');    
                            } 
                            while (pid);
                        }
                    });

                }

                function eredrawTreeCheckboxes() {
                    // redraw the selection 
                    //console.log('redraw triggered');
                    nodes = $('#eaccordion-level0 input:checkbox');
                    $.each( nodes, function( index, chkbox ) {
                        if (eg_employees_by_org.hasOwnProperty(chkbox.value)) {
                            // console.log( 'org checkbox ' + chkbox.value);

                            all_emps = eg_employees_by_org[ chkbox.value ].map( function(x) {return x.employee_id} );

                            // console.log(all_emps);
                            // console.log(g_selected_employees);
                            b = all_emps.every(v=> eg_selected_employees.indexOf(v) !== -1);

                            if (all_emps.every(v=> eg_selected_employees.indexOf(v) !== -1)) {
                                $(chkbox).prop('checked', true);
                                $(chkbox).prop("indeterminate", false);
                            } else if (all_emps.some(v=> eg_selected_employees.indexOf(v) !== -1)) {
                                $(chkbox).prop('checked', false);
                                $(chkbox).prop("indeterminate", true);
                            } else {
                                $(chkbox).prop('checked', false);
                                $(chkbox).prop("indeterminate", false);
                            }
                            
                        } else {
                            if ( $(chkbox).attr('name') == 'euserCheck[]') {
                                if (eg_selected_employees.includes(chkbox.value)) {
                                    $(chkbox).prop('checked', true);
                                } else {
                                    $(chkbox).prop('checked', false);
                                }
                            }
                        }
                    });

                    // reset checkbox state
                    reverse_list = nodes.get().reverse();
                    $.each( reverse_list, function( index, chkbox ) {
                        if (eg_employees_by_org.hasOwnProperty(chkbox.value)) {
                            pid = $(chkbox).attr('pid');
                            do {
                                value = '#eorgCheck' + pid;
                                //console.log(  value );
                                etoggle_indeterminate( value );
                                //console.log("parent : " + pid);                
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



            });

        </script>



    </x-slot>
</x-side-layout>

