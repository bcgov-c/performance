<script src="https://cdn.ckeditor.com/4.20.1/standard-all/ckeditor.js"></script>
<style>
.p-3{
    width:100%;
    margin-left: 7.5;
    margin-right: 7.5;
}
</style>    
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
            <x-button icon="clone" href="{{ route('goal.library') }}" >
                Add Goal from Goal Bank
            </x-button>
            <x-button icon="question" href="{{ route('resource.user-guide','t=1') }} " target="_blank" data-trigger="focus" data-toggle="popover" tooltip='Click here to access goal setting resources and examples (opens in new window).'>    
                Need Help?
            </x-button>
        @endif

    @endif
    <div class="mt-4">
        {{-- {{$dataTable->table()}} --}}

        <div class="row">
            @if ($type != 'supervisor')   
                <div class="card p-3">         
                    <h3>Search Goals</h3>
                    <form action="" method="get" id="filter-menu">    
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="title">Goal Title<br/>
                                <input type="text" id="title" name="title" class="form-control" value="{{request()->title}}">
                                </label>
                            </div>
                            <div class="form-group col-md-2">
                                <x-dropdown :list="$goaltypes" id="goal_type" label="Goal Type" name="goal_type" :selected="request()->goal_type"></x-dropdown>
                            </div>
                            @if ($type == 'past')
                                <div class="form-group col-md-2">
                                    <x-dropdown :list="$statusList" id="status" label="Status" name="status" :selected="request()->status"></x-dropdown>                      
                                </div>
                            @endif
                            <div class="form-group col-md-2">
                                <x-dropdown :list="$tagsList" label="Tags" id="tag_id" name="tag_id" :selected="request()->tag_id"></x-dropdown>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="filter_start_date">Start Date<br/>
                                <input aria-label="Enter the goals start date in format MM/DD/YYYY" placeholder="MM/DD/YYYY" type="text" class="form-control" id="filter_start_date" name="filter_start_date" value="{{request()->filter_start_date ?? ''}}">
                                </label>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="filter_target_date">End Date<br/>
                                <input aria-label="Enter the goals target date in format MM/DD/YYYY" placeholder="MM/DD/YYYY" type="text" class="form-control" id="filter_target_date" name="filter_target_date" value="{{request()->filter_target_date ?? ''}}">
                                </label>
                            </div>
                            <div class="form-group col-md-2">
                            </div>
                            <div class="form-group col-md-2 p-3 float-left float-bottom" style="display: flex;flex-direction: column;margin-left: 0px;">
                                <div class="form-group row">
                                    <span class="float-left float-bottom">  
                                        <button type="submit" class="btn btn-primary mr-2" aria-label="Click the button to search goals">Search</button>
                                        <button type="button" class="btn btn-secondary" onclick="resetForm()" aria-label="Click the button to reset the form">Reset</button>
                                    </span>
                                </div>
                            </div>
                        </div>                    
                    </form>    
                </div>

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

<div class="modal fade" id="unsavedChangesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index:1060"> 
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Unsaved Changes</h5>
            </div>
            <div class="modal-body">
                <p>Save changes to this goal?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary saveChangesBtn" id="saveChangesBtn">Save Changes</button>
                <button type="button" class="btn btn-secondary" id="discardChangesBtn">Don't Save</button>
                <button type="button" class="btn btn-secondary" id="cancelChangesBtn">Cancel</button>
            </div>
            </div>
        </div>
</div>

<div class="modal fade" id="addGoalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="addGoalModalLabel">Create New Goal</h5>
        <button type="button" class="close focus-border" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="color:white">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <form id="goal_form" action="{{ route ('goal.store')}}" method="POST">
            <input type="hidden" name="created_goal_id" id="created_goal_id" value="0">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger" role="alert" style="display:none">
                        <i class="fa fa-info-circle"></i> There are one or more errors on the page. Please review and try again.
                    </div>
                </div>
                <div class="col-6">
                    <div>
                        <b>Goal Type</b>
                        <i class="fa fa-info-circle" data-trigger='focus' tabindex="0" data-toggle="popover" data-placement="right" data-html="true" data-content="{{$type_desc_str}}" aria-label="{{$type_desc_str}}"> </i>
                        <x-dropdown :list="$goal_types_modal" name="goal_type_id" />
                    </div>
                </div>
                <div class="col-6">
                       <b>Goal Title</b>
                        <i class="fa fa-info-circle" data-trigger='focus' tabindex="0" data-toggle="popover" data-placement="right" data-html="true" data-content="A short title (1-3 words) used to reference the goal throughout the Performance Development Platform." aria-label="A short title (1-3 words) used to reference the goal throughout the Performance Development Platform."> </i>                        
                        <x-input-modal id="goal_title" name="title"  aria-label="Goal title input"/>
                </div>
                <div class="col-sm-6">
                        <b>Tags</b>
                        <i class="fa fa-info-circle" id="tags_label" tabindex="0" data-trigger='focus' data-toggle="popover" data-placement="right" data-html="true" data-content="Tags help to more accurately identity, sort, and report on your goals. You can add more than one tag to a goal. The list of tags will change and grow over time. <br/><br/><a href='/resources/goal-setting?t=8' target=\'_blank\'><u>View full list of tag descriptions.</u></a><br/><br/>Don't see the goal tag you are looking for? <a href='mailto:performance.development@gov.bc.ca?subject=Suggestion for New Goal Tag'>Suggest a new goal tag</a>." aria-label="Tags help to more accurately identity, sort, and report on your goals. You can add more than one tag to a goal. The list of tags will change and grow over time."></i>				
                        <x-xdropdown :list="$tags" name="tag_ids[]"  class="tags" displayField="name" aria-label="Goal tags" multiple/>
                        <small  class="text-danger error-tag_ids"></small>
                </div>
                <div class="col-12">
                        <!-- <label style="font-weight: normal;"> -->
                        <b>Goal Description</b>          
                        <p>
				        Each goal should include a description of <b>WHAT</b>  
				        <i class="fa fa-info-circle" data-trigger="focus" tabindex="0" data-toggle="popover" data-placement="right" data-html="true" data-content='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".' aria-label='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".'> </i> you will accomplish, <b>WHY</b> 
				        <i class="fa fa-info-circle" data-trigger="focus" tabindex="0" data-toggle="popover" data-placement="right" data-html="true" data-content='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".' aria-label='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".'> </i> it is important, and <b>HOW</b> 
				        <i class="fa fa-info-circle" data-trigger="focus" tabindex="0" data-toggle="popover" data-placement="right" data-html="true" data-content='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".' aria-label='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".'> </i> you will achieve it. 
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
                            <i class="fa fa-info-circle" data-trigger='focus' tabindex="0" data-toggle="popover" data-placement="right" data-html="true" data-content='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"' aria-label='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"'> </i>                        
                            <x-textarea-modal id="measure_of_success" name="measure_of_success" />
                            <small class="text-danger error-measure_of_success"></small>
                </div>
                <div class="col-sm-6">
                    <label for="start_date">Start Date<br/>
                        <input aria-label="Enter the goals start date in format YYYY-MM-DD" placeholder="YYYY-MM-DD" type="text" class="form-control" id="start_date" name="start_date" value="">
                    </label>
                    <small  class="text-danger error-start_date"></small>
                </div>
                <div class="col-sm-6">
                    <label for="start_date">End Date<br/>
                        <input aria-label="Enter the goals target date in format YYYY-MM-DD" placeholder="YYYY-MM-DD" type="text" class="form-control" id="target_date" name="target_date" value="">
                    </label>
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
                    <!----
                    <x-button type="button" class="btn-md btn-submit"> Save Changes</x-button>
                    ---->
                    <x-button type="button" class="btn-md" id="saveGoalBtn"> Save Changes</x-button>                    
                    <x-button icon="question" href="{{ route('resource.goal-setting') }} " target="_blank" tooltip='Click here to access goal setting resources and examples (opens in new window).'>
                        Need Help
                    </x-button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    
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

<!-- Auto Save Modal -->
<div class="modal fade" id="autoSaveModal" tabindex="-1" aria-labelledby="autoSaveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="autoSaveModalLabel">Auto Save Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        You have not saved your work in 20 minutes. To protect your work, it has been automatically saved.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeModalBtn">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to share the goal with the selected employee?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelShareBtn">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmShareBtn">Share</button>
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
        var saved = false;
        var saved_id = 0;
        var isContentModified = false;
        
        var initialFormValues = {};

        $(document).ready(function () {
            // Iterate over all input, select, textarea elements inside the form
            $('#goal_form :input').each(function () {
                var element = $(this);
                // Store the initial value of each element
                initialFormValues[element.attr('name')] = element.val();
            });

            // Event listener for input, select, textarea changes
            $('#goal_form :input').on('change', function () {
                isContentModified = true;
            });

            // Event listener for CKEditor changes (assuming you have a CKEditor instance with the id 'editor')
            CKEDITOR.instances['what'].on('change', function () {
                isContentModified = true;
            });

            CKEDITOR.instances['measure_of_success'].on('change', function () {
                isContentModified = true;
            });
        });
        
        
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
        $('#addGoalModal').focus(); // Set focus to the modal itself
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
        var has_alert = false;
        var errorMessage = "There are one or more errors on the page. Please review and try again.";

        if ($('.alert-danger').html().includes(errorMessage)) {
            has_alert = true;
        } 

        if(isContentModified || has_alert){
            e.preventDefault();
            $('#unsavedChangesModal').modal('show');
        } else {
            location.reload();
        }
    });

    $(document).on('click', '#saveChangesBtn', function(e){
        isContentModified = false;
        for (var i in CKEDITOR.instances){
            CKEDITOR.instances[i].updateElement();
        };
        const whatInput = CKEDITOR.instances['what'];
        var what_value = whatInput.getData();
        if(saved) {
            console.log('update existing goal');
            $.ajax({
                url:'/goal',
                type : 'POST',
                data: $('#goal_form').serialize(),
                success: function (result) {
                    console.log(result);
                    need_fresh = true;
                    if(result.success){
                        //window.location.href= '/goal';
                        $('.alert-danger').show();
                        $('.alert-danger').html('Your goal has been updated.');
                        $('.btn-submit').hide();
                        $('.text-danger').hide();
                        $('.form-control').removeClass('is-invalid');  
                        
                        saved = true;
                        isContentModified = false;
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
        } else {
            console.log('create new goal');
            $.ajax({
                url:'/goal',
                type : 'POST',
                data: $('#goal_form').serialize(),
                success: function (result) {
                    console.log(result);
                    need_fresh = true;
                    if(result.success){
                        saved = true;
                        saved_id = result.goal_id;
                        //window.location.href= '/goal';
                        $('#addGoalModalLabel').html('Update Current Goal');
                        $('#created_goal_id').val(saved_id);
                        $('.alert-danger').show();
                        $('.alert-danger').html('Your goal has been saved.');
                        $('.btn-submit').hide();
                        $('.text-danger').hide();
                        $('.form-control').removeClass('is-invalid');  
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
            
        $('#unsavedChangesModal').modal('hide');
    });

    $(document).on('click', '#saveGoalBtn', function(e){
        isContentModified = false;
        $(this).prop('disabled', true);
        for (var i in CKEDITOR.instances){
            CKEDITOR.instances[i].updateElement();
        };
        const whatInput = CKEDITOR.instances['what'];
        var what_value = whatInput.getData();
        if(saved) {
            console.log('update existing goal');
            $(this).prop('disabled', true);
            $.ajax({
                url:'/goal',
                type : 'POST',
                data: $('#goal_form').serialize(),
                success: function (result) {
                    console.log(result);
                    need_fresh = true;
                    if(result.success){
                        //window.location.href= '/goal';
                        $('.alert-danger').show();
                        $('.alert-danger').html('Your goal has been updated.');
                        $('.btn-submit').hide();
                        $('.text-danger').hide();
                        $('.form-control').removeClass('is-invalid');  
                        
                        saved = true;
                        isContentModified = false;
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
            $(this).prop('disabled', false);
        } else {
            console.log('create new goal');
            $.ajax({
                url:'/goal',
                type : 'POST',
                data: $('#goal_form').serialize(),
                success: function (result) {
                    console.log(result);
                    need_fresh = true;
                    if(result.success){
                        saved = true;
                        saved_id = result.goal_id;
                        //window.location.href= '/goal';
                        $('#addGoalModalLabel').html('Update Current Goal');
                        $('#created_goal_id').val(saved_id);
                        $('.alert-danger').show();
                        $('.alert-danger').html('Your goal has been saved.');
                        $('.btn-submit').hide();
                        $('.text-danger').hide();
                        $('.form-control').removeClass('is-invalid');  
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
            $(this).prop('disabled', false);

        }
            
        $('#unsavedChangesModal').modal('hide');
    });




    $(document).on('click', '#discardChangesBtn', function(e){
        location.reload();
        e.preventDefault();
    });

    $(document).on('click', '#cancelChangesBtn', function(e){
        $('#unsavedChangesModal').modal('hide');
        modal_open = true;
        e.preventDefault();
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



        $(document).ready(function() {
            $('select[name="tag_ids[]"]').attr('tabindex', '0');


            // Initialize the start_date daterangepicker
            $('input[name="start_date"]').daterangepicker({
                autoApply: true,
                autoUpdateInput: false, // Prevent the input from auto-updating
                singleDatePicker: true, // Set to true for a single date picker
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // Manually update the input field when a date is selected
            $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                $(this).val(startDate);

                // Update the minDate of the target_date picker
                $('input[name="target_date"]').data('daterangepicker').minDate = picker.startDate;
                $('input[name="target_date"]').val(''); // Optionally clear the target_date value
            });

            // Ensure the placeholder remains
            $('input[name="start_date"]').attr('placeholder', 'YYYY-MM-DD');

            // Initialize the target_date daterangepicker
            $('input[name="target_date"]').daterangepicker({
                autoApply: true,
                autoUpdateInput: false, // Prevent the input from auto-updating
                singleDatePicker: true, // Set to true for a single date picker
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // Manually update the input field when a date is selected
            $('input[name="target_date"]').on('apply.daterangepicker', function(ev, picker) {
                var targetDate = picker.startDate.format('YYYY-MM-DD');

                // Check if start_date is empty
                if ($('input[name="start_date"]').val() === '') {
                    alert('Please choose a start date first.');
                    $(this).val(''); // Clear the target_date value
                } else {
                    $(this).val(targetDate);
                }
            });

            // Ensure the placeholder remains
            $('input[name="target_date"]').attr('placeholder', 'YYYY-MM-DD');

            // Add change event for start_date
            $('input[name="start_date"]').on('change', function() {
                var startDate = $(this).val();
                var targetDate = $('input[name="target_date"]').val();

                // If start date is empty or later than target date
                if (startDate === '' || (targetDate !== '' && moment(startDate).isAfter(moment(targetDate)))) {
                    alert('The start date cannot be empty or later than the target date. Both dates will be cleared.');
                    $('input[name="start_date"]').val('');
                    $('input[name="target_date"]').val('');
                    // Reset minDate for target_date picker
                    $('input[name="target_date"]').data('daterangepicker').minDate = false;
                }
            });

            // Check if start_date has an initial value on page load
            var initialStartDate = $('input[name="start_date"]').val();
            if (initialStartDate) {
                // Set the minDate of the target_date picker
                var initialStartMoment = moment(initialStartDate, 'YYYY-MM-DD');
                $('input[name="target_date"]').data('daterangepicker').minDate = initialStartMoment;
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
            autoApply: true,
            autoUpdateInput: false, // Prevent the input from auto-updating
            singleDatePicker: true, // Set to true for a single date picker
            locale: {
                format: 'MMM/DD/YYYY'
            }          
        });
        // Manually update the input field when a date is selected
        $('input[name="filter_start_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));
        });

        // Ensure the placeholder remains
        $('input[name="filter_start_date"]').attr('placeholder', 'MM/DD/YYYY');

        $('input[name="filter_target_date"]').daterangepicker({
            autoApply: true,
            autoUpdateInput: false, // Prevent the input from auto-updating
            singleDatePicker: true, // Set to true for a single date picker
            locale: {
                format: 'MM/DD/YYYY'
            }
        });
        // Manually update the input field when a date is selected
        $('input[name="filter_target_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));
        });

        // Ensure the placeholder remains
        $('input[name="filter_target_date"]').attr('placeholder', 'MM/DD/YYYY');
            
            
            function sort(obj){
                $('#sortby').val(obj);
                $("#filter-menu").submit();
            }
                
            function setTimeRoll() {
                const minutes = 3;
                const SessionTime = 1000 * 60 * minutes;
                if (myTimeout) { clearInterval(myTimeout) };
                //const myTimeout = setTimeout(sessionWarning, SessionTime);
                myTimeout = setInterval(function() { 
                    if (modal_open == true && autosave == true) {
                        isContentModified = false;
                        //$(".btn-submit").trigger("click");  
                        for (var i in CKEDITOR.instances){
                            CKEDITOR.instances[i].updateElement();
                        };
                        if(saved) {
                            console.log('update existing goal');
                            $(this).prop('disabled', true);
                            $.ajax({
                                url:'/goal',
                                type : 'POST',
                                data: $('#goal_form').serialize(),
                                success: function (result) {
                                    console.log(result);
                                    need_fresh = true;
                                    if(result.success){
                                        //window.location.href= '/goal';
                                        $('.alert-danger').show();
                                        $('.alert-danger').html('Your goal has been saved.');
                                        $('.btn-submit').hide();
                                        $('.text-danger').hide();
                                        $('.form-control').removeClass('is-invalid');  
                                        
                                        saved = true;
                                        isContentModified = false;
                                        $('#autoSaveModal').modal('show'); // Show the modal
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
                                        //$('#autoSaveModal').modal('show'); // Show the modal if there is an error
                                    }
                            });   
                            $(this).prop('disabled', false);
                        } else {
                            console.log('create new goal');
                            $.ajax({
                                url:'/goal',
                                type : 'POST',
                                data: $('#goal_form').serialize(),
                                success: function (result) {
                                    console.log(result);
                                    need_fresh = true;
                                    if(result.success){
                                        saved = true;
                                        saved_id = result.goal_id;
                                        //window.location.href= '/goal';
                                        $('#addGoalModalLabel').html('Update Current Goal');
                                        $('#created_goal_id').val(saved_id);
                                        $('.alert-danger').show();
                                        $('.alert-danger').html('Your goal has been saved.');
                                        $('.btn-submit').hide();
                                        $('.text-danger').hide();
                                        $('.form-control').removeClass('is-invalid');  
                                        $('#autoSaveModal').modal('show'); // Show the modal
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
                                        //$('#autoSaveModal').modal('show'); // Show the modal if there is an error
                                    }
                            });   
                            $(this).prop('disabled', false);
                        }
                    }    
                }, SessionTime);                
            }

            
            $(document).ready(function() {
                var initialValue;
                var selectedValue;
                var goalId;
                var currentDropdown;

                // Event handler for dropdown list change
                $(document).on('select2:selecting', '.share-with-users', function(e) {
                    // Track the initial selected values
                    currentDropdown = $(this);
                    initialValue = $(this).val() || [];
                });

                $(document).on('change', '.share-with-users', function() {
                    // Get the selected value
                    selectedValue = $(this).val() || [];

                    // Get the corresponding goal ID in the row
                    goalId = $(this).closest('tr').data('goal-id');

                    // Check if initialValue is undefined or empty
                    if (!initialValue || initialValue.length === 0) {
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
                    } else {
                        // Check if the action is adding a new value
                        if (selectedValue.length > initialValue.length) {
                            // Show the confirmation modal
                            $('#confirmationModal').modal('show');
                        } else {
                            // Update the initial value for future changes
                            initialValue = selectedValue.slice(); // Copy the selected values as the new initial values
                            $(this).data('initial-value', initialValue);
                        }
                    }
                    
                });

                // Handle the confirmation button click
                $('#confirmShareBtn').on('click', function() {
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

                    // Hide the modal
                    $('#confirmationModal').modal('hide');
                    // Update the initial value for future changes
                    currentDropdown.data('initial-value', selectedValue);
                });

                // Handle the cancel button click
                $('#cancelShareBtn').on('click', function() {
                    location.reload();
                });

                // Handle the close button (X) click
                $('#closeModalBtn').on('click', function() {
                    location.reload();
                });

                // Select2 initialization
                $(".share-with-users").select2({
                    placeholder: "Search and add employees from the list with whom you want to share the goal",
                    language: {
                        errorLoading: function () {
                            return "Searching for results.";
                        },
                        inputTooShort: function (args) {
                            return "Type to search for employees";
                        },
                        searching: function () {
                            return "Searching for results";
                        },
                        noResults: function () {
                            return "No results found";
                        },
                        input: function () {
                            return "Search and add employees from the list with whom you want to share the goal";
                        }
                    },
                    width: '100%',
                    data: [{
                        id: '',
                        text: '--- Select the employee ---'
                    }],
                    ajax: {
                        url: '{{ route("goal.get-all-user-options") }}',
                        dataType: 'json',
                        data: function (params) {
                            const query = {
                                search: params.term,
                                page: params.page || 1
                            };
                            return query;
                        },
                        processResults: function (response, params) {
                            const results = $.map(response.data.data, function (item) {
                                return {
                                    text: item.name + (item.employee_email ? ' - ' + item.employee_email : ''),
                                    id: item.id
                                };
                            });
                            results.unshift({ id: '', text: '--- Select the employee ---' });

                            return {
                                results: results,
                                pagination: {
                                    more: response.data.current_page !== response.data.last_page
                                }
                            };
                        }
                    }
                });

                // Store the initial value in a data attribute
                $(".share-with-users").each(function() {
                    $(this).data('initial-value', $(this).val() || []);
                });

                
            });


</script>    


@include('goal.partials.accessibility')
 
    