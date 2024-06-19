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
            <table class="table table-bordered table-striped" id="aemployee-list-table"></table>
        </div>    
    </div>   
</div>


@push('css')

    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<style>
	#aemployee-list-table_filter label {
		text-align: right !important;
        padding-right: 10px;
	} 
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    
        $(document).ready(function() {
            var user_selected = [];

            var oTable = $('#aemployee-list-table').DataTable({
                scrollX: true,
                retrieve: true,
                searching: false,
                processing: true,
                serverSide: true,
                select: true,
                order: [[1, 'asc']],
                ajax: {
                    url: '/'+'{{ request()->segment(1) }}'+'/goalbank/employee-list/a',

                    data: function (d) {
                        d.add_level0 = $('#add_level0').val();
                        d.add_level1 = $('#add_level1').val();
                        d.add_level2 = $('#add_level2').val();
                        d.add_level3 = $('#add_level3').val();
                        d.add_level4 = $('#add_level4').val();
                        d.add_superv = $('#add_superv').val();
                        d.acriteria = $('#acriteria').val();
                        d.asearch_text = $('#asearch_text').val();
                    }
                },
                preDrawCallback: function ( settings ) {
                    document.getElementById('aemployee-list-select-all').disabled = true;
                }, 
                drawCallback: function( settings ) {
                    list = ( $('#aemployee-list-table input:checkbox') );
                    $.each(list, function( index, item ) {
                        var index = $.inArray( item.value , ag_selected_employees);
                        if ( index === -1 ) {
                            $(item).prop('checked', false); // unchecked
                        } else {
                            $(item).prop('checked', true);  // checked 
                        }
                    });
                    // update the check all checkbox status 
                    if (ag_selected_employees.length == 0) {
                        $('#aemployee-list-select-all').prop("checked", false);
                        $('#aemployee-list-select-all').prop("indeterminate", false);   
                    } else if (ag_selected_employees.length == ag_matched_employees.length) {
                        $('#aemployee-list-select-all').prop("checked", true);
                        $('#aemployee-list-select-all').prop("indeterminate", false);   
                    } else {
                        $('#aemployee-list-select-all').prop("checked", false);
                        $('#aemployee-list-select-all').prop("indeterminate", true);    
                    }
                    // Get all selection
                    $.ajax({
                        url: '{{ "/" . request()->segment(1) . "/goalbank/getfilteredlist" }}',
                        data: {
                            add_level0 : $('#add_level0').val(),
                            add_level1 : $('#add_level1').val(),
                            add_level2 : $('#add_level2').val(),
                            add_level3 : $('#add_level3').val(),
                            add_level4 : $('#add_level4').val(),
                            add_superv : $('#add_superv').val(),
                            acriteria : $('#acriteria').val(),
                            asearch_text : $('#asearch_text').val(),
                            option : 'a',
                        },
                        type: 'GET',
                        success: function (data) {
                            ag_matched_employees = data;
                            document.getElementById('aemployee-list-select-all').disabled = false;
                    },
                        error: function (error) {
                            console.log('Unable to GET Select All values.');
                        }
                    });
                },
                rowCallback: function ( row, data, displayNum, displayIndex, displayIndex ) {
                },
                columns: [
                    {title: '<input name="aselect_all" value="1" id="aemployee-list-select-all" type="checkbox" />', ariaTitle: 'aemployee-list-select-all', target: 0, orderData: [0, 1], type: 'string', data: 'aselect_users', name: 'aselect_users', orderable: false, searchable: false},
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

            $('#aemployee-list-table tbody').on( 'click', 'input:checkbox', function () {
                // if the input checkbox is selected 
                var id = this.value;
                var index = $.inArray(id, ag_selected_employees);
                if(this.checked) {
                    ag_selected_employees.push( id );
                    ag_selected_employees = [...new Set(ag_selected_employees)];
                } else {
                    ag_selected_employees.splice( index, 1 );
                }
                // update the check all checkbox status 
                if (ag_selected_employees.length == 0) {
                    $('#aemployee-list-select-all').prop("checked", false);
                    $('#aemployee-list-select-all').prop("indeterminate", false);   
                } else if (ag_selected_employees.length == ag_matched_employees.length) {
                    $('#aemployee-list-select-all').prop("checked", true);
                    $('#aemployee-list-select-all').prop("indeterminate", false);   
                } else {
                    $('#aemployee-list-select-all').prop("checked", false);
                    $('#aemployee-list-select-all').prop("indeterminate", true);    
                }
            });

            // Handle click on "Select all" control
            $('#aemployee-list-select-all').on('click', function() {
                // Check/uncheck all checkboxes in the table
                $('#aemployee-list-table tbody input:checkbox').prop('checked', this.checked);
                if (this.checked) {
                    ag_selected_employees = ag_selected_employees.concat(ag_matched_employees);
                    ag_selected_employees = [...new Set(ag_selected_employees)];
                    $('#aemployee-list-select-all').prop("checked", true);
                    $('#aemployee-list-select-all').prop("indeterminate", false);    
                } else {
                    ag_selected_employees = ag_selected_employees.filter(x => ($.inArray( x , ag_matched_employees) === -1))
                    $('#aemployee-list-select-all').prop("checked", false);
                    $('#aemployee-list-select-all').prop("indeterminate", false);    
                }    
            });

        });

    </script>
@endpush

