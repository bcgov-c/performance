<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="modal-form" action="{{ route(request()->segment(1).'.employeeshares.removeallshare', "+shared_id+") }}" method="delete">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="sharedDetailLabel">Edit Employee Share Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 text-left pb-5 mt-3">
                            <div class="p-4" id="modal_text" name="modal_text" aria-label="Modal Text">
                            </div>
                            <div class="p-4">
                                <input type="hidden" id="user_id" name="user_id" value="">
                                <input type="hidden" id="employee_name" name="employee_name" value="">
                                <input type="hidden" id="message" name="message" value="">
                                <input type="hidden" id="shared_status" name="shared_status" value="">
                                <x-button id="removeAllShareButton" type="button" class="btn btn-primary" data-toggle="modal" data-target="#editModal" name="removeAllShareButton" aria-label="Remove All Share">Remove All Share</x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
