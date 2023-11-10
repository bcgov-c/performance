<div class="card p-3" id="afilter">
    <div class="form-row">
        <div class="form-group col-md-2" id="alvlgroup0">
            <label for="add_level0">Organization</label>
            <select id="add_level0" name="add_level0" class="form-control select2">
                @if ( old('add_level0') && session()->get('alevel0') )
                    <option value="{{ session()->get('alevel0')->id }}">{{ session()->get('alevel0')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="alvlgroup1">
            <label for="add_level1">Level 1</label>
            <select id="add_level1" name="add_level1" class="form-control select2">
                @if ( old('add_level1') && session()->get('alevel1') )
                    <option value="{{ session()->get('alevel1')->id }}">{{ session()->get('alevel1')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="alvlgroup2">
            <label for="add_level2">Level 2</label>
            <select id="add_level2" name="add_level2" class="form-control select2">
                @if ( old('add_level2') && session()->get('alevel2') )
                    <option value="{{ session()->get('alevel2')->id }}">{{ session()->get('alevel2')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="alvlgroup3">
            <label for="add_level3">Level 3</label>
            <select id="add_level3" name="add_level3" class="form-control select2">
                @if ( old('add_level3') && session()->get('alevel3') )
                    <option value="{{ session()->get('alevel3')->id }}">{{ session()->get('alevel3')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="alvlgroup4">
            <label for="add_level4">Level 4</label>
            <select id="add_level4" name="add_level4" class="form-control select2">
                @if ( old('add_level4') && session()->get('alevel4') )
                    <option value="{{ session()->get('alevel4')->id }}">{{ session()->get('alevel4')->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group col-md-2" id="ablank5th">
        </div>
        @if ($currentView == "editone")
            <div class="form-group col-md-2" id="asuperv_group">
                <div class="mb-2">
                    <b> Supervisor Status </b> 
                    <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content='This filter includes direct reports from PeopleSoft and delegated reports in the PDP.'> </i> 
                </div>
                <select id="add_superv" name="add_superv" class="form-control select2">
                    @foreach( $supervisorList as $key => $value )
                        <option value="{{ $key }}" {{  old('add_superv') == $key ? 'selected' : '' }} >{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="form-group col-md-2" id="acriteria_group">
            <label for="acriteria">Search Criteria</label>
            <select id="acriteria" name="acriteria" class="form-control">
                @foreach( $acriteriaList as $key => $value )
                    <option value="{{ $key }}" {{  old('acriteria') == $key ? 'selected' : '' }} >{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2" id="asearch_text_group">
            <label for="asearch_text">Search Text</label>
            <input type="text" id="asearch_text" name="asearch_text" class="form-control" value="{{ old('asearch_text') }}" placeholder="Search Text">
        </div>
        <div class="form-group col-md-2 p-3 float-left float-bottom" style="text-align:left; vertical-align:bottom;">
            <div class="form-group row"> </div>
            <div class="form-group row">
                <span class="float-left float-bottom align-self-end" style="float: left; vertical-align: bottom;">  
                    <button type="button" class="align-self-end btn btn-primary" id="abtn_search" name="abtn_search">Filter</button>
                    <button type="button" class="align-self-end btn btn-secondary" id="abtn_search_reset" name="abtn_reset" value="abtn_reset">Reset</button>
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
        $('#add_level0').select2({
            placeholder: 'Select Organization',
            allowClear: true,
            ajax: {
                url: '{{ "/".request()->segment(1)."/org-list/3/0" }}'
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
        
        $('#add_level1').select2({
            placeholder: 'Select Level 1',
            allowClear: true,
            ajax: {
                url: '{{ "/".request()->segment(1)."/org-list/3/1" }}'
                , dataType: 'json'
                , delay: 250
                , data: function(params) {
                    var query = {
                        'q': params.term,
                        'alevel0': $('#add_level0').children("option:selected").val()
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

        $('#add_level2').select2({
            placeholder: 'Select Level 2',
            allowClear: true,
            ajax: {
                url: '{{ "/".request()->segment(1)."/org-list/3/2" }}'
                , dataType: 'json'
                , delay: 250
                , data: function(params) {
                    var query = {
                        'q': params.term,
                        'alevel0': $('add_level0').children("option:selected").val(),
                        'alevel1': $('add_level1').children("option:selected").val()
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

        $('#add_level3').select2({
            placeholder: 'Select Level 3',
            allowClear: true,
            ajax: {
                url: '{{ "/".request()->segment(1)."/org-list/3/3" }}'
                , dataType: 'json'
                , delay: 250
                , data: function(params) {
                    var query = {
                        'q': params.term,
                        'alevel0': $('#add_level0').children("option:selected").val(),
                        'alevel1': $('#add_level1').children("option:selected").val(),
                        'alevel2': $('#add_level2').children("option:selected").val()
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

        $('#add_level4').select2({
            placeholder: 'Select Level 4',
            allowClear: true,
            ajax: {
                url: '{{ "/".request()->segment(1)."/org-list/3/4" }}'
                , dataType: 'json'
                , delay: 250
                , data: function(params) {
                    var query = {
                        'q': params.term,
                        'alevel0': $('#add_level0').children("option:selected").val(),
                        'alevel1': $('#add_level1').children("option:selected").val(),
                        'alevel2': $('#add_level2').children("option:selected").val(),
                        'alevel3': $('#add_level3').children("option:selected").val()
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
        
        $('#add_level0').change(function (e){
            e.preventDefault();
        });

        $('#add_level1').change(function (e){
            e.preventDefault();
        });

        $('#add_level2').change(function (e){
            e.preventDefault();
        });

        $('#add_level3').change(function (e){
            e.preventDefault();
        });

        $('#add_level4').change(function (e){
            e.preventDefault();
            $('#abtn_search').click();
        });

        $('#add_superv').change(function (e){
            e.preventDefault();
            $('#abtn_search').click();
        });

        $('#acriteria').change(function (e){
            e.preventDefault();
            $('#abtn_search').click();
        });

        $('#asearch_text').change(function (e){
            e.preventDefault();
            $('#abtn_search').click();
        });

        $('#asearch_text').keydown(function (e){
            if (e.keyCode == 13) {
                e.preventDefault();
                $('#abtn_search').click();
            }
        });

        $('#abtn_search_reset').click(function(e) {
        	e.preventDefault();
        	$('#acriteria').val('all');
        	$('#asearch_text').val(null);
        	$('#add_superv').val('all');
        	$('#add_level0').val(null);
        	$('#add_level1').val(null);
        	$('#add_level2').val(null);
        	$('#add_level3').val(null);
        	$('#add_level4').val(null);
        });

        $('#add_level0').on('select2:select', function (e) {
            e.preventDefault();
            $('#add_level1').val(null).trigger('change');
            $('#add_level2').val(null).trigger('change');
            $('#add_level3').val(null).trigger('change');
            $('#add_level4').val(null).trigger('change');
        });

        $('#add_level1').on('select2:select', function (e) {
            e.preventDefault();
            $('#add_level2').val(null).trigger('change');
            $('#add_level3').val(null).trigger('change');
            $('#add_level4').val(null).trigger('change');
        });

        $('#add_level2').on('select2:select', function (e) {
            e.preventDefault();
            $('#add_level3').val(null).trigger('change');
            $('#add_level4').val(null).trigger('change');
        });

        $('#add_level3').on('select2:select', function (e) {
            e.preventDefault();
            $('#add_level4').val(null).trigger('change');
        });

        $('#add_level4').on('select2:select', function (e) {
            e.preventDefault();
        });

        $('#add_level0').on('select2:unselect', function (e) {
            e.preventDefault();
            $('#add_level0').val(null).trigger('change');
            $('#add_level1').val(null).trigger('change');
            $('#add_level2').val(null).trigger('change');
            $('#add_level3').val(null).trigger('change');
            $('#add_level4').val(null).trigger('change');
        });

        $('#add_level1').on('select2:unselect', function (e) {
            e.preventDefault();
            $('a#dd_level1').val(null).trigger('change');
            $('a#dd_level2').val(null).trigger('change');
            $('a#dd_level3').val(null).trigger('change');
            $('a#dd_level4').val(null).trigger('change');
        });

        $('#add_level2').on('select2:unselect', function (e) {
            e.preventDefault();
            $('#add_level2').val(null).trigger('change');
            $('#add_level3').val(null).trigger('change');
            $('#add_level4').val(null).trigger('change');
        });

        $('#add_level3').on('select2:unselect', function (e) {
            e.preventDefault();
            $('#add_level3').val(null).trigger('change');
            $('#add_level4').val(null).trigger('change');
        });

        $('#add_level4').on('select2:unselect', function (e) {
            e.preventDefault();
            $('#add_level4').val(null).trigger('change');
            $('#abtn_search').click();
        });

    </script>

@endpush