<div class="modal fade editModal" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    {{ method_field('DELETE') }}
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" type="hidden" id="shareDetailLabel">View Employee Shares</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="mt-4 p-3">
                <div class="row">
                    <div class="col-12">
                        <p id='paragraph'><b>Custom text</b></p>
                    </div>
                </div>
            </div>
            <div class="modal-body p-3">
                <table class="table table-bordered admintable" id="admintable" name="admintable" style="width: 100%; overflow-x: auto; "></table>
            </div>
            <div class="modal-footer p-3">
                <div class="col">
                    <button id="cancelButton" name="cancelButton" type="button" class="btn btn-secondary float-right" style="margin:5px;" data-dismiss="modal" aria-label="Close">Close</button>                    
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script type="text/javascript">
        $('#editModal').on('show.bs.modal', function(e) {
            var button = $(e.relatedTarget);
            var user_id = button.data('userid');
            var username = button.data('username');
            $('#shareDetailLabel').text('Edit Employee Shares by '+username);
            $('#paragraph').text('Below are Goal(s) and/or Conversation(s) shared by '+username+'.');
            $('#admintable').show();
            $('#admintable').DataTable ( {
                processing: true,
                serverSide: false,
                scrollX: true,
                stateSave: false,
                deferRender: false,
                stripeClasses: ['odd-row', 'even-row'],
                ajax: {
                    type: 'GET',
                    url: hradmin/employeeshares/manageindexviewshares/"+user_id,
                },                        
                columns: [
                    {title: 'Item Type', ariaTitle: 'Item Type', target: 0, type: 'string', data: 'item_type', name: 'item_type', searchable: false, className: 'dt-nowrap'},
                    {title: 'Delegate ID', ariaTitle: 'Delegate ID', target: 0, type: 'string', data: 'employee_id2', name: 'employee_id2', searchable: true, className: 'dt-nowrap'},
                    {title: 'Delegate Name', ariaTitle: 'Delegate Name', target: 0, type: 'string', data: 'employee_name2', name: 'employee_name2', searchable: true, className: 'dt-nowrap'},
                    {title: 'Shared Elements', ariaTitle: 'Shared Elements', target: 0, type: 'string', data: 'element_name', name: 'element_name', searchable: true, className: 'dt-nowrap'},
                    {title: 'Action', ariaTitle: 'Action', target: 0, type: 'num', data: 'action', name: 'action', searchable: false, className: 'dt-nowrap'},
                    {title: 'Item ID', ariaTitle: 'Item ID', target: 0, type: 'string', data: 'item_id', name: 'item_id', searchable: false, visible: false, className: 'dt-nowrap'},
                    {title: 'Participant ID', ariaTitle: 'Participant ID', target: 0, type: 'string', data: 'part_id', name: 'part_id', searchable: false, visible: false, className: 'dt-nowrap'},
                    {title: 'Shared With User ID', ariaTitle: 'Shared With User ID', target: 0, type: 'string', data: 'shared_with_id', visible: false, name: 'shared_with_id', searchable: true, className: 'dt-nowrap'},
                ],  
            } );
        });
        $('#editModal').on('hidden.bs.modal', function(e) {
            if($.fn.dataTable.isDataTable('#admintable')) {
                $('#admintable').DataTable().clear();
                $('#admintable').DataTable().destroy();
                $('#admintable').empty();
            }
        });
    </script>
@endpush
