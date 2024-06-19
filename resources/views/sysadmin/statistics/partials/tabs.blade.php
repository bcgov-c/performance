<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.statistics.goalsummary' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('sysadmin.statistics.goalsummary')" style="">
          Goals Summary
        </x-button>
    </div>
    <!----
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.statistics.conversationstatus' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('sysadmin.statistics.conversationstatus')" style="">
          Conversations Status
        </x-button>
    </div>
    ----->
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.statistics.conversationsummary' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('sysadmin.statistics.conversationsummary')" style="">
          Conversations Summary
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.statistics.sharedsummary' ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('sysadmin.statistics.sharedsummary')" style="">
        Shared Employees Summary
      </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.statistics.excusedsummary' ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('sysadmin.statistics.excusedsummary')" style="">
        Excused Employee Summary
      </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.statistics.filereports' ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('sysadmin.statistics.filereports')" style="">
        Employee Record
      </x-button>
    </div>
</div>
