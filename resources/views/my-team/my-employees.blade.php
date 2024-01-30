@extends('my-team.layout')
@section('tab-content')

 <style>            
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1A5A96;
    }      
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        float: left;
    }
</style>

<div>
    <div class="h5 p-3">{{__('My Direct Reports')}}</div>
    <div class="card">
        <div class="card-body">
            {{$myEmpTable->table()}}
        </div>
    </div>
</div>
<div>
    <div class="h5 p-3">{{__('Shared With Me')}}</div>
    <div class="card">
        <div class="card-body">
            {{$sharedEmpTable->table()}}
        </div>
    </div>
</div>
@endsection
@push('js')
    {{$myEmpTable->scripts()}}
    {{$sharedEmpTable->scripts()}}
    <script>
        // $(document).ready(function(){
        //     $('[data-toggle="popover"]').popover(); 
        // });
    
        $('body').popover({
            selector: '[data-toggle]',
            trigger: 'click',
        });
        
        $('.modal').popover({
            selector: '[data-toggle-select]',
            trigger: 'click',
        });

        $('body').on('click', function (e) {
                $('[data-toggle=popover]').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    }
                });
            });        
    </script>
@endpush


       