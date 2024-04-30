<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{ Route::current()->getName() == 'goal.current' ? 'border-primary' : '' }}">
        <x-button role="tab" :href="route('goal.current')" style="" tabindex="0" class="tab-button">
          @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
              My Current Goals
          @else
              {{ $user->name }}'s Current Goals
          @endif
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{ Route::current()->getName() == 'goal.past' ? 'border-primary' : '' }}">
        <x-button role="tab" :href="route('goal.past')" style="" tabindex="0" class="tab-button">
          @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
              My Past Goals
          @else
              {{ $user->name }}'s Past Goals
          @endif
        </x-button>
    </div>
    @if ((session()->get('original-auth-id') != Auth::id() && session()->get('original-auth-id') != null ))
    <div class="px-4 py-1 mr-2 border-bottom {{ Route::current()->getName() == 'goal.library' ? 'border-primary' : '' }}">
        <x-button role="tab" :href="route('goal.library')" style="" tabindex="0" class="tab-button">
              {{ $user->name }}'s Goal Bank
        </x-button>
    </div>
    @endif
    @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
    <div class="px-4 py-1 border-bottom {{ Route::current()->getName() == 'goal.share' ? 'border-primary' : '' }}">
        <x-button role="tab" :href="route('goal.share')" style="" tabindex="0" class="tab-button">
            Goals Shared With Me
        </x-button>
    </div>
    <div class="px-4 py-1 border-bottom {{ Route::current()->getName() == 'goal.library' ? 'border-primary' : '' }}">
        <x-button role="tab" :href="route('goal.library')" style="" tabindex="0" class="tab-button">
            Goal Bank
        </x-button>
    </div>
    @endif
</div>
