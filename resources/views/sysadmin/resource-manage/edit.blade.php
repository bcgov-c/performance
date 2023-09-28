<x-side-layout title="{{ __('Resource Manage - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Edit Resource Detail
        </h2> 
    </x-slot>

<div class="card">
    <div class="card-body">
        <form id="resourceform" name="resourceform" action="{{ route('resource-manage.store', $resource->content_id ) }}" method="post">
            <div class="form-group row">
                <label for="question" class="col-sm-2 col-form-label">Category:</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="category" name="category" value="{{ $resource->category }}" >
                </div>
            </div>

            <div class="form-group row">
                <label for="question" class="col-sm-2 col-form-label">Subject:</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="question" name="question" value="{{ $resource->question }}" >
                </div>
            </div>

            <div class="form-group row">
                <label for="question" class="col-sm-2 col-form-label">Body:</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="answer" name="answer">{{ $resource->answer }}</textarea>
                </div>
            </div>
        </form>
    </div>
</div>        


<div class="form-row m-3">
    <a href="{{ route('sysadmin.resource-manage') }}"> 
        <button type="button" class="btn btn-warning float-right ">back</button>
        </a>
    &nbsp;    
        <button id="formsub" type="button" class="btn btn-primary">Save</button>    
    </div>
</div>

@push('js')
              <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
              <script>

                $(document).ready(function(){
                    CKEDITOR.replace('answer', {
                         readOnly: false,
                         toolbar: "Custom",
                         toolbar_Custom: [
                            ["Bold", "Italic", "Underline"],
                            ["NumberedList", "BulletedList"],
                            ["Outdent", "Indent"],
                        ],
                    });

                });

                $('#formsub').click(function(){
                    $('#resourceform').submit();
                });
              </script>
          @endpush

</x-side-layout>