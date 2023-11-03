<x-side-layout title="{{ __('Share Employees - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Share Employees</h3>
            @include('shared.employeeshares.partials.tabs')
        </div>
    </div>	
    @include('shared.employeeshares.partials.employee-profile-sharing-modal') 
    <p class="px-3">Supervisors and administrators may share an employee's PDP profile with another supervisor or staff for a legitimate business reason. The profile should only be shared with people who normally handle employees' permanent personnel records (i.e. Public Service Agency or co-supervisors). An employee may also wish to share their profile with someone other than a direct supervisor (for example, a hiring manager). In order to do this - the employee's consent is required.</p>
	
        @if(Session::has('message'))
            <div class="col-12">                    
                <div class="alert alert-danger" style="display:">
                    <i class="fa fa-info-circle"></i> {{ Session::get('message') }}
                </div>
            </div>
        @endif
        <div class="container-fluid">
            <br>
            <h6 class="text-bold">Step 1: Select Employee(s)</h6>
            <p class="px-3">Use the search functions to find the employee(s). If you are managing the status of a single employee, you can click on the Yes/No under Share Status in the employee row to make the changes directly. Otherwise, select the employee(s) you want to manage and proceed to Step 2 below.</p>
            @include('shared.employeeshares.partials.loader')

            <div class="p-3">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-list-tab" data-toggle="tab" href="#nav-list" role="tab" aria-controls="nav-list" aria-selected="true">List</a>
                        <a class="nav-item nav-link" id="nav-tree-tab" data-toggle="tab" href="#nav-tree" role="tab" aria-controls="nav-tree" aria-selected="false">Tree</a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
                        @include('shared.employeeshares.partials.recipient-list')
                    </div>
                    <div class="tab-pane fade" id="nav-tree" role="tabpanel" aria-labelledby="nav-tree-tab" loaded="">

                    </div>
                </div>
            </div>
        </div>

        <form id="notify-form" action="{{ route(request()->segment(1).'.employeeshares.saveall') }}" method="post">
        @csrf

        <div class="container-fluid">
            <br>
            <h6 class="text-bold">Step 2: Select shared supervisor</h6> 
            <h6 class="px-3">Select who you would like to share the selected employee(s) with. Then proceed to Step 3.</h6> 

            <input type="hidden" id="selected_org_nodes" name="selected_org_nodes" value="">
            <input type="hidden" id="selected_emp_ids" name="selected_emp_ids" value="">
            <input type="hidden" id="eselected_org_nodes" name="eselected_org_nodes" value="">
            <input type="hidden" id="eselected_emp_ids" name="eselected_emp_ids" value="">

            @include('shared.employeeshares.partials.loader2')

            <div class="p-3">
                <nav>
                    <div class="nav nav-tabs" id="enav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="enav-list-tab" data-toggle="tab" href="#enav-list" role="tab" aria-controls="enav-list" aria-selected="true">List</a>
                        {{-- <a class="nav-item nav-link" id="enav-tree-tab" data-toggle="tab" href="#enav-tree" role="tab" aria-controls="enav-tree" aria-selected="false">Tree</a> --}}
                    </div>
                </nav>
                <div class="tab-content" id="enav-tabContent">
                    <div class="tab-pane fade show active" id="enav-list" role="tabpanel" aria-labelledby="enav-list-tab">
                        @include('shared.employeeshares.partials.erecipient-list')
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
                        <div class="col col-12">
                            <b>Reason for sharing</b>
                            <i class="fa fa-info-circle" label="Reason for sharing" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="Provide a brief explanation of why the profile is being shared. For example: <br><br><ul><li> Sharing profile with co-supervisor </li><li>Sharing profile because of inaccurate data in PeopleSoft</li><li>Sharing with hiring manager per employee request</li></ul>"> </i> 
                            <x-input id="reason" name="input_reason"/>                            
                            @error('input_reason')
                                {{-- <div class="alert alert-danger alert-dismissable fade show"> "Reason for sharing" is required. </div> --}}
					            <small  class="text-danger error-reason" id="error-reason"></small>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <br>
            <h6 class="text-bold">Step 4. Share selected profile(s)</h6>
            <br>
            <div class="col-md-3 mb-2">
                <button class="btn btn-primary mt-2" type="button" onclick="confirmSaveAllModal()" id="btn_send" name="btn_send" value="btn_send">Share</button>
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
                        <p class="msg">Are you sure to send out this message ?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary mt-2 sharebtn" type="button" name="btn_send" value="btn_send">Share</button>
                        <button type="button" class="btn btn-secondary cancelbtn" data-dismiss="modal">Cancel</button>
                    </div>
                    
                </div>
            </div>
        </div>
        <!--Modal ends here--->	

    </form>

    @include('shared/employeeshares/partials/shared-edit-modal')

    <h6 class="m-20">&nbsp;</h6>
    <h6 class="m-20">&nbsp;</h6>
    <h6 class="m-20">&nbsp;</h6>

    <x-slot name="css">
        <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
    </x-slot>

    <x-slot name="js">
	
        <script src="//cdn.ckeditor.com/4.17.2/standard/ckeditor.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>

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
		</script>


        <script>
            let g_matched_employees = {!!json_encode($matched_emp_ids)!!};
            let eg_matched_employees = {!!json_encode($ematched_emp_ids)!!};
            let g_selected_employees = {!!json_encode($old_selected_emp_ids)!!};
            let eg_selected_employees = {!!json_encode($eold_selected_emp_ids)!!};
            let g_employees_by_org = [];
            let eg_employees_by_org = [];
            let g_selected_orgnodes = [];
            let eg_selected_orgnodes = [];

            $(document).on('submit', '.share-profile-form-edit', function (e) {
                $form = $(this);
                const data = $form.serializeArray();
                data.push({name: 'action', value: this.submitted});
                $.ajax({
                    method: 'POST',
                    url: $form.attr('action'),
                    data: data,
                    success: function () {
                        loadSharedProfileData(currentUserForModal, $form.parents('.modal'));
                    }
                });
                e.preventDefault();
            });

            $(document).on('submit', '#share-profile-form', function (e) {
                e.preventDefault();
                const $form = $(this);
                $.ajax({
                    url: $form.attr('action'),
                    type : 'POST',
                    data: $form.serialize(),
                    success: function (result) {
                        if(result.success){
                            // window.location.href= '/goal';
                            //$("#employee-profile-sharing-modal").modal('hide');
                            alert("Successfully shared");
                            window.location.reload(true);
                        } else {
                            alert(result.message);
                        }
                    },
                    beforeSend: function() {
                        $form.find('.text-danger').each(function(i, obj) {
                            $('.text-danger').text('');
                        });
                    },
                    error: function (error){
                        var errors = error.responseJSON.errors;

                        Object.entries(errors).forEach(function callback(value, index) {
                            var className = '.error-' + value[0];
                            $form.find(className).text(value[1]);
                        });
                    }
                });
            });

            function loadSharedProfileData(userId, $modal) {
                $.ajax({
                    url: "/{{request()->segment(1)}}/profile-shared-with/xxx".replace('xxx', userId),
                    success: function (response) {
                        $modal.find('.shared-with-list').html(response);
                        $(".items-to-share-edit").multiselect({
                            allSelectedText: 'All',
                            selectAllText: 'All',
                            includeSelectAllOption: true,
                            nonSelectedText: null,
                        });
                    }
                });
            }

            $(document).on('click', ".edit-field", function (e) {
                $('.view-mode').removeClass("d-none");
                $('.edit-mode').addClass("d-none");
                $viewArea = $(this).parents('.view-mode');
                $editArea = $(this).parents('td').find('.edit-mode');
                $editArea.removeClass("d-none");
                $viewArea.addClass("d-none");
            });


            function confirmSaveAllModal(){
				$('#saveAllModal .modal-body p').html('Are you sure you want to share the selected profile(s)?');
				$('#saveAllModal').modal();
			}

            $(document).ready(function(){

                $(document).on('show.bs.modal', '#employee-profile-sharing-modal', function (e) {
                    var userId = $(e.relatedTarget).data('user_id'); 
                    var userName = $(e.relatedTarget).data('userName'); 
                    $("#employee-profile-sharing-modal").find(".user-name").html(userName);
                    $(this).find('#share-profile-form').find('[name=shared_id]').val(userId);
                    $modal = $(this);
                    currentUserForModal = userId;
                    loadSharedProfileData(userId, $modal);
                });

                $(document).on('hide.bs.modal', '#employee-profile-sharing-modal', function (e) {
                    table  = $('#employee-list-table').DataTable();
                    table.rows().invalidate().draw();
                });

                function loadSharedProfileData(userId, $modal) {
                    $.ajax({
                        url: "{{ route(request()->segment(1).'.employeeshares.profile-shared-with', 'xxx')}}".replace('xxx', userId), 
                        success: function (response) {
                            // console.log(response);
                            $modal.find('.shared-with-list').html(response);
                            $(".items-to-share-edit").multiselect({
                                allSelectedText: 'All',
                                selectAllText: 'All',
                                includeSelectAllOption: true,
                                nonSelectedText: null,
                            });
                        }
                    });
                }

                $('#employee-list-table').DataTable( {
                    scrollX: true,
                    retrieve: true,
                    searching: false,
                    processing: true,
                    serverSide: true,
                    select: true,
                    order: [[1, 'asc']],
                    ajax: {
                        url: "{{ '/' . request()->segment(1) . '/employeeshares/employee-list/1' }}",
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
                        {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'string', data: 'user_id', name: 'user_id', className: 'dt-nowrap', visible: false},
                        {title: '<input name="select_all" value="1" id="employee-list-select-all" type="checkbox" />', ariaTitle: 'employee-list-select-all', target: 0, type: 'string', data: 'select_users', name: 'select_users', orderable: false, searchable: false},
                        {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_id', className: 'dt-nowrap'},
                        {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_name', className: 'dt-nowrap'},
                        {title: 'Shared', ariaTitle: 'Shared', target: 0, type: 'string', data: 'shared_status', name: 'shared_status', className: 'dt-nowrap'},
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
                        url: "{{ '/' . request()->segment(1) . '/employeeshares/employee-list/2' }}",
                        data: function (d) {
                            d.edd_level0 = $('#edd_level0').val();
                            d.edd_level1 = $('#edd_level1').val();
                            d.edd_level2 = $('#edd_level2').val();
                            d.edd_level3 = $('#edd_level3').val();
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
                        {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_id', className: 'dt-nowrap'},
                        {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_name', className: 'dt-nowrap'},
                        {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc', name: 'jobcode_desc', className: 'dt-nowrap'},
                        // {title: 'Email', ariaTitle: 'Email', target: 0, type: 'string', data: 'eemployee_email', name: 'eemployee_email', className: 'dt-nowrap'},
                        {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', className: 'dt-nowrap'},
                        {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', className: 'dt-nowrap'},
                        {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', className: 'dt-nowrap'},
                        {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', className: 'dt-nowrap'},
                        {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', className: 'dt-nowrap'},
                        {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'deptid', className: 'dt-nowrap'},
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
                    e.preventDefault();
                    $('#employee-list-table').DataTable().rows().invalidate().draw();
                });


                $('#ebtn_search').click(function(e) {
                    e.preventDefault();
                    $('#eemployee-list-table').DataTable().rows().invalidate().draw();
                });

				$('#notify-form').submit(function() {
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
                                    url: "{{ '/' . request()->segment(1) . '/employeeshares/org-tree/1' }}",
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
                                        $(".tree-loading-spinner").hide();
                                    },
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
						$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Please apply the organization filter before creating a tree view.');
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
                                    url: "{{ '/' . request()->segment(1) . '/employeeshares/org-tree/2' }}",
                                    type: 'GET',
                                    data: $("notify-form").serialize(),
                                    dataType: 'html',
                                    beforeSend: function() {
                                        $("#etree-loading-spinner").show();                    
                                    },
                                    success: function (result) {
                                        $(etarget).html(''); 
                                        $(etarget).html(result);

                                        $('#enav-tree').attr('loaded','loaded');
                                    },
                                    complete: function() {
                                        $(".etree-loading-spinner").hide();
                                    },
                                    error: function () {
                                        alert("error");
                                        $(etarget).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
                                    }
                                })
                                
                            ).then(function( data, textStatus, jqXHR ) {
                                //alert( jqXHR.status ); // Alerts 200
                                enodes = $('#eaccordion-level0 input:checkbox');
                                eredrawTreeCheckboxes();	
                            }); 
                        
                        } else {
                            eredrawTreeCheckboxes();
                        }
                    } else {
						$(etarget).html('<i class="glyphicon glyphicon-info-sign"></i> Please apply the organization filter before creating a tree view.');
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

                            all_emps = eg_employees_by_org[ chkbox.value ].map( function(x) {return x.employee_id} );

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
                    reverse_list = enodes.get().reverse();
                    $.each( reverse_list, function( index, chkbox ) {
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
                    enodes = $(prev_location).find("input:checkbox[name='eorgCheck[]']");
                    $.each( enodes, function( index, chkbox ) {
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
            
            @error('input_reason')
                $('input[name=input_reason]').addClass('is-invalid');
            @enderror
            
            $(".sharebtn").click(function(){
                $(".sharebtn").hide();
                $(".cancelbtn").hide();
                $(".msg").html("Processing request. Please do not close this window.");
                
                $("#notify-form").submit();
            });

            $(document).on('click', '.share-profile-btn', function() {
                const userName = $(this).data("user-name");
                $("#employee-profile-sharing-modal").find(".user-name").html(userName);
            });

            $('#editModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                shared_status = button.data('shared_status');
                $("#shared_status").val(shared_status);
                user_id = button.data('user_id');
                $("#user_id").val(user_id);
                employee_name = button.data('employee_name');
                $("#employee_name").val(employee_name);
                message = '';
                if (shared_status == 'Yes') {
                    $("#removeAllShareButton").attr('disabled', false);
                    message = "The employee is currently shared.  Click on Remove All Shares button to remove all sharing for the employee.";

                } else {
                    $("#removeAllShareButton").attr('disabled', true);
                    message = "The employee is currently not shared with anyone.";
                }
                $("#message").val(message);
                $("#sharedDetailLabel").html("Edit Employee Share:  "+employee_name);
                $("#modal_text").html(message);
            });

            $('#removeAllShareButton').on('click', function(event) {
                let confirmation = 'Confirm removal of all shares for '+$("#employee_name").val()+'?';
                if(confirm(confirmation)) {
                    let parray = JSON.stringify($("#user_id").val());
                    var deleteall_url = "{{ route(request()->segment(1) . '.employeeshares.removeallshare', ':parray') }}";
                    deleteall_url = deleteall_url.replace(':parray', parray);
                    let _url = deleteall_url;
                    window.location.href = _url;
                }
            });

            $(".share-with-users").select2({
                language: {
                        errorLoading: function () {
                            return "Searching for results.";
                        }
                        },
                width: '100%',
                ajax: {
                    url: '/users',
                    dataType: 'json',
                    data: function (params) {
                        const query = {
                            search: params.term,
                            page: params.page || 1
                        };
                        return query;
                    },
                    processResults: function (response, params) {
                        return {
                            results: $.map(response.data.data, function (item) {
                                return {
                                    text: item.name+(item.email ? ' - '+item.email : '')+(item.deptid ? ' - ['+item.deptid + ']' : ''),
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: response.data.current_page !== response.data.last_page
                            }
                        }
                    }
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
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1A5A96;
    }    

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        float: left;
        border-right: 0px;
        margin-left: 0px;
        position: relative;
        left: 0;
        top: 0;
    }

    
</style> 