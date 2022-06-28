<form id="modal-form" class="form-control" action="manageindexupdate" method="post" enctype="multipart/form-data">
    
    <div class="modal fade editModal" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">

        {{ method_field('PUT') }}
        {{ csrf_field() }}
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" type="hidden" id="excusedDetailLabel">Edit Employee Excuse Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="mt-4 p-3">
                    <div class="row">
                        <div class="col-12">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."</p>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-3">
                    <div class="row  p-3">
                        <div class="col col-md-4">
                            <x-input label="Start Date " class="error-start" type="date" id="start_date" name="start_date" />
                        </div>
                        <div class="col col-md-4">
                            <x-input label="End Date " class="error-target" type="date" id="target_date" name="target_date" />
                        </div>
                    </div>
                    <div class="row  p-3">
                        <label for='reasons' title='Excused Reasons Tooltip'>Reason
                            <select name="reasons" class="form-control" id="reasons">
                                @foreach($reasons as $reason)
                                    <option value = {{ $reason->id }} {{ '$reason->id' == '$excused_reason_id' ? "selected" : "" }}> {{ $reason->name }} </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
                <div class="modal-footer p-3">
                    <div class="col">
                        <button id="removeButton" name="removeButton" type="button" class="btn btn-outline-danger float-left" onClick="return confirm('Are you sure?')" aria-label="Remove Access">Remove Excuse</button>
                    </div>
                    <div class="col">
                        <button id="cancelButton" name="cancelButton" type="button" class="btn btn-secondary float-right" style="margin:5px;" data-dismiss="modal" aria-label="Cancel">Cancel</button>                    
                        <div class="space"></div>
                        <button id="saveButton" name="saveButton" type="submit" class="btn btn-primary float-right" onClick="return confirm('Are you sure?')" style="margin:5px;" role="save" data-toggle="modal" data-target="#updateModal" aria-label="Save Changes">Save Changes</button>                    
                    </div>
                </div>
            </div>
        </div>
    
    </div>

</form>
