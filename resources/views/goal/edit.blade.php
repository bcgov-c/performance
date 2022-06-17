<x-side-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit: {{ $goal-> title}}
        </h2>
        <small><a href="{{ route('goal.index') }}">Back to list</a></small>
    </x-slot>

    <div class="container-fluid">
        <form action="{{ route ('goal.update', $goal->id)}}" method="POST" onsubmit="confirm('Are you sure you want to update Goal ?')">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-12">
                    <x-tooltip-dropdown-outside name="goal_type_id" :options="$goaltypes" label="Goal Type" popoverstr="{{$type_desc_str}}" tooltipField="description" displayField="name" />                
                    <x-input-modal label="Goal Title" name="title" tooltip='A short title (1-3 words) used to reference the goal throughout the Performance Development Platform.' :value="$goal->title"/>
                </div>                                                   
                <div class="col-12">
                    <x-xdropdown :list="$tags" label="Tags" name="tag_ids[]" :selected="array_column($goal->tags->toArray(), 'id')" class="tags" multiple/>
                </div>
                <div class="col-12">
                   <b>Goal Description</b>                  
                   <p class="py-2">Each goal should include a description of <b>WHAT</b><x-tooltip-modal text='A concise opening statement of what you plan to achieve. For example, "My goal is to deliver informative Performance Development sessions to ministry audiences".' /> you will accomplish, <b>WHY</b><x-tooltip-modal text='Why this goal is important to you and the organization (value of achievement). For example, "This will improve the consistency and quality of the employee experience across the BCPS".' /> it is important, and <b>HOW</b><x-tooltip-modal text='A few high level steps to achieve your goal. For example, "I will do this by working closely with ministry colleagues to develop presentations that respond to the needs of their employees in each aspect of the Performance Development process".'/> you will achieve it.</p>                        
                   <x-textarea-modal id="what" name="what" :value="$goal->what" />
                   </div>
                <div class = "col-12">
                    <x-textarea-modal id="measure_of_success" label="Measures of Success" name="measure_of_success" class="content" tooltip='A qualitative or quantitative measure of success for your goal. For example, "Deliver a minimum of 2 sessions per month that reach at least 100 people"' :value="$goal->measure_of_success" />
                </div>                                        
                <div class="col-sm-6">
                    <x-input label="Start Date" type="date" name="start_date" id="start_date" :value="$goal->start_date ? $goal->start_date->format('Y-m-d') : ''" />
                </div>
                <div class="col-sm-6">
                    <x-input label="End Date" type="date" name="target_date" id="target_date" :value="$goal->target_date ? $goal->target_date->format('Y-m-d') : ''" />
                </div>
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
        $(document).ready(() => {
            $('.tags').multiselect({
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true
            });
        });
    </script>
    <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            CKEDITOR.replace('what', {
                toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent"] ],disableNativeSpellChecker: false  });
            CKEDITOR.replace('measure_of_success', {
                toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent"] ],disableNativeSpellChecker: false  });
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
