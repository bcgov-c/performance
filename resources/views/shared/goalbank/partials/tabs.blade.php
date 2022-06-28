<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.goalbank.createindex' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route(request()->segment(1).'.goalbank.createindex')" style="">
          Add a New Goal to the Goal Bank
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.goalbank.manageindex' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route(request()->segment(1).'.goalbank.manageindex')" style="">
          Manage Goals in Goal Bank
        </x-button>
    </div>
</div>
