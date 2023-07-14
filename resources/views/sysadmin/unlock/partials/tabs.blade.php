<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom 
        {{ str_contains( Route::current()->getName(), 'sysadmin.unlock.unlockconversation') ? 'border-primary' : '' }}">
        {{-- {{Route::current()->getName() == 'sysadmin.unlock.unlockconversation' ? 'border-primary' : ''}}"> --}}
        <x-button role="tab" :href="route('sysadmin.unlock.unlockconversation')" style="">
          Unlock New Conversation
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom 
        {{ str_contains( Route::current()->getName(), 'sysadmin.unlock.manageunlocked') ? 'border-primary' : '' }}">
        {{-- {{Route::current()->getName() == 'sysadmin.unlock.manageunlocked' ? 'border-primary' : ''}}"> --}}
        <x-button role="tab" :href="route('sysadmin.unlock.manageunlocked')" style="">
          Manage Existing Unlocked Conversations
        </x-button>
    </div>
</div>
