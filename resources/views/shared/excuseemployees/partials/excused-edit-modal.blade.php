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
                            <p>Excusing an employee will remove them from any reporting and will pause the employee’s conversation deadlines. Employees will automatically be excused if 1) they are on leave or inactive status in PeopleSoft; or 2) if they are a DM, ADM, or Executive Lead covered by a different performance review process. You should only use this form to manually excuse an employee if they fit into one of the categories listed in the dropdown box below."</p>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-3">
                    <div class="row  p-3">
                        <div class="col-2 mt-1">
                            <x-dropdown :list="$yesOrNo" label="Excused" name="excused_flag" id="excused_flag" value=""/>
                        </div>
                        <div class="col-10 mt-1">
                            <x-dropdown :list="$reasons" label="Reason" name="excused_reason_id" id="excused_reason_id" value=""/>
                            {{-- <small class="text-danger error-excused_reason_id"></small> --}}
                        </div>
                        {{-- <div class="col-10 mt-1">
                            <label for='reasons' title='Excused Reasons Tooltip'>Reason
                                <select name="reasons" class="form-control" id="reasons">
                                    @foreach($reasons as $reason)
                                        <option value = {{ $reason->id }} {{ '$reason->id' == '$excused_reason_id' ? "selected" : "" }}> {{ $reason->name }} </option>
                                    @endforeach
                                </select>
                            </label>
                        </div> --}}
                    </div>
                </div>
                <div class="modal-footer p-3">
                    {{-- <div class="col">
                        <button id="removeButton" name="removeButton" type="button" class="btn btn-outline-danger float-left" onClick="return confirm('Are you sure?')" aria-label="Remove Access">Remove Excuse</button>
                    </div> --}}
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
