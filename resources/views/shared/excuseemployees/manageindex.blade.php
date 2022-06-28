<x-side-layout title="{{ __('Dashboard') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Excuse Employees</h3>
            @include('shared.excuseemployees.partials.tabs')
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {{-- <div class="h4">{{__('Manage Existing Excused')}}</div> --}}
            @include('shared.excuseemployees.partials.filter')
            <div class="p-3">  
                <table class="table table-bordered filtertable table-striped" id="filtertable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   
    @include('shared/excuseemployees/partials/excused-edit-modal')
    {{-- @endsection --}}


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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script type="text/javascript">
            jq = jQuery.noConflict();
            jq(function( $ ) {
                var table = $('.filtertable').DataTable
                (
                    {
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        stateSave: true,
                        deferRender: true,
                        ajax: {
                            url: "{{ route(request()->segment(1).'.excuseemployees.manageindexlist') }}",
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
                            {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id', name: 'employee_demo.employee_id', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name', name: 'employee_demo.employee_name', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc', name: 'employee_demo.jobcode_desc', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Excused Start Date', ariaTitle: 'Excused Start Date', target: 0, type: 'num', data: 'excused_start_date', name: 'excused_start_date', searchable: true, visible: true, className: 'dt-nowrap show-modal'},
                            {title: 'Excused End Date', ariaTitle: 'Excused End Date', target: 0, type: 'num', data: 'excused_end_date', name: 'excused_end_date', searchable: true, visible: true, className: 'dt-nowrap show-modal'},
                            {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization', name: 'employee_demo.organization', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program', name: 'employee_demo.level1_program', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division', name: 'employee_demo.level2_division', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch', name: 'employee_demo.level3_branch', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4', name: 'employee_demo.level4', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid', name: 'employee_demo.deptid', searchable: true, className: 'dt-nowrap show-modal'},
                            {title: 'Action', ariaTitle: 'Action', target: 0, type: 'string', data: 'action', name: 'action', orderable: false, searchable: false},
                            {title: 'User ID', ariaTitle: 'User ID', target: 0, type: 'num', data: 'id', name: 'id', searchable: false, visible: false},
                        ]
                    }
                );
            });

            $('#editModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var excused_start_date = button.data('excused_start_date');
                var excused_end_date = button.data('excused_end_date');
                var excused_reason_id = button.data('excused_reason_id');
                var employee_name = button.data('employee_name');
                var current = {{ auth()->user()->id }};
                // $('#reason').val(reason);
                // $('#model_id').val(model_id);
                // $('#saveButton').prop('disabled', current == model_id);
                // $('removeButton').prop('disabled', current == model_id);
                // $('#accessselect').prop('disabled', current == model_id);
                // $('#reason').prop('disabled', current == model_id);


                $('#start_date').val(excused_start_date);
                $('#target_date').val(excused_end_date);
                $('#excused_reason_id').val(excused_reason_id);
                $('#excusedDetailLabel').text('Edit Employee Excuse:  '+employee_name);
                // $('#saveButton').prop('disabled', current == model_id);
                // $('removeButton').prop('disabled', current == model_id);
                // $('#accessselect').prop('disabled', current == model_id);
                // $('#reason').prop('disabled', current == model_id);
            });

            $('#editModal').on('hidden.bs.modal', function(event) {
                if($.fn.DataTable.isDataTable( '#admintable' )) {
                    table = $('#admintable').DataTable();
                    table.clear();
                    table.draw();
                };
            });

            $('#cancelButton').on('click', function(event) {
                // if($.fn.DataTable.isDataTable( '#admintable' )) {
                //     table = $('#admintable').DataTable();
                //     table.destroy();
                // };
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

            $('#removeButton').on('click', function(event) {
                console.log('Delete button clicked');
                var model_id = $('#model_id').val();
                var token = $('meta[name="csrf-token"]').attr('content');
                event.preventDefault();
                $.ajax ( {
                    type: 'POST',
                    url: 'manageindexclear/'+model_id,
                    data: {
                        'model_id':model_id,
                        '_token':token,
                        '_method':"DELETE",
                    },
                    success: function (result) {
                        window.location.href = 'manageindex';
                    }
                });
            });

            $(window).on('beforeunload', function(){
                $('#pageLoader').show();
            });

            $(window).resize(function(){
                location.reload();
                return;
            });

        </script>
    @endpush

</x-side-layout>