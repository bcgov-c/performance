<x-side-layout title="{{ __('Dashboard') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Shared Employees</h3>
            @include('shared.employeeshares.partials.tabs')
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="h4">{{__('Manage Existing Employee Shares')}}</div>
            @include('shared.employeeshares.partials.loader')
            <div class="p-3">  
                <table class="table table-bordered generictable table-striped" id="generictable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   

    @include('shared.employeeshares.partials.share-edit-modal')

    @push('css')
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>   --}}
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script type="text/javascript">

            function showModal ($id) {
                $("#edit-modal").modal('show');
            }

			$(document).ready( function() {

                $('#generictable').DataTable ( {
                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    stateSave: true,
                    deferRender: true,
                    ajax: {
                        url: "{{ route($request->segment(1).'.employeeshares.manageindexlist') }}",
                        type: 'GET',
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
                        {title: 'ID', ariaTitle: 'ID', target: 0, type: 'string', data: 'employee_id'
                            , name: 'employee_id', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Name', ariaTitle: 'Name', target: 0, type: 'string', data: 'employee_name'
                            , name: 'employee_name', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Delegate ID', ariaTitle: 'Delegate ID', target: 0, type: 'string', data: 'delegate_ee_id'
                            , name: 'delegate_ee_id', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Delegate Name', ariaTitle: 'Delegate Name', target: 0, type: 'string', data: 'delegate_ee_name'
                            , name: 'delegate_ee_name', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Shared Item', ariaTitle: 'Shared Item', target: 0, type: 'string', data: 'shared_item'
                            , name: 'shared_item', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Classification', ariaTitle: 'Classification', target: 0, type: 'string', data: 'jobcode_desc'
                            , name: 'jobcode_desc', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Organization', ariaTitle: 'Organization', target: 0, type: 'string', data: 'organization'
                            , name: 'organization', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Level 1', ariaTitle: 'Level 1', target: 0, type: 'string', data: 'level1_program'
                            , name: 'level1_program', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Level 2', ariaTitle: 'Level 2', target: 0, type: 'string', data: 'level2_division'
                            , name: 'level2_division', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Level 3', ariaTitle: 'Level 3', target: 0, type: 'string', data: 'level3_branch'
                            , name: 'level3_branch', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Level 4', ariaTitle: 'Level 4', target: 0, type: 'string', data: 'level4'
                            , name: 'level4', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Dept', ariaTitle: 'Dept', target: 0, type: 'string', data: 'deptid'
                            , name: 'deptid', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Shared By', ariaTitle: 'Shared By', target: 0, type: 'string', data: 'created_name'
                            , name: 'created_name', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Created At', ariaTitle: 'Created At', target: 0, type: 'string', data: 'created_at'
                            , name: 'created_at', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Updated At', ariaTitle: 'Updated At', target: 0, type: 'string', data: 'updated_at'
                            , name: 'updated_at', searchable: false, className: 'dt-nowrap show-modal'},
                        {title: 'Action', ariaTitle: 'Action', target: 0, type: 'string', data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-nowrap'},
                        {title: 'Shared Profile ID', ariaTitle: 'Shared Profile ID', target: 0, type: 'num', data: 'shared_profile_id', name: 'shared_profile_id', searchable: false, visible: false},
                    ]
                } );


                $('#btn_search').click(function(e) {
                    e.preventDefault();
					$('#generictable').DataTable().rows().invalidate().draw();
                } );

                // $('#btn_search').click();

                $('#cancelButton').on('click', function(e) {
                     e.preventDefault();
                    if($.fn.dataTable.isDataTable('#admintable')) {
                        $('#admintable').DataTable().clear();
                        $('#admintable').DataTable().destroy();
                        $('#admintable').empty();
                    }
                    // $('#admintable').DataTable().rows().invalidate().draw();
                });

                $('#removeButton').on('click', function(e) {
                    console.log('Delete button clicked');
                    // var model_id = $('#model_id').val();
                    // var token = $('meta[name="csrf-token"]').attr('content');
                    // event.preventDefault();
                    // $.ajax ( {
                    //     type: 'POST',
                    //     url: 'manageexistingaccessdelete/'+model_id,
                    //     data: {
                    //         'model_id':model_id,
                    //         '_token':token,
                    //         '_method':"DELETE",
                    //     },
                    //     success: function (result) {
                    //         window.location.href = 'manageexistingaccess';
                    //     }
                    // });
                });

                $(window).on('beforeunload', function(e){
                    $('#pageLoader').show();
                });

            });

        </script>
    @endpush

</x-side-layout>