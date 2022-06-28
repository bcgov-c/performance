<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.excuseemployees.addindex' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route(request()->segment(1).'.excuseemployees.addindex')" style="">
          Excuse New Employee(s)
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.excuseemployees.manageindex' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route(request()->segment(1).'.excuseemployees.manageindex')" style="">
          Manage Existing Excused
        </x-button>
    </div>
</div>
