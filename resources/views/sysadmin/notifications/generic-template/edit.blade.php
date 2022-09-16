<x-side-layout title="{{ __('Notifications - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Generic Templates
        </h2> 
        @include('sysadmin.notifications.partials.tabs')
    </x-slot>

<div class="card">
    <div class="card-body">

        <form action="{{ isset($generic_template) ? route('generic-template.update', $generic_template->id ) : route('generic-template.store') }}" 
            method="post">
            @csrf
            @method('PUT')

              <div class="form-group row">
                <label for="template" class="col-sm-2 col-form-label">Template:</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="template" name="template" value="{{ $generic_template->template }}" readonly>
                </div>
              </div>


              <div class="form-group row">
                <label for="description" class="col-sm-2 col-form-label">Description:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" 
                        @error('description') 
                            value="{{ old('description') }}">
                        @else
                            value="{{ old('description') ? old('description') : $generic_template->description }}">
                        @enderror
                    
                    @error('description')
                        <span class="invalid-feedback">
                        {{  $message  }}
                        </span>
                    @enderror
                </div>
              </div>

              <div class="form-group row">
                <label for="instructional_text" class="col-sm-2 col-form-label">Instructional Text:</label>
                <div class="col-sm-9">
                    <textarea type="text" class="form-control @error('instructional_text') is-invalid @enderror" id="instructional_text" name="instructional_text" 

                        @error('instructional_text') 
                           >{{ old('instructional_text') }}</textarea>
                        @else
                           >{{ old('instructional_text') ? old('instructional_text') : $generic_template->instructional_text }}</textarea>
                        @enderror

                    @error('instructional_text')
                        <span class="invalid-feedback">
                        {{  $message  }}
                        </span>
                    @enderror
                </div>
              </div>

              <div class="form-group row ">
                <label for="sender" class="col-sm-2 col-form-label">Sender Type:</label>
                <div class="col-sm-2">
                    <select id="sender" class="form-control @error('sender') is-invalid @enderror" name="sender">
                        
                        @error('sender')
                            {{ $val_status = old('sender')  }}    
                        @else
                            {{ $val_status = old('sender') ? old('sender') : $generic_template->sender }}    
                        @enderror
                        <option value="1" {{ $val_status == '1' ? 'selected' : '' }}>{{ 'User'   }}</option>
                        <option value="2" {{ $val_status == '2' ? 'selected' : '' }}>{{ 'System' }}</option>
                    </select>
                    @error('sender')
                        <span class="invalid-feedback">
                        {{  $message  }}
                        </span>
                    @enderror
              </div>
              <label for="sender_id" class="col-sm-2 col-form-label text-right">User Name:</label>
              <div class="col-sm-5" >
                <select class="form-control select2 @error('sender_id') is-invalid @enderror" 
                 name="sender_id" id="sender_id" style="width:100%">

                  @if (old('sender_id')) 

                     @foreach ( Session::get('old_sender_ids') ?? [] as $key =>$value )
                        <option value="{{ $key }}" selected="selected">{{ $value }}</option>
                     @endforeach
{{-- 
                  @else
                        <option value="{{ $generic_template->azure_id }}" selected="selected">{{ $generic_template->email }}</option>
--}}
                  @endif

    
                </select>
                @error('sender_id')
                    <span class="invalid-feedback">
                    {{  $message  }}
                    </span>
                @enderror
          </div>
            </div>

            <div class="form-group row ">
                <label for="subject" class="col-sm-2 col-form-label">Subject:</label>
                <div class="col-sm-9">
                    <textarea rows="2" type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" 

                    @error('subject') 
                        >{{ old('subject') }}</textarea>
                    @else
                        >{{ old('subject') ? old('subject') : $generic_template->subject }}</textarea>
                    @enderror
                    
                    @error('subject')
                        <span class="invalid-feedback">
                        {{  $message  }}
                        </span>
                    @enderror
              </div>
            </div>

            <div class="form-group row">
                <label for="body" class="col-sm-2 col-form-label">Body:</label>
                <div class="col-sm-9">
                    <textarea rows="5" type="text" class="form-control @error('body') is-invalid @enderror" id="body" name="body"

                        @error('body') 
                           >{{ old('body') }}</textarea>
                        @else
                            >{{ old('body') ? old('body') : $generic_template->body }}</textarea>
                        @enderror
                    
                    @error('body')
                        <span class="invalid-feedback">
                        {{  $message  }}
                        </span>
                    @enderror
                </div>
              </div>

            {{--  Detail -- Bind Variables --}}
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
                                <input  name="binds[]" class="form-control
                                @error('bind'.$index) is-invalid @enderror"  
                                value="{{ old('binds.' . $loop->index) ?? $generic_template->binds[$index]->bind ?? ''  }}" />
                                @error( 'bind'.$index)
                                    <span class="invalid-feedback">
                                        {{  $message  }}
                                    </span>
                                @enderror
                            </td>
                            <td class="col-8">
                                <input  name="descriptions[]" class="form-control" 
                                value="{{ old('descriptions.' . $loop->index) ?? $generic_template->binds[$index]->description  ?? '' }}" />
                            </td>
                            <td class="col-2">
                                <div type="button" class="pull-right btn btn-sm btn-danger delete_this_row">Delete</div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="row mx-1">
                    <div class="col-md-12">
                        <button id="add_row" type="button" class="btn btn-default btn-sm pull-left">Add Row</button>
                        {{-- <button id='delete_row' type="button"  class="pull-right btn btn-danger">- Delete Row</button>
                        --}}
                    </div>
                </div>
            </div>
            </div>

            {{--  Save Bitton --}}
            <div class="form-row m-3">
                <div>
                <button type="submit" class="btn btn-primary">Save</button>
                </div>
                <div class="pl-2">
                    <a href="{{ route('generic-template.index') }}"> 
                    <button type="button" class="btn btn-secondary ">Cancel</button>
                    </a>
                </div>
            </div>
          </form>

        @push('css')
        <style>
        .select2-container .select2-selection--single {
        height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        }
        </style>
        @endpush
          @push('js')
              <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
              <script>

                $(document).ready(function(){
                    CKEDITOR.replace('body', {
                         toolbar: "Custom",
                         toolbar_Custom: [
                            ["Bold", "Italic", "Underline"],
                            ["NumberedList", "BulletedList"],
                            ["Outdent", "Indent"],
                            ["Link"],
                        ],
                    });

                    let row_number = {{ count(old('binds', [''])) }};

                    let str = '<tr>';   
                    str += '<td>';
                    str += '<input  name="binds[]" class="form-control" value="{{ old('binds.' . $index) ?? '' }}" />';
                    str += '</td>';         
                    str += '<td>';
                    str += '<input  name="descriptions[]" class="form-control"';
                    str += 'value="{{ '' }}" />';
                    str += '</td>';
                    str += '<td>';
                    str += '<div type="button" class="pull-right btn btn-sm btn-danger delete_this_row">Delete</div>';
                    str += '</td>';
                    str += '</tr>';

                    $("#add_row").click(function(e){
                        e.preventDefault();
                        
                        let new_row_number = row_number - 1;
                        $('#binds_table').append(str);

                    });
                });

                $(document).on("click", "div.delete_this_row" , function(e) {
                    e.preventDefault();
                        $(this).parent().parent().remove();
                });

                $('#sender_id').select2({
                    allowClear: true,
                    placeholder: "Search for a user",
                    ajax: {
                        url: '/graph-users'
                        , dataType: 'json'
                        , delay: 250
                        , data: function(params) {
                            var query = {
                                'q': params.term
                            , }
                            return query;
                        }
                        , processResults: function(data) {
                            return {
                                results: data
                                };
                        }
                        , cache: false
                    }
                });

                $('#sender').change(function() {
                    if ($('#sender').val() == '1') {
                        $('#sender_id option:selected').remove();
                    }
                });

              </script>
          @endpush

</x-side-layout>
