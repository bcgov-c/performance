<x-side-layout title="{{ __('Resources - Performance Development Platform') }}">
    <x-slot name="header">
        <h3>Resources</h3>
        <div class="col-md-8"> @include('resource.partials.tabs')</div>
    </x-slot>
    @yield('tab-content')
    
</x-side-layout>
