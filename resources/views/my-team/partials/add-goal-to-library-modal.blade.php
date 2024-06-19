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
</style>
<div class="modal fade" id="addGoalToLibraryModal" aria-labelledby="addGoalToLibraryLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="addGoalToLibraryLabel">Add Goal to Bank</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="color:white">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <form action="{{ route('my-team.add-goal-to-library')}}" method="POST" id='add-goal-to-library-form'>
            <input type="hidden" id="created_id" name="created_id" value="0">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger" style="display:none">
                        <i class="fa fa-info-circle"></i> There are one or more errors on the page. Please review and try again.
                    </div>
                </div>
                <div class="col-6">
                    <b>Goal Type</b>
                    <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="{{$type_desc_str}}"> </i>
                    <?php unset($goaltypes[4]);?>
                    <x-dropdown :list="$goaltypes" name="goal_type_id" />
                </div>
                <div class="col-6">
                    <b>Goal Title</b>
                    <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="A short title (1-3 words) used to reference the goal throughout the Performance Development Platform."> </i>                        
                    <x-input-modal name="title" id="goal_title"/>                    
                </div>                
                <div class="col-sm-6">
                        <b>Tags</b>
                        <i class="fa fa-info-circle" id="tags_label" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="Tags help to more accurately identity, sort, and report on your goals. You can add more than one tag to a goal. The list of tags will change and grow over time. <br/><br/><a href='/resources/goal-setting?t=8' target=\'_blank\'><u>View full list of tag descriptions.</u></a><br/><br/>Don't see the goal tag you are looking for? <a href='mailto:performance.development@gov.bc.ca?subject=Suggestion for New Goal Tag'>Suggest a new goal tag</a>."></i>				
                        <x-xdropdown :list="$tags" name="tag_ids[]" id="tag_ids"  class="tags" displayField="name" multiple/>
                        <small  class="text-danger error-tag_ids"></small>
                </div>
                <div class="col-12">
                        <b>Goal Description</b>
                        <p>
				        Each goal should include a description of <b>WHAT</b>  
				        <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".'> </i> you will accomplish, <b>WHY</b> 
				        <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".'> </i> it is important, and <b>HOW</b> 
				        <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".'> </i> you will achieve it. 
				        </p>                                                                                          
                        <x-textarea-modal id="what" name="what" />
                        <small class="text-danger error-what"></small>
                </div>
                <div class="col-12">
                    <b>Measures of Success</b>
                    <i class="fa fa-info-circle" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"'> </i>                        
                    <x-textarea-modal id="measure_of_success" name="measure_of_success"/>
                    <small class="text-danger error-measure_of_success"></small>
                </div>
                <div class="col-sm-6">
                    <x-input label="Start Date" type="date" name="start_date" id="start_date" />
                </div>
                <div class="col-sm-6">
                    <x-input label="End Date" type="date" name="target_date" id="target_date" />
                </div>
                <div class="col-6">
                        <b>Mandatory/Suggested</b>
                        <select class="form-control" name="is_mandatory">
                            <option value="1">Mandatory</option>
                            <option value="0">Suggested</option>
                        </select>
                </div>
                <div class="col-6">
                    <!-- <label> -->
                        <b>Audience</b><br>                        
                        <select multiple class="form-control items-to-share" id="itemsToShare" name="itemsToShare[]">
                            @foreach ($employees_list as $employee)
                                <option value="{{ $employee['id'] }}" > {{$employee["name"]}}</option>                                
                            @endforeach
                        </select>
                    <!-- </label> -->
                    <br/>
                    <small class="text-danger error-itemsToShare"></small>
                </div>
            </div>
            <br/>
            <div class="row">
                <div class="col-12 text-left">
                    <x-button type="submit" class="btn-md" id="savebtn"> Save Changes</x-button>                    
                    <x-button icon="question" href="{{ route('resource.goal-setting') }} " target="_blank" tooltip='Click here to access goal setting resources and examples (opens in new window).'>
                        Need Help
                    </x-button>
                </div>
            </div>
        </form>
      </div>

    </div>
  </div>
</div>

@push('js')    
    <script>

        $('body').popover({
            selector: '[data-toggle]',
            trigger: 'click',
        });
        
        $('.modal').popover({
            selector: '[data-toggle-select]',
            trigger: 'click',
        });

        // $('.tags').multiselect({
        //         	enableFiltering: true,
        //         	enableCaseInsensitiveFiltering: true,
		// 			// nonSelectedText: null,
        //     	});              
                
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

        $(document).on('show.bs.modal', '#addGoalToLibraryModal', function(e) {
            modal_open = true;
            $('.alert-danger').hide();
            $('.text-danger').html('');
            $('.form-control').removeClass('is-invalid');
            
            const minutes = 20;
            const SessionTime = 1000 * 60 * minutes;
            const myTimeout = setTimeout(sessionWarning, SessionTime);    
            
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
                    
        });
        $(document).on('hide.bs.modal', '#addGoalToLibraryModal', function(e) {
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
            if (isContentModified() && !confirm("If you continue you will lose any unsaved changes.")) {
                e.preventDefault();
            } else {
                location.reload();
            }
        });
        $("#add-goal-to-library-form").on('submit', function (e) {
            $('#savebtn').prop('disabled', true);
            e.preventDefault();
            for (var i in CKEDITOR.instances){
                CKEDITOR.instances[i].updateElement();
            };
            const form = this;
            $.ajax({
                url: $(form).attr('action'),
                type : 'POST',
                data: $(form).serialize(),
                success: function (result) {
                    if(result.success){
                        $('.alert-danger').hide();
                        window.location.reload();
                    }
                },
                error: function (error){
                    $('#savebtn').prop('disabled', false);
                    var errors = error.responseJSON.errors;
                    $('.alert-danger').show();
                    $('.modal-body').animate({scrollTop: 0},100);
                    $('.text-danger').each(function(i, obj) {
                        $('.text-danger').text('');
                    });
                    Object.entries(errors).forEach(function callback(value, index) {
                         console.log(value[0]);
                        var className = '.error-' + value[0];
                        $('#addGoalToLibraryModal input[name='+value[0]+']').addClass('is-invalid');
                        $(className).text(value[1]);
                    });
                }
            });
        });
        
        
				$('body').on('click', function (e) {
                $('[data-toggle=popover]').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    	if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    	}
                	});
				});
    </script>
@endpush


