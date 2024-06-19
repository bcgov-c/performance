<x-side-layout title="{{ __('Notifications - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Generic Templates
        </h2> 
        @include('hradmin.notifications.partials.tabs')
    </x-slot>

<div class="card">
    <div class="card-body">
        
        @if(count($generic_templates) > 0)
            @include('hradmin.notifications.generic-template.partials.list') 
        @else
        
            <div class="px-4">
                <form class="" action="{{ route('generic-template.create') }}" method="GET">
                    <button class="btn btn-primary" type="submit">Add a New Value</button>
                </form>
            </div>

        <div class="text-center text-primary">
            <p>
                <strong>No generic template has been setup yet.</strong>
            </p>
           
        </div>
        @endif
    </div>
</div>

</x-side-layout>