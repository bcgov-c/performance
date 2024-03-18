<x-side-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create new goal
        </h2>
        <a role="button" class="btn btn-primary btn-md" href="{{ route('goal.index') }}">
                        <i class="fa fa-undo"></i>&nbsp;        Back to list
        </a>
        <small><a href="{{ route('goal.index') }}">Back to list</a></small>
    </x-slot>

    <div class="container-fluid">
        <form action="{{ route ('goal.store')}}" method="POST">
            @csrf
            <div class="row">
                <div class="col-12">
                    <x-dropdown :list="$goaltypes" label="Goal Type" name="goal_type_id"/>
                    <x-input label="Goal Title" name="title" value="{{ old('title') }}"/>
                    <!-- <x-textarea label="What" name="what" :value="old('what')" /> -->
                    <label for='what'>Description</label>
                    <textarea id="what" class="form-control" name="what" :value="$goal->what">{!!$goal->what!!}</textarea>
                    
                    <!-- <x-textarea label="Measures of Success" name="measure_of_success" :value="old('measure_of_success')" /> -->
                    <label for='measure_of_success'>Measure of Success</label>
                    <textarea id="measure_of_success" class="form-control" name="measure_of_success" :value="$goal->measure_of_success">{!!$goal->measure_of_success!!}</textarea>
                </div>
                <div class="col-sm-6">
                    <x-input label="Start Date" type="date" name="start_date" :value="old('start_date')" />
                </div>
                <div class="col-sm-6">
                    <x-input label="End Date" type="date" name="target_date" :value="old('target_date')" />
                </div>
                <div class="col-6">
                <a href="https://www2.gov.bc.ca/gov/content/careers-myhr/all-employees/career-development/myperformance/myperformance-guides" target="_blank">Related Document Guide</a>
                </div>
                <div class="col-6 text-right pb-5">
                    <x-button type="submit" class="btn-md"> Save </x-button>
                </div>
            </div>
        </form>
    </div>
</x-side-layout>

<script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        CKEDITOR.replace('what', {
            toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent"] ],disableNativeSpellChecker: false });
        CKEDITOR.replace('measure_of_success', {
            toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent"] ],disableNativeSpellChecker: false });
    });
</script>
