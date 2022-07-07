<x-side-layout title="{{ __('Dashboard') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Access and Permissions</h3>
            @include('sysadmin.accesspermissions.partials.tabs')
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {{-- <div class="h4">{{__('Manage Existing Access')}}</div> --}}
            @include('sysadmin.accesspermissions.partials.filter3')
            {{-- <p></p> --}}
            <div class="p-3">  
                <table class="table table-bordered filtertable table-striped" id="filtertable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   
    @include('sysadmin/accesspermissions/partials/access-edit-modal')
    {{-- @endsection --}}


    @push('css')
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <x-slot name="css">
            <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
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
                    text-align: right !important;
                }
            </style>
        </x-slot>
        
    @endpush

    @push('js')
        {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>   --}}
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script type="text/javascript">
			$(document).ready(function(){

                $('#filtertable').DataTable ( {
                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    stateSave: true,
                    deferRender: true,
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
                        {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_demo.employee_id', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_demo.employee_name', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'eMail', ariaTitle: 'eMail', target: 0, type: 'string', data: 'email', name: 'users.email', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc', name: 'employee_demo.jobcode_desc', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'employee_demo.organization', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'employee_demo.level1_program', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'employee_demo.level2_division', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'employee_demo.level3_branch', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'employee_demo.level4', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'employee_demo.deptid', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Access Level', ariaTitle: 'Access Level', target: 0, type: 'string', data: 'longname', name: 'roles.longname', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Action', ariaTitle: 'Action', target: 0, type: 'string', data: 'action', name: 'action', orderable: false, searchable: false},
                        {title: 'Model ID', ariaTitle: 'Model ID', target: 0, type: 'num', data: 'model_id', name: 'model_has_roles.model_id', searchable: false, visible: false},
                        {title: 'Role ID', ariaTitle: 'Role ID', target: 0, type: 'num', data: 'role_id', name: 'model_has_roles.role_id', searchable: false, visible: false},
                        {title: 'Reason', ariaTitle: 'Reason', target: 0, type: 'num', data: 'reason', name: 'model_has_roles.reason', searchable: false, visible: false},
                    ]
                } );

                $('#btn_search').click(function(e) {
                    e.preventDefault();
					$('#filtertable').DataTable().rows().invalidate().draw();
               } );

                $('#btn_search').click();

                $('#editModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var reason = button.data('reason');
                    var role_id = parseInt(button.data('roleid'));
                    var email = button.data('email');
                    var model_id = button.data('modelid');
                    var current = {{ auth()->user()->id }};
                    $('#reason').val(reason);
                    $('#accessselect').val(role_id);
                    $('#model_id').val(model_id);
                    $('#accessDetailLabel').text('Edit Employee Access Level:  '+email);
                    $('#saveButton').prop('disabled', current == model_id);
                    $('removeButton').prop('disabled', current == model_id);
                    $('#accessselect').prop('disabled', current == model_id);
                    $('#reason').prop('disabled', current == model_id);
                    if($('#accessselect').val() == 4) {
                        $('#accessselect').prop('disabled', true);
                    }
                    if($('#accessselect').val() == 3) {
                        $('#admintable').show();
                        var table = $('#admintable').DataTable
                        (
                            {
                                processing: true,
                                serverSide: false,
                                scrollX: true,
                                stateSave: false,
                                deferRender: false,
                                ajax: {
                                    type: 'GET',
                                    url: "/sysadmin/accesspermissions/manageexistingaccessadmin/"+model_id,
                                },                        
                                columns: [
                                    {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', searchable: true},
                                    {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', searchable: true},
                                    {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', searchable: true},
                                    {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true},
                                    {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', searchable: true},
                                    {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'num', data: 'user_id', name: 'user_id', searchable: false, visible: false},
                                ],  
                            }
                        );
                    } else {
                        $('#admintable').hide();
                    };
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
                    if($('#accessselect').val() == 3) {
                        $('#admintable').show();
                    } else {
                        $('#admintable').hide();
                    };
                });

                $('#removeButton').on('click', function(event) {
                    console.log('Delete button clicked');
                    var model_id = $('#model_id').val();
                    var token = $('meta[name="csrf-token"]').attr('content');
                    event.preventDefault();
                    $.ajax ( {
                        type: 'POST',
                        url: 'manageexistingaccessdelete/'+model_id,
                        data: {
                            'model_id':model_id,
                            '_token':token,
                            '_method':"DELETE",
                        },
                        success: function (result) {
                            window.location.href = 'manageexistingaccess';
                        }
                    });
                });

                $(window).on('beforeunload', function(){
                    $('#pageLoader').show();
                });

                $(window).resize(function(){
                    location.reload();
                    return;
                });

            });
       </script>
    @endpush

</x-side-layout>