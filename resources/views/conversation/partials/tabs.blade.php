<div class="d-flex justify-content-center justify-content-lg-start mb-2">
    <div class="px-4 py-1 border-bottom  {{Route::current()->getName() == 'conversation.templates' || Route::current()->getName() == 'my-team.conversations' ? 'border-primary' : ''}}">
        <x-button :href="request()->routeIs('my-team*') ?  route('my-team.conversations') : route('conversation.templates')" style="" id='templates'>
            Conversation Templates
        </x-button>
    </div>
    <div class="px-4 py-1 border-bottom upcoming-border  {{Route::current()->getName() == 'conversation.upcoming' || Route::current()->getName() == 'my-team.conversations.upcoming' || Route::current()->getName() == 'my-team.conversations.upcoming.filter' ? 'border-primary' : ''}}">
        <x-button :href="request()->routeIs('my-team*') ?  route('my-team.conversations.upcoming') : route('conversation.upcoming')" style="" id='upcoming'>
            Open Conversations
        </x-button>
    </div>
    <div class="px-4 py-1 border-bottom complete-border  {{Route::current()->getName() == 'conversation.past' || Route::current()->getName() == 'my-team.conversations.past' || Route::current()->getName() == 'my-team.conversations.past.filter' ? 'border-primary' : ''}}">
        <x-button :href="request()->routeIs('my-team*') ?  route('my-team.conversations.past') : route('conversation.past')" style="" id='past'>
            Completed Conversations
        </x-button>
    </div>
</div>