<div class="d-flex justify-content-center justify-content-lg-start mb-1">
    <div class="px-4 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.employeelists' ? 'border-primary' : ''}}">
        <x-button :href="route(request()->segment(1).'.employeelists')" style="">
            Current Employees
        </x-button>
    </div>
    <div class="px-4 mr-2 border-bottom {{Route::current()->getName() == request()->segment(1).'.employeelists.pastlist' ? 'border-primary' : ''}}">
        <x-button :href="route(request()->segment(1).'.employeelists.pastlist')" style="">
            Past Employees
        </x-button>
    </div>
</div>

