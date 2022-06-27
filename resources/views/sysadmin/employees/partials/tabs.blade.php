<div class="d-flex justify-content-center justify-content-lg-start mb-1">
    <div class="px-4 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.employeelists.currentlist' ? 'border-primary' : ''}}">
        <x-button :href="route('sysadmin.employeelists.currentlist')" style="">
            Current Employees
        </x-button>
    </div>
    <div class="px-4 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.employeelists.pastlist' ? 'border-primary' : ''}}">
        <x-button :href="route('sysadmin.employeelists.pastlist')" style="">
            Past Employees
        </x-button>
    </div>
</div>

