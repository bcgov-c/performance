<x-side-layout title="{{ __('Excuse Employees - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Excuse Employees</h3>
            @include('shared.excuseemployees.partials.tabs')
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @include('shared.excuseemployees.partials.filter')
            <div class="p-3">  
                <table class="table table-bordered filtertable table-striped" id="filtertable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   

    @push('css')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
    @endpush
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
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script type="text/javascript">

            $(document).ready(function()
            {

                var table = $('#filtertable').DataTable ( {
                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    stateSave: true,
                    deferRender: true,
                    ajax: {
                        url: "{{ route(request()->segment(1).'.excuseemployees.managehistorylist') }}",
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
                        {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'u.employee_id', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_demo.employee_name', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc', name: 'employee_demo.jobcode_desc', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Excused Type', ariaTitle: 'Excused Type', target: 0, type: 'string', data: 'excusedtype', name: 'excusedtype', searchable: false, visible: true, className: 'dt-nowrap show-modal'},
                        {title: 'Excused By', ariaTitle: 'Excused By', target: 0, type: 'string', data: 'n_name', name: 'n_name', searchable: false, visible: true, className: 'dt-nowrap show-modal'},
                        {title: 'Start Date', ariaTitle: 'Start Date', target: 0, type: 'string', data: 'j_created_at', name: 'j_created_at', searchable: false, visible: true, className: 'dt-nowrap show-modal'},
                        {title: 'End Date', ariaTitle: 'End Date', target: 0, type: 'string', data: 'k_created_at', name: 'k_created_at', searchable: false, visible: true, className: 'dt-nowrap show-modal'},
                        {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'employee_demo.organization', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'employee_demo.level1_program', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'employee_demo.level2_division', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'employee_demo.level3_branch', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'employee_demo.level4', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'employee_demo.deptid', searchable: true, className: 'dt-nowrap show-modal'},
                        {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'num', data: 'id', name: 'id', searchable: false, visible: false},
                    ]
                } );

                $('#btn_search').click(function(e) {
                    e.preventDefault();
                    console.log('search button clicked');
                    $('#filtertable').DataTable().rows().invalidate().draw();
                } );

            });

            $('#editModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var excused_flag = button.data('excused').excused_flag;
                var excused_reason_id = button.data('excused').reason_id;
                var excused_reason_id2 = button.data('excused').reason_id;
                var employee_name = button.data('employee_name');
                var excused_type = button.data('excused-type');
                var user_id = button.data('user-id');
                var current_status = button.data('current_status');
                $('#excusedDetailLabel').text('Edit Employee Excuse:  '+employee_name);
                $("#editModal").find(".employee_name").html(employee_name);
                $("#editModal").find("input[name=id]").val(user_id);
                $("#editModal").find("input[name=user_id]").val(user_id);
                $("#editModal").find("select[name=excused_flag]").val(excused_flag ?? 0);
                $("#editModal").find("select[name=excused_reason_id]").val(excused_reason_id ?? 3);
                $("#editModal").find("select[name=excused_reason_id2]").attr('disabled', true);
                if (excused_type == 'A') {
                    $("#editModal").find("select[name=excused_reason_id2]").val(current_status == 'A' ? 2 : 1);
                    $("#editModal").find("select[name=excused_flag]").attr('disabled', true);
                    $("#editModal").find("select[name=excused_reason_id]").attr('disabled', true);
                    $("#divReason1").hide();
                    $("#divReason2").show();
                    $("#editModal").find("button[name=saveExcuseButton]").attr('disabled', true);
                } else {
                    $("#editModal").find("select[name=excused_flag]").attr('disabled', false);
                    $("#editModal").find("select[name=excused_reason_id]").attr('disabled', false);
                    $("#divReason1").show();
                    $("#divReason2").hide();
                    $("#editModal").find("button[name=saveExcuseButton]").attr('disabled', false);
                }
            });

            $('#editModal').on('hidden.bs.modal', function(event) {
                if($.fn.DataTable.isDataTable( '#admintable' )) {
                    table = $('#admintable').DataTable();
                    table.clear();
                    table.draw();
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

            // $(window).on('beforeunload', function(){
            //     $('#pageLoader').show();
            // });

            // $(window).resize(function(){
            //     location.reload();
            //     return;
            // });

        </script>
    @endpush

</x-side-layout>