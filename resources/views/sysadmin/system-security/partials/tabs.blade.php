<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{ str_contains( Route::current()->getName(), 'access_logs' ) ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('sysadmin.system_security.access_logs')" style="">
        Access Log
      </x-button>
    </div>

    <div class="px-4 py-1 mr-2 border-bottom {{ str_contains( Route::current()->getName(), 'access-orgs' ) ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('access-orgs.index')" style="">
        Access Organizations
      </x-button>
    </div>

</div>
