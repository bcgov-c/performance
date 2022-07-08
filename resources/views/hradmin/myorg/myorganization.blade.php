<x-side-layout title="{{ __('Dashboard') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>My Organization</h3>
        </div>
    </div>

    <div class="card">
        <div class="card-body">            
            @include('hradmin.myorg.partials.filter')
            <p></p>
            <table class="table table-bordered myorgtable table-striped" id="myorgtable" style="width: 100%; overflow-x: auto; "></table>
        </div>    
    </div>   


    @push('css')
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <x-slot name="css">
            <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
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
            </style>
        </x-slot>
        
    @endpush
    @push('js')
        {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>   --}}
        {{-- <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script> --}}
        {{-- <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap4.min.js"></script> --}}

        <script type="text/javascript">

            $(document).ready() 
            {
                $(function () {
                    var table = $('#myorgtable').DataTable
                    (
                        {
                            processing: true,
                            serverSide: true,
                            scrollX: true,
                            stateSave: true,
                            deferRender: true,
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
                                {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id'
                                    , name: 'employee_demo.employee_id', searchable: true, className: 'dt-nowrap'},
                                {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name'
                                    , name: 'employee_demo.employee_name', searchable: true, className: 'dt-nowrap'},
                                {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc'
                                    , name: 'employee_demo.jobcode_desc', searchable: true, className: 'dt-nowrap'},
                                {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization'
                                    , name: 'employee_demo.organization', searchable: true, className: 'dt-nowrap'},
                                {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program'
                                 , name: 'employee_demo.level1_program', searchable: true, className: 'dt-nowrap'},
                                {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division'
                                    , name: 'employee_demo.level2_division', searchable: true, className: 'dt-nowrap'},
                                {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch'
                                    , name: 'employee_demo.level3_branch', searchable: true, className: 'dt-nowrap'},
                                {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4'
                                    , name: 'employee_demo.level4', searchable: true, className: 'dt-nowrap'},
                                {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid'
                                    , name: 'employee_demo.deptid', searchable: true, className: 'dt-nowrap'},
                                {title: 'Active Goals', ariaTitle: 'Active Goals', target: 0, type: 'string', data: 'activeGoals'
                                    , name: 'activeGoals', searchable: false, className: 'dt-nowrap'},
                                {title: 'Next Conversation', ariaTitle: 'Next Conversation', target: 0, type: 'date', data: 'nextConversationDue'
                                    , name: 'nextConversationDue', searchable: false, className: 'dt-nowrap'},
                                {title: 'Excused', ariaTitle: 'Excused', target: 0, type: 'string', data: 'excused'
                                    , name: 'excused', searchable: false, className: 'dt-nowrap'},
                                {title: 'Shared', ariaTitle: 'Shared', target: 0, type: 'string', data: 'shared'
                                    , name: 'shared', searchable: false, className: 'dt-nowrap'},
                                {title: 'Direct Reports', ariaTitle: 'Direct Reports', target: 0, type: 'string', data: 'reportees'
                                    , name: 'reportees', searchable: false, className: 'dt-nowrap'},
                                {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'num', data: 'id'
                                    , name: 'users.id', searchable: true, visible: false, className: 'dt-nowrap'},
                            ]
                        }
                    );
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
