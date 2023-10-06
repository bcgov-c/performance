<div class="card p-3" id="filter2">
    <div class="form-row">
        <div class="form-group col-md-2" id="elvlgroup0">
            <label for="edd_level0">Organization</label>
            <select id="edd_level0" name="edd_level0" class="form-control select2">
                @if ( old('edd_level0') && session()->get('elevel0') )
                    <option value="{{ session()->get('elevel0')->id }}">{{ session()->get('elevel0')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="elvlgroup1">
            <label for="edd_level1">Level 1</label>
            <select id="edd_level1" name="edd_level1" class="form-control select2">
                @if ( old('edd_level1') && session()->get('elevel1') )
                    <option value="{{ session()->get('elevel1')->id }}">{{ session()->get('elevel1')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="elvlgroup2">
            <label for="edd_level2">Level 2</label>
            <select id="edd_level2" name="edd_level2" class="form-control select2">
                @if ( old('edd_level2') && session()->get('elevel2') )
                    <option value="{{ session()->get('elevel2')->id }}">{{ session()->get('elevel2')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="elvlgroup3">
            <label for="edd_level3">Level 3</label>
            <select id="edd_level3" name="edd_level3" class="form-control select2">
                @if ( old('edd_level3') && session()->get('elevel3') )
                    <option value="{{ session()->get('elevel3')->id }}">{{ session()->get('elevel3')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="elvlgroup4">
            <label for="edd_level4">Level 4</label>
            <select id="edd_level4" name="edd_level4" class="form-control select2">
                @if ( old('edd_level4') && session()->get('elevel4') )
                    <option value="{{ session()->get('elevel4')->id }}">{{ session()->get('elevel4')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="eblank5th">
        </div>
        <div class="form-group col-md-2" id="ecriteria_group">
            <label for="ecriteria">Search Criteria</label>
            <select id="ecriteria" name="ecriteria" class="form-control">
                @foreach( $ecriteriaList as $key => $value )
                    <option value="{{ $key }}"  >{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2" id="esearch_text_group">
            <label for="esearch_text">Search Text</label>
            <input type="text" id="esearch_text" name="esearch_text" class="form-control" value="" placeholder="Search Text">
        </div>
        <div class="form-group col-md-2 p-3 float-left float-bottom" style="text-align:left; vertical-align:bottom;">
            <div class="form-group row"> </div>
            <div class="form-group row">
                <span class="float-left float-bottom align-self-end" style="float: left; vertical-align: bottom;">  
                    <button type="button" class="align-self-end btn btn-primary" id="ebtn_search" name="ebtn_search" aria-label="Filter Button">Generate Org Tree</button>
                    <button type="button" class="align-self-end btn btn-secondary" id="ebtn_search_reset" name="ebtn_reset" value="ebtn_reset" aria-label="Reset Button">Reset</button>
                </span>
            </div>
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

        $(document).ready(function() {

            $('#edd_level0').select2({
                placeholder: 'Select Organization',
                allowClear: true,
                ajax: {
                    url: '{{ "/".request()->segment(1)."/org-list/2/0" }}'
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

            $('#edd_level1').select2({
                placeholder: 'Select Level 1',
                allowClear: true,
                ajax: {
                    url: '{{ "/".request()->segment(1)."/org-list/2/1" }}'
                    , dataType: 'json'
                    , delay: 250
                    , data: function(params) {
                        var query = {
                            'q': params.term,
                            'elevel0': $('#edd_level0').children("option:selected").val()
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

            $('#edd_level2').select2({
                placeholder: 'Select Level 2',
                allowClear: true,
                ajax: {
                    url: '{{ "/".request()->segment(1)."/org-list/2/2" }}'
                    , dataType: 'json'
                    , delay: 250
                    , data: function(params) {
                        var query = {
                            'q': params.term,
                            'elevel0': $('#edd_level0').children("option:selected").val(),
                            'elevel1': $('#edd_level1').children("option:selected").val()
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

            $('#edd_level3').select2({
                placeholder: 'Select Level 3',
                allowClear: true,
                ajax: {
                    url: '{{ "/".request()->segment(1)."/org-list/2/3" }}'
                    , dataType: 'json'
                    , delay: 250
                    , data: function(params) {
                        var query = {
                            'q': params.term,
                            'elevel0': $('#edd_level0').children("option:selected").val(),
                            'elevel1': $('#edd_level1').children("option:selected").val(),
                            'elevel2': $('#edd_level2').children("option:selected").val()
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

            $('#edd_level4').select2({
                placeholder: 'Select Level 4',
                allowClear: true,
                ajax: {
                    url: '{{ "/".request()->segment(1)."/org-list/2/4" }}'
                    , dataType: 'json'
                    , delay: 250
                    , data: function(params) {
                        var query = {
                            'q': params.term,
                            'elevel0': $('#edd_level0').children("option:selected").val(),
                            'elevel1': $('#edd_level1').children("option:selected").val(),
                            'elevel2': $('#edd_level2').children("option:selected").val(),
                            'elevel3': $('#edd_level3').children("option:selected").val()
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
            
            $('#edd_level0').on('select2:select', function (e) {
                e.preventDefault();
                $('#edd_level1').val(null).trigger('change');
                $('#edd_level2').val(null).trigger('change');
                $('#edd_level3').val(null).trigger('change');
                $('#edd_level4').val(null).trigger('change');
            });

            $('#edd_level1').on('select2:select', function (e) {
                e.preventDefault();
                $('#edd_level2').val(null).trigger('change');
                $('#edd_level3').val(null).trigger('change');
                $('#edd_level4').val(null).trigger('change');
            });

            $('#edd_level2').on('select2:select', function (e) {
                e.preventDefault();
                $('#edd_level3').val(null).trigger('change');
                $('#edd_level4').val(null).trigger('change');
            });

            $('#edd_level3').on('select2:select', function (e) {
                e.preventDefault();
                $('#edd_level4').val(null).trigger('change');
            });

            $('#ebtn_search_reset').click(function() {
                $('#edd_level0').val(null).trigger('change');
                $('#edd_level1').val(null).trigger('change');
                $('#edd_level2').val(null).trigger('change');
                $('#edd_level3').val(null).trigger('change');
                $('#edd_level4').val(null).trigger('change');
            });

        });

    </script>

@endpush