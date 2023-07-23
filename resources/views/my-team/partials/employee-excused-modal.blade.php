<!-- Modal -->
<div class="modal fade" id="employee-excused-modal" tabindex="-1" aria-labelledby="employeeExcused" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="employeeExcused">{{__('Excuse an Employee')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="p-4">
                <p>Excusing an employee will remove them from any reporting and will pause the employee’s conversation deadlines. Employees will automatically be excused if 1) they are on leave or inactive status in PeopleSoft; or 2) if they are a DM, ADM, or Executive Lead covered by a different performance review process. You should only use this form to manually excuse an employee if they fit into one of the categories listed in the dropdown box below.</p>
                <u><strong>Declaration</strong></u>
                <p>I wish to excuse <strong><span class="user-name"></span></strong> from the Performance Development process.</p>
                <div class="alert alert-default-warning alert-dismissible">
                    <span class="h5"><i class="icon fas fa-exclamation-triangle"></i>Note: By doing so, this employee will not show up in PDP reports.</span>
                </div>
                <form id="excused_form" action="{{ route ('excused.updateExcuseDetails')}}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="">
                    <div class="row">
                        <div class="col-2 mt-1" id="divExcused1">
                            <x-dropdown :list="$yesOrNo" label="Excused" name="excused_flag" id="excused_flag" value=""/>
                        </div>
                        <div class="col-2 mt-1" id="divExcused2">
                            <x-dropdown :list="$yesOrNo2" label="Excused" name="excused_flag2" id="excused_flag2" value=""/>
                        </div>
                        <div class="col-10 mt-1" id="divReason1">
                            <x-dropdown :list="$eReasons" label="Reason" name="excused_reason_id" id="excused_reason_id" value=""/>
                        </div>
                        <div class="col-10 mt-1" id="divReason2">
                            <x-dropdown :list="$eReasons2" label="Reason" name="excused_reason_id2" id="excused_reason_id2" value=""/>
                        </div>
                        <div class="col-12 text-left pb-5 mt-3">
                            <x-button type="button" class="btn-md btn-submit" name="excused_update_btn" id="excused_update_btn">Update</x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

