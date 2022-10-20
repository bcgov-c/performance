<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="modal-form" action="{{ route(request()->segment(1).'.excuseemployees.manageindexupdate') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="excusedDetailLabel">Edit Employee Excuse Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-4">
                        <p>Excusing an employee will remove them from any reporting and will pause the employee’s conversation deadlines. Employees will automatically be excused if 1) they are on leave or inactive status in PeopleSoft; or 2) if they are a DM, ADM, or Executive Lead covered by a different performance review process. You should only use this form to manually excuse an employee if they fit into one of the categories listed in the dropdown box below."</p>
                        <u><strong>Declaration</strong></u>
                        <p>I wish to excuse <strong><span class="employee_name"></span></strong> from the Performance Development process.</p>
                        <div class="alert alert-default-warning alert-dismissible">
                            <span class="h5"><i class="icon fas fa-exclamation-triangle"></i>Note: By doing so, this employee will not show up in PDP reports.</span>
                        </div>
                        <input type="hidden" name="id" value="">
                        <div class="row">
                            <div class="col-2 mt-1" id="divExcuse1">
                                <x-dropdown :list="$yesOrNo" label="Excused" name="excused_flag" id="excused_flag" value=""/>
                            </div>
                             <div class="col-2 mt-1" id="divExcuse2">
                                <x-dropdown :list="$yesOrNo2" label="Excused" name="excused_flag2" id="excused_flag2" value=""/>
                            </div>
                            <div class="col-10 mt-1" id="divReason1">
                                <x-dropdown :list="$reasons" label="Reason" name="excused_reason_id" id="excused_reason_id" value=""/>
                            </div>
                            <div class="col-10 mt-1" id="divReason2">
                                <x-dropdown :list="$reasons2" label="Reason" name="excused_reason_id2" id="excused_reason_id2" value=""/>
                            </div>
                            <div class="col-12 text-left pb-5 mt-3">
                                <x-button id="saveExcuseButton" type="submit" class="btn btn-primary" onClick="return confirm('Are you sure?')" role="save" data-toggle="modal" data-target="#editModal" name="saveExcuseButton" aria-label="Save Changes">Update</x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>
