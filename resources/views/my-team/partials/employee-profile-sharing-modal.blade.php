<!-- Modal -->
<ol style="overflow-y: scroll; overflow-x: hidden;">
<div class="modal fade" id="employee-profile-sharing-modal" tabindex="-1" aria-labelledby="employeeProfileSharing"
    aria-hidden="true" style="overflow-y: scroll; overflow-x: hidden;">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="employeeProfileSharing">{{__('Employee Profile Sharing')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="p-3">
                <strong>The profile of <span class="user-name"></span> is currently being shared with the following users:</strong> <br>
                    <div class="shared-with-list">None</div>
                <strong>Share this profile with another user</strong>
                <p>Supervisors and administrators may share an employee's PDP profile with another supervisor or staff for a legitimate business reason. The profile should only be shared with people who normally handle employees' permanent personnel records (i.e. Public Service Agency or co-supervisors). An employee may also wish to share their profile with someone other than a direct supervisor (for example, a hiring manager). In order to do this - the employee's consent is required.</p>
                <!-- <p>An employee may also wish to share their profile with someone other than a direct supervisor (for example, a hiring manager). In order to do this - the employee's consent is required.</p>
                <p>To continue, please use the functions below to select the employee profiles that you would like to share, the person you would like to share the profiles with, which elements you would like to share, and your reason for sharing the profile.</p> -->
                <form id="share-profile-form" action="{{ route('my-team.share-profile') }}" method="POST" onsubmit="confirm('Are you sure you want to share the selected profile(s)?')">
                    @csrf
                    <input type="hidden" name="shared_id">
                    <div class="row">
                        <div class="col-6">
                            <!-- <x-dropdown name="share_with_users[]" label="Share With" multiple class="share-with-users"></x-dropdown> -->
                            <b>Share With</b>
                            <x-dropdown name="share_with_users[]" multiple class="share-with-users"></x-dropdown>
                        </div>
                        <div class="col-6">
                            <!-- <x-dropdown name="items_to_share[]" :list="[['id'=>1, 'name'=> 'Goals', 'selected'=>true], ['id'=>2, 'name'=> 'Conversations',  'selected'=>true]]" label="Elements to share" multiple class="items-to-share"></x-dropdown> -->
                            <b>Elements to share</b>
                            <x-dropdown name="items_to_share[]" :list="[['id'=>1, 'name'=> 'Goals', 'selected'=>false], ['id'=>2, 'name'=> 'Conversations',  'selected'=>false]]" multiple class="items-to-share"></x-dropdown>
                        </div>
                        <div class="col-6">
                            <!-- <x-input name="reason" label="Reason" tooltip="Reason tooltip"></x-input> -->
                            <b>Reason for sharing</b>
                            <i class="fa fa-info-circle" name="reason" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="Provide a brief explanation of why the profile is being shared. For example: <br><br><ul><li> Sharing profile with co-supervisor </li><li>Sharing profile because of inaccurate data in PeopleSoft</li><li>Sharing with hiring manager per employee request</li></ul>"> </i> 
                            <x-input name="reason"></x-input>
                        </div>
                    </div>
                    <div class="py-2">
                        <div class="my-3">
                            <strong><u>Agreement to Terms</u></strong>
                        </div>
                        <label class="form-check-label">
                            <input type="checkbox" name="accepted">
                            <span class="font-weight:normal">I wish to share this employee's profile with another supervisor. In doing so, I confirm that there is a legtimate business reason for providing shared access.</span>
                            <small class="text-danger error-accepted">
                                {{ $errors->first('accepted') }}
                            </small>
                        </label>
                        <x-button icon="user" class="mt-4">Share Profile</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</ol>
@push('css')
    <style>
        /* .modal {
            display: block !important;
        } */

        .modal-dialog {
            overflow-y: initial !important;
        }

        .modal-body {
            max-height: 80vh;
            overflow-y: auto;
        }
        .p-3{
            max-height: 95vh;
            overflow-y: auto;
        }
        
    </style>
@endpush
