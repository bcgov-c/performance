<div class="d-flex justify-content-center justify-content-lg-start mb-2">
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'resource.user-guide' ? 'border-primary' : ''}}">
        <x-button :href="route('resource.user-guide')" style="">
            User Guide
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'resource.video-tutorials' ? 'border-primary' : ''}}">
        <x-button :href="route('resource.video-tutorials')" style="">
            Video Tutorials
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'resource.goal-setting' ? 'border-primary' : ''}}">
        <x-button :href="route('resource.goal-setting')" style="">
            Goal Setting
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'resource.conversations' ? 'border-primary' : ''}}">
        <x-button :href="route('resource.conversations')" style="">
            Performance Conversations
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'resource.faq' ? 'border-primary' : ''}}">
        <x-button :href="route('resource.faq')" style="">
            FAQ
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'resource.hr-admin' ? 'border-primary' : ''}}">
        <x-button :href="route('resource.hr-admin')" style="">
            HR Admin Access
        </x-button>
    </div>
    <div class="px-4 py-1 mr-2 border-bottom {{Route::current()->getName() == 'resource.contact' ? 'border-primary' : ''}}">
        <x-button :href="route('resource.contact')" style="">
            Contact Us
        </x-button>
    </div>
</div>
