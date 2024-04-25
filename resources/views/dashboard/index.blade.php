@include('dashboard.partials.accessibility')
 
   
<x-side-layout title="{{ __('Dashboard - Performance Development Platform') }}" >
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-primary leading-tight" role="banner">
            {{ $greetings }}, {{ Auth::user()->name }}
        </h1> 
    </x-slot>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 col-sm-4 col-md-4" index-tab="0" aria-label="My Current Supervisor">
                <strong>
                My Current Supervisor
                    <i tabindex="0" class="fa fa-info-circle" data-trigger="focus"  data-toggle="popover" data-placement="right" data-html="true" aria-label="{{ $supervisorTooltip }}" data-content="{{ $supervisorTooltip }}"></i>
                </strong>
                <div class="bg-white border-b rounded p-2 mt-2 shadow-sm">
                    {{-- <x-profile-pic></x-profile-pic> --}}
                    @if($supervisorListCount <= 1)
                        @if($supervisorListCount == 1)
                            @foreach($supervisorList as $supv)
                                {{ $supv ? $supv->user_name : 'No supervisor' }}
                            @endforeach
                        @else
                            <button class="btn p-0 text-left" style="width:100%" >
                                Vacant
                            </button>
                        @endif
                    @else
                    <label for="supervisor_btn">
                        <button type="button" icon="fas fa-xs fa-ellipsis-v" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ $preferredSupervisor ? $preferredSupervisor->name : 'Select a supervisor' }}
                        </button>
                        <div class="dropdown-menu" size="xs">
                            @foreach($supervisorList as $supv)
                                @php
                                    $isSelected = $preferredSupervisor && $supv->employee_id == $preferredSupervisor->supv_empl_id;
                                @endphp
                                <x-button icon="fas fa-xs fa-fw {{ $isSelected ? 'fa-solid fa-user-check' : '' }}" 
                                        value="{{ $supv->employee_id }}" 
                                        data-id="{{ $supv->employee_id }}" 
                                        data-name="{{ $supv->name }}" 
                                        class="dropdown-item {{ $isSelected ? 'selected' : '' }}" 
                                        name="change_supervisor" 
                                        id="change_supervisor_{{ $supv->employee_id }}">
                                    {{ $supv->user_name }}
                                </x-button>
                            @endforeach
                        </div>
                    </label>
                    @endif             
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-4" index-tab="0" aria-label="My Profile Shared with">
                <strong>
                    My Profile is Shared with
                    <i tabindex="0"  class="fa fa-info-circle" data-trigger="focus" data-toggle="popover" data-placement="right" data-html="true" aria-label="{{ $profilesharedTooltip }}" data-content="{{ $profilesharedTooltip }}"></i>
                </strong>
                <div class="bg-white border-b rounded p-2 mt-2 shadow-sm">
                    @if(count($sharedList) > 0)
                    <button class="btn p-0" style="width:100%" data-toggle="modal" data-target="#profileSharedWithViewModal">
                        <div class="d-flex align-items-center">
                            {{-- <x-profile-pic></x-profile-pic> --}}
                            <span id="sharedWithName">{{ $sharedList[0]->sharedWithUser->name }}
                            @if(count($sharedList) > 1)
                                and {{ count($sharedList) - 1 }} Others.
                            @else
                                .     
                            @endif
                            Click to view more details.
                            </span>
                            <div class="flex-fill"></div>
                            <i class="fa fa-chevron-right"></i>
                        </div>
                    </button>    
                    @else
                    <button class="btn p-0" style="width:100%">
                        No one
                    </button>    
                    @endif                    
                </div>
            </div>
            @if($jobList && $jobList->count() > 1)
                <div class="col-12 col-sm-4 col-md-4">
                    <strong>
                        My Primary Job
                        <i tabindex="0" class="fa fa-info-circle" data-trigger="focus" data-toggle="popover" data-placement="right" data-html="true" aria-label="{{ $jobTooltip }}" data-content="{{ $jobTooltip }}"></i>
                    </strong>
                    <div class="bg-white border-b rounded p-2 mt-2 shadow-sm">
                        {{-- <x-profile-pic></x-profile-pic> --}}
                        <label for="primaryjob_btn">
                            <button type="button" icon="fas fa-xs fa-ellipsis-v" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ $primaryJob ? $primaryJob->job : 'No job selected' }}
                            </button>
                            <div class="dropdown-menu"  size="xs">
                                @foreach($jobList as $jobItem)
                                    @if(!$primaryJob || ($primaryJob && $jobItem->empl_record != $primaryJob->empl_record))
                                        <x-button icon="fas fa-xs fa-fw" value="{{ $jobItem->empl_record }}" data-id="{{ $jobItem->empl_record }}" data-name="{{ $jobItem->job }}" class="dropdown-item change_job" name="change_job" id="change_job">
                                            {{ $jobItem->job }}
                                        </x-button>
                                    @else
                                        <x-button icon="fas fa-xs fa-fw fa-solid fa-check" value="{{ $jobItem->empl_record }}" data-id="{{ $jobItem->empl_record }}" data-name="{{ $jobItem->job }}" class="dropdown-item no_change_job" name="no_change_job" id='no_change_job'>
                                            {{ $jobItem->job }}
                                        </x-button>
                                    @endif
                                @endforeach
                            </div>
                        </label>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="p-4 bg-white border-b border-gray-200">
                    @include('dashboard.partials.tabs')
                    @if ($tab == 'todo')
                        @include('dashboard.partials.todo')
                    @else
                        @include('dashboard.partials.notifications')
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('dashboard.partials.shared_with_view-modal')
    @if(!empty($message))
    @include('dashboard.partials.message-modal', ['content' => $message->message])
    @endif

    <div class="container-fluid">
        <div class="pt-5">
            <div class="col-12">
                Your personal information is collected by the BC Public Service Agency and your Ministry pursuant to section 26(c)and 27(1)(f) of the <i>Freedom of Information and Protection of Privacy Act</i> for the purpose of managing and developing, staff training, educational and career development.
            </div>
        </div>
    </div>

<style>
.badge {
    font-size: 100%;
}
</style> 
    
    @push('js')
        {{-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script> --}}
        <script>
            $(document).ready(function(){
                
                @if(!empty(Session::get('displayModalMessage'))) 
                    $("#messageModal").modal();
                @endif

                $('[data-toggle="popover"]').popover();
            });

            $('body').on('click', function (e) {
                $('[data-toggle=popover]').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    }
                });
            });

            $('.change_supervisor').on('click', function(e) {
                e.preventDefault();
                var check = confirm("Are you sure you want to change supervisor?");
                if (check == true) {
                    // alert($(this).data('id'));
                    $.ajax({
                        url: "{{ route('dashboard.updateSupervisor') }}",
                        type: 'POST',
                        dataType: 'json',
                        data: { 
                            id: $(this).data('id'),
                        },
                        complete: function () {
                            window.location.reload();
                        }
                    });
                }
            });

            $('.no_change_supervisor').on('click', function(e) {
                e.preventDefault();
            });

            $('.change_job').on('click', function(e) {
                e.preventDefault();
                var check = confirm("Are you sure you want to change primary job?");
                if(check == true){
                    // alert($(this).data('id'));
                    $.ajax({
                        url: "{{ route('dashboard.updateJob') }}",
                        type: 'POST',
                        dataType: 'json',
                        data: { id : $(this).data('id'), 
                        },
                        success: function (data) {
                            window.location.reload();
                        }
                    });
                }
            });

            $('.no_change_job').on('click', function(e) {
                e.preventDefault();
            });

        </script>


        <script type="text/javascript">
            // $("#modalMessage").modal();

            // @if(!empty(Session::get('displayModalMessage'))) 
            //     // $("#messageModal modal-title").html('{{ $message->title }}');
            //     // $("#messageModal modal-body data-placeholder").html('{{ $message->message }}');
            //     // console.log("Show message");
            //     $("#messageModal").modal('show');
            // @endif
        </script>

    @endpush
</x-side-layout>
