<x-side-layout title="{{ __('My Organization - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>My Organization</h3>
        </div>
    </div>
    <!-- @include('hradmin.myorg.partials.reportees-modal')  -->

    <div class="card">
        <div class="card-body">    
            <div class="m-n2 pb-n3">
                @include('hradmin.myorg.partials.filter')
            </div>
            <div class="m-2 mt-n2">
                <table class="table table-bordered myorgtable table-striped" id="myorgtable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   


    @push('css')
        <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
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
            #myorgtable_filter label {
                text-align: right !important;
            }

            #myorgtable_wrapper .dataTables_processing {
                top: 50px;
            }

            </style>
        </x-slot>
        
    @endpush
    @push('js')
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

        <script type="text/javascript">

            $(document).ready() 
            {
                $(function () {
                    var table = $('#myorgtable').DataTable
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
                            stateSave: true,
                            ajax: 
                            {
                                url: "{{ route('hradmin.myorg.myorganization') }}",
                                data: function (d) 
                                {
                                    d.dd_level0 = $('#dd_level0').val();
                                    d.dd_level1 = $('#dd_level1').val();
                                    d.dd_level2 = $('#dd_level2').val();
                                    d.dd_level3 = $('#dd_level3').val();
                                    d.dd_level4 = $('#dd_level4').val();
                                    d.criteria = $('#criteria').val();
                                    d.search_text = $('#search_text').val();
                                }
                            },
                            "rowCallback": function( row, data ) 
                            {
                            },
                            columns: 
                            [
                                {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_id', searchable: true, className: 'dt-nowrap'},
                                {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'u.employee_name', searchable: true, className: 'dt-nowrap'},
                                {title: 'Email', ariaTitle: 'Email', target: 0, type: 'string', data: 'employee_email', name: 'u.employee_email', searchable: false, className: 'dt-nowrap show-modal'},
                                {title: 'Position #', ariaTitle: 'Position #', target: 0, type: 'string', data: 'position_number', name: 'u.position_number', searchable: false, className: 'dt-nowrap show-modal'},
                                {title: 'Reports To Name', ariaTitle: 'Reports To Name', target: 0, type: 'string', data: 'reporting_to_name', name: 'u.reporting_to_name', searchable: false, className: 'dt-nowrap show-modal'},
                                {title: 'Reports To Position #', ariaTitle: 'Reports To Position #', target: 0, type: 'string', data: 'reporting_to_position_number', name: 'u.reporting_to_position_number', searchable: true, className: 'dt-nowrap show-modal'},
                                {title: 'Status', ariaTitle: 'Status', target: 0, type: 'string', data: 'employee_status', name: 'u.employee_status', searchable: false, className: 'dt-nowrap show-modal'},
                                {title: 'Record #', ariaTitle: 'Record #', target: 0, type: 'string', data: 'empl_record', name: 'u.empl_record', searchable: false, className: 'dt-nowrap show-modal'},
                                {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc', name: 'jobcode_desc', searchable: true, className: 'dt-nowrap'},
                                {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', searchable: true, className: 'dt-nowrap'},
                                {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'level1_program', searchable: true, className: 'dt-nowrap'},
                                {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'level2_division', searchable: true, className: 'dt-nowrap'},
                                {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'level3_branch', searchable: true, className: 'dt-nowrap'},
                                {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'level4', searchable: true, className: 'dt-nowrap'},
                                {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'deptid', searchable: true, className: 'dt-nowrap'},
                                {title: 'Active Goals', ariaTitle: 'Active Goals', target: 0, type: 'string', data: 'activeGoals', name: 'activeGoals', searchable: false, className: 'dt-nowrap'},
                                {title: 'Next Conversation', ariaTitle: 'Next Conversation', target: 0, type: 'date', data: 'nextConversationDue', name: 'u.next_conversation_date', searchable: false, className: 'dt-nowrap'},
                                {title: 'Excused', ariaTitle: 'Excused', target: 0, type: 'string', data: 'excusedtype', name: 'excusedtype', searchable: true, className: 'dt-nowrap'},
                                {title: 'Shared', ariaTitle: 'Shared', target: 0, type: 'string', data: 'shared', name: 'shared', searchable: false, className: 'dt-nowrap'},
                                {title: 'Reports', ariaTitle: 'Reports', target: 0, type: 'string', data: 'reportees', name: 'reportees', searchable: false, className: 'dt-nowrap'},
                                {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'num', data: 'user_id', name: 'user_id', searchable: true, visible: false, className: 'dt-nowrap'},
                            ]
                        }
                    );
                });

                $('#reportees-modal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var user_id = button.data('user_id');
                    var employee_name = button.data('employee_name');
                    $('#reporteesTitle').text('Reports List for '+employee_name);
                    if($.fn.DataTable.isDataTable( "#reporteesTable" )) {
                        $('#reporteesTable').DataTable().clear().destroy();
                    };
                    $('#reporteesTable').DataTable({
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
                            url: "/hradmin/myorg/reporteeslist/"+user_id,
                        },                    
                        fnDrawCallback: function() {
                        },
                        fnRowCallback: function( row, data ) {
                        },
                        columns: [
                            {title: 'Employee ID', ariaTitle: 'Employee ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_id', searchable: true},
                            {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_name', searchable: true},
                            {title: 'Type', ariaTitle: 'Type', target: 0, type: 'string', data: 'reporteetype', name: 'reporteetype', searchable: true},
                        ],  
                    });
                });

                $('#btn_search').click(function(e) {
                    e.preventDefault();
                    $('#myorgtable').DataTable().rows().invalidate().draw();
                });

                $(window).on('beforeunload', function(){
                    $('#pageLoader').show();
                });

                $(window).resize(function(){
                    location.reload();
                    return;
                });

            }
                        
        </script>
    @endpush


</x-side-layout>
