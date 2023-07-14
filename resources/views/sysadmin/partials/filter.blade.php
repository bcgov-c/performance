<div class="card" style="width: 100%">
    <form action="" method="get">
        <table class="uk-table m-3" style="overflow-x: auto; width: 98%">
            <tbody>
                <tr style="text-align: left;" class="p-1 form-group">
                    <td class="p-1 form-group" style="text-align: left; width: 20%; ">
                        <label for='dd_level0'>Organization</label>
                        <select class="form-control" name="dd_level0" id="dd_level0">
                            @if ( old('dd_level0') && session()->get('level0') )
                                <option value="{{ session()->get('level0')->id }}">{{ session()->get('level0')->name }}</option>
                            @endif
                        </select>
                    </td>
                    <td class="p-1 form-group" style="text-align: left; width: 20%; ">
                        <label for='dd_level1'>Level 1</label>
                        <select class="form-control" name="dd_level1" id="dd_level1">
                            @if ( old('dd_level1') && session()->get('level1') )
                                <option value="{{ session()->get('level1')->id }}">{{ session()->get('level1')->name }}</option>
                            @endif
                        </select>
                    </td>
                    <td class="p-1 form-group" style="text-align: left; width: 20%; ">
                        <label for='dd_level2'>Level 2</label>
                        <select class="form-control" name="dd_level2" id="dd_level2">
                            @if ( old('dd_level2') && session()->get('level2') )
                                <option value="{{ session()->get('level2')->id }}">{{ session()->get('level2')->name }}</option>
                            @endif
                        </select>
                    </td>
                    <td class="p-1 form-group" style="text-align: left; width: 20%; ">
                        <label for='dd_level3'>Level 3</label>
                        <select class="form-control" name="dd_level3" id="dd_level3">
                            @if ( old('dd_level3') && session()->get('level3') )
                                <option value="{{ session()->get('level3')->id }}">{{ session()->get('level3')->name }}</option>
                            @endif
                        </select>
                    </td>
                    <td class="p-1 form-group" style="text-align: left; width: 20%; ">
                        <label for='dd_level4'>Level 4</label>
                        <select class="form-control" name="dd_level4" id="dd_level4">
                            @if ( old('dd_level4') && session()->get('level4') )
                                <option value="{{ session()->get('level4')->id }}">{{ session()->get('level4')->name }}</option>
                            @endif
                        </select>
                    </td>
                </tr>
                <tr style="text-align: left;" class="p-1 form-group">
                    <td style="text-align: left; width: 300px; " class="p-1 form-group">
                        <label for="criteria">Search Criteria</label>
                        <select id="criteria" name="criteria" class="form-control">
                            @foreach( $criteriaList as $key => $value )
                                <option value="{{ $key }}" >{{ $value }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td style="text-align: left; width: 300px; " class="p-1 form-group">
                        <label for="search_text">Search Text</label>
                        <input type="text" id="search_text" name="search_text" class="form-control" value="" placeholder="Search Text">
                    </td>
                    <td style="text-align: left; align: center; vertical-align: bottom; width: 300px; " class="p-1 form-group">
                        <span class="float-left float-bottom">  
                            {{-- <button type="submit" class="btn btn-primary" name="btn_search" value="btn_search" id="btn_search" style="width: 140px; " formaction="{{ route('sysadmin.myorg') }}">Search</button> --}}
                            <button type="submit" class="btn btn-primary" name="btn_search" value="btn_search" id="btn_search" style="width: 140px; " formaction="">Search</button>
                            <button type="reset" class="btn btn-secondary" name="btn_search_reset" value="btn_reset" id="btn_search_reset" style="width: 140px; " formaction="">Reset</button>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
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
        placeholder: 'Select Organization',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-organizations'
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
        placeholder: 'Select Level 1',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-programs' 
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
        placeholder: 'Select Level 2',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-divisions' 
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
        placeholder: 'Select Level 3',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-branches' 
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
        placeholder: 'Select Level 4',
        allowClear: true,
        ajax: {
            url: '/sysadmin/org-level4' 
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

    $('#btn_search_reset').click(function() {
        $('#dd_level0').val(null).trigger('change');
        $('#dd_level1').val(null).trigger('change');
        $('#dd_level2').val(null).trigger('change');
        $('#dd_level3').val(null).trigger('change');
        $('#dd_level4').val(null).trigger('change');
        $('#criteria').val('all').trigger('change');
        $('#search_text').val(null);
    });



    </script>

@endpush