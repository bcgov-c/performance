<x-side-layout title="{{ __('Dashboard') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Shared Employees</h3>
            @include('sysadmin.employeeshares.partials.tabs')
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="h4">{{__('Manage Existing Employee Shares')}}</div>
            @include('sysadmin.employeeshares.partials.loader')
            <div class="p-3">  
                <table class="table table-bordered generictable" id="generictable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   
    @include('sysadmin/employeeshares/partials/share-edit-modal')

    @push('css')
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script type="text/javascript">
			$(document).ready( function() {

                $('#btn_search').click(function(e) {
                    e.preventDefault();
                    if($.fn.dataTable.isDataTable('#generictable')) {
                        $('#generictable').DataTable().clear();
                        $('#generictable').DataTable().destroy();
                        $('#generictable').empty();
                    }
                    $('#generictable').DataTable ( {
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        stateSave: true,
                        deferRender: true,
                        ajax: {
                            url: "{{ route('sysadmin.employeeshares.manageindexlist') }}",
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
                            {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_id', searchable: true, className: 'dt-nowrap'},
                            {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_name', searchable: true, className: 'dt-nowrap'},
                            {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc', name: 'jobcode_desc', searchable: true, className: 'dt-nowrap'},
                            {title: 'Shared Item', ariaTitle: 'Shared Item', target: 0, type: 'num', data: 'item_type', name: 'item_type', searchable: false, visible: true, className: 'dt-nowrap'},
                            {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', searchable: true, className: 'dt-nowrap'},
                            {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', searchable: true, className: 'dt-nowrap'},
                            {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', searchable: true, className: 'dt-nowrap'},
                            {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true, className: 'dt-nowrap'},
                            {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', searchable: true, className: 'dt-nowrap'},
                            {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'deptid', searchable: true, className: 'dt-nowrap'},
                            {title: 'Action', ariaTitle: 'Action', target: 0, type: 'string', data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-nowrap'},
                            {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'num', data: 'user_id', name: 'user_id', searchable: false, visible: false},
                            {title: 'Item ID', ariaTitle: 'Item ID', target: 0, type: 'num', data: 'item_id', name: 'item_id', searchable: false, visible: false},
                        ]
                    } );
                } );

                $('#editModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    console.log($(event.relatedTarget));
                    var user_id = button.data('userid');
                    var username = button.data('username');
                    // console.log('ASDRFVBHBNJN');
                    $('#shareDetailLabel').text('View Employee Shares:  '+username);
                    $('#admintable').show();
                    $('#admintable').DataTable ( {
                        processing: true,
                        serverSide: false,
                        scrollX: true,
                        stateSave: false,
                        deferRender: false,
                        ajax: {
                            type: 'GET',
                            url: "/sysadmin/employeeshares/manageindexviewshares/"+user_id,
                        },                        
                        columns: [
                            {title: 'Item Type', ariaTitle: 'Item Type', target: 0, type: 'string', data: 'item_type', name: 'item_type', searchable: true, className: 'dt-nowrap'},
                            {title: 'Shared With ID', ariaTitle: 'Shared With ID', target: 0, type: 'string', data: 'employee_id2', name: 'employee_id2', searchable: true, className: 'dt-nowrap'},
                            {title: 'Shared With Name', ariaTitle: 'Shared With Name', target: 0, type: 'string', data: 'employee_name2', name: 'employee_name2', searchable: true, className: 'dt-nowrap'},
                            {title: 'Item ID', ariaTitle: 'Item ID', target: 0, type: 'string', data: 'item_id', name: 'item_id', searchable: true, className: 'dt-nowrap'},
                            {title: 'Shared With User ID', ariaTitle: 'Shared With User ID', target: 0, type: 'string', data: 'shared_with_id', name: 'shared_with_id', searchable: true, className: 'dt-nowrap'},
                            // {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'num', data: 'user_id', name: 'user_id', searchable: false, visible: false, className: 'dt-nowrap'},
                        ],  
                    } );
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

                $('#removeButton').on('click', function(event) {
                    console.log('Delete button clicked');
                    // var model_id = $('#model_id').val();
                    // var token = $('meta[name="csrf-token"]').attr('content');
                    // event.preventDefault();
                    // $.ajax ( {
                    //     type: 'POST',
                    //     url: 'manageexistingaccessdelete/'+model_id,
                    //     data: {
                    //         'model_id':model_id,
                    //         '_token':token,
                    //         '_method':"DELETE",
                    //     },
                    //     success: function (result) {
                    //         window.location.href = 'manageexistingaccess';
                    //     }
                    // });
                });

                $(window).on('beforeunload', function(){
                    $('#pageLoader').show();
                });

            });

        </script>
    @endpush

</x-side-layout>