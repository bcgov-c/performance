<x-side-layout title="{{ __('Notifications - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Generic Templates
        </h2> 
        @include('sysadmin.notifications.partials.tabs')
    </x-slot>

<div class="card">
    <div class="card-body">

        <div class="form-group row">
            <label for="template" class="col-sm-2 col-form-label">Template:</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" id="template" name="template" value="{{ $generic_template->template }}" readonly>
            </div>
        </div>

        
        <div class="form-group row">
            <label for="description" class="col-sm-2 col-form-label">Description:</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="description" name="description" 
                   value="{{ $generic_template->description }}" readonly> 
            </div>
        </div>

        <div class="form-group row">
            <label for="instructional_text" class="col-sm-2 col-form-label">Instructional Text:</label>
            <div class="col-sm-9">
                <textarea type="text" class="form-control" id="instructional_text" name="instructional_text" 
                readonly>{{ $generic_template->instructional_text }}</textarea>
            </div>
        </div>

        <div class="form-group row ">
            <label for="sender" class="col-sm-2 col-form-label">Sender:</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" id="sender" name="sender" 
                value="{{ $generic_template->sender == '1' ? 'User' : 'System' }}" readonly>
            </div>
          
            <label for="email" class="col-sm-1 col-form-label text-right">Email:</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="email" name="email"
                    value="{{ $generic_template->email }}" readonly>
            </div>
        </div>

        <div class="form-group row ">
            <label for="subject" class="col-sm-2 col-form-label">Subject:</label>
            <div class="col-sm-9">
                <textarea rows="2" type="text" class="form-control" id="subject" name="subject" 
                    readonly>{{ $generic_template->subject }}</textarea>
          </div>
        </div>

        <div class="form-group row">
            <label for="body" class="col-sm-2 col-form-label">Body:</label>
            <div class="col-sm-9">
                <textarea rows="5" type="text" class="form-control @error('body') is-invalid @enderror" id="body" name="body"
                    readonly>{{ $generic_template->body }}</textarea>
            </div>
          </div>

    {{--  Bind Variables --}}
    <div class="my-4">
    <h5>Template Varaiables</h5> 
    <table class="table" id="binds_table">
        <thead>
        <tr>
            <th class="col-2">Value</th>
            <th class="col-8">Description</th>
            <th class="col-2"></th>
        </tr>
        </thead>
        <tbody>
        @foreach (old('binds', $generic_template->binds->count() ? $generic_template->binds : ['']) as $index => $oldBind)
            <tr id="bind{{ $index }}"> 
                <td class="col-2">
                    <input  name="binds[]" class="form-control" value="{{ $generic_template->binds[$index]->bind }}" readonly/>
                </td>
                <td class="col-8">
                    <input  name="descriptions[]" class="form-control" 
                    value="{{  $generic_template->binds[$index]->description }}"" readonly/>
                </td>
                <td class="col-2">
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    </div>
</div>

{{--  Audit information --}}
<div class="container mx-3 my-3">
    <div class="row no-gutters my-3">
        <div class="border-bottom col-8">
            <h5>Audit Information</h5>
        </div>
    </div>
    <div class="row no-gutters">
        <div class="col-3">
            <p>Created by: 
                {{ $generic_template->created_by ? $generic_template->created_by->name : '' }} </p>
        </div>
        <div class="col-3">
            <p>Created at: 
                {{ date_timezone_set($generic_template->created_at, timezone_open('America/Vancouver')) }}
                   </p>
        </div>
      </div>

    <div class="row">
      <div class="col-3">
        <p>Modified by: 
            {{ isset($generic_template->modified_by) ? $generic_template->modified_by->name : ''}} </p>
      </div>
      <div class="col-3">
        <p>Modified at: 
            {{ isset($generic_template->updated_at) ? date_timezone_set($generic_template->updated_at, timezone_open('America/Vancouver')) 
               : '' }} </p>
      </div>
    </div>

  </div>


<div class="form-row m-3">
    <a href="{{ route('generic-template.index') }}"> 
        <button type="button" class="btn btn-primary float-right ">back</button>
        </a>
    </div>
</div>

@push('js')
              <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
              <script>

                $(document).ready(function(){
                    CKEDITOR.replace('body', {
                         readOnly: true,
                         toolbar: "Custom",
                         toolbar_Custom: [
                            ["Bold", "Italic", "Underline"],
                            ["NumberedList", "BulletedList"],
                            ["Outdent", "Indent"],
                        ],
                    });

                });
              </script>
          @endpush

</x-side-layout>