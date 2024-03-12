<div>

    <table style="height: 2em;" class="ml-3">
        <tbody>
          <tr>
            <td class="align-middle">
                <div class="pr-3">
                    <label for="employee-list-select-all ">
                    <div role="group" aria-labelledby="id-group-label">
                        <ul class="checkboxes">
                            <li><div role="checkbox" aria-checked="false"  name="select_all" id="employee-list-select-all" tabindex="0">Select All</div></li>
                        </ul>
                    </div>
                </div>

            </td>
            <td class="align-middle ">
                <label for="action_btn">
                    <button type="button" icon="fas fa-xs fa-ellipsis-v" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Actions
                    </button>
                    <div class="dropdown-menu"  size="xs">
                        <x-button icon="fas fa-xs fa-trash-alt" class="dropdown-item delete_all" id='delete_all'>
                            Delete selected
                        </x-button>
                        <x-button icon="fas fa-xs fa-envelope-open" href="{{ route('dashboard.updatestatus') }}" class="dropdown-item update_status" >
                            Mark as read
                        </x-button>
                        <x-button icon="fas fa-xs fa-envelope" href="{{ route('dashboard.resetstatus') }}" class="dropdown-item reset_status" >
                            Mark as unread
                        </x-button>
                    </div>
                  </label>
                </td>
            </tr>
    </table>

    <hr class="separator"/>

    <table class="table" id="notification-table" style="width:100%;">
        <thead>
            <tr>
                <th>Created At</th>
                <th>Checkbox</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>

</div>

<x-slot name="css">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        #notification-table_filter label {
            text-align: right !important;
        }

        #notification-table_wrapper .row:first-child {
            display: none;
        }

        #notification-table thead {
            display: none;
        }

        table.inner, table.inner th, table.inner td {
            padding: 0px;
            margin: 2px;
            border: none;
        }

        table.inner td.new {
            /* background-color: green; */
            margin-top: 3px;
            margin-bottom: 3px;
            border-left: 3px solid #1a5a96;
        }

        table.inner td.read {
            /* background-color: green; */
            margin-top: 3px;
            margin-bottom: 3px;

            border-left: 3px solid #ffffff;
        }

        .table tr.odd:first-child td {
            border-top: none !important;
        }
        
        #notification-table input[name='itemCheck[]'] {
            width: 1.1em;
            height: 1.1em;
        }

        input[name='select_all'] {
            width: 1.1em;
            height: 1.1em;
        }

        hr.separator {
                border: 1px solid #EED202;
                border-radius: 5px;
                padding: 0px;
                margin: 2px;
        }

    </style>
</x-slot>


{{-- @include('dashboard.partials.delete-notification-hidden-form') --}}

<x-slot name="js">

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
    let g_matched_employees = {!!json_encode($matched_dn_ids)!!};
    let g_selected_employees = {!!json_encode($old_selected_dn_ids)!!};

    $(function() {

@if ($open_modal)
        $('#profileSharedWithViewModal').modal('show');
@endif 

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': '{{csrf_token()}}'
            }
        });

        var oTable = $('#notification-table').DataTable({
            retrieve: true,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: {
                url: '{!! route("dashboard") !!}',
                data: function (d) {}
            },
            createdRow: function( row, data, dataIndex){
                if( data.status == 'R'){
                    $(row).addClass('bg-light');
                } 
            },
            fnDrawCallback: function() {
                list = $('#notification-table input:checkbox');

                $.each(list, function( index, item ) {
                    var pos = $.inArray(parseInt(item.value), g_selected_employees);
                    if (pos === -1) {
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

                // Update Badge Count
                updateBadgeCount();
            },
            columns: [
                {data: 'created_at', visible: false },
                {data: 'item_detail', 
                    name: 'item_detail', 
                    orderable: false, 
                    searchable: false,  
                    width: '80%', 
                    className: 'py-1',
                    render: function(data, type, full, meta) {
                        // Here you can customize the HTML content of the column
                        return `
                            <div role="group" aria-labelledby="id-group-label">
                                <ul class="checkboxes">
                                    <li><div role="checkbox" aria-checked="false" tabindex="0">${data}</div></li>
                                </ul>
                            </div>
                        `;
                    }
                },
                {data: 'action', name: 'action', orderable: false, searchable: false, width: '20%', className: 'dt-right dt-nowrap my-0'},
            ]
        });


        
        $('#notification-table tbody').on( 'click', 'input:checkbox', function () {

            // if the input checkbox is selected 
            var id = parseInt(this.value);
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

        });

        // Handle click on "Select all" control
        $('#employee-list-select-all').on('click', function() {

            // Check/uncheck all checkboxes in the table
            $('#notification-table tbody input:checkbox').prop('checked', this.checked);
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

        function resetSelectedCheckBox() {

            g_selected_employees = [];

            $('#notification-table tbody input:checkbox').prop('checked', false);
            $('#employee-list-select-all').prop("checked", false);

        }

        function updateBadgeCount() {

            $.ajax({
                url: "{{ route('dashboard.badgecount') }}",
                type: 'GET',
                dataType: 'json',
                data: {
                },
                success: function (data) {
                    $('#count-badge').html( data.count );
                }
            });
        }
        
        // Delete Button Click 
        $(document).on("click", ".delete-dn" , function(e) {
            e.preventDefault();

            id = $(this).attr('data-id');
            comment = $(this).attr('data-comment');

            $.ajax({
                    method: "DELETE",
                    url:  '/dashboard/' + id,
                    success: function(data)
                    {
                        oTable.ajax.reload(null, false);	// reload datatables
                        updateBadgeCount();
                    },
                    error: function(response) {
                        console.log('Error');
                    }
            });

        });

        // Mass Action 
        $('.update_status').on('click', function(e) {
            e.preventDefault();
            
            if (g_selected_employees.length == 0) {
                alert("Please select row(s) to update.");
            }  else {

                    $.ajax({
                        url: "{{ route('dashboard.updatestatus') }}",
                        // url: $(this).data('url'),
                        type: 'POST',
                        dataType: 'json',
                        data: { ids : JSON.stringify(g_selected_employees), },
                        success: function (data) {
                            oTable.ajax.reload(null, false);	// reload datatables
                            resetSelectedCheckBox();
                            updateBadgeCount();
                        }
                    });
            }
        });

        $('.reset_status').on('click', function(e) {
            e.preventDefault();

            // ajax call and refresh table, and counter
            if (g_selected_employees.length == 0) {
                alert("Please select row(s) to update.");
            }  else {
                    $.ajax({
                        url: "{{ route('dashboard.resetstatus') }}",
                        type: 'POST',
                        dataType: 'json',
                        data: { ids : JSON.stringify(g_selected_employees), 
                        },
                        success: function (data) {
                            oTable.ajax.reload(null, false);	// reload datatables
                            updateBadgeCount();
                            resetSelectedCheckBox();
                        }
                    });
            }
        });


        $('.delete_all').on('click', function(e) {
            e.preventDefault();

            // ajax call and refresh table, and counter
            if (g_selected_employees.length == 0) {
              alert("Please select row(s) to update.");
            } else {
                var check = confirm("Are you sure you want to delete selected row(s)?");
                if(check == true){

                    $.ajax({
                        url: "{{ route('dashboard.destroyall') }}",
                        type: 'DELETE',
                        dataType: 'json',
                        data: { ids : JSON.stringify(g_selected_employees), 
                        },
                        success: function (data) {
                            oTable.ajax.reload(null, true);	// reload datatables
                            updateBadgeCount();
                            resetSelectedCheckBox();
                        },
                        error: function (data) {
                            // alert(data.responseText);
                        }
                    });
                    $.each(allVals, function( index, value ) {
                        $('table tr').filter("[data-row-id='" + value + "']").remove();
                    });
                }
            }
        });

    });    

</script>

</x-slot>