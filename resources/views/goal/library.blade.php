<x-side-layout>
    <div class="container-fluid">
        <div class="row ">
            <div class="col-md-6 col-6">
                <x-slot name="header">
                    @include('goal.partials.tabs')
                    <form action="" id="search-form">
                        <div class="position-relative w-50">
                            <button class="btn btn-primary btn-sm position-absolute" style="right:0;margin:3px;height:2rem">Search</button>
                            <div class="form-control input clearfix float-left" id="search-input" name="search"></div>
                        </div>
                    </form>
                </x-slot>
            </div>
        </div>
    </div>
    <div class="mt-5 pb-5">
        <h2>Suggested Goals</h2>
        <p>Click below to add goals to your profile that were suggested by:</p>
        <form id="goal_form" method="POST" action="{{ route('goal.library') }}">
            @csrf
            <input type="hidden" name="selected_goal" value="">
            <div class="accordion" id="accordionLibrary">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left  {{$expanded ? '' : 'collapsed'}}" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="{{$expanded ? 'true' : 'false'}}" aria-controls="collapseOne">
                                Your organization <i class="fas fa-{{ $expanded ? 'minus' : 'plus' }}-circle float-right "></i>
                            </button>
                        </h2>
                    </div>

                    <div id="collapseOne" class="collapse {{$expanded ? 'show' : ''}}" aria-labelledby="headingOne" data-parent="#accordionLibrary">
                        <div class="card-body">
                            <table class="table table-borderless">
                                <thead>
                                    <th style="width:50%"> Goal Title</th>
                                    <th style="width:20%"> Date Added</th>
                                    <th style="width:20%"> Created By</th>
                                </thead>
                                <tbody>
                                    @foreach($organizationGoals as $o)
                                    <tr>
                                        <td style="width:50%">
                                            <a href="#" class="show-goal-detail highlighter" data-id="{{$o->id}}">{{ $o->title }}</a>
                                        </td>
                                        <td style="width:20%">{{ $o->created_at->format('M d, Y') }} </td>
                                        <td style="width:20%">Division X</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingTwo">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left {{$expanded ? '' : 'collapsed'}} " type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="{{$expanded ? 'true' : 'false'}}" aria-controls="collapseTwo">
                                Your supervisor <i class="fas fa-{{ $expanded ? 'minus' : 'plus' }}-circle float-right "></i>

                            </button>
                        </h2>
                    </div>
                    <div id="collapseTwo" class="collapse {{$expanded ? 'show' : ''}}" aria-labelledby="headingTwo" data-parent="#accordionLibrary">
                        <div class="card-body">
                            <table class="table table-borderless">
                                <thead>
                                    <th style="width:50%"> Goal Title</th>
                                    <th style="width:20%"> Date Added</th>
                                    <th style="width:20%"> Created By</th>
                                </thead>
                                <tbody>
                                    @foreach($supervisorGoals as $o)
                                    <tr>
                                        <td style="width:50%">
                                            <a href="#" class="show-goal-detail highlighter" data-id="{{$o->id}}">{{ $o->title }}</a>
                                        </td>
                                        <td style="width:20%">{{ $o->created_at->format('M d, Y') }} </td>
                                        <td style="width:20%">Supervisor </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
        <link rel="stylesheet" href="{{asset('css/taggle.css')}}">
    @endpush
    <x-slot name="js">
        <script src="{{asset('js/taggle.js')}}"> </script>
        <script>
            (function () {let searchString = '{{ $currentSearch ?? ''}}';
                new Taggle("search-input", {
                    placeholder: '',
                    hiddenInputName: 'search[]',
                    tags: (searchString) ? searchString.split(' ') : [],
                    onTagAdd: performSearch,
                    onTagRemove: performSearch
                });

                function performSearch() {
                    debugger;
                    $("#search-form").submit();
                };
            })();
        </script>
        @if ($expanded)
        {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/mark.min.js" integrity="sha512-5CYOlHXGh6QpOFA/TeTylKLWfB3ftPsde7AnmhuitiTX4K5SqCLBeKro6sPS8ilsz1Q4NRx3v8Ko2IBiszzdww==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
        <script>
            {{-- let markInstance = new Mark(document.querySelectorAll(".highlighter"));

            (function performMark() {

                // Read the keyword
                var keyword = '{{ request()->search}}';

                // Determine selected options
                var options = {
                    separateWordSearch: true
                };

                // Remove previous marked elements and mark
                // the new keyword inside the context
                markInstance.unmark({
                    done: function(){
                        markInstance.mark(keyword, options);
                    }
                });
            })(); --}}
        </script>
        @endif
        <script>
            $('.card-header').click(function() {
                $(this).find('i').toggleClass('fas fa-plus-circle fas fa-minus-circle');
            });


            $(document).on('click', '.btn-submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/goal/goalbank'
                    , type: 'POST'
                    , data: $('#goal_form').serialize() + '&start_date=' + $('#start_date').val() + '&target_date=' + $('#target_date').val(),

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

            $(document).on('click', '.show-goal-detail', function(e) {
                e.preventDefault();
                $("#goal_form").find('input[name=selected_goal]').val($(this).data('id'));

                $.get('/goal/goalbank/'+$(this).data('id')+'?add=true', function (data) {
                    $("#goal-detail-modal").find('.data-placeholder').html(data);
                    $("#goal-detail-modal").modal('show');
                });
            });

        </script>
    </x-slot>


</x-side-layout>
