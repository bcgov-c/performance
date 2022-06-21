<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
  <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'hradmin.employeeshares.addnew' ? 'border-primary' : ''}}">
    <x-button role="tab" :href="route('hradmin.employeeshares.addnew')" style="">
          Share an Employee
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'hradmin.employeeshares.manageindex' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('hradmin.employeeshares.manageindex')" style="">
          Manage Existing Employee Shares
        </x-button>
    </div>
</div>
