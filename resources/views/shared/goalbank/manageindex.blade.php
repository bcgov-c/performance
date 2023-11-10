<x-side-layout title="{{ __('Goal Bank - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Goal Bank</h3>
            @include('shared.goalbank.partials.tabs')
        </div>
    </div>

    <p class="px-3">Use the table below to modify or delete goals currently in the goal bank. Changes to content will be updated in employee goal banks as soon as you save the new version. You can also edit the audience if you want to add or remove individuals or business units.</p>




    <p class="px-3"><b>Be very careful modifying or deleting goals that were created by other people!</b> Your changes will impact everyone in the audience, not just those in your authorized area. If in doubt, connect with the original goal creator to discuss before taking any action.</p>


    <div class="card">
        <div class="card-body">
            <!-- <div class="h5">{{__('Manage Goals in Goal Bank')}}</div> -->
            @include('shared.goalbank.partials.filter')
            <div class="p-3" id='datagrid'>  
                <table class="table table-bordered filtertable table-striped" id="filtertable" name="filtertable" style="width: 100%; overflow-x: auto; "></table>
            </div>
        </div>    
    </div>   

    <!----modal starts here--->
    <div id="deleteGoalModal" class="modal" role='dialog'>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure to send out this message ?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary mt-2" type="submit" name="btn_delete" value="btn_delete">Delete Goal</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
                
            </div>
        </div>
    </div>
    <!--Modal ends here--->	
    @include('goal.partials.goal-detail-modal')


@push('css')
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <x-slot name="css">
        <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
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
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
    <script type="text/javascript">

        function confirmDeleteModal(){
            $('#saveGoalModal .modal-body p').html('Are you sure to delete goal?');
            $('#saveGoalModal').modal();
        }

        function showModal ($id) {
                $showAddBtn = false;
                $.get('/goal/goalbank/' + $id, function (data) {
                    $("#goal-detail-modal").find('.data-placeholder').html(data);
                    $("#goal-detail-modal").modal('show');
                });
            }

        $(document).ready(function() {

            $('#lvlgroup0').hide();
            $('#lvlgroup1').hide();
            $('#lvlgroup2').hide();
            $('#lvlgroup3').hide();
            $('#lvlgroup4').hide();
            $('#blank5th').hide();
            $('#datagrid').hide();

            $('#btn_search').click(function(e) {
                e.preventDefault();
                $('#datagrid').show();
                if($.fn.dataTable.isDataTable('#filtertable')) {
                    $('#filtertable').DataTable().clear();
                    $('#filtertable').DataTable().destroy();
                    $('#filtertable').empty();
                }
                $('#filtertable').DataTable ( {
                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    stateSave: true,
                    deferRender: true,
                    ajax: 
                    {
                        url: "managegetlist",
                        data: function (d) 
                        {
                            d.criteria = $('#criteria').val();
                            d.search_text = $('#search_text').val();
                        }
                    },
                    columns: 
                    [
                        {title: 'Goal Title', ariaTitle: 'Goal Title', target: 0, orderData: [0, 10], type: 'string', data: 'click_title', name: 'click_title', searchable: true, className: 'dt-nowrap'},
                        {title: 'Goal Type', ariaTitle: 'Goal Type', target: 1, orderData: [1, 10], type: 'string', data: 'click_goal_type', name: 'click_goal_type', searchable: true, className: 'dt-nowrap'},
                        {title: 'Mandatory', ariaTitle: 'Mandatory', target: 2, orderData: [2, 10], type: 'string', data: 'mandatory', name: 'mandatory', searchable: true, className: 'dt-nowrap'},
                        {title: 'Goal Creation Date', ariaTitle: 'Goal Creation Date', target: 3, orderData: [3, 10], type: 'date', data: 'created_at', name: 'created_at', searchable: true, className: 'dt-nowrap'},
                        {title: 'Display Name', ariaTitle: 'Display Name', target: 4, orderData: [4, 10], type: 'string', data: 'click_display_name', name: 'click_display_name', searchable: true, className: 'dt-nowrap'},
                        {title: 'Created By', ariaTitle: 'Created By', target: 5, orderData: [5, 10], type: 'string', data: 'click_creator_name', name: 'click_creator_name', searchable: true, className: 'dt-nowrap'},
                        {title: 'Created By Organization', ariaTitle: 'Created By Organization', target: 6, orderData: [6, 10], type: 'string', data: 'click_creator_organization', name: 'click_creator_organization', searchable: true, className: 'dt-nowrap'},
                        {title: 'Individual Audience', ariaTitle: 'Individual Audience', target: 7, orderData: [7, 10], type: 'num', data: 'audience', name: 'audience', searchable: true},
                        {title: 'Business Unit Audience', ariaTitle: 'Business Unit Audience', target: 8, orderData: [8, 10], type: 'num', data: 'org_audience', name: 'org_audience', searchable: true},
                        {title: 'Action', ariaTitle: 'Action', target: 9, orderData: [9, 10], type: 'string', data: 'action', name: 'action', orderable: false, searchable: false},
                        {title: 'Goal ID', ariaTitle: 'Goal ID', target: 10, orderData: [10], type: 'string', data: 'id', name: 'id', searchable: false, visible: false},
                    ]
                } );
            });

            $('#btn_search').click();

            $('#search_text').keydown(function (e){
                if (e.keyCode == 13) {
                    e.preventDefault();
                    $('#btn_search').click();
                }
            });

            // $('#btn_search_reset').click(function(e) {
            //     e.preventDefault();
            //     $('#criteria').val('all');
            //     $('#search_text').val(null);
            //     // $('#btn_search').click();
            // });
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