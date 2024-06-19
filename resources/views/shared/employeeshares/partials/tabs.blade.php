<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.employeeshares' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route(request()->segment(1).'.employeeshares')" style="">
            Create New Shares
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.employeeshares.manageindex' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route(request()->segment(1).'.employeeshares.manageindex')" style="">
            View or Delete Existing Shares
        </x-button>
    </div>
</div>
