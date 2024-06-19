<div class="d-flex justify-content-center justify-content-lg-start mb-2">
    <div role="region" aria-label="Weather" class="px-4 py-1 border-bottom {{$tab == 'notifications' ? 'border-primary' : ''}}">
        <x-button style="-" :href="route('dashboard')">
            Notifications
            <span id="count-badge" class="badge badge-{{$tab == 'notifications' ? 'primary' : 'secondary' }}"></span>
        </x-button>
    </div>
</div>
