<x-side-layout title="{{ __('System Security - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Access Organizations
        </h2> 
		@include('sysadmin.system-security.partials.tabs')
    </x-slot>

    <div class="card search-filter">

        <div class="card-body pb-0">
            <h2>Search Criteria</h2>
    
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="filer_organization">
                        Organization
                    </label>
                    <input name="user" id="filer_organization"  class="form-control" />
                </div>
    
                <div class="form-group col-md-2">
                    <label for="filter_allow_login">
                        Allow Login
                    </label>
                    <select name="login_method" id="filter_allow_login" value="" class="form-control">
                        <option value="">Select a method</option>
                        <option value="Y">Yes</option>
                        <option value="N">No</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="filter_allow_inapp_msg">
                        Allow In-App Message
                    </label>
                    <select name="login_method" id="filter_allow_inapp_msg" value="" class="form-control">
                        <option value="">Select a method</option>
                        <option value="Y">Yes</option>
                        <option value="N">No</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="filter_allow_email_msg">
                        Allow Email Message
                    </label>
                    <select name="login_method" id="filter_allow_email_msg" value="" class="form-control">
                        <option value="">Select a method</option>
                        <option value="Y">Yes</option>
                        <option value="N">No</option>
                    </select>
                </div>
    
                <div class="form-group col-md-1">
                    <label for="search">
                        &nbsp;
                    </label>
                    <button type="button" id="search-btn" value="search" class="form-control btn btn-primary" />Search</button>
                </div>
                <div class="form-group col-md-1">
                    <label for="search">
                        &nbsp;
                    </label>            
                    <button type="button" id="reset-btn" value="reset" class="form-control btn btn-secondary">Reset</button>        
                </div>
            </div>
    
        </div>    
    </div>        


<div class="card">
        
    <div class="px-4"></div>

	<div class="card-body">

            <table class="table table-bordered" class="row-border" id="accessorgs-table" style="width:100%"></table>

	</div>
</div>

@include('sysadmin.system-security.access-orgs.partials.modal-edit')

<x-slot name="css">

    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedheader/3.2.4/css/fixedHeader.dataTables.min.css" rel="stylesheet">

	<style>
    #accessorgs-table_filter {
        display: none;
    }

    .dataTables_scrollBody {
        margin-bottom: 10px;
    }
    </style>

</x-slot>


<x-slot name="js">

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.2.4/js/dataTables.fixedHeader.min.js"></script>

    <script type="x-tmpl" id="charity-tmpl">
    <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" id="enable-login-selected">Enable Login</a>
            <a class="dropdown-item" id="disable-login-selected">Disable Login</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" id="reset-selected">Reset selected</a>
        </div>
    </div>
    </script>
    
    <script>
    let g_matched_employees = {!!json_encode($matched_emp_ids)!!};
	let g_selected_employees = {!!json_encode($old_selected_emp_ids)!!};

    $(function() {
        // Datatables
        var oTable = $('#accessorgs-table').DataTable({
            "scrollX": true,
            retrieve: true,
            "searching": true,
            processing: true,
            serverSide: true,
            // select: true,
            fixedHeader: true,    
            pageLength: 10,
            dom: '<"toolbar">frtip',
            ajax: {
                url: '{!! route('access-orgs.index') !!}',
                data: function (data) {
                    data.organization = $('#filer_organization').val();
                    data.allow_login = $('#filter_allow_login').val();
                    data.allow_inapp_msg = $('#filter_allow_inapp_msg').val();
                    data.allow_email_msg = $('#filter_allow_email_msg').val();
                }
            },
            "fnDrawCallback": function() {
                list = ( $('#accessorgs-table input:checkbox') );
                $.each(list, function( index, item ) {
                    var index = $.inArray( parseInt(item.value) , g_selected_employees);
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
            columns: [
                {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', visible: false, className: "dt-nowrap" },
                {title: '<input name="select_all" value="1" id="employee-list-select-all" type="checkbox" />', ariaTitle: 'employee-list-select-all', target: 0, type: 'string', data: 'select_users', name: 'select_users', orderable: false, searchable: false},
                {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'organization', className: "dt-nowrap" },
                {title: 'No. Of Employees', ariaTitle: 'No. Of Employees', target: 0, type: 'string', data: 'active_employee_ids_count', className: 'dt-center'},
                {title: 'Allow Login', ariaTitle: 'Allow Login', target: 0, type: 'string', data: 'allow_login', className: 'dt-center', render: function ( data, type, row, meta ) {
                        if(data == 'Y') {
                            return '<i class="fa fa-user-check fa-lg text-primary"</i>';
                        } else {
                            return '<i class="fa fa-user-times fa-lg text-danger"> </i>';
                        }
                    }
                },
                {title: 'Allow In-App Message', ariaTitle: 'Allow In-App Message', target: 0, type: 'string', data: 'allow_inapp_msg', className: 'dt-center', render: function ( data, type, row, meta ) {
                        if(data == 'Y') {
                            return '<i class="fa fa-check fa-lg text-primary"> </i>';
                        } else {
                            return '<i class="fa fa-times fa-lg text-danger"> </i>';
                        }
                    }
                },
                {title: 'Allow eMail Message', ariaTitle: 'Allow eMail Message', target: 0, type: 'string', data: 'allow_email_msg', className: 'dt-center', render: function ( data, type, row, meta ) {
                        if(data == 'Y') {
                            return '<i class="fa fa-check fa-lg text-primary"> </i>';
                        } else {
                            return '<i class="fa fa-times fa-lg text-danger"> </i>';
                        }
                    }
                },
                {title: 'Action', ariaTitle: 'Action', target: 0, type: 'string', data: 'action', name: 'action', orderable: false, searchable: false, className: "dt-nowrap"},
                {title: 'Created By', ariaTitle: 'Created By', target: 0, type: 'string', data: 'created_by.name', name: 'created_by.name', defaultContent: '', orderable: false, searchable: false, className: "dt-nowrap"},
                {title: 'Updated By', ariaTitle: 'Updated By', target: 0, type: 'string', data: 'updated_by.name', name: 'updated_by.name', defaultContent: '', orderable: false, searchable: false, className: "dt-nowrap"},
                {title: 'Created At', ariaTitle: 'Created At', target: 0, type: 'string', data: 'created_at', name: 'created_at', orderable: false, searchable: false, className: "dt-nowrap"},
                {title: 'Updated At', ariaTitle: 'Updated At', target: 0, type: 'string', data: 'updated_at', name: 'updated_at', orderable: false, searchable: false, className: "dt-nowrap"},
            ],
        });


        text = $("#charity-tmpl").html();
        $('div.toolbar').html( text );

        $(document).on( 'click', '#accessorgs-table tbody input:checkbox', function (e) {

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
        $(document).on('click', '#employee-list-select-all', function(e) {
  
            // Check/uncheck all checkboxes in the table
            $('#accessorgs-table tbody input:checkbox').prop('checked', this.checked);
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


        function toggleAllowLogin( option ) {

            var form = $('#accessorgs-form');
            $("input[name='allow_login']").val( option );
            
            $.ajax({
                url:  '{{ route("access-orgs-toggle-allow-login") }}',
                method: "POST",
                data: { 'selected_orgs': g_selected_employees,
                         'allow_login' : option,
                        },
                dataType: 'json',
                success: function(data)
                {
                    oTable.ajax.reload(null, false);	// reload datatables
                    Toast('Success', 'The selected organizations were successfully updated.', 'bg-success' );
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        }

        $(document).on('click', '#enable-login-selected', function(e) {
            e.preventDefault();

            if (g_selected_employees.length == 0) {
                alert("Please select row(s).");
            } else {
                info = 'Confirm to enable login for the sslected rows?';
                if (confirm(info)) 
                    toggleAllowLogin('Y');
            }
        });

        $(document).on('click', '#disable-login-selected', function(e) {
            e.preventDefault();

            if (g_selected_employees.length == 0) {
                alert("Please select row(s).");
            } else {
                info = 'Confirm to disable login for the sslected rows?';
                if (confirm(info)) 
                    toggleAllowLogin('N');
            }
        });

        // Reset 
        $(document).on('click', '#reset-selected', function(e) {

            e.preventDefault();

            if (g_selected_employees.length == 0) {
                alert("Please select row(s).");
            } else {
                info = 'Confirm to enable login for the sslected rows?';
                if (confirm(info)) {
                    $.ajax({
                        url:  '{{ route("access-orgs-reset") }}',
                        method: "POST",
                        data: { 'selected_orgs': g_selected_employees,
                                },
                        dataType: 'json',
                        success: function(data)
                        {
                            oTable.ajax.reload(null, false);	// reload datatables
                            Toast('Success', 'The selected organizations were successfully reset.', 'bg-success' );
                        },
                        error: function(response) {
                            console.log('Error');
                        }
                    });
                }
            }            
        });


        // $('#user').on('keyup change', function () {
        //     oTable.draw();
        // });

        // $('#login_method').on('change', function () {
        //     oTable.columns( 'login_method:name' ).search( this.value ).draw();            
        // });

        // $('.edit-org').on('change', function () {
        //     oTable.draw();
        // });

        $('#search-btn').on('click', function() {
            oTable.draw();
        });

        $('#reset-btn').on('click', function() {
            $('.search-filter input').map( function() {$(this).val(''); });
            $('.search-filter select').map( function() { return $(this).val(''); })

            oTable.search( '' ).columns().search( '' ).draw();
        });
        

       //
        function Toast( toast_title, toast_body, toast_class) {
            $(document).Toasts('create', {
                            class: toast_class,
                            title: toast_title,
                            autohide: true,
                            delay: 3000,
                            body: toast_body
            });
        }

        // Modal -- Edit 
        $(document).on("click", ".edit-org" , function(e) {
            e.preventDefault();

            var fields = ['organization', 'allow_login', 'allow_inapp_msg', 'allow_email_msg'];
                $.each( fields, function( index, field_name ) {
                    $('#accessorg-edit-model-form [name='+field_name+']').nextAll('span.text-danger').remove();
            });

            id = $(this).attr('data-id');

            $.ajax({
                method: "GET",
                url:  'access-orgs/' + id  + '/edit',
                dataType: 'json',
                success: function(data)
                {
                    console.log(data);
                    $.each(data, function(field_name,field_value ){
                        $(document).find('#accessorg-edit-model-form [name='+field_name+']').val(field_value).change();
                    });
                    $('#accessorg-edit-modal').modal('show');
                },
                error: function(response) {
                    console.log('Error');
                }
            });
        });

        $(document).on("click", "#save-confirm-btn" , function(e) {

            var form = $('#accessorg-edit-model-form');
            var id = $("#accessorg-edit-model-form [name='id']").val();

            info = 'Confirm to update this record?';
            if (confirm(info))
            {
                var fields = ['organization', 'allow_login', 'allow_inapp_msg', 'allow_email_msg'];
                $.each( fields, function( index, field_name ) {
                    $('#accessorg-edit-model-form [name='+field_name+']').nextAll('span.text-danger').remove();
                });

                $.ajax({
                    method: "PUT",
                    url:  'access-orgs/' + id,
                    data: form.serialize(), // serializes the form's elements.
                    success: function(data)
                    {
                        oTable.ajax.reload(null, false);	// reload datatables
                        $('#accessorg-edit-modal').modal('hide');

                        var organization = $("#accessorg-edit-model-form [name='code']").val();
                        Toast('Success', 'Organization "' + data.organization +  '" was successfully updated.', 'bg-success' );

                    },
                    error: function(response) {
                        if (response.status == 422) {

                            $.each(response.responseJSON.errors, function(field_name,error){
                                $(document).find('[name='+field_name+']').after('<span class="text-strong text-danger">' +error+ '</span>')
                            })
                        }
                        console.log('Error');
                    }
                });

            };
        });


    });

    </script>
</x-slot>    


</x-side-layout>