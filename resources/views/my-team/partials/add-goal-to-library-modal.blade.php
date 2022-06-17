
<div class="modal fade" id="addGoalToLibraryModal" tabindex="-1" aria-labelledby="addGoalToLibraryLabel" aria-hidden="true">
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
            @csrf
            <div class="row">
                <div class="col-6">
                    <x-tooltip-dropdown-outside name="goal_type_id" :options="$goaltypes" label="Goal Type" popoverstr="{{$type_desc_str}}" tooltipField="description" displayField="name" />
                </div>
                <div class="col-6">
                    <x-input-modal label="Goal Title" name="title"  tooltip='A short title (1-3 words) used to reference the goal throughout the Performance platform.' />
                </div>                
                <div class="col-sm-6">
                        <x-xdropdown :list="$tags" label="Tags" name="tag_ids[]"  class="tags" tooltipField="description" displayField="name" multiple/>
                        <small  class="text-danger error-tag_ids"></small>
                </div>
                <div class="col-12">
                    <label style="font-weight: normal;">
                        <b>Goal Description</b>
                        <p class="py-2">Each goal should include a description of <b>WHAT</b><x-tooltip-modal text='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative MyPerformance sessions to ministry audiences".' /> you will accomplish, <b>WHY</b><x-tooltip-modal text='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".' /> it is important, and <b>HOW</b><x-tooltip-modal text='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the need of their employees in advance of each phase of the performance management cycle".'/> you will achieve it.</p>
                        <textarea id="what" label="Goal Description" name="what" ></textarea>
                        <small class="text-danger error-what"></small>
                    </label>
                </div>
                <div class="col-12">
                    <x-textarea-modal id="measure_of_success" label="Measures of Success" name="measure_of_success" tooltip='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"'  />
                    <small class="text-danger error-measure_of_success"></small>
                </div>
                <div class="col-sm-6">
                    <x-input label="Start Date" type="date" name="start_date" id="start_date" />
                </div>
                <div class="col-sm-6">
                    <x-input label="End Date" type="date" name="target_date" id="target_date" />
                </div>
                <div class="col-6">
                    <label>
                        Mandatory/Suggested
                        <select class="form-control" name="is_mandatory">
                            <option value="1">Mandatory</option>
                            <option value="0">Suggested</option>
                        </select>
                    </label>
                </div>
                <div class="col-6">
                    <label>
                        Audience <br>
                        <select multiple class="form-control items-to-share" name="itemsToShare[]">
                            @foreach ($employees_list as $employee)
                                <option value="{{ $employee["id"] }}" selected> {{$employee["name"]}}</option>
                            @endforeach
                        </select>
                    </label>
                    <small class="text-danger error-share_with"></small>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-right">
                <button type="submit" class="btn btn-primary mt-3">Save</button>
                <button type="button" class="btn btn-secondary mt-3" data-dismiss="modal">Cancel</button>
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
            trigger: 'hover',
        });
        
        $('.modal').popover({
            selector: '[data-toggle-select]',
            trigger: 'click',
        });
        
        $(document).on('show.bs.modal', '#addGoalModal', function(e) {
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
        $(document).on('hide.bs.modal', '#addGoalModal', function(e) {
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
            if (isContentModified() && !confirm("If you continue you will lose any unsaved information.")) {
                e.preventDefault();
            }
        });
        $("#add-goal-to-library-form").on('submit', function (e) {
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
                        window.location.reload();
                    }
                },
                error: function (error){
                    var errors = error.responseJSON.errors;
                    $('.text-danger').each(function(i, obj) {
                        $('.text-danger').text('');
                    });
                    Object.entries(errors).forEach(function callback(value, index) {
                        var className = '.error-' + value[0];
                        $(className).text(value[1]);
                    });
                }
            });
        });
    </script>
@endpush


