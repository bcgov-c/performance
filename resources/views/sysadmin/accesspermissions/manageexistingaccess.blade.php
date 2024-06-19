<x-side-layout title="{{ __('Access and Permissions - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Access and Permissions</h3>
            @include('sysadmin.accesspermissions.partials.tabs')
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @include('sysadmin.accesspermissions.partials.filter3')
            <div class="p-3">  
                <table class="table table-bordered filtertable table-striped" id="filtertable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   
    @include('sysadmin/accesspermissions/partials/access-edit-modal')

    @push('css')
        <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
        <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet">
        <x-slot name="css">
            <style>
                .text-truncate-30 {
                    white-space: wrap; 
                    overflow: hidden;
                    text-overflow: ellipsis;
                    width: 30em;
                }
            
                .text-truncate-10 {
                    white-space: wrap; 
                    overflow: hidden;
                    text-overflow: ellipsis;
                    width: 5em;
                }

                #filtertable_filter label {
                    display: none;
                    /* text-align: right !important; */
                }

                #filtertable_wrapper .dt-buttons {
                    float: none;
                    text-align:right;
                }

                #filtertable_wrapper .dataTables_processing {
                    top: 50px;
                }

                #admintable_filter label {
                    display: none;
                }

                #admintable_wrapper .dt-buttons {
                    float: left;
                }

            </style>
        </x-slot>
        
    @endpush

    @push('js')
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
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
			$('body').on('click', function (e) {
			$('[data-toggle=dropdown]').each(function () {
				// hide any open popovers when the anywhere else in the body is clicked
				if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
					$(this).popover('hide');
				}
				});
			});	

		</script>


        <script type="text/javascript">

            let g_selected_employees = {!!json_encode($old_selected_emp_ids)!!};
            
			$(document).ready(function(){

                $('#filtertable').DataTable ( {
                    dom: 'lfrtip',
                    serverSide: true,
                    searching: true,
                    processing: true,
                    paging: true,
                    deferRender: true,
                    retrieve: true,
                    scrollCollapse: true,
                    scroller: true,
                    scrollX: true,
                    stateSave: true,
                    ajax: {
                        url: "{{ route('sysadmin.accesspermissions.manageexistingaccesslist') }}",
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
                        {title: 'ID', ariaTitle: 'ID', target: 0, orderData: [0], type: 'string', data: 'employee_id', name: 'employee_id', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Name', ariaTitle: 'Name', target: 1, orderData: [1, 0], type: 'string', data: 'display_name', name: 'display_name', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'eMail', ariaTitle: 'eMail', target: 2, orderData: [2, 0], type: 'string', data: 'user_email', name: 'user_email', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Classification', ariaTitle: 'Classification', target: 3, orderData: [3, 0], type: 'string', data: 'jobcode_desc', name: 'jobcode_desc', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Organization', ariaTitle: 'Organization', target: 4, orderData: [4, 0], type: 'string', data: 'organization', name: 'organization', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 1', ariaTitle: 'Level 1', target: 5, orderData: [5, 0], type: 'string', data: 'level1_program', name: 'level1_program', searchable: true, className: 'dt-nowrap show-modal', visible: false},
                        {title: 'Level 2', ariaTitle: 'Level 2', target: 6, orderData: [6, 0], type: 'string', data: 'level2_division', name: 'level2_division', searchable: true, className: 'dt-nowrap show-modal', visible: false},
                        {title: 'Level 3', ariaTitle: 'Level 3', target: 7, orderData: [7, 0], type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true, className: 'dt-nowrap show-modal', visible: false},
                        {title: 'Level 4', ariaTitle: 'Level 4', target: 8, orderData: [8, 0], type: 'string', data: 'level4', name: 'level4', searchable: true, className: 'dt-nowrap show-modal', visible: false},
                        {title: 'Dept', ariaTitle: 'Dept', target: 9, orderData: [9, 0], type: 'string', data: 'deptid', name: 'deptid', searchable: true, className: 'dt-nowrap show-modal', visible: false},
                        {title: 'Access Level', ariaTitle: 'Access Level', target: 10, orderData: [10, 0], type: 'string', data: 'role_longname', name: 'role_longname', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Access Description', ariaTitle: 'Access Description', target: 11, orderData: [11, 0], type: 'num', data: 'reason', name: 'reason', searchable: true, className: 'dt-nowrap show-modal', visible: true},
                        {title: 'BU Assigned', ariaTitle: 'BU Assigned', target: 12, orderData: [12, 0], type: 'string', data: 'org_count', name: 'org_count', searchable: false, className: 'dt-nowrap show-modal', visible: false},
                        {title: 'Action', ariaTitle: 'Action', target: 13, orderData: [13, 0], type: 'string', data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Model ID', ariaTitle: 'Model ID', target: 14, orderData: [14, 0], type: 'num', data: 'model_id', name: 'model_id', searchable: false, visible: false, className: 'dt-nowrap show-modal', visible: false},
                        {title: 'Role ID', ariaTitle: 'Role ID', target: 15, orderData: [15, 0], type: 'num', data: 'role_id', name: 'role_id', searchable: false, visible: false, className: 'dt-nowrap show-modal', visible: false},
                        {title: 'SysAdmin', ariaTitle: 'SysAdmin', target: 16, orderData: [16, 0], type: 'num', data: 'sysadmin', name: 'sysadmin', searchable: false, className: 'dt-nowrap show-modal', visible: false},
                    ]
                } );

                // add export button on right
                $("#filtertable_filter").append("<button id='export-btn' value='export' class='dt-button buttons-csv buttons-html5'>Export</button> ");

                $('#export-btn').on('click', function() {
                    let parray = encodeURIComponent(JSON.stringify([
                        $('#dd_level0').val(), 
                        $('#dd_level1').val(),
                        $('#dd_level2').val(),
                        $('#dd_level3').val(),
                        $('#dd_level4').val(),
                        $('#criteria').val(),
                        $('#search_text').val()
                    ]));
                    var export_url = "{{ route('sysadmin.accesspermissions.export', ':parray') }}";
                    export_url = export_url.replace(':parray', parray);
                    let _url = export_url;
                    window.location.href = _url;
                });

                $('#btn_search').click(function(e) {
                    e.preventDefault();
					$('#filtertable').DataTable().rows().invalidate().draw();
                } );

                $('#editModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var reason = button.data('reason');
                    var role_id = parseInt(button.data('roleid'));
                    var email = button.data('email');
                    var sysadmin = button.data('sysadmin');
                    var model_id = button.data('modelid');
                    var current = {{ auth()->user()->id }};
                    $('#reason').val(reason);
                    $('#accessselect').val(role_id);
                    $('#model_id').val(model_id);
                    $('#role_id').val(role_id);
                    $('#accessDetailLabel').text('Edit Employee Access Level:  '+email);
                    $('#saveButton').prop('disabled', current == model_id);
                    $('removeButton').prop('disabled', current == model_id);
                    $('#accessselect').prop('disabled', current == model_id);
                    $('#reason').prop('disabled', current == model_id);
                    if($.fn.DataTable.isDataTable( "#admintable" )) {
                        $('#admintable').DataTable().clear().destroy();
                    };
                    if($('#accessselect').val() == 5) {
                        $('#accessselect').prop('disabled', true);
                    }
                    if($('#accessselect').val() == 4) {
                        $('#accessselect').prop('disabled', true);
                    }
                    if($('#accessselect').val() == 3) {
                        $('#accessselect').prop('disabled', sysadmin);
                        $('#admintable').show();
                        var table = $('#admintable').DataTable
                        (
                            {
                                serverSide: true,
                                searching: false,
                                processing: true,
                                paging: true,
                                deferRender: true,
                                retrieve: true,
                                scrollCollapse: true,
                                scroller: true,
                                scrollX: true,
                                stateSave: false,
                                ajax: {
                                    type: 'GET',
                                    url: "/sysadmin/accesspermissions/manageexistingaccessadmin/"+model_id,
                                },                        
                                fnDrawCallback: function() {
                                },
                                fnRowCallback: function( row, data ) {
                                    var index = $.inArray(data.id, g_selected_employees);
                                    if ( index === -1 ) {
                                        $(row).find('input[id="orgCheck'+data.id+'"]').prop('checked', false);
                                    } else {
                                        $(row).find('input[id="orgCheck'+data.id+'"]').prop('checked', true);
                                    }
                                },
                                columns: [
                                    {title: 'Admin Org ID', ariaTitle: 'Admin Org ID', target: 0, orderData: [0], type: 'num', data: 'id', name: 'id', searchable: false, visible: false},
                                    {title: ' ', ariaTitle: 'Org Checkboxes', target: 1, orderData: [1, 0], type: 'string', data: 'select_orgs', name: 'select_orgs', orderable: false, searchable: false},
                                    {title: 'Organization', ariaTitle: 'Organization', target: 2, orderData: [2, 0], type: 'string', data: 'organization', name: 'organization', searchable: true},
                                    {title: 'Level 1', ariaTitle: 'Level 1', target: 3, orderData: [3, 0], type: 'string', data: 'level1_program', name: 'level1_program', searchable: true},
                                    {title: 'Level 2', ariaTitle: 'Level 2', target: 4, orderData: [4, 0], type: 'string', data: 'level2_division', name: 'level2_division', searchable: true},
                                    {title: 'Level 3', ariaTitle: 'Level 3', target: 5, orderData: [5, 0], type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true},
                                    {title: 'Level 4', ariaTitle: 'Level 4', target: 6, orderData: [6, 0], type: 'string', data: 'level4', name: 'level4', searchable: true},
                                    {title: 'Inherited', ariaTitle: 'Inherited', target: 7, orderData: [7, 0], type: 'string', data: 'inherited', name: 'inherited', searchable: true},
                                    {title: 'User ID', ariaTitle: 'User ID', target: 8, orderData: [8, 0], type: 'num', data: 'user_id', name: 'user_id', searchable: false, visible: false},
                                    {title: 'Action', ariaTitle: 'Action', target: 9, orderData: [9, 0], type: 'string', data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-nowrap'},
                                ],  
                            }
                        );
                    } else {
                        $('#admintable').hide();
                    };
                });

                // add delete selected button
                $("#admintable").append("<button id='delete-selected-btn' name='delete-selected-btn' value='delete-selected' class='btn btn-xs btn-primary dt-buttons buttons-csv buttons-html5'>Delete Selected</button> ");

                $('#delete-selected-btn').attr('disabled', true);

                $('#admintable').on('click', 'input:checkbox', function () {
                    // if the input checkbox is selected 
                    var id = parseInt(this.value);
                    var index = $.inArray(id, g_selected_employees);
                    var table = $('#admintable').DataTable();
                    if(this.checked) {
                        g_selected_employees.push( id );
                    } else {
                        g_selected_employees.splice( index, 1 );
                    }
                    if(g_selected_employees.length === 0) {
                        $('#delete-selected-btn').attr('disabled', true);
                    } else {
                        $('#delete-selected-btn').attr('disabled', false);
                    }
                });

                $('#delete-selected-btn').on('click', function(e) {
                    e.preventDefault();
                    let g_selected_string = g_selected_employees.toString();
                    let parray = encodeURIComponent(JSON.stringify(g_selected_employees));
                    let count = g_selected_employees.length;
                    let message = 'Confirm deletion of selected row?';
                    if(count>1){
                        message = 'Confirm deletion of '+count+' selected rows?';
                    }
                    if(confirm(message)) {
                        var deleteall_url = "{{ route(request()->segment(1) . '.accesspermissions.deletemultiorgs', ':parray') }}";
                        deleteall_url = deleteall_url.replace(':parray', parray);
                        let _url = deleteall_url;
                        window.location.href = _url;
                    }
                });

                $('#editModal').on('hidden.bs.modal', function(event) {
                    if($.fn.DataTable.isDataTable( '#admintable' )) {
                        table = $('#admintable').DataTable();
                        table.clear();
                        table.draw();
                    };
                });

                $('#cancelButton').on('click', function(event) {
                    if($.fn.DataTable.isDataTable( '#admintable' )) {
                        table = $('#admintable').DataTable();
                        table.destroy();
                    };
                });

                $('#accessselect').on('change', function(event) {
                    if($.fn.DataTable.isDataTable( '#admintable' )) {
                        table = $('#admintable').DataTable();
                        table.destroy();
                    };
                    if($('#accessselect').val() == 3 ) {
                        $('#admintable').show();
                    } else {
                        $('#admintable').hide();
                    };
                });

                $('#removeButton').on('click', function(event) {
                    event.preventDefault();
                    if (confirm('Are you sure?')) {
                        $('#removeButton').attr('disabled', true);
                        var model_id = $('#model_id').val();
                        var role_id = $('#role_id').val();
                        var token = $('meta[name="csrf-token"]').attr('content');
                        $.ajax ( {
                            type: 'POST',
                            url: 'manageexistingaccessdelete/'+model_id+'/'+role_id,
                            data: {
                                'model_id':model_id,
                                'role_id':role_id,
                                '_token':token,
                                '_method':"DELETE",
                            },
                            success: function (result) {
                                window.location.href = 'manageexistingaccess';
                            }
                        });
                    };
                });

                $(window).on('beforeunload', function(){
                    $('#pageLoader').show();
                });

                // $(window).resize(function(){
                //     location.reload();
                //     return;
                // });

            });
       </script>
    @endpush

</x-side-layout>