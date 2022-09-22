<x-side-layout title="{{ __('Account Preferences - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Account Preference 
        </h2> 
		{{-- @include('sysadmin.system-security.partials.tabs') --}}
    </x-slot>

<form action="{{ route('user-preference.store') }}" method="post">
    <?php echo csrf_field(); ?>

    <div class="card">
        <div class="px-4"></div>
        <div class="card-body">
            <h4 class=" text-primary">Notifications</h4>

            <div>
                <br>
                All of the activities below will generate a notification on your PDP home page.  Please indicate which activities should also generate an email notification for you.
            </div>

            <p class="font-weight-bold pt-4">I want to receive an email when someone:</p>
            
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="goal_comment_flag" value="Y"
                    {{ $pref->goal_comment_flag == 'Y' ? 'checked' : '' }}>
                <label class="form-check-label pl-2" for="goal_comment_flag">Makes a comment on one of my goals</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="goal_bank_flag" value="Y"
                    {{ $pref->goal_bank_flag == 'Y' ? 'checked' : '' }}>
                <label class="form-check-label pl-2" for="goal_bank_flag">Adds a new goal to my goal bank</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="share_profile_flag" value="Y"
                    {{ $pref->share_profile_flag == 'Y' ? 'checked' : '' }}>
                <label class="form-check-label pl-2" for="share_profile_flag">Shares my profile with another supervisor </label>
            </div>


            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="conversation_setup_flag" value="Y"
                    {{ $pref->conversation_setup_flag == 'Y' ? 'checked' : '' }} disabled>
                <label class="form-check-label pl-2" for="conversation_setup_flag">Wants to set up a performance conversation with me</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="conversation_signoff_flag" value="Y"
                    {{ $pref->conversation_signoff_flag == 'Y' ? 'checked' : '' }} disabled>
                <label class="form-check-label pl-2" for="conversation_signoff_flag">Signs-off on a performance conversation with me</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="conversation_disagree_flag" value="Y"
                {{ $pref->conversation_disagree_flag == 'Y' ? 'checked' : '' }} disabled>
                <label class="form-check-label pl-2" for="conversation_disagree_flag">Disagrees with the content of a performance conversation with me </label>
            </div>

            <p class="font-weight-bold pt-4">I want to receive an email:</p>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="conversation_due_month" value="Y"
                    {{ $pref->conversation_due_month == 'Y' ? 'checked' : '' }}>   
                <label class="form-check-label pl-2" for="conversation_due_month">One month before my next conversation due date </label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="conversation_due_week" value="Y"
                    {{ $pref->conversation_due_week == 'Y' ? 'checked' : '' }}>    
                <label class="form-check-label pl-2" for="conversation_due_week">One week before my next conversation due date</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="conversation_due_past" value="Y"
                    {{ $pref->conversation_due_past == 'Y' ? 'checked' : '' }} disabled>
                <label class="form-check-label pl-2" for="conversation_due_past">When my conversation is past due</label>
            </div>

@if ($isSupervisor)
            <p class="font-weight-bold pt-4">For supervisors only, I want to receive an email</p>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="team_conversation_due_month" value="Y"
                    {{ $pref->team_conversation_due_month == 'Y' ? 'checked' : '' }}>
                <label class="form-check-label pl-2" for="team_conversation_due_month">One month before my team member's next conversation due date </label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="team_conversation_due_week" value="Y"
                    {{ $pref->team_conversation_due_week == 'Y' ? 'checked' : '' }}>
                <label class="form-check-label pl-2" for="team_conversation_due_week">One week before my team member's next conversation due date</label>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="team_conversation_due_past" value="Y"
                    {{ $pref->team_conversation_due_past == 'Y' ? 'checked' : '' }} disabled>
                <label class="form-check-label pl-2" for="team_conversation_due_past">When my team member's conversation is past due</label>
            </div>
@endif

        </div>
    </div>

    <div class="col-md-3 mb-2">
	    <button class="btn btn-primary mt-2" type="submit">Save</button>
	    <button class="btn btn-secondary mt-2" type="button" onclick="location.href='/dashboard'">Cancel</button>
    </div>

</form>


<x-slot name="css">

   	<style>
    
    input[type='checkbox'] { 
        width: 1.1em; height: 1.1em;
    }

    </style>

</x-slot>


<x-slot name="js">




</x-slot>    


</x-side-layout>