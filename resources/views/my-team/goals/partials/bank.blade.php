<p>
Create a goal for your employees to use in their own profile. Goals can be suggested (for example, a learning goal to help increase team skill or capacity in a relevant area) or mandatory (for example, a work goal detailing a new priority that all employees are responsible for). Employees will be notified when a new goal has been added to their Goal Bank.
</p>
@if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
<x-button id="add-goal-to-library-btn" class="my-2">
    Add Goal to Bank
</x-button>
@endif
<div class="row">
    @foreach ($suggestedGoals as $goal)
    <div class="col-12 col-lg-6 col-xl-4">
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
@push('js')
<script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
<script>
    $(document).on('click', '#add-goal-to-library-btn', function () {
        modal_open = true;
        $("#addGoalToLibraryModal").modal('show');
    });
    $(".items-to-share").multiselect({
        allSelectedText: 'All',
        selectAllText: 'All',
        includeSelectAllOption: true
    });
    $(document).ready(function(){
        CKEDITOR.replace('what', {
            toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent", "Link"] ] });
        CKEDITOR.replace('measure_of_success', {
            toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent", "Link"] ] });
    });
    $('#addGoalToLibraryModal').on('hidden.bs.modal', function (e) {
        $('#what').val('');
        $('#measure_of_success').val('');
        $("#goal_title").val('');
        $('input[name=goal_type_id]').val(1);
        
        modal_open = false;
    })
</script>
@endpush