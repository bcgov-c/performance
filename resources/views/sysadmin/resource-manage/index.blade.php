<x-side-layout title="{{ __('Resources - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Generic Resources
        </h2> 
    </x-slot>

<div class="card">
    <div class="card-body">
        
        @if(count($resources) > 0)
            @include('sysadmin.resource-manage.partials.list') 
        @else
        
            <div class="px-4">
                <form class="" action="{{ route('resource-manage.create') }}" method="GET">
                    <button class="btn btn-primary" type="submit">Add a New Value</button>
                </form>
            </div>

        <div class="text-center text-primary">
            <p>
                <strong>No resource has been setup yet.</strong>
            </p>
           
        </div>
        @endif
    </div>
</div>

</x-side-layout>