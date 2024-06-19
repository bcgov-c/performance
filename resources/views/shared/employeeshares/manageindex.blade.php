<x-side-layout title="{{ __('Share Employees - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Share Employees</h3>
            @include('shared.employeeshares.partials.tabs')
        </div>
    </div>
    <p class="px-3">Each row below is a separate shared relationship in the PDP. You can search by employee or delegated supervisor to find the relationship you are looking for and view or delete as needed.</p>

    <div class="card">
        <div class="card-body">
            @include('shared.employeeshares.partials.loader')
            <div class="p-3">  
                <table class="table table-bordered generictable table-striped" id="generictable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   

    @include('shared.employeeshares.partials.share-edit-modal')

    @push('css')
        <!-- <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet"> -->
        <x-slot name="css">
            <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
            <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css" rel="stylesheet" />
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
                
                #generictable_filter label {
                    display: none;
                }

                #generictable_wrapper .dt-buttons {
                    float: left;
                }

                .share-with-users {
                    background-color: #1A5A96;
                    border-color: #164d80;
                    color: #fff;
                    padding: 0 10px;
                    margin-top: 0.31rem;
                }

            </style>
        </x-slot>
    @endpush

    @push('js')
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script type="text/javascript">

            let g_selected_employees = {!!json_encode($old_selected_emp_ids)!!};
            
            function showModal ($id) {
                $("#edit-modal").modal('show');
            }

			$(document).ready( function() {

                $('#generictable').DataTable ( {
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
                        url: "{{ route(request()->segment(1).'.employeeshares.manageindexlist') }}",
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
                    "fnDrawCallback": function() {
                    },
                    "fnRowCallback": function( row, data ) {
                        var index = $.inArray(data.shared_profile_id, g_selected_employees);
                        if ( index === -1 ) {
                            $(row).find('input[id="userCheck'+data.shared_profile_id+'"]').prop('checked', false);
                        } else {
                            $(row).find('input[id="userCheck'+data.shared_profile_id+'"]').prop('checked', true);
                        }
                    },
                    columns: [
                        {title: 'Shared Profile ID', ariaTitle: 'Shared Profile ID', target: 0, type: 'num', data: 'shared_profile_id', name: 'shared_profile_id', searchable: false, visible: false},
                        {title: ' ', ariaTitle: 'Shared Checkboxes', target: 0, type: 'string', data: 'select_users', name: 'select_users', orderable: false, searchable: false},
                        {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_id', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_name', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Delegate ID', ariaTitle: 'Delegate ID', target: 0, type: 'string', data: 'delegate_ee_id', name: 'delegate_ee_id', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Delegate Name', ariaTitle: 'Delegate Name', target: 0, type: 'string', data: 'delegate_ee_name', name: 'delegate_ee_name', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc', name: 'jobcode_desc', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'deptid', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Shared By', ariaTitle: 'Shared By', target: 0, type: 'string', data: 'created_name', name: 'created_name', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Created At', ariaTitle: 'Created At', target: 0, type: 'string', data: 'created_at', name: 'created_at', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Updated At', ariaTitle: 'Updated At', target: 0, type: 'string', data: 'updated_at', name: 'updated_at', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Action', ariaTitle: 'Action', target: 0, type: 'string', data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-nowrap'},
                    ]
                } );

                // add delete selected button
                $("#generictable_filter").append("<button id='delete-selected-btn' value='delete-selected' class='btn btn-primary dt-buttons buttons-csv buttons-html5'>Delete Selected</button> ");

                $('#delete-selected-btn').attr('disabled', true);

                $('#generictable tbody').on( 'click', 'input:checkbox', function () {
                    // if the input checkbox is selected 
                    var id = parseInt(this.value);
                    var index = $.inArray(id, g_selected_employees);
                    var table = $('#generictable').DataTable();
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

                $('#delete-selected-btn').on('click', function() {
                    let g_selected_string = g_selected_employees.toString();
                    let parray = encodeURIComponent(JSON.stringify(g_selected_employees));
                    let count = g_selected_employees.length;
                    let message = 'Confirm deletion of selected row?';
                    if(count>1){
                        message = 'Confirm deletion of '+count+' selected rows?';
                    }
                    if(confirm(message)) {
                        var deleteall_url = "{{ route(request()->segment(1) . '.employeeshares.deletemultishare', ':parray') }}";
                        deleteall_url = deleteall_url.replace(':parray', parray);
                        let _url = deleteall_url;
                        window.location.href = _url;
                    }
                });

                $('#btn_search').click(function(e) {
                    e.preventDefault();
                    // console.log('btn_search clicked');
                    g_selected_employees = [];
                    $('#delete-selected-btn').attr('disabled', true);
					$('#generictable').DataTable().rows().invalidate().draw();
                } );

                $('#cancelButton').on('click', function(e) {
                     e.preventDefault();
                    if($.fn.dataTable.isDataTable('#generictable')) {
                        $('#generictable').DataTable().clear();
                        $('#generictable').DataTable().destroy();
                        $('#generictable').empty();
                    }
                    $('#generictable').DataTable().rows().invalidate().draw();
                });

                $('#removeButton').on('click', function(e) {

                });

                $(window).on('beforeunload', function(e){
                    $('#pageLoader').show();
                });

            });

        </script>
    @endpush

</x-side-layout>