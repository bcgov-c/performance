<div class="card px-3 pb-3">
    <div class="p-0">
        <div class="accordion-option">
            @error('userCheck')                
            <span class="text-danger">
                {{  'The recipient is required.'  }}
            </span>
            @enderror
        </div>
    </div>


<div class="card">
	<div class="card-body">
		<h6></h6>
		<table class="table table-bordered table-striped" id="employee-list-table">
			<thead>
				<tr>
                    <th><input name="select_all" value="1" id="employee-list-select-all" type="checkbox" /></th>
                    <th>Employee ID</th>
					<th>Employee Name</th>
                    <th>Job Title</th>
					<th>Email</th>
					<th>Organization</th>
					<th>Program</th>
					<th>Division</th>
					<th>Branch</th>
                    <th>Level 4</th>
                    <th>Department </th>
                    <th>Action </th>
				</tr>
			</thead>
		</table>

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
            "scrollX": true,
            retrieve: true,
            "searching": false,
            processing: true,
            serverSide: true,
            select: true,
            'order': [[1, 'asc']],
            ajax: {
                url: '{!! route('sysadmin.notifications.employee.list') !!}',
                data: function (d) {
                    d.dd_level0 = $('#dd_level0').val();
                    d.dd_level1 = $('#dd_level1').val();
                    d.dd_level2 = $('#dd_level2').val();
                    d.dd_level3 = $('#dd_level3').val();
                    d.dd_level4 = $('#dd_level4').val();
                    d.job_titles = $('#job_titles').val();
                    d.hire_dt = $('#hire_dt').val();
                    d.criteria = $('#criteria').val();
                    d.search_text = $('#search_text').val();
                }
            },
            "fnDrawCallback": function() {

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

            },
            "rowCallback": function( row, data ) {
                // if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
                //     $(row).addClass('selected');
                // }
            },
            columns: [
                {data: 'select_users', name: 'select_users', orderable: false, searchable: false},
                {data: 'employee_id', name: 'employee_id'},
                {data: 'employee_name', name: 'employee_name'},
                {data: 'job_title', name: 'job_title'},
                {data: 'employee_email', name: 'employee_email' },
                {data: 'organization', name: 'organization'},
                {data: 'level1_program', name: 'level1_program'},
                {data: 'level2_division', name: 'level2_division'},
                {data: 'level3_branch', name: 'level3_branch'},
                {data: 'level4', name: 'level4'},
                {data: 'deptid', name: 'deptid'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            columnDefs: [
                    // {
                    //         'targets': 0,
                    //         'searchable':false,
                    //         'orderable':false,
                    //         'className': 'dt-body-center',
                    //         'render': function (data, type, full, meta){
                    //             return '<input pid="1335" type="checkbox" id="userCheck' +
                    //             $data->id +'" name="userCheck[]" value="'+data->id.'">';

                    //             return '<input type="checkbox" name="id[]" value="' 
                    //                 + $('<div/>').text(data).html() + '">';
                    //         }
                    // }, 
                    {
                        // render: function (data, type, full, meta) {
                        //     console.log(data);
                        //     array_tos = data.split(";");
                        //     if (array_tos.length > 3) {
                        //         text = '( ' + array_tos.length + ' recipients )';
                        //     } else { text = data; }
                        //     return '<div data-toggle="tooltip" class="text-truncate-10xx" title="' + data + '">' + text + "</div>";
                        // },
                        // targets: 2
                    },

                    {
                        // render: function (data, type, full, meta) {
                        //     return '<div data-toggle="tooltip" class="text-truncate-30" title="' + data + '">' + data + "</div>";
                        // },
                        // targets: 1
                    },
                    {
                        // render: function (data, type, full, meta) {
                        //     return "<small>" + data + "</small>";
                        // },
                        // targets: 0
                        className: "dt-nowrap",
                        targets: 2
                    },
                    {
                        className: "dt-nowrap",
                        targets: 3
                    },
                    {
                        className: "dt-nowrap",
                        targets: 4
                    },
                    {
                        className: "dt-nowrap",
                        targets: 5
                    },        
                    {
                        className: "dt-nowrap",
                        targets: 6
                    },
                    {
                        className: "dt-nowrap",
                        targets: 7
                    },        
                    {
                        className: "dt-nowrap",
                        targets: 8
                    },        
                    {
                        className: "dt-nowrap",
                        targets: 9
                    }        

                ]
        });


        $('#employee-list-table tbody').on( 'click', 'input:checkbox', function () {

            // if the input checkbox is selected 
            var id = this.value;
            var index = $.inArray(id, g_selected_employees);
            if(this.checked) {
                g_selected_employees.push( id );
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

            // console.log (g_selected_employees );

        });

        // Handle click on "Select all" control
        $('#employee-list-select-all').on('click', function() {
            
            //g_selected_employees = g_matched_employees.map((x) => x);

            // Check/uncheck all checkboxes in the table
            $('#employee-list-table tbody input:checkbox').prop('checked', this.checked);
            if (this.checked) {
                g_selected_employees = g_matched_employees.map((x) => x);
                $('#employee-list-select-all').prop("checked", true);
                $('#employee-list-select-all').prop("indeterminate", false);    
            } else {
                g_selected_employees = [];
                $('#employee-list-select-all').prop("checked", false);
                $('#employee-list-select-all').prop("indeterminate", false);    
            }    
              
        });

    });

    </script>
@endpush

