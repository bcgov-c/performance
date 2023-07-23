
<div class="card p-3">
       
        <div class="form-row">
          <div class="form-group col-md-3">
            <label for="dd_level0">Organization</label>
            <select id="dd_level0" name="dd_level0" class="form-control select2">
                @if ( old('dd_level0') && session()->get('level0') )
                    <option value="{{ session()->get('level0')->id }}">{{ session()->get('level0')->name }}</option>
                @endif
            </select>
          </div>
          <div class="form-group col-md-3">
            <label for="dd_level1">Program</label>
            <select id="dd_level1" name="dd_level1" class="form-control select2">
                @if ( old('dd_level1') && session()->get('level1') )
                    <option value="{{ session()->get('level1')->id }}">{{ session()->get('level1')->name }}</option>
                @endif
            </select>
          </div>
          <div class="form-group col-md-3">
            <label for="dd_level2">Division</label>
            <select id="dd_level2" name="dd_level2" class="form-control select2">
                @if ( old('dd_level2') && session()->get('level2') )
                    <option value="{{ session()->get('level2')->id }}">{{ session()->get('level2')->name }}</option>
                @endif
            </select>
          </div>
          <div class="form-group col-md-3">
            <label for="dd_level3">Branch</label>
            <select id="dd_level3" name="dd_level3" class="form-control select2">
                @if ( old('dd_level3') && session()->get('level3') )
                    <option value="{{ session()->get('level3')->id }}">{{ session()->get('level3')->name }}</option>
                @endif
            </select>
          </div>
          <div class="form-group col-md-3">
            <label for="dd_level4">Level 4</label>
            <select id="dd_level4" name="dd_level4" class="form-control select2">
                @if ( old('dd_level4') && session()->get('level4') )
                    <option value="{{ session()->get('level4')->id }}">{{ session()->get('level4')->name }}</option>
                @endif
            </select>
          </div>

          <div class="form-group col-md-3">
                <label for="job_titles">Job Titles</label>
                <select id="job_titles" name="job_titles[]" class="form-control select2" multiple="multiple">
                    {{-- @if (old('job_titles') )
                    @foreach( old('job_titles') as $job_title)
                        <option value="{{ $job_title }}" selected>{{ $job_title }}</option>
                    @endforeach
                    @endif --}}
                    @if ( old('job_titles') && session()->get('job_titles') )
                    @foreach( session()->get('job_titles') as $job_title)
                        <option value="{{ $job_title }}" selected>{{ $job_title }}</option>
                    @endforeach
                    @endif
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="active_since">Active Since</label>
                <input type="date" class="form-control" id="active_since" name="active_since" 
                    value="{{ old('active_since') }}">
            </div>

            <div class="form-group col-md-2">
                <label for="criteria">Search Criteria</label>
                <select id="criteria" name="criteria" class="form-control">
                    @foreach( $criteriaList as $key => $value )
                        <option value="{{ $key }}"  >{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-2">
                <label for="search_text">search</label>
                <input type="text" id="search_text" name="search_text" class="form-control" 
                        value="" placeholder="Employee name">
            </div>

        </div>

        <div class="form-row">
            <div class="form-group col-md-12">
              <span class="float-right">  
               <button type="submit" class="btn btn-primary" name="btn_search" 
                    value="btn_search" formaction="{{ route('sysadmin.notifications.search') }}">Search</button>
               <button type="button" class="btn btn-secondary  " id="btn_search_reset" name="btn_reset" value="btn_reset">reset</button>
              </span>
            </div>
        </div>

</div>


@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
    .select2-selection--multiple{
        overflow: hidden !important;
        height: auto !important;
        min-height: 38px !important;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
        }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
    }

    </style>

@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $('#dd_level0').select2({
        placeholder: 'select organization',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-list/1/0'
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

    $('#dd_level1').select2({
        placeholder: 'select program',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-list/1/1' 
            , dataType: 'json'
            , delay: 250
            , data: function(params) {
                var query = {
                    'q': params.term,
                    'level0': $('#dd_level0').children("option:selected").val()
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

    $('#dd_level2').select2({
        placeholder: 'select division',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-list/1/2' 
            , dataType: 'json'
            , delay: 250
            , data: function(params) {
                var query = {
                    'q': params.term,
                    'level0': $('#dd_level0').children("option:selected").val(),
                    'level1': $('#dd_level1').children("option:selected").val()
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

    $('#dd_level3').select2({
        placeholder: 'select branch',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-list/1/3' 
            , dataType: 'json'
            , delay: 250
            , data: function(params) {
                var query = {
                    'q': params.term,
                    'level0': $('#dd_level0').children("option:selected").val(),
                    'level1': $('#dd_level1').children("option:selected").val(),
                    'level2': $('#dd_level2').children("option:selected").val()
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

    $('#dd_level4').select2({
        placeholder: 'select level 4',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-list/1/4' 
            , dataType: 'json'
            , delay: 250
            , data: function(params) {
                var query = {
                    'q': params.term,
                    'level0': $('#dd_level0').children("option:selected").val(),
                    'level1': $('#dd_level1').children("option:selected").val(),
                    'level2': $('#dd_level2').children("option:selected").val(),
                    'level3': $('#dd_level3').children("option:selected").val()
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
    
    $('#dd_level0').on('select2:select', function (e) {
        // Do something
        $('#dd_level1').val(null).trigger('change');
        $('#dd_level2').val(null).trigger('change');
        $('#dd_level3').val(null).trigger('change');
        $('#dd_level4').val(null).trigger('change');
    });

    $('#dd_level1').on('select2:select', function (e) {
        // Do something
        $('#dd_level2').val(null).trigger('change');
        $('#dd_level3').val(null).trigger('change');
        $('#dd_level4').val(null).trigger('change');
    });

    $('#dd_level2').on('select2:select', function (e) {
        // Do something
        $('#dd_level3').val(null).trigger('change');
        $('#dd_level4').val(null).trigger('change');
    });

    $('#dd_level3').on('select2:select', function (e) {
        // Do something
        $('#dd_level4').val(null).trigger('change');
    });

    $('#job_titles').select2({
        placeholder: 'select job title',
        allowClear: true,
        ajax: {
            url: '/sysadmin/notifications/job-titles' 
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

    $('#btn_search_reset').click(function() {
        $('#dd_level0').val(null).trigger('change');
        $('#dd_level1').val(null).trigger('change');
        $('#dd_level2').val(null).trigger('change');
        $('#dd_level3').val(null).trigger('change');
        $('#dd_level4').val(null).trigger('change');
        $('#job_titles').val(null).trigger('change');
        $('#active_since').val(null);
        $('#search_text').val(null);
    });



    </script>

@endpush