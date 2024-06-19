<div class="d-flex justify-content-center justify-content-lg-start mb-2" role="tablist">
    <div class="px-4 py-1 mr-2 border-bottom {{ str_contains( Route::current()->getName(), 'generic-template' ) ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('generic-template.index')" style="">
        Generic Template
      </x-button>
    </div>

    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.notifications' ? 'border-primary' : ''}}">
        <x-button role="tab" :href="route('sysadmin.notifications')" style="">
          View Past Notification
        </x-button>
    </div>

    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'sysadmin.notifications.notify' ? 'border-primary' : ''}}">
      <x-button role="tab" :href="route('sysadmin.notifications.notify')" style="">
        Create New Notification
      </x-button>
    </div>

</div>
