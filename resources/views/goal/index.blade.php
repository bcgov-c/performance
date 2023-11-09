<script src="https://cdn.ckeditor.com/4.20.1/standard-all/ckeditor.js"></script>
<x-side-layout title="{{ __('My Goals - Performance Development Platform') }}">
    <x-slot name="header">
        <h3>
        @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
              My Goals
        @else
            {{ $user->name }}'s Goals
        @endif
        </h3>
        @include('goal.partials.tabs')
    </x-slot>
    @if($type != 'supervisor' && !$disableEdit)
        @if(request()->is('goal/current'))
            <x-button icon="plus-circle" data-toggle="modal" data-target="#addGoalModal">
                Create New Goal
            </x-button>
            <x-button icon="clone" href="{{ route('goal.library') }}">
                Add Goal from Goal Bank
            </x-button>
            <x-button icon="question" href="{{ route('resource.user-guide','t=1') }} " target="_blank" tooltip='Click here to access goal setting resources and examples (opens in new window).'>    
                Need Help?
            </x-button>
        @endif

    @endif
    <div class="mt-4">
        {{-- {{$dataTable->table()}} --}}

        <div class="row">
            @if ($type != 'supervisor')   
                <form action="" method="get" id="filter-menu">
                    <div class="row">
                        
                        <div class="col-12"  id="msgdiv"></div>
                        
                        <div class="col">
                            <label>
                                Title
                                <input type="text" name="title" class="form-control" value="{{request()->title}}">
                            </label>
                        </div>
                        <div class="col">
                            <x-dropdown :list="$goaltypes" label="Goal Type" name="goal_type" :selected="request()->goal_type"></x-dropdown>
                        </div>
                        @if ($type == 'past')
                            <div class="col">
                                <x-dropdown :list="$statusList" label="Status" name="status" :selected="request()->status"></x-dropdown>                      
                            </div>
                        @endif
                        <div class="col">
                            <x-dropdown :list="$tagsList" label="Tags" name="tag_id" :selected="request()->tag_id"></x-dropdown>
                        </div>
                        <div class="col">
                            <label>
                                Start Date
                                <input type="text" class="form-control" name="filter_start_date" value="{{request()->filter_start_date ?? 'Any'}}">
                            </label>
                        </div>
                        <div class="col">
                            <label>
                                End Date
                                <input type="text" class="form-control" name="filter_target_date" value="{{request()->filter_target_date ?? 'Any'}}">
                            </label>
                        </div>
                    </div>
                    <input name="sortby" id="sortby" value="{{$sortby}}" type="hidden">
                    <input name="sortorder" id="sortorder" value="{{$sortorder}}" type="hidden">
                </form>    
            @endif    
            @if ($type == 'current' || $type == 'supervisor')
                @if($type == 'supervisor')
                    <div class="col-12 mb-4">
                        @if($goals->count() != 0)
                            These goals have been shared with you by a colleague. You can view and comment on these goals and collaborate with others to track progress. 
                            Note that whoever created the goal is the one that can change the goal status to achieved or archived.
                        @else
                            <div class="alert alert-warning alert-dismissible no-border"  style="border-color:#d5e6f6; background-color:#d5e6f6" role="alert">
                            <span class="h5" aria-hidden="true"><i class="icon fa fa-info-circle"></i><b>No goals are currently being shared with you.</b></span>
                            </div>
                        @endif
                    </div>
                    @foreach ($goals as $goal)
                        <div class="col-12 col-lg-6 col-xl-4">
                            @include('goal.partials.card')
                        </div>
                    @endforeach
                @else
                    <div class="col-12 col-sm-12">
                        @include('goal.partials.target-table',['goals'=>$goals])
                    </div>
                @endif            
            @else
                <div class="col-12 col-sm-12">                 
                    @include('goal.partials.past-target-table',['goals'=>$goals])
                </div>
            @endif
        </div>
        {{ $goals->links() }}
    </div>

@include('goal.partials.supervisor-goal')
@include('goal.partials.goal-detail-modal')
<div class="modal fade" id="addGoalModal"  aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="addGoalModalLabel">Create New Goal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="color:white">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <form id="goal_form" action="{{ route ('goal.store')}}" method="POST">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger" style="display:none">
                        <i class="fa fa-info-circle"></i> There are one or more errors on the page. Please review and try again.
                    </div>
                </div>
                <div class="col-6">
                    <div>
                        <b>Goal Type</b>
                        <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="{{$type_desc_str}}"> </i>
                        <x-dropdown :list="$goal_types_modal" name="goal_type_id" />
                    </div>
                    </div>
                       <div class="col-6">
                       <b>Goal Title</b>
                        <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="A short title (1-3 words) used to reference the goal throughout the Performance Development Platform."> </i>                        
                        <x-input-modal id="goal_title" name="title" />
                    </div>
                    <div class="col-sm-6">
                        <b>Tags</b>
                        <i class="fa fa-info-circle" id="tags_label" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="Tags help to more accurately identity, sort, and report on your goals. You can add more than one tag to a goal. The list of tags will change and grow over time. <br/><br/><a href='/resources/goal-setting?t=5' target=\'_blank\'><u>View full list of tag descriptions.</u></a><br/><br/>Don't see the goal tag you are looking for? <a href='mailto:performance.development@gov.bc.ca?subject=Suggestion for New Goal Tag'>Suggest a new goal tag</a>."></i>				
                        <x-xdropdown :list="$tags" name="tag_ids[]"  class="tags" displayField="name" multiple/>
                        <small  class="text-danger error-tag_ids"></small>
                    </div>
                       <div class="col-12">
                        <!-- <label style="font-weight: normal;"> -->
                        <b>Goal Description</b>          
                        <p>
				        Each goal should include a description of <b>WHAT</b>  
				        <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".'> </i> you will accomplish, <b>WHY</b> 
				        <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".'> </i> it is important, and <b>HOW</b> 
				        <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".'> </i> you will achieve it. 
				        </p>                                                                  
                        <!-- <p class="py-2">Each goal should include a description of <b>WHAT</b><x-tooltip-modal text='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".' /> you will accomplish, <b>WHY</b><x-tooltip-modal text='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".' /> it is important, and <b>HOW</b><x-tooltip-modal text='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".'/> you will achieve it.</p>                                                                 -->
                        <!-- <textarea id="what" label="Goal Descriptionxxx" name="what" ></textarea> -->
                        <!-- <textarea id="what" name="what" ></textarea>                             -->
                        <small class="text-danger error-what"></small>
                        <x-textarea-modal id="what" name="what"/>
                        <!-- </label> -->
                  </div>
                       <div class="col-12">
                            <b>Measures of Success</b>
                            <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"'> </i>                        
                            <x-textarea-modal id="measure_of_success" name="measure_of_success" />
                            <small class="text-danger error-measure_of_success"></small>
                        </div>
                <div class="col-sm-6">
                    <x-input label="Start Date " class="error-start" type="date" name="start_date" id="start_date" />
                    <small  class="text-danger error-start_date"></small>
                </div>
                <div class="col-sm-6">
                    <x-input label="End Date " class="error-target" type="date" name="target_date" id="target_date" />
                     <small  class="text-danger error-target_date"></small>
                </div>
                
                <!-- 
                <div class="col-12">
                    <div class="card mt-3 p-3" icon="fa-question">
                        <span>Supporting Material</span>
                        <a href="{{route('resource.goal-setting')}}" target="_blank">Goal Setting Resources</a>
                    </div>
                </div> -->
                <div class="col-12 text-left pb-5 mt-3">
                    <x-button type="button" class="btn-md btn-submit"> Save Changes</x-button>
                    <x-button icon="question" href="{{ route('resource.goal-setting') }} " target="_blank" tooltip='Click here to access goal setting resources and examples (opens in new window).'>
                        Need Help?
                    </x-button>
                </div>
            </div>
        </form>
        <form action="{{ route('goal.sync-goals')}}" method="POST" id="share-my-goals-form">
            @csrf
            <div class="d-none" id="syncGoalSharingData"></div>
            <input type="hidden" name="sync_goal_id" id="sync_goal_id" value=""> 
            <input type="hidden" name="sync_users" id="sync_users" value=""> 
        </form>
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
    @endpush

    <x-slot name="js">
        {{-- {{$dataTable->scripts()}} --}}
        
    <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            CKEDITOR.replace('what', {
                toolbar: "Custom",
                toolbar_Custom: [
                    ["Bold", "Italic", "Underline"],
                    ["NumberedList", "BulletedList"],
                    ["Outdent", "Indent"],
                    ["Link"],
                ],
                extraPlugins: 'editorplaceholder',
                editorplaceholder: 'Please complete goal type, title, and tags before entering this content.',
                disableNativeSpellChecker: false
            });
            CKEDITOR.replace('measure_of_success', {
                toolbar: "Custom",
                toolbar_Custom: [
                    ["Bold", "Italic", "Underline"],
                    ["NumberedList", "BulletedList"],
                    ["Outdent", "Indent"],
                    ["Link"],
                ],
                disableNativeSpellChecker: false
            });
        });
    </script>
    
    <script>
        var modal_open = false;
        var need_fresh = false;
        var autosave = true;
        var no_warning = false;
        var myTimeout;
        $('body').popover({
            selector: '[data-toggle]',
            trigger: 'click',
        });
        
        $('.modal').popover({
            selector: '[data-toggle-select]',
            trigger: 'click',
        });

        $(".tags").multiselect({
                	enableFiltering: true,
                	enableCaseInsensitiveFiltering: true,
					// nonSelectedText: null,
            	});

				$('body').on('click', function (e) {
                $('[data-toggle=popover]').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    	if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    	}
                	});
				});

                $('body').on('click', function (e) {
                $('[data-toggle=dropdown]').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    	if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    	}
                	});
				});
                

/* 
    $('select[name="goal_type_id"]').trigger('change');

    $('select[name="goal_type_id"]').on('change',function(e){
        console.log(this);
        var desc = $('option:selected', this).attr('data-desc');;
        console.log(desc);
        $('.goal_type_text').text(desc);
    }); */
    $(document).on('show.bs.modal', '#addGoalModal', function(e) {
        modal_open = true;
        
        $('.alert-danger').hide();
        $('.alert-danger').html('<i class="fa fa-info-circle"></i> There are one or more errors on the page. Please review and try again.');
        $('.text-danger').html('');
        $('.form-control').removeClass('is-invalid');
        
        setTimeRoll();
        
        $('#what').val('');
        $('#measure_of_success').val('');
        $("#goal_title").val('');
        $('input[name=goal_type_id]').val(1);
        $('.tooltip-dropdown').find('.dropdown-item[data-value="1"]').click();
        $("input[name=start_date]").val('');
        $("input[name=target_date]").val('');
        
        
        for (var i in CKEDITOR.instances){
            CKEDITOR.instances[i].setData('');
        };
        
        CKEDITOR.instances['what'].setReadOnly(true);
        CKEDITOR.instances['measure_of_success'].setReadOnly(true);
        $('#start_date').prop("readonly",true);
        $('#target_date').prop("readonly",true);
    });
    $(document).on('hide.bs.modal', '#addGoalModal', function(e) {
        modal_open = false;
        const isContentModified = () => {
            if ($('#what').val() !== '' || $('#measure_of_success').val() !== ''
                 || $("#goal_title").val() !== '' || $('input[name=goal_type_id]').val() != 1 
                 || $("input[name=start_date]").val() !== '' || $("input[name=target_date]").val() != ''
                 ) {
                return true;
            } 
            return false;
        };
        for (var i in CKEDITOR.instances){
            CKEDITOR.instances[i].updateElement();
        };
        if(no_warning == false) {
            if (isContentModified() && !confirm("If you continue you will lose any unsaved changes.")) {                
                e.preventDefault();
            } else {
                location.reload();
            } 
        } else {
            localStorage.setItem('savemsg', 'Your goal is saved');
            alert('Your goal is saved');
            location.reload();            
        }
    });

    $(document).on('click', '.btn-submit', function(e){
        $('.btn-submit').prop('disabled',true);
        $('.btn-submit').html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        e.preventDefault();
        for (var i in CKEDITOR.instances){
            CKEDITOR.instances[i].updateElement();
        };
        $.ajax({
            url:'/goal',
            type : 'POST',
            data: $('#goal_form').serialize(),
            success: function (result) {
                console.log(result);
                if(result.success){
                    window.location.href= '/goal';
                }
            },
            error: function (error){
                $('.btn-submit').prop('disabled',false);
                $('.btn-submit').html('Save Changes');
                $('.alert-danger').show();
                $('.modal-body').animate({scrollTop: 0},100);
                var errors = error.responseJSON.errors;
                $('.text-danger').each(function(i, obj) {
                    $('.text-danger').text('');
                });
                Object.entries(errors).forEach(function callback(value, index) {
                    var className = '.error-' + value[0];
                    $('#addGoalModal input[name='+value[0]+']').addClass('is-invalid');
                    $(className).text(value[1]);
                });
            }
        });

    });
    $(document).on('click', ".link-goal", function () {
        $.get('/goal/supervisor/'+$(this).data('id'), function (data) {
            $("#supervisorGoalModal").find('.data-placeholder').html(data);
            $("#supervisorGoalModal").modal('show');
        });
    });

    $(document).on('click', '.show-goal-detail', function(e) {
        $.get('/goal/goalbank/'+$(this).data('id'), function (data) {
            $("#goal-detail-modal").find('.data-placeholder').html(data);
            $("#goal-detail-modal").modal('show');
        });
    });

    

    $(document).on('click', '.btn-link', function(e) {
        let linkedGoals = [];
        if(e.target.innerText == 'Link'){
            linkedGoals.push(e.target.getAttribute('data-id'));
            e.target.innerText = 'Unlink';
        }else{
            linkedGoals.pop(e.target.getAttribute('data-id'));
            e.target.innerText = 'Link';
        }
        $('#linked_goal_id').val(linkedGoals);
    });

    $(document).on('click', '.goal-change a', function (e) {
        const movingToPastMessage = "Changing the status of this goal will move it to your Past Goals tab. You can click there to make the goal active again at any time. Proceed?";
        const movingToCurrentMessage = "Changing the status of this goal will move it to your Current Goals tab. You can click there to access the goal again at any time. Proceed?";
        if($(this).data('current-status') === 'active' && !confirm(movingToPastMessage)) {
        e.preventDefault();
        } else if($(this).data('status') === 'active' && !confirm(movingToCurrentMessage)) {
        e.preventDefault();
        }
    });

        $(document).ready(() => {            
            $(".tags").multiselect({
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
            });
            $(".search-users").each(function() {
                const goalId = $(this).data('goal-id');
                const selectDropdown = this;
                let valueBeforeChange = [];
                $(this).multiselect({
                    allSelectedText: 'All Team Members',
                    selectAllText: 'All Team Members',
                    nonSelectedText: 'No one',
                    // nonSelectedText: null,
                    includeSelectAllOption: true,
                    onDropdownShow: function () {
                        valueBeforeChange = [...selectDropdown.options].filter(option => option.selected).map(option => option.value);
                    },
                    onDropdownHide: function () {
                        const valueAfterChange = [...selectDropdown.options].filter(option => option.selected).map(option => option.value);
                        let toRevert;
                        if (valueBeforeChange.length === 0 && valueAfterChange.length !== 0) {
                            toRevert = !confirm("Sharing this goal will make it visible to the selected employees. Continue?");
                        }

                        if (valueBeforeChange.length !==0 && valueAfterChange.length === 0) {
                            toRevert = !confirm("Making this goal private will hide it from all employees. Continue?");
                        }

                        if (toRevert) {
                            valueAfterChange.forEach((value) => {
                                if (!valueBeforeChange.includes(value)) {
                                    $(selectDropdown).multiselect('deselect', value);
                                }
                            });
                            valueBeforeChange.forEach((value) => {
                                $(selectDropdown).multiselect('select', value);
                            });
                        }
                        const finalSelectedOptions = [...selectDropdown.options].filter(option => option.selected).map(option => option.value);
                        document.getElementById("syncGoalSharingData").innerHTML = "";
                        finalSelectedOptions.forEach((value) => {
                            const input = document.createElement("input");
                            input.setAttribute('value', value);
                            input.name = $(selectDropdown).attr('name');
                            document.getElementById("syncGoalSharingData").appendChild(input);
                        });
                        const input = document.createElement("input");
                        input.setAttribute('value', finalSelectedOptions.length !== 0 ? "1" : "0");
                        input.name = "is_shared["+goalId+"]";
                        document.getElementById("syncGoalSharingData").appendChild(input);
                        const form = $("#share-my-goals-form").get()[0];
                        fetch(form.action, {method:'POST', body: new FormData(form)});
                    }
                });
            });
            
            $("#goal_title").change(function(){
                var goal_title = $("#goal_title").val();
                var tags = $('.tags').val(); 
                if (goal_title != '' && tags != ''){
                   CKEDITOR.instances['what'].setReadOnly(false);
                   CKEDITOR.instances['measure_of_success'].setReadOnly(false);
                   $('#start_date').prop("readonly",false);
                   $('#target_date').prop("readonly",false);
                } else {
                   CKEDITOR.instances['what'].setReadOnly(true);
                   CKEDITOR.instances['measure_of_success'].setReadOnly(true);
                   $('#start_date').prop("readonly",true);
                   $('#target_date').prop("readonly",true);
                }
            });
            
            $(".tags").change(function(){
                var goal_title = $("#goal_title").val();
                var tags = $('.tags').val(); 
                if (goal_title != '' && tags != ''){ 
                    CKEDITOR.instances['what'].setReadOnly(false);
                    CKEDITOR.instances['measure_of_success'].setReadOnly(false);
                    $('#start_date').prop("readonly",false);
                    $('#target_date').prop("readonly",false);
                } else {
                   CKEDITOR.instances['what'].setReadOnly(true);
                   CKEDITOR.instances['measure_of_success'].setReadOnly(true);
                   $('#start_date').prop("readonly",true);
                   $('#target_date').prop("readonly",true);
                }
            });
            
        });        
    </script>
    
    
<script>
$(".share-with-users").select2({
    language: {
                        errorLoading: function () {
                            return "Searching for results.";
                        }
                        },
    width: '100%',
    ajax: {
        url: '{{ route("get-all-user-options") }}',
        dataType: 'json',
        data: function (params) {
            const query = {
                search: params.term,
                page: params.page || 1
            };
            return query;
        },
        processResults: function (response, params) {
            return {
                results: $.map(response.data.data, function (item) {
                    return {
                        text: item.name+(item.employee_email ? ' - '+item.employee_email : ''),
                        id: item.id
                    }
                }),
                pagination: {
                    more: response.data.current_page !== response.data.last_page
                }
            }
        }
    }
});
</script>
    </x-slot>

    
    
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
        
        $(document).ready(function(){
            $(":button").removeClass('text-center');
            $(":button").addClass('text-left');     
            
            
            var savemsg = localStorage.getItem('savemsg');
            if (savemsg == 'Your goal is saved') {
                $('#msgdiv').html('<div class="alert alert-info"><p><i class="fa fa-info-circle"></i> '+savemsg+'</p></div>');
                localStorage.setItem('savemsg', '');
            }
            
        });
        
        $('input[name="filter_start_date"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Any',
                    format: 'MMM DD, YYYY'
                }
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MMM DD, YYYY') + ' - ' + picker.endDate.format('MMM DD, YYYY'));
                $("#filter-menu").submit();
            }).on('cancel.daterangepicker', function(ev, picker) {
                $('input[name="filter_start_date"]').val('Any');
                $("#filter-menu").submit();
            });
            
        $('input[name="filter_target_date"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Any',
                    format: 'MMM DD, YYYY'
                }
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MMM DD, YYYY') + ' - ' + picker.endDate.format('MMM DD, YYYY'));
                $("#filter-menu").submit();
            }).on('cancel.daterangepicker', function(ev, picker) {
                $('input[name="filter_target_date"]').val('Any');
                $("#filter-menu").submit();
            });    
            
            $('#filter-menu select, #filter-menu input').change(function () {
                $("#filter-menu").submit();
            });
            
            function sort(obj){
                $('#sortby').val(obj);
                $("#filter-menu").submit();
            }
                
        function setTimeRoll(){
                const minutes = 15;
                const SessionTime = 1000 * 60 * minutes;
                if (myTimeout) { clearInterval(myTimeout) };
                //const myTimeout = setTimeout(sessionWarning, SessionTime);
                myTimeout = setInterval(function() { 
                    if (modal_open == true && autosave == true) {
                        //$(".btn-submit").trigger("click");  
                        for (var i in CKEDITOR.instances){
                            CKEDITOR.instances[i].updateElement();
                        };
                        $.ajax({
                            url:'/goal',
                            type : 'POST',
                            data: $('#goal_form').serialize(),
                            success: function (result) {
                                console.log(result);
                                need_fresh = true;
                                if(result.success){
                                    autosave = false;
                                    no_warning = true;
                                    alert('You have been inactive for more than 15 minutes. Your goal has been automatically saved.');
                                    //window.location.href= '/goal';
                                    $('.alert-danger').show();
                                    $('.alert-danger').html('Your goal has been saved.');
                                    $('.btn-submit').hide();
                                    $('.text-danger').hide();
                                    $('.form-control').removeClass('is-invalid');                                    
                                    $('#addGoalModal').modal('toggle');
                                }
                            },
                            error: function (error){
                                console.log(error);
                                need_fresh = false;
                                autosave =  true;
                                $('.btn-submit').show();
                                $('.btn-submit').prop('disabled',false);
                                $('.btn-submit').html('Save Changes');
                                $('.alert-danger').html('<i class="fa fa-info-circle"></i> There are one or more errors on the page. Please review and try again.');
                                $('.alert-danger').show();
                                $('.modal-body').animate({scrollTop: 0},100);
                                var errors = error.responseJSON.errors;
                                $('.text-danger').each(function(i, obj) {
                                    $('.text-danger').text('');
                                });
                                Object.entries(errors).forEach(function callback(value, index) {
                                    var className = '.error-' + value[0];
                                    $('#addGoalModal input[name='+value[0]+']').addClass('is-invalid');
                                    $(className).text(value[1]);
                                });
                                //alert('You have been inactive for more than 15 minutes. Your goal has been automatically saved.');
                            }
                        });
                    }    
                }, SessionTime);                
            }
            
            $(document).ready(function() {
                // Event handler for dropdown list change
                $(document).on('change', '.share-with-users', function() {
                  // Get the selected value
                  var selectedValue = $(this).val();

                  // Get the corresponding goal ID in the row
                  var goalId = $(this).closest('tr').data('goal-id');

                  // Perform desired actions with the selected value and goal ID
                  console.log('Selected value:', selectedValue);
                  console.log('Goal ID:', goalId);

                  console.log('Goal ID:', goalId);
                  console.log('Selected Values:', selectedValue);
                  $('#sync_goal_id').val(goalId);
                  $('#sync_users').val(selectedValue);
                  
                  // Prepare the data to be sent
                    var formData = {
                      sync_goal_id: goalId,
                      sync_users: selectedValue
                    };

                    $.ajax({
                      url: '{{ route("goal.sync-goals") }}',
                      type: 'POST',
                      data: formData,
                      success: function(response) {
                        // Handle success response
                        console.log('Data submitted successfully');
                        // Optionally, you can perform additional actions here
                      },
                      error: function(xhr, status, error) {
                        // Handle error response
                        console.error('Error submitting data:', error);
                        // Optionally, you can display an error message or perform additional actions here
                      }
                    });
                  });

            });
</script>    

<style>
    .multiselect {
            overflow: hidden;
            text-overflow: ellipsis;
            width: 275px;
    }
    
    .alert-danger {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    
    
    .multiselect-container{
        height: 350px; 
        overflow-y: scroll;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1A5A96;
    }
</style>    
 
    