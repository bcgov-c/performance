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
            <table class="table table-bordered" id="employee-list-table"></table>
        </div>    
    </div>   
</div>


@push('css')

    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<style>
	#employee-list-table_filter label {
		text-align: right !important;
        padding-right: 10px;
	} 
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
    
        $(document).ready( function() {

        //     $('#employee-list-table tbody').on( 'click', 'input:checkbox', function () {

        //         // if the input checkbox is selected 
        //         var id = this.value;
        //         var index = $.inArray(id, g_selected_employees);
        //         if(this.checked) {
        //             g_selected_employees.push( id );
        //         } else {
        //             g_selected_employees.splice( index, 1 );
        //         }

        //         // update the check all checkbox status 
        //         if (g_selected_employees.length == 0) {
        //             $('#employee-list-select-all').prop("checked", false);
        //             $('#employee-list-select-all').prop("indeterminate", false);   
        //         } else if (g_selected_employees.length == g_matched_employees.length) {
        //             $('#employee-list-select-all').prop("checked", true);
        //             $('#employee-list-select-all').prop("indeterminate", false);   
        //         } else {
        //             $('#employee-list-select-all').prop("checked", false);
        //             $('#employee-list-select-all').prop("indeterminate", true);    
        //         }
        //     });

        //     // Handle click on "Select all" control
        //     $('#employee-list-select-all').on('click', function() {
        //         // Check/uncheck all checkboxes in the table
        //         $('#employee-list-table tbody input:checkbox').prop('checked', this.checked);
        //         if (this.checked) {
        //             g_selected_employees = g_matched_employees.map((x) => x);
        //             $('#employee-list-select-all').prop("checked", true);
        //             $('#employee-list-select-all').prop("indeterminate", false);    
        //         } else {
        //             g_selected_employees = [];
        //             $('#employee-list-select-all').prop("checked", false);
        //             $('#employee-list-select-all').prop("indeterminate", false);    
        //         }    
        //     });

        });

    </script>
@endpush

