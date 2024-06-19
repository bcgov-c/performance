<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.accesspermissions' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('sysadmin.accesspermissions')" style="">
          Create New Access
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.accesspermissions.manageindex' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('sysadmin.accesspermissions.manageindex')" style="">
          Manage Existing Access
        </x-button>
    </div>
</div>
