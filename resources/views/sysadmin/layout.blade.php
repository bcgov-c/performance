<x-side-layout title="{{ $tab_title ?? 'Performance Development Platform'}}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>{{ $title ?? 'System Administration: Employee List'}}</h3>
        </div>
    </div>
    @yield('page-content')
</x-side-layout>



