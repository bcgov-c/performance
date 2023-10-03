<x-side-layout title="{{ __('Goal Bank - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Goal Bank</h3>
        </div>
    </div>

	<small><a href=" {{ route(request()->segment(1).'.goalbank.manageindex') }}" class="btn btn-md btn-primary"><i class="fa fa-arrow-left"></i> Back to goals</a></small>

	<br><br>

	<h4>Edit: {{ $goaldetail->title }}</h4>

	<form id="notify-form" action="{{ route(request()->segment(1).'.goalbank.updategoaldetails', $request->id) }}" method="post">
		@csrf

                @if(Session::has('message'))
                <div class="col-12">                    
                    <div class="alert alert-danger" style="display:">
                        <i class="fa fa-info-circle"></i> {{ Session::get('message') }}
                    </div>
                </div>
                @endif
                
		<div class="row">
				<div class="col col-md-2">
					<b> Goal Type </b>
				<i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="{{$type_desc_str}}"> </i>
				<x-dropdown id="goal_type_id" :list="$goalTypes" aria-label="Goal Type" name="goal_type_id" :selected="$goaldetail->goal_type_id" />
			</div>
			<div class="col col-md-8">
				<b> Goal Title </b>
				<i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="A short title (1-3 words) used to reference the goal throughout the Performance Development Platform."> </i>
				<x-input name="title" :value="$goaldetail->title" />
				@if(session()->has('title_miss'))                           
                                    <small class="text-danger">The title field is required</small>
                                @endif
			</div>
			<div class="col col-md-2">
				<x-dropdown :list="$mandatoryOrSuggested" label="Mandatory/Suggested" name="is_mandatory" :selected="$goaldetail->is_mandatory" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<b>Tags</b>
				<i class="fa fa-info-circle" id="tags_label" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="Tags help to more accurately identity, sort, and report on your goals. You can add more than one tag to a goal. The list of tags will change and grow over time. <br/><br/>Don't see the goal tag you are looking for? <a href='mailto:performance.development@gov.bc.ca?subject=Suggestion for New Goal Tag'>Suggest a new goal tag</a>."></i>				
				<x-dropdown :list="$tags" name="tag_ids[]" :selected="array_column($goaldetail->tags->toArray(), 'id')" class="tags" multiple/>								
				@if(session()->has('tags_miss'))                           
                                    <small class="text-danger">The tags field is required</small>
                                @endif
			</div>
		</div>
		<div class="row">
				<div class="col col-md-2">
					<b> Display Name </b>
					<i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="Describe who created or approved the goal content (i.e. PSA Human Resources). If you don’t enter anything here, your own name will be shown as the creator of the goal throughout the platform. This could be confusing for users who may not know you or your role in the organization."> </i>
					<x-input name="display_name" :value="$goaldetail->display_name"/>
				</div>
			</div>
		<div class="row">
			<div class="col-md-12">
				<b>Goal Description</b>
				<p>
					Each goal should include a description of <b>WHAT</b>  
					<i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".'> </i> you will accomplish, <b>WHY</b> 
					<i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".'> </i> it is important, and <b>HOW</b> 
					<i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".'> </i> you will achieve it. 
				</p>
				<x-textarea id="what" name="what" :value="$goaldetail->what" />
				@if(session()->has('what_miss'))
                                    <small class="text-danger">The description field is required</small>
                                @endif
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<b>Measures of Success</b>
				<i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"'> </i>
				<x-textarea name="measure_of_success" :value="$goaldetail->measure_of_success" />
				<small class="text-danger error-measure_of_success"></small>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2">
				<x-input label="Start Date " class="error-start" type="date" id="start_date" name="start_date" :value="$goaldetail->start_date ? $goaldetail->start_date->format('Y-m-d') : ''" />
				<small  class="text-danger error-start_date"></small>
			</div>
			<div class="col-md-2">
				<x-input label="End Date " class="error-target" type="date" id="target_date" name="target_date" :value="$goaldetail->target_date ? $goaldetail->target_date->format('Y-m-d') : ''" />
				<small  class="text-danger error-target_date"></small>
			</div>
		</div>
		
		<div class="col-md-3 mb-2">
			<button class="btn btn-primary mt-2" type="submit" name="btn_send" value="btn_send">Save Changes</button>
			<button type="button" class="btn btn-cancel mt-2" onClick="window.location='{{ URL::previous() }}'">Cancel</button>
		</div>

	</form>

	<h6 class="m-20">&nbsp;</h6>
	<h6 class="m-20">&nbsp;</h6>
	<h6 class="m-20">&nbsp;</h6>


    @push('css')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
    @endpush

	<x-slot name="css">
		<style>

            #currenttable_filter label {
                text-align: right !important;
            }

			.select2-container .select2-selection--single {
				height: 38px !important;
			}EmployeeID

			.select2-container--default .select2-selection--single .select2-selection__arrow {
				height: 38px !important;
			}

			.pageLoader{
				/* background: url(../images/loader.gif) no-repeat center center; */
				position: fixed;
				top: 0;
				left: 0;
				height: 100%;
				width: 100%;
				z-index: 9999999;
				background-color: #ffffff8c;

			}

			.pageLoader .spinner {
				/* background: url(../images/loader.gif) no-repeat center center; */
				position: fixed;
				top: 25%;
				left: 47%;
				/* height: 100%;
				width: 100%; */
				width: 10em;
				height: 10em;
				z-index: 9000000;
			}

		</style>
	</x-slot>

	<x-slot name="js">
		<script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
		<script src="//cdn.ckeditor.com/4.17.2/standard/ckeditor.js"></script>

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
			$(document).ready(function(){

				$(".tags").multiselect({
                	enableFiltering: true,
                	enableCaseInsensitiveFiltering: true
            	});

				$('#pageLoader').hide();

				$('#notify-form').keydown(function (e) {
					if (e.keyCode == 13) {
						e.preventDefault();
						return false;
					}
				});

				CKEDITOR.replace('what', {
					toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent", "Link"] ],disableNativeSpellChecker: false});

				CKEDITOR.replace('measure_of_success', {
					toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent", "Link"] ],disableNativeSpellChecker: false});

				$(window).on('beforeunload', function(){
					$('#pageLoader').show();
				});

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

			});

		</script>
	</x-slot>

</x-side-layout>



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