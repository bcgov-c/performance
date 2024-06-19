<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'my-team.statistics.goalsummary' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('my-team.statistics.goalsummary')" style="">
          Goals Summary
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'my-team.statistics.conversationsummary' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('my-team.statistics.conversationsummary')" style="">
          Conversations Summary
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'my-team.statistics.sharedsummary' ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('my-team.statistics.sharedsummary')" style="">
        Shared Employees Summary
      </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'my-team.statistics.excusedsummary' ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('my-team.statistics.excusedsummary')" style="">
        Excused Employee Summary
      </x-button>
    </div>

</div>
