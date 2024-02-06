<x-side-layout title="{{ __('My Goals - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit: {{ $goal-> title}} 
        </h2>
        <!----<small><a href="{{ route('goal.index') }}">Back to list</a></small>---->
    </x-slot>

    <div class="container-fluid">
        <form action="{{ route ('goal.update', $goal->id)}}" method="POST">
            <input type ="hidden" id="datatype" name="datatype" value"manual">
            @csrf
            @method('PUT')
            <div class="row">
                @if(Session::has('message'))
                <div class="col-12">                    
                    <div class="alert alert-danger" style="display:">
                        <i class="fa fa-info-circle"></i> {{ Session::get('message') }}
                    </div>
                </div>
                @endif
                <div class="col-12">
                    <b>Goal Type</b>
                    <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="{{$type_desc_str}}"> </i>
                    <!-- <x-tooltip-dropdown-outside name="goal_type_id" :options="$goaltypes" data-trigger='click' data-toggle="popover" data-html="true" data-content="{{$type_desc_str}}" label="Goal Type" popoverstr="{{$type_desc_str}}" tooltipField="description" displayField="name" />                                                                             -->
                    <x-dropdown :list="$goaltypes" name="goal_type_id" :selected="$goal->goal_type_id" />
                    <b>Goal Title</b>
                    <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="A short title (1-3 words) used to reference the goal throughout the Performance Development Platform."> </i>
                    <x-input-modal name="title"  :value="$goal->title"/>                    
                    @if(session()->has('title_miss'))                           
                        <small class="text-danger">The title field is required</small>
                    @endif
                    <!-- <x-input-modal label="Goal Title" name="title" tooltip='A short title (1-3 words) used to reference the goal throughout the Performance Development Platform.' :value="$goal->title"/>                     -->
                </div>                                                   
                <div class="col-12">
                    <b>Tags</b>    
                    <i class="fa fa-info-circle" id="tags_label" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="Tags help to more accurately identity, sort, and report on your goals. You can add more than one tag to a goal. The list of tags will change and grow over time. <br/><br/><a href='/resources/goal-setting?t=8' target=\'_blank\'><u>View full list of tag descriptions.</u></a><br/><br/>Don't see the goal tag you are looking for? <a href='mailto:performance.development@gov.bc.ca?subject=Suggestion for New Goal Tag'>Suggest a new goal tag</a>."></i>				
                    <x-xdropdown :list="$tags" name="tag_ids[]" :selected="array_column($goal->tags->toArray(), 'id')" class="tags" multiple/>
                    @if(session()->has('tags_miss'))                           
                        <small class="text-danger">The tags field is required</small>
                    @endif
                </div>
                <div class="col-12">
                   <b>Goal Description</b>      
                   <p>
				    Each goal should include a description of <b>WHAT</b>  
				    <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".'> </i> you will accomplish, <b>WHY</b> 
				    <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".'> </i> it is important, and <b>HOW</b> 
				    <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".'> </i> you will achieve it. 
				    </p>            
                   <!-- <p class="py-2">Each goal should include a description of <b>WHAT</b><x-tooltip-modal text='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".' /> you will accomplish, <b>WHY</b><x-tooltip-modal text='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".' /> it is important, and <b>HOW</b><x-tooltip-modal text='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".'/> you will achieve it.</p>                         -->
                   <x-textarea-modal id="what" name="what" :value="$goal->what" />
                   @if(session()->has('what_miss'))
                        <small class="text-danger">The description field is required</small>
                    @endif
                   </div>
                <div class = "col-12">
                    <b>Measures of Success</b>
                    <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"'> </i>
                    <x-textarea-modal id="measure_of_success" name="measure_of_success" class="content" :value="$goal->measure_of_success" />
                    <!-- <x-textarea-modal id="measure_of_success" label="Measures of Success" name="measure_of_success" class="content" tooltip='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"' :value="$goal->measure_of_success" /> -->
                </div>                                        
                <div class="col-sm-6">
                    <x-input label="Start Date" type="date" name="start_date" id="start_date" :value="$goal->start_date ? $goal->start_date->format('Y-m-d') : ''" />
                </div>
                <div class="col-sm-6">
                    <x-input label="End Date" type="date" name="target_date" id="target_date" :value="$goal->target_date ? $goal->target_date->format('Y-m-d') : ''" />
                </div>
                
                @if(session()->has('is_bank')) 
                <div class="col-6">
                    <!-- <label> -->
                        <b>Mandatory/Suggested</b>
                        <select class="form-control" name="is_mandatory">
                            <option value="1" @if($goal->is_mandatory == 1) selected @endif>Mandatory</option>
                            <option value="0" @if($goal->is_mandatory == 0) selected @endif>Suggested</option>
                        </select>
                    <!-- </label> -->
                </div>
                <div class="col-6">&nbsp;</div>
                <div class="col-12">&nbsp;</div>
                @endif
   
                <div class="col-12 text-center mb-3">
                    <x-button type="submit" class="btn-lg"> Save </x-button>
                </div>
            </div>
        </form>
    </div>
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
    @endpush

    @push('js')
    <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>

    <script>				
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
		</script>


    <script>
        var no_msg = false;
        $(document).ready(() => {
            $('.tags').multiselect({
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true
            });
            
            var curr_start_date = $( "#start_date" ).val();
            $( "#target_date" ).attr("min",curr_start_date);    
            
        });
    </script>
    <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            CKEDITOR.replace('what', {
                toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent", "Link"] ],disableNativeSpellChecker: false  });
            CKEDITOR.replace('measure_of_success', {
                toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent", "Link"] ],disableNativeSpellChecker: false  });
        });
    </script>
    <script>
        if(!!window.performance && window.performance.navigation.type === 2)
        {
            console.log('Reloading');
            window.location.reload();
        }
        window.isDirty = true;
        $('form').on('submit', () => {
            window.isDirty = false;
            if (no_msg == false) {
                // Display a confirmation dialog
                const userConfirmed = confirm('Are you sure you want to update Goal ?');                
                // Cancel the form submission if the user did not confirm
                if (!userConfirmed) {
                    event.preventDefault();
                }
            }
        });
        let originalData = $('form').serialize();
        $(document).ready(function () {
            originalData = $('form').serialize();
        });
        window.onbeforeunload = function () {
            if (!window.isDirty) {
                return;
            }
            for (var i in CKEDITOR.instances) {
                CKEDITOR.instances[i].updateElement();
            };
            const currentData = $('form').serialize();
            if (currentData != originalData) {
                return "If you continue you will lose any unsaved information";
            }
        };
        
        $('body').popover({
            selector: '[data-toggle-select]',
            trigger: 'click',
        });
        
        const minutes = 3;
        const SessionTime = 1000 * 60 * minutes;
        
        $(document).ready(function () { 
            @if (!\Session::has('autosave')) 
                const myTimeout = setTimeout(sessionWarning, SessionTime);  
            @endif
        });    
            
        function sessionWarning() {
            no_msg = true;    
            $('#datatype').val('auto');
            $(".btn-primary").trigger("click");
            alert('You have not saved your work in 20 minutes. To protect your work, it has been automatically saved.');    
        } 

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
        
        @if(session()->has('title_miss'))                           
            $('input[name=title]').addClass('is-invalid');
        @endif
        
</script>    


<style> 
    .alert-danger {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    .multiselect-container{
        height: 350px; 
        overflow-y: scroll;
    }
</style>    
