<div class="modal fade editModal" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="min-width:90%">
        <form   id="modal-form" action="manageexistingaccessupdate" method="post" style="min-width:90%">
            {{ method_field('PUT') }}
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" type="hidden" id="accessDetailLabel">Edit Employee Access Level</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @if(Session::has('error'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert">
                            <i class="fa fa-times"></i>
                        </button>
                        <strong>Error !</strong> System Administrator already assigned to selected user.
                    </div>
                @endif
                <div class="mt-4 p-3">
                    <div class="row">
                        <div class="col-12">
                            <p>Change/Update user access details below.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-3">
                    <div class="row  p-3">
                        <div class="col col-4">
                            <input id="model_id" name="model_id" type="hidden" value="secret">
                            <input id="role_id" name="role_id" type="hidden" value="secret">
                            <label for='accessselect' title='Access Level Tooltip'>Access Level
                            <select name="accessselect" class="form-control" id="accessselect">
                                @foreach($roles as $rid => $desc)
                                    <option value = {{ $rid }} > {{ $desc }} </option>
                                @endforeach
                            </select>
                            </label>
                        </div>
                        <div class="col col-8">
                            <b> Access Description </b>
                            <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="List org levels (i.e. BCPSA - All; or SDPR - Corporate Services) followed by the role of the individual (i.e. SHR, Ambassador, Reporting). Full example: BCPSA - Corporate Workforce Strategies: Ambassador."> </i>
                            <x-input class="form-control" id="reason" name="reason" aria-label="Access Description"/>
                        </div>
                    </div>
                    <table class="table table-bordered admintable table-striped" id="admintable" name="admintable" style="width: 100%; overflow-x: auto; "></table>
                </div>
                <div class="modal-footer p-3">
                    <div class="col">
                        <button id="removeButton" name="removeButton" type="button" class="btn btn-outline-danger float-left" onClick="return confirm('Are you sure?')" aria-label="Remove Access">Remove Access</button>
                    </div>
                    <div class="col">
                        <button id="cancelButton" name="cancelButton" type="button" class="btn btn-secondary float-right" style="margin:5px;" data-dismiss="modal" aria-label="Cancel">Cancel</button>                    
                        <div class="space"></div>
                        <button id="saveButton" name="saveButton" type="submit" class="btn btn-primary float-right" onClick="return confirm('Are you sure?')" style="margin:5px;" role="save" data-toggle="modal" data-target="#updateModal" aria-label="Save Changes">Save Changes</button>                    
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
