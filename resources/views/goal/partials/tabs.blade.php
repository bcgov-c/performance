<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'goal.current' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('goal.current')" style="">
          @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
              My Current Goals
          @else
              {{ $user->name }}'s Current Goals
          @endif
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'goal.past' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('goal.past')" style="">
          @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
              My Past Goals
          @else
              {{ $user->name }}'s Past Goals
          @endif
        </x-button>
    </div>
    @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
    <div class="px-4 py-1 border-bottom {{Route::current()->getName() == 'goal.my-supervisor' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('goal.my-supervisor')" style="">
            My Supervisor's Goals
        </x-button>
    </div>
    <div class="px-4 py-1 border-bottom {{Route::current()->getName() == 'goal.library' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('goal.library')" style="">
            Goal Bank
        </x-button>
    </div>
    @endif
</div>
