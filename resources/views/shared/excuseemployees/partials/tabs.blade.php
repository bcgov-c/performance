<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.excuseemployees' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route(request()->segment(1).'.excuseemployees')" style="">
          Managed Excused Status
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.excuseemployees.managehistory' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route(request()->segment(1).'.excuseemployees.managehistory')" style="">
          View Excused History
        </x-button>
    </div>
</div>
