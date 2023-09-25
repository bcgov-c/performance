@extends('adminlte::page')

@section('title', $attributes['title'])

@section('content_header')
    {{ $header ?? '' }}
@stop

@section('content')
    {{ $slot }}
@stop

@section('css')
    <style>
        label{
            width: 100%;
        }
    </style>
    {{ $css ?? '' }}
@stop

@section('js')
    {{ $js ?? '' }}
@stop



<script>
@if(session()->has('sr_user'))
    var routeUrl = "{{ route('dashboard') }}";
    var pathname = window.location.pathname;
    var segments = pathname.split('/');
    var controllerName = segments[1];
    if(controllerName == 'hradmin' || controllerName == 'sysadmin') {
        window.location.href = routeUrl;
    }
@endif


@if(session()->has('view-profile-as'))
    @if(session()->has('GOALS_ALLOWED'))
        var routeUrl = "{{ route('goal.current') }}";
    @else
        var routeUrl = "{{ route('conversation.upcoming') }}";
    @endif
    var pathname = window.location.pathname;
    var segments = pathname.split('/');
    var controllerName = segments[1];
    if(controllerName == 'dashboard' || controllerName == 'my-team' || controllerName == 'resources') {
        window.location.href = routeUrl;
    }
@endif
</script>    
