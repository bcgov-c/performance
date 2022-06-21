<x-side-layout title="{{ __('Dashboard') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Shared Employees</h3>
            @include('hradmin.employeeshares.partials.tabs')
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="h4">{{__('Manage Existing Employee Shares')}}</div>
            @include('hradmin.employeeshares.partials.filter')
            <div class="p-3">  
                <table class="table table-bordered generictable" id="generictable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   

    @include('hradmin/employeeshares/partials/share-edit-modal')

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
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        "{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script type="text/javascript">
			$(document).ready(function(){
                var table = $('.generictable').DataTable
                (
                    {
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        stateSave: true,
                        deferRender: true,
                        ajax: {
                            url: "{{ route('hradmin.employeeshares.manageindexlist') }}",
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
                            {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_id', searchable: true},
                            {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_name', searchable: true},
                            {title: 'Job Title', ariaTitle: 'Job Title', target: 0, type: 'string', data: 'job_title', name: 'job_title', searchable: true},
                            {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', searchable: true},
                            {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', searchable: true},
                            {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', searchable: true},
                            {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true},
                            {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', searchable: true},
                            {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'deptid', searchable: true},
                            {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'num', data: 'user_id', name: 'user_id', searchable: false, visible: false},
                            {title: 'Rec ID', ariaTitle: 'Rec ID', target: 0, type: 'num', data: 'shared_id', name: 'shared_id', searchable: false, visible: false},
                        ]
                    }
                );
            });

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
                            url: "{{ route('hradmin.employeeshares.manageindexlist') }}",
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

                $('#btn_search').click();

                $('#cancelButton').on('click', function(e) {
                    if($.fn.dataTable.isDataTable('#admintable')) {
                        $('#admintable').DataTable().clear();
                        $('#admintable').DataTable().destroy();
                        $('#admintable').empty();
                    }
                );

                $('#btn_search_reset').click(function(e) {
                        e.preventDefault();
                        $('#search_text').val(null);
                        $('#dd_level0').val(null);
                        $('#dd_level1').val(null);
                        $('#dd_level2').val(null);
                        $('#dd_level3').val(null);
                        $('#dd_level4').val(null);
                    });

                    $('#removeButton').on('click', function(e) {
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

                $(window).on('beforeunload', function(e){
                    $('#pageLoader').show();
                });

            });

        </script>
    @endpush

</x-side-layout>