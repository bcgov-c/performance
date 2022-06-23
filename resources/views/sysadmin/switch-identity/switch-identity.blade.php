@extends('sysadmin.layout')
@section('tab-content')
<div>
    <div class="h5 p-3">{{__('Pick a user')}}</div>
    <div class="card">
        <div class="card-body">
            {{$switchIdentityTable->table()}}
        </div>
    </div>
</div>
@endsection
@push('js')
    {{$switchIdentityTable->scripts()}}
    <script>
        $(document).ready(function(){
            $('[data-toggle="popover"]').popover(); 
        });
    </script>
@endpush