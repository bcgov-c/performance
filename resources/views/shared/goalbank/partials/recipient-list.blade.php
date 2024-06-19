<div class="card px-3 pb-3" id='listitem'>
    <div class="p-0">
        <div class="accordion-option">
            @error('userCheck')                
            <span class="text-danger">
                {{  'The recipient is required.'  }}
            </span>
            @enderror
        </div>
    </div>


    <div class="card" id="listdata">
        <div class="card-body">
            <h6></h6>
            <table class="table table-bordered table-striped" id="employee-list-table"></table>
        </div>    
    </div>   
</div>


@push('css')

    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<style>
        #employee-list-table_filter label {
            text-align: right !important;
            padding-right: 10px;
        } 
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
    
        $(document).ready(function() {
            var user_selected = [];

            var oTable = $('#employee-list-table').DataTable({
                scrollX: true,
                retrieve: true,
                searching: false,
                processing: true,
                serverSide: true,
                select: true,
                order: [[1, 'asc']],
                ajax: {
                    url: '/'+'{{ request()->segment(1) }}'+'/goalbank/employee-list',
                    data: function (d) {
                        d.dd_level0 = $('#dd_level0').val();
                        d.dd_level1 = $('#dd_level1').val();
                        d.dd_level2 = $('#dd_level2').val();
                        d.dd_level3 = $('#dd_level3').val();
                        d.dd_level4 = $('#dd_level4').val();
                        d.dd_superv = $('#dd_superv').val();
                        d.criteria = $('#criteria').val();
                        d.search_text = $('#search_text').val();
                    }
                },
                preDrawCallback: function ( settings ) {
                    document.getElementById('employee-list-select-all').disabled = true;
                }, 
                drawCallback: function( settings ) {
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
                    // Get all selection
                    $.ajax({
                        url: '{{ "/" . request()->segment(1) . "/goalbank/getfilteredlist" }}',
                        data: {
                            dd_level0 : $('#dd_level0').val(),
                            dd_level1 : $('#dd_level1').val(),
                            dd_level2 : $('#dd_level2').val(),
                            dd_level3 : $('#dd_level3').val(),
                            dd_level4 : $('#dd_level4').val(),
                            dd_superv : $('#dd_superv').val(),
                            criteria : $('#criteria').val(),
                            search_text : $('#search_text').val(),
                            option : '',
                        },
                        type: 'GET',
                        success: function (data) {
                            g_matched_employees = data;
                            document.getElementById('employee-list-select-all').disabled = false;
                        },
                        error: function (error) {
                            console.log('Unable to GET Select All values.');
                        }
                    });
                },
                rowCallback: function( row, data, displayNum, displayIndex, dataIndex ) {
                },
                columns: [
                    {title: '<input name="select_all" value="1" id="employee-list-select-all" type="checkbox" />', ariaTitle: 'employee-list-select-all', target: 0, orderData: [0, 1], type: 'string', data: 'select_users', name: 'select_users', orderable: false, searchable: false},
                    {title: 'ID', ariaTitle: 'ID', target: 1, orderData: [1], type: 'string', data: 'employee_id', name: 'employee_id', className: 'dt-nowrap'},
                    {title: 'Name', ariaTitle: 'Name', target: 2, orderData: [2, 1], type: 'string', data: 'employee_name', name: 'employee_name', className: 'dt-nowrap'},
                    {title: 'Supervisor', ariaTitle: 'Supervisor', target: 3, orderData: [3, 1], type: 'string', data: 'isSupervisor', name: 'isSupervisor', searchable: true, className: 'dt-nowrap'},
                    {title: 'Classification', ariaTitle: 'Classification', target: 4, orderData: [4, 1], type: 'string', data: 'jobcode_desc', name: 'jobcode_desc', className: 'dt-nowrap'},
                    {title: 'Email', ariaTitle: 'Email', target: 5, orderData: [5, 1], type: 'string', data: 'employee_email', name: 'employee_email', className: 'dt-nowrap'},
                    {title: 'Organization', ariaTitle: 'Organization', target: 6, orderData: [6, 1], type: 'string', data: 'organization', name: 'organization', className: 'dt-nowrap'},
                    {title: 'Level 1', ariaTitle: 'Level 1', target: 7, orderData: [7, 1], type: 'string', data: 'level1_program', name: 'level1_program', className: 'dt-nowrap'},
                    {title: 'Level 2', ariaTitle: 'Level 2', target: 8, orderData: [8, 1], type: 'string', data: 'level2_division', name: 'level2_division', className: 'dt-nowrap'},
                    {title: 'Level 3', ariaTitle: 'Level 3', target: 9, orderData: [9, 1], type: 'string', data: 'level3_branch', name: 'level3_branch', className: 'dt-nowrap'},
                    {title: 'Level 4', ariaTitle: 'Level 4', target: 10, orderData: [10, 1], type: 'string', data: 'level4', name: 'level4', className: 'dt-nowrap'},
                    {title: 'Dept', ariaTitle: 'Dept', target: 11, orderData: [11, 1], type: 'string', data: 'deptid', data: 'deptid', name: 'deptid', className: 'dt-nowrap'},
                ],
            });

            $('#employee-list-table tbody').on( 'click', 'input:checkbox', function () {
                // if the input checkbox is selected 
                var id = this.value;
                var index = $.inArray(id, g_selected_employees);
                if(this.checked) {
                    g_selected_employees.push( id );
                    g_selected_employees = [...new Set(g_selected_employees)];
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
            // Handle click on "Select all" control
            $('#employee-list-select-all').on('click', function() {
                // Check/uncheck all checkboxes in the table
                $('#employee-list-table tbody input:checkbox').prop('checked', this.checked);
                if (this.checked) {
                    g_selected_employees = g_selected_employees.concat(g_matched_employees);
                    g_selected_employees = [...new Set(g_selected_employees)];
                    $('#employee-list-select-all').prop("checked", true);
                    $('#employee-list-select-all').prop("indeterminate", false);    
                } else {
                    g_selected_employees = g_selected_employees.filter(x => ($.inArray( x , g_matched_employees) === -1))
                    $('#employee-list-select-all').prop("checked", false);
                    $('#employee-list-select-all').prop("indeterminate", false);    
                }    
            });
        });

    </script>
@endpush

