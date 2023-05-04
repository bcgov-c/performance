<x-side-layout title="{{ __('Unlock Conversations - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Unlock Conversations
        </h2> 
		@include('sysadmin.unlock.partials.tabs')
    </x-slot>

<p class="px-3">The table below displays all completed conversations for employees in your oragnization(s). 
		Use the filters to search for conversations by organization level, topic, participants 
		or by completion date range. You may also use the text input field to search by name or keyword. 
		Conversation marked with an "unlocked" icon in the far left column are still within the 
		two-week window of time that allows for any additional content edits by either conversation participant.
		You can view the initial conversation completion date to right of the 'Participants' column.</p>
		<p class="px-3">Conversations marked with "locked" icon have passed the two-week window of the time to
				 allow for any additional content edits by either conversation participant. If you need to 
				 unlock the conversion, you can click on the "Unlock" button to the right of the "View" 
				 	button in the 'Action' column.</p>

@if ($message = Session::get('success'))
 <div class="alert alert-success">
	 <p>{{ $message }}</p>
 </div>
@endif


{{-- Search Criteria Section --}}
<form id="unlocked-conversation-form" action="{{ route('sysadmin.unlock.unlockconversation.search') }}" method="post" class="search-filter">
	@csrf

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
			<label for="dd_level1">Level 1</label>
			<select id="dd_level1" name="dd_level1" class="form-control select2">
				@if ( old('dd_level1') && session()->get('level1') )
					<option value="{{ session()->get('level1')->id }}">{{ session()->get('level1')->name }}</option>
				@endif
			</select>
		</div>
		<div class="form-group col-md-3">
			<label for="dd_level2">Level 2</label>
			<select id="dd_level2" name="dd_level2" class="form-control select2">
				@if ( old('dd_level2') && session()->get('level2') )
					<option value="{{ session()->get('level2')->id }}">{{ session()->get('level2')->name }}</option>
				@endif
			</select>
		</div>
		<div class="form-group col-md-3">
			<label for="dd_level3">Level 3</label>
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
				<label for="topic_id">Topic</label>
				<select id="topic_id" name="topic_id" class="form-control" >
					<option  value="">Select Topic</option>
					@foreach( $topicList as $topic)
						{{-- <option value="{{ $topic->id }}" {{  old('topic_id') == $topic->id ? 'selected' : ''  }}> --}}
						<option value="{{ $topic->id }}">
							{{ $topic->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label for="completion_date_from">Completion Date (From)</label>
				<input type="date" class="form-control" id="completion_date_from" name="completion_date_from" 
					value="">
			</div>
			<div class="form-group col-md-3">
				<label for="completion_date_to">Completion Date (To)</label>
				<input type="date" class="form-control" id="completion_date_to" name="completion_date_to" 
					value="">
			</div>


			<div class="form-group col-md-2">
				<label for="criteria">Search Criteria</label>
				<select id="criteria" name="criteria" class="form-control">
					@foreach( $criteriaList as $key => $value )
						<option value="{{ $key }}">{{ $value }}</option>
					@endforeach
				</select>
			</div>

			<div class="form-group col-md-2">
				<label for="search_text">Search</label>
				<input type="text" id="search_text" name="search_text" class="form-control" 
						value="" placeholder="Keyword">
			</div>

		</div>

		<div class="form-row">
			<div class="form-group col-md-12">
			<span class="float-right">  
			<button type="button" class="btn btn-primary" name="btn_search" id ="btn_search" 
					value="btn_search" 
					{{-- formaction="{{ route('sysadmin.unlock.unlockconversation.search') }}" --}}
					>Search</button>
			<button type="button" class="btn btn-secondary  " id="btn_search_reset" name="btn_reset" value="btn_reset">Reset</button>
			</span>
			</div>
		</div>

	</div>
</form>


{{-- Table to list the result --}}
<div class="card">
	<div class="card-body">
		<h6></h6>
		<table class="table table-bordered table-striped" id="conversation-list-table" style="width: 100%">
			<thead>
				<tr>
                    <th>&nbsp;</th>
                    <th>Topic</th>
					<th>Participants</th>
                    <th>Completion Date</th>
					<th>Action</th>
				</tr>
			</thead>
		</table>

	</div>    
</div>  


{{-- Modal for  --}}
<div class="modal fade" id="unlock-conversation-modal" tabindex="-1" role="dialog" aria-labelledby="unlockModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header bg-primary">
		  <h5 class="modal-title" id="unlockModalLabel">Unlock Conversation</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="mt-4 px-3 ">
			<div class="row">
				
					<div class="col-12  alert alert-default-warning alert-dismissible">
						<p>
							<i class="icon fas fa-exclamation-circle"></i>
					
						Unlocking this record of coversation will allow participants to un-sign and 
						edit the content. The conversation will remain unlocked until the date entered below.
					</p>
				</div>
			</div>
		</div>
		<div class="modal-body">
		  <form id="unlock-model-form">
			<div class="form-group col-sm-6 mx-auto">
			  <label for="unlock_until" class="col-form-label">Select Lock Date</label>
			  <input type="date" class="form-control" id="unlock_until" name="unlock_until">
			</div>
{{-- 
			<div class="form-group">
			  <label for="message-text" class="col-form-label">Message:</label>
			  <textarea class="form-control" id="message-text"></textarea>
			</div>
--}}						
		  </form>
		</div>
		<div class="modal-footer">
		  <button type="button" id="unlock-confirm-btn" value="11"  class="btn btn-primary" >Unlock Conversation</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
		</div>
	  </div>
	</div>
</div>

@include('conversation.partials.view-conversation-modal')

<x-slot name="css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">

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

</x-slot>

<x-slot name="js">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>

<script>            
	CKEDITOR.replace('info_comment1', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment2', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment3', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment4', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment5', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment6', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment7', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment8', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment9', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	CKEDITOR.replace('info_comment10', {
		toolbar: "Custom",
		toolbar_Custom: [
			["Bold", "Italic", "Underline"],
			["NumberedList", "BulletedList"],
			["Outdent", "Indent"],
			["Link"],
		],
		disableNativeSpellChecker: false
	});
	
	 modal_open=false;
</script> 

<script>
$.ajaxSetup({
	headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	}
}); 

$(function() {

	var oTable = $('#conversation-list-table').DataTable({
            "scrollX": true,
            retrieve: true,
            "searching": false,
            processing: true,
            serverSide: true,
            select: true,
            'order': [[1, 'asc']],
            ajax: {
                url: '{!! route('sysadmin.unlock.lockedconversation.list') !!}',
                data: function (d) {
                    d.dd_level0 = $('#dd_level0').val();
                    d.dd_level1 = $('#dd_level1').val();
                    d.dd_level2 = $('#dd_level2').val();
                    d.dd_level3 = $('#dd_level3').val();
                    d.dd_level4 = $('#dd_level4').val();
                    d.topic_id = $('#topic_id').val();
					d.completion_date_from = $('#completion_date_from').val();
					d.completion_date_to = $('#completion_date_to').val();
                    d.criteria = $('#criteria').val();
                    d.search_text = $('#search_text').val();
                }
            },
            columns: [
				{data: 'unlock', name: 'unlock', orderable: false, searchable: false},
                {data: 'topic.name', name: 'topic.name'},
                {data: 'participants', name: 'participants', orderable: false},
                {data: 'completed_date', name: 'completed_date'},			
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            columnDefs: [
					{
                        className: "dt-nowrap",
                        targets: 1
                    },
                    {
                        className: "dt-nowrap",
                        targets: 2
                    },
					{
                        className: "dt-nowrap",
                        targets: 3
                    }
                ]
        });





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
		placeholder: 'select level 1',
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
		placeholder: 'select level 2',
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
		placeholder: 'select level 3',
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


	$('#btn_search').on('click', function() {
            // oTable.ajax.reload(null, true);
            oTable.draw();
    });

	$('#btn_search_reset').click(function() {

		// Reset filter fields value
		$('.search-filter input').map( function() {$(this).val(''); });
        $('.search-filter select').map( function() { return $(this).val(''); })
		$(".search-filter .select2").map(function() { $(this).val('').trigger('change'); })

        oTable.search( '' ).columns().search( '' ).draw();

		// $('#dd_level0').val(null).trigger('change');
		// $('#dd_level1').val(null).trigger('change');
		// $('#dd_level2').val(null).trigger('change');
		// $('#dd_level3').val(null).trigger('change');
		// $('#dd_level4').val(null).trigger('change');
		// $('#topic_id').val('').trigger('change');
		// $('#completion_date_from').val(null);
		// $('#completion_date_to').val(null);
		// $('#search_text').val(null);

	});

	$('#unlocked-conversation-form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });

	// dispaly Detail in Modal
	$(document).on("click", ".unlock-modal" , function(e) {
			e.preventDefault();

			// Clear all those error message if exists
			$('#unlock_until').val('');
			$('#unlock_until').nextAll('span.text-danger').remove();

			$('#unlock-confirm-btn').val( $(this).attr('data-id')  );
			$('#unlock-conversation-modal').modal('show');

	});

	$(document).on("click", "#unlock-confirm-btn" , function(e) {
		
		var form = $('#unlock-model-form');
		var id = e.target.value;
		
		info = 'Participants will be able to un-sign and edit the content of this conversation record until the specified lock date. Would you like to proceed?';
		if (confirm(info))
		{

			$('#unlock_until').nextAll('span.text-danger').remove();

			$.ajax({
				method: "PUT",
				url:  '/sysadmin/unlock/unlockconversation/' + id,
				data: form.serialize(), // serializes the form's elements.
				success: function(data)
				{
					oTable.ajax.reload();	// reload datatables
					$('#unlock-conversation-modal').modal('hide');
				},
				error: function(response) {
					if (response.status == 422) {
						
						$.each(response.responseJSON.errors, function(field_name,error){
							$(document).find('[name='+field_name+']').after('<span class="text-strong text-danger">' +error+ '</span>')
						})
					}
					console.log('Error');
				}
			});
		
		};
    });

});


@include('sysadmin.unlock.partials.conversation-modal-script')

</script>

</x-slot>

</x-side-layout>



