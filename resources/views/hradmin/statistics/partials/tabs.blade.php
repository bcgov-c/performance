<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'hradmin.statistics.goalsummary' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('hradmin.statistics.goalsummary')" style="">
          Goals Summary
        </x-button>
    </div>
    <!-----
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'hradmin.statistics.conversationstatus' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('hradmin.statistics.conversationstatus')" style="">
          Conversations Status
        </x-button>
    </div>
    ------>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'hradmin.statistics.conversationsummary' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('hradmin.statistics.conversationsummary')" style="">
          Conversations Summary
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'hradmin.statistics.sharedsummary' ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('hradmin.statistics.sharedsummary')" style="">
        Shared Employees Summary
      </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'hradmin.statistics.excusedsummary' ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('hradmin.statistics.excusedsummary')" style="">
        Excused Employee Summary
      </x-button>
    </div>

</div>
