<div class="card px-3 pb-3">
    <div class="p-0">
        <div class="accordion-option">
            @error('euserCheck')                
            <span class="text-danger">
                {{  'The recipient is required.'  }}
            </span>
            @enderror
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <h6></h6>
            <table class="table table-bordered" id="eemployee-list-table"></table>
        </div>    
    </div>   
</div>


@push('css')

    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<style>
	#eemployee-list-table_filter label {
		text-align: right !important;
        padding-right: 10px;
	} 
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
    
        $(document).ready(function() {
            // var euser_selected = [];

            // var oTable = $('#eemployee-list-table').DataTable({
            //     "scrollX": true,
            //     retrieve: true,
            //     "searching": false,
            //     processing: true,
            //     serverSide: true,
            //     select: true,
            //     'order': [[1, 'asc']],
            //     ajax: {
            //         url: '{!! route('sysadmin.employeeshares.eemployee.list') !!}',
            //         data: function (d) {
            //             d.edd_level0 = $('#edd_level0').val();
            //             d.edd_level1 = $('#edd_level1').val();
            //             d.edd_level2 = $('#edd_level2').val();
            //             d.edd_level3 = $('#edd_level3').val();
            //             d.edd_level4 = $('#edd_level4').val();
            //             d.ecriteria = $('#ecriteria').val();
            //             d.esearch_text = $('#esearch_text').val();
            //         }
            //     },
            //     "fnDrawCallback": function() {

            //         elist = ( $('#eemployee-list-table input:checkbox') );

            //         $.each(elist, function( index, item ) {
            //             var index = $.inArray( item.value , eg_selected_employees);
            //             if ( index === -1 ) {
            //                 $(item).prop('checked', false); // unchecked
            //             } else {
            //                 $(item).prop('checked', true);  // checked 
            //             }
            //         });

            //         // update the check all checkbox status 
            //         if (eg_selected_employees.length == 0) {
            //             $('#eemployee-list-select-all').prop("checked", false);
            //             $('#eemployee-list-select-all').prop("indeterminate", false);   
            //         } else if (eg_selected_employees.length == eg_matched_employees.length) {
            //             $('#eemployee-list-select-all').prop("checked", true);
            //             $('#eemployee-list-select-all').prop("indeterminate", false);   
            //         } else {
            //             $('#eemployee-list-select-all').prop("checked", false);
            //             $('#eemployee-list-select-all').prop("indeterminate", true);    
            //         }

            //     },
            //     "rowCallback": function( row, data ) {
            //     },
            //     columns: [
            //         {title: '<input name="select_all" value="1" id="eemployee-list-select-all" type="checkbox" />', ariaTitle: 'eemployee-list-select-all', target: 0, type: 'string', data: 'eselect_users', name: 'eselect_users', orderable: false, searchable: false},
            //         {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'eemployee_id', name: 'eemployee_id'},
            //         {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'eemployee_name', name: 'eemployee_name'},
            //         {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'ejobcode_desc', name: 'ejobcode_desc'},
            //         {title: 'Email', ariaTitle: 'Email', target: 0, type: 'string', data: 'eemployee_email', name: 'eemployee_email' },
            //         {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'eorganization', name: 'eorganization'},
            //         {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'elevel1_program', name: 'elevel1_program'},
            //         {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'elevel2_division', name: 'elevel2_division'},
            //         {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'elevel3_branch', name: 'elevel3_branch'},
            //         {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'elevel4', name: 'elevel4'},
            //         {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', data: 'edeptid', name: 'edeptid'},
            //     ],
            //     columnDefs: [
            //         {
            //         },
            //         {
            //         },
            //         {
            //             className: "dt-nowrap",
            //             targets: 2
            //         },
            //         {
            //             className: "dt-nowrap",
            //             targets: 3
            //         },
            //         {
            //             className: "dt-nowrap",
            //             targets: 4
            //         },
            //         {
            //             className: "dt-nowrap",
            //             targets: 5
            //         },        
            //         {
            //             className: "dt-nowrap",
            //             targets: 6
            //         },
            //         {
            //             className: "dt-nowrap",
            //             targets: 7
            //         },        
            //         {
            //             className: "dt-nowrap",
            //             targets: 8
            //         },        
            //         {
            //             className: "dt-nowrap",
            //             targets: 9
            //         },        
            //         {
            //             className: "dt-nowrap",
            //             targets: 10
            //         }        
            //     ]
            // });


            // $('#eemployee-list-table tbody').on( 'click', 'input:checkbox', function () {

            //     // if the input checkbox is selected 
            //     var id = this.value;
            //     var index = $.inArray(id, eg_selected_employees);
            //     if(this.checked) {
            //         eg_selected_employees.push( id );
            //     } else {
            //         eg_selected_employees.splice( index, 1 );
            //     }

            //     // update the check all checkbox status 
            //     if (eg_selected_employees.length == 0) {
            //         $('#eemployee-list-select-all').prop("checked", false);
            //         $('#eemployee-list-select-all').prop("indeterminate", false);   
            //     } else if (eg_selected_employees.length == eg_matched_employees.length) {
            //         $('#eemployee-list-select-all').prop("checked", true);
            //         $('#eemployee-list-select-all').prop("indeterminate", false);   
            //     } else {
            //         $('#eemployee-list-select-all').prop("checked", false);
            //         $('#eemployee-list-select-all').prop("indeterminate", true);    
            //     }
            // });

            // // Handle click on "Select all" control
            // $('#eemployee-list-select-all').on('click', function() {
            //     // Check/uncheck all checkboxes in the table
            //     $('#eemployee-list-table tbody input:checkbox').prop('checked', this.checked);
            //     if (this.checked) {
            //         eg_selected_employees = eg_matched_employees.map((x) => x);
            //         $('#eemployee-list-select-all').prop("checked", true);
            //         $('#eemployee-list-select-all').prop("indeterminate", false);    
            //     } else {
            //         eg_selected_employees = [];
            //         $('#eemployee-list-select-all').prop("checked", false);
            //         $('#eemployee-list-select-all').prop("indeterminate", false);    
            //     }    
            // });

        });

    </script>
@endpush

