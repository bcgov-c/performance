<x-side-layout title="{{ __('Resource Manage - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Conversation Template Detail
        </h2> 
    </x-slot>

<div class="card">
    <div class="card-body">

        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Topic:</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" id="name" name="name" value="{{ $resource->name }}" readonly>
            </div>
        </div>

        <div class="form-group row">
            <label for="when_to_use" class="col-sm-2 col-form-label">When to use:</label>
            <div class="col-sm-4">
                <textarea class="form-control" id="when_to_use" name="when_to_use">{{ $resource->when_to_use }}</textarea>
            </div>
        </div>

        <div class="form-group row">
        <label for="question_html" class="col-sm-2 col-form-label">Suggested discussion questions:</label>
            <div class="col-sm-9">
                <textarea class="form-control" id="question_html" name="question_html">{{ $resource->question_html }}</textarea>
            </div>
        </div>
        <div class="form-group row">
        <label for="preparing_for_conversation" class="col-sm-2 col-form-label">Preparing for the conversation:</label>
            <div class="col-sm-9">
                <textarea class="form-control" id="preparing_for_conversation" name="preparing_for_conversation">{{ $resource->preparing_for_conversation }}</textarea>
            </div>
        </div>
</div>
        


<div class="form-row m-3">
    <a href="{{ route('sysadmin.conversation-template') }}"> 
        <button type="button" class="btn btn-primary float-right ">back</button>
        </a>
    </div>
</div>

@push('js')
              <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
              <script>

                $(document).ready(function(){
                    CKEDITOR.replace('when_to_use', {
                         readOnly: true,
                         toolbar: "Custom",
                         toolbar_Custom: [
                            ["Bold", "Italic", "Underline"],
                            ["NumberedList", "BulletedList"],
                            ["Outdent", "Indent"],
                            ["Link"],
                        ],
                    });

                    CKEDITOR.replace('question_html', {
                         readOnly: true,
                         toolbar: "Custom",
                         toolbar_Custom: [
                            ["Bold", "Italic", "Underline"],
                            ["NumberedList", "BulletedList"],
                            ["Outdent", "Indent"],
                            ["Link"],
                        ],
                    });

                    CKEDITOR.replace('preparing_for_conversation', {
                         readOnly: true,
                         toolbar: "Custom",
                         toolbar_Custom: [
                            ["Bold", "Italic", "Underline"],
                            ["NumberedList", "BulletedList"],
                            ["Outdent", "Indent"],
                            ["Link"],
                        ],
                    });

                });
              </script>
          @endpush

</x-side-layout>