<x-side-layout>
    <div class="container-fluid">
        <div class="row ">
            <div class="col-md-6 col-6">
                <x-slot name="header">
                    @include('goal.partials.tabs')
                </x-slot>
            </div>
        </div>
        <div>
            <!-- <b>My Goal Bank</b> <br> -->
            The goals below have been created for you by your supervisor or organization. Click on a goal to view it and add it to your own profile. If needed, you can edit the goal to personalize it once it is in your profile. 
            <br>
            <br>
        </div>
        <form action="" method="get" id="filter-menu">
            <div class="row">
                <div class="col">
                    <label>
                        Title
                        <input type="text" name="title" class="form-control" value="{{request()->title}}">
                    </label>
                </div>
                <div class="col">
                    <x-dropdown :list="$goalTypes" label="Goal Type" name="goal_type" :selected="request()->goal_type"></x-dropdown>
                </div>
                <div class="col">
                    <x-dropdown :list="$tagsList" label="Tags" name="tag_id" :selected="request()->tag_id"></x-dropdown>
                </div>
                <div class="col">
                    <label>
                        Date Added
                        <input type="text" class="form-control" name="date_added" value="{{request()->date_added ?? 'Any'}}">
                    </label>
                </div>
                <div class="col">
                    <x-dropdown :list="$createdBy" name="created_by" :selected="request()->created_by" label="Created by"></x-dropdown>
                </div>
                <div class="col">
                    <x-dropdown :list="$mandatoryOrSuggested" label="Mandatory/Suggested" name="is_mandatory" :selected="request()->is_mandatory"></x-dropdown>
                </div><!-- 
                <div class="col">
                    <button class="btn btn-primary mt-4 px-5">Filter</button>
                </div> -->
            </div>
        </form>

        <form action="{{ route('goal.library.save-multiple') }}" method="post">
            @csrf
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-borderless">
                                <thead>
                                    <tr class="border-bottom">
                                        <th>
                                            <input type="checkbox" id="select_all">
                                        </th>
                                        <th style="width:35%"> Goal Title</th>
                                        <th style="width:20%"> Goal Type</th>
                                        <th style="width:15%"> Tags</th>
                                        <th style="width:15%"> Date Added</th>
                                        <th style="width:15%"> Created By</th>
                                        <th style="width:15%"> Mandatory/Suggested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bankGoals as $goal)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="goal_ids[]" value="{{$goal->id}}">
                                        </td>
                                        <td style="width:25%">
                                            <a href="#" class="show-goal-detail highlighter" data-id="{{$goal->id}}">{{ $goal->title }}</a>
                                        </td>
                                        <td style="width:15%">
                                            <a href="#" class="show-goal-detail highlighter" data-id="{{$goal->id}}">{{ $goal->typename }}</a>
                                        </td>
                                        <td style="width:15%">
                                            <a href="#" class="show-goal-detail highlighter" data-id="{{$goal->id}}">{{ $goal->tagnames }}</a>
                                        </td>
                                        <td style="width:15%">
                                            <a href="#" class="show-goal-detail highlighter" data-id="{{$goal->id}}">{{ $goal->created_at == null ?: $goal->created_at->format('M d, Y') }}</a>
                                        </td>
                                        <td style="width:15%">
                                            <a href="#" class="show-goal-detail highlighter" data-id="{{$goal->id}}">{{ $goal->username }}</a>
                                        </td>
                                        <td style="width:15%">
                                            <a href="#" class="show-goal-detail highlighter" data-id="{{$goal->id}}">{{ $goal->is_mandatory ? 'Mandatory' : 'Suggested' }}</a>
                                        </td>
                                        <td>
                                        <button class="btn btn-primary btn-sm float-right ml-2 btn-view-goal show-goal-detail highlighter" data-id="{{$goal->id}}" data-toggle="modal" data-target="#viewGoal">
                                            View
                                        </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                {{ $bankGoals->links() }}
                            </table>
                        </div>
                    </div>
                    <div class="text-center">
                        <x-button id="addMultipleGoalButton" disabled>Add Selected Goals to Your Profile <span class="selected_count">(0)</span></x-button>
                    </div>
                </div>
            </div>
        </form>
        @if(Auth::user()->hasRole('Supervisor'))
        @php $shareWithLabel = 'Audience' @endphp
        @php $doNotShowInfo = true @endphp
        <div>
            <b>Team Goal Bank</b> <br>
        </div>
        <form action="{{ route('my-team.sync-goals-sharing')}}" method="POST" id="share-my-goals-form">
            @csrf
            <div class="d-none" id="syncGoalSharingData"></div>
        </form>
        @push('css')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
        @endpush
        @push('js')
            <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script>
            $(document).on('click', '[data-action="delete-goal"]', function () {
                if (confirm($(this).data('confirmation'))) {
                    let action = $("#delete-goal-form").attr('action');
                    action = action.replace('xxx', $(this).data("goal-id"));
                    $("#delete-goal-form").attr('action', action);
                    $("#delete-goal-form").submit();
                }
            });
            $(document).on('change', '.is-shared', function (e) {
                let confirmMessage = "Making this goal private will hide it from all employees. Continue?";
                if ($(this).val() == "1") {
                    confirmMessage = "Sharing this goal will make it visible to the selected employees. Continue?"
                }
                if (!confirm(confirmMessage)) {
                    // this.checked = !this.checked;
                    $(this).val($(this).val() == "1" ? "0" : "1");
                    e.preventDefault();
                    return;
                }
                // $(this).parents("label").find("span").html(this.checked ? "Shared" : "Private");
                const goalId = $(this).data('goal-id');
                $("#search-users-" + goalId).multiselect($(this).val() == "1" ? 'enable' : 'disable');
                const form = $(this).parents('form').get()[0];
                fetch(form.action,{method:'POST', body: new FormData(form)});
            });
            $(document).ready(() => {
                $(".search-users").each(function() {
                    const goalId = $(this).data('goal-id');
                    const selectDropdown = this;
                    $(this).multiselect({
                        allSelectedText: 'All Direct Report',
                        selectAllText: 'All Direct Report',
                        includeSelectAllOption: true,
                        onDropdownHide: function () {
                            document.getElementById("syncGoalSharingData").innerHTML = "";
                            finalSelectedOptions = [...selectDropdown.options].filter(option => option.selected).map(option => option.value);
                            finalSelectedOptions.forEach((value) => {
                                const input = document.createElement("input");
                                input.setAttribute('value', value);
                                input.name = "itemsToShare[]";
                                document.getElementById("syncGoalSharingData").appendChild(input);
                            });
                            const input = document.createElement("input");
                            input.setAttribute('value', goalId);
                            input.name = "goal_id";
                            document.getElementById("syncGoalSharingData").appendChild(input);
                            const form = $("#share-my-goals-form").get()[0];
                            fetch(form.action,{method:'POST', body: new FormData(form)});
                        }
                    });
                });
            });
        </script>
        @endpush
        @include('my-team.goals.partials.bank')
        @endif
    </div>

    @include('goal.partials.goal-detail-modal')
    <div class="modal fade" id="addGoalModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="addGoalModalLabel">Select Date</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">

                    <div class="row">
                        <div class="col-sm-6">
                            <x-input label="Start Date" class="error-start" type="date" id="start_date" />
                            <small class="text-danger error-start_date"></small>
                        </div>
                        <div class="col-sm-6">
                            <x-input label="End Date " class="error-target" type="date" id="target_date" />
                            <small class="text-danger error-target_date"></small>
                        </div>

                        <div class="col-12 text-left pb-5 mt-3">
                            <x-button type="button" class="btn-md btn-submit"> Save Changes</x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
    @endpush
    @push('js')
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script>
            $('#filter-menu select, #filter-menu input').change(function () {
                $("#filter-menu").submit();
            });
            
            $('input[name="date_added"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Any',
                    format: 'MMM DD, YYYY'
                }
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MMM DD, YYYY') + ' - ' + picker.endDate.format('MMM DD, YYYY'));
                $("#filter-menu").submit();
            }).on('cancel.daterangepicker', function(ev, picker) {
                $('input[name="date_added"]').val('Any');
            });
        </script>
        <script>
            $(document).on('click', '#select_all', function (e) {
                $('input:checkbox').prop('checked', this.checked);
            });
            $(document).on('click', 'input:checkbox', function (e) {
                let count = $('input:checkbox:checked').length;
                if ($("#select_all").get(0).checked) {
                    count--;
                }
                $('#addMultipleGoalButton').find('span.selected_count').html("("+count+")");
                $('#addMultipleGoalButton').prop('disabled', count === 0);    
            });
            $(document).on('click', '.show-goal-detail', function(e) {
                e.preventDefault();
                $("#goal_form").find('input[name=selected_goal]').val($(this).data('id'));

                $.get('/goal/library/'+$(this).data('id')+'?add=true', function (data) {
                    $("#goal-detail-modal").find('.data-placeholder').html(data);
                    $("#goal-detail-modal").modal('show');
                });
            });
        </script>
        <script>
            $(document).on('click', '#addBankGoalToUserBtn', function(e) {
                const goalId = $(this).data("id");
                e.preventDefault();
                $.ajax({
                    url: '/goal/library'
                    , type: 'POST'
                    , data: {
                        selected_goal: goalId
                    },
                    beforeSend: function(request) {
                        return request.setRequestHeader('X-CSRF-Token', $(
                            "meta[name='csrf-token']").attr('content'));
                    },

                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            window.location.href = '/goal';
                        }
                    }
                    , error: function(error) {
                        var errors = error.responseJSON.errors;

                    }
                });

            });
            
            $(document).ready(() => {
                $(".tags").multiselect({
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true
                });
            });
        </script>
    @endpush
</x-side-layout>


<script>    
        $( "#start_date" ).change(function() {
            var start_date = $( "#start_date" ).val();
            $( "#target_date" ).attr("min",start_date);            
        });
        
        $( "#target_date" ).change(function() {
            var start_date = $( "#start_date" ).val();
            if (start_date === '') {
                alert('Please choose start date first.');
                $( "#target_date" ).val('');
            }           
        });
        
</script>    
