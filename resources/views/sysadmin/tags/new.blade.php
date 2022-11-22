<x-side-layout title="{{ __('New Tag - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
           New Tag
        </h2>
    </x-slot>

    <div class="container-fluid">
        <p><a href="/sysadmin/tags">Back to list</a></p>
        <form action="{{ route ('sysadmin.tag-insert')}}" method="POST" onsubmit="confirm('Are you sure you want to create Tag ?')">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-12">
                    <b>Tag Name</b>
                    <x-input-modal name="name" />                    
                    @if(session()->has('name_miss'))                           
                        <small class="text-danger">The tag name field is required</small>
                    @endif
                </div>      
                <div class="col-12">
                   <b>Tag Description</b>      
                   <x-textarea-modal id="description" name="description" />
                   @if(session()->has('description_miss'))
                        <small class="text-danger">The description field is required</small>
                    @endif
                </div>
                <div class="col-6">&nbsp;</div>
                <div class="col-12">&nbsp;</div>
   
                <div class="col-12 text-center mb-3">
                    <x-button type="submit" class="btn-lg"> Save </x-button>
                </div>
            </div>
        </form>
    </div>


</x-side-layout>


<style> 
    .alert-danger {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    .multiselect-container{
        height: 350px; 
        overflow-y: scroll;
    }
</style>    
