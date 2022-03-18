@extends('my-team.goals.layout')
@section('tab-content')
<p>
Create a goal for your employees to use in their own profile. Goals can be suggested (for example, a learning goal to help increase team skill or capacity in a relevant area) or mandatory (for example, a work goal detailing a new priority that all employees are responsible for). Employees will be notified when a new goal has been added to their Goal Bank.
</p>
<x-button id="add-goal-to-library-btn" tooltip="Create a goal for your employees to use in their own profile." tooltipPosition="bottom" class="my-2">
    Add Goal to Bank
</x-button>
<div class="row">
    @foreach ($suggestedGoals as $goal)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        @include('goal.partials.card')
    </div>
    @endforeach
</div>
<div class="row">
    <div class="col">
        {{$suggestedGoals->links()}}
    </div>
</div>
@include('my-team.partials.add-goal-to-library-modal')
@endsection
@push('js')
<script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
<script>
    $(document).on('click', '#add-goal-to-library-btn', function () {
        $("#addGoalToLibraryModal").modal('show');
    });
    $(".items-to-share").multiselect({
        allSelectedText: 'All',
        selectAllText: 'All',
        includeSelectAllOption: true
    });
    $(document).ready(function(){
        CKEDITOR.replace('what', {
            toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent"] ] });
        CKEDITOR.replace('measure_of_success', {
            toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent"] ] });
    });
</script>
@endpush