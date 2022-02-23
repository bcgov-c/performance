<div class="d-flex justify-content-center justify-content-lg-start mb-2">
    <div role="region" aria-label="Weather" class="px-4 py-1 border-bottom {{$tab == 'notifications' ? 'border-primary' : ''}}">
        <x-button style="-" :href="route('dashboard')">
            Notifications
            <span class="badge badge-{{$tab == 'notifications' ? 'primary' : 'secondary' }}">{{$notifications_unread->count()}}</span>
        </x-button>
    </div>
</div>
