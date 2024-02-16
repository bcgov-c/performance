<x-side-layout title="{{ __('Resources - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Conversation Templates
        </h2> 
    </x-slot>

<div class="card">
    <div class="card-body">
        
        @if(count($resources) > 0)
            @include('sysadmin.conversation-template.partials.list') 
        @else
        

        <div class="text-center text-primary">
            <p>
                <strong>No conversation has been setup yet.</strong>
            </p>
           
        </div>
        @endif
    </div>
</div>

</x-side-layout>