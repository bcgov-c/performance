<x-side-layout title="{{ __('Notifications - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Notify Users
        </h2> 
		@include('sysadmin.notifications.partials.tabs')
    </x-slot>

<div class="pageLoader" id="pageLoader">
	<div class="spinner spinner-border text-secondary" role="status">
		<span class="sr-only">Loading...</span>
	</div>
</div>
@if(Session::has('message'))
                    <div class="col-12"> 
                        <div class="alert alert-danger" style="display:">
                            <i class="fa fa-info-circle"></i> {{ Session::get('message') }}
                        </div>
                    </div>
        @endif

<p class="px-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed tincidunt, nibh nec interdum fermentum, est metus rutrum elit, in molestie ex massa ut urna. Duis dignissim tortor ipsum, dignissim rutrum quam gravida sed. Mauris auctor malesuada luctus. Praesent vitae ante et diam gravida lobortis. Donec eleifend euismod scelerisque. Curabitur laoreet erat sit amet tortor rutrum tristique. Sed lobortis est ac mauris lobortis euismod. Morbi tincidunt porta orci eu elementum. Donec lorem lacus, hendrerit a augue sed, tempus rhoncus arcu. Praesent a enim vel eros elementum porta. Nunc ut leo eu augue dapibus efficitur ac ac risus. Maecenas risus tellus, tincidunt vitae finibus vel, ornare vel neque. Curabitur imperdiet orci ac risus tempor semper. Integer nec varius urna, sit amet rhoncus diam. Aenean finibus, sapien eu placerat tristique, sapien dui maximus neque, id tempor dui magna eget lorem. Suspendisse egestas mauris non feugiat bibendum.</p>

<p class="px-3">Cras quis augue quis risus auctor facilisis quis ac ligula. Fusce vehicula consequat dui, et egestas augue sodales aliquam. In hac habitasse platea dictumst. Curabitur sit amet nulla nibh. Morbi mollis malesuada diam ut egestas. Pellentesque blandit placerat nisi ac facilisis. Vivamus consequat, nisl a lacinia ultricies, velit leo consequat magna, sit amet condimentum justo nibh id nisl. Quisque mattis condimentum cursus. Nullam eget congue augue, a molestie leo. Aenean sollicitudin convallis arcu non maximus. Curabitur ut lacinia nisi. Nam cursus venenatis lacus aliquet dapibus. Nulla facilisi.</p>



 @if ($message = Session::get('success'))
 <div class="alert alert-success">
	 <p>{{ $message }}</p>
 </div>
@endif

<h6 class="text-bold">Step 1. Select employees to notify</h6>
{{-- 
<div class="card">
	<div class="card-body">

		<div class="form-row mb-2">
			<div class="col-6">
				<label for="recipients">To</label>
				<select class="form-control select2 @error('recipients') is-invalid @enderror" 
					name="recipients[]" id="recipients" multiple="multiple">
					@if (old('recipients')) 
						@foreach ( Session::get('old_recipients') ?? [] as $key =>$value )
							<option value="{{ $key }}" selected="selected">{{ $value }}</option>
						@endforeach
					@endif
				</select>
				@error('recipients')
					<span class="invalid-feedback">
					{{  $message  }}
					</span>
				@enderror
			</div>
		</div>
	</div>    
</div>   
 --}}

 <form id="notify-form" action="{{ route('sysadmin.notifications.send') }}" method="post">
	@csrf
	<input type="hidden" id="selected_emp_ids" name="selected_emp_ids" value="">


<!----modal starts here--->
<div id="sendNotifyModal" class="modal" role='dialog'>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
				<h5 class="modal-title">Confirmation</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
            </div>
            <div class="modal-body">
                <p>Are you sure to send out this message ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary mt-2" type="submit" 
					name="btn_send" value="btn_send">Send message</button>
            </div>
			
        </div>
      </div>
  </div>
<!--Modal ends here--->	
	
@include('sysadmin.notifications.partials.filter')

<nav>
	<div class="nav nav-tabs" id="nav-tab" role="tablist">
	  <a class="nav-item nav-link active" id="nav-list-tab" data-toggle="tab" href="#nav-list" role="tab" aria-controls="nav-list" aria-selected="true">List</a>
	  <a class="nav-item nav-link" id="nav-tree-tab" data-toggle="tab" href="#nav-tree" role="tab" aria-controls="nav-tree" aria-selected="false">Tree</a>
	  
	</div>
  </nav>
  <div class="tab-content" id="nav-tabContent">
	<div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
		@include('sysadmin.notifications.partials.recipient-list')
	</div>
	<div class="tab-pane fade" id="nav-tree" role="tabpanel" aria-labelledby="nav-tree-tab" loaded="">
		<div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" id="tree-loading-spinner" role="status" style="display:none">
			<span class="sr-only">Loading...</span>
		</div>
{{-- 
		@include('sysadmin.notifications.partials.recipient-tree')
		Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed tincidunt, nibh nec interdum fermentum, est metus rutrum elit, in molestie ex massa ut urna. Duis dignissim tortor ipsum, dignissim rutrum quam gravida sed. Mauris auctor malesuada luctus. Praesent vitae ante et diam gravida lobortis. Donec eleifend euismod scelerisque. Curabitur laoreet erat sit amet tortor rutrum tristique. Sed lobortis est ac mauris lobortis euismod. Morbi tincidunt porta orci eu elementum. Donec lorem lacus, hendrerit a augue sed, tempus rhoncus arcu. Praesent a enim vel eros elementum porta. Nunc ut leo eu augue dapibus efficitur ac ac risus. Maecenas risus tellus, tincidunt vitae finibus vel, ornare vel neque. Curabitur imperdiet orci ac risus tempor semper. Integer nec varius urna, sit amet rhoncus diam. Aenean finibus, sapien eu placerat tristique, sapien dui maximus neque, id tempor dui magna eget lorem. Suspendisse egestas mauris non feugiat bibendum.		
--}}
	</div>

  </div>



<h6 class="text-bold">Step 2. Enter notificatio details</h6> 


<div class="card">
	<div class="card-body">

			<div class="form-row mb-2">
{{-- 
			  <div class="col-6">
				<label for="recipients">To</label>
				<select class="form-control select2 @error('recipients') is-invalid @enderror" 
					name="recipients[]" id="recipients" multiple="multiple">
					@if (old('recipients')) 
						@foreach ( Session::get('old_recipients') ?? [] as $key =>$value )
							<option value="{{ $key }}" selected="selected">{{ $value }}</option>
						@endforeach
					@endif
				</select>
				@error('recipients')
					<span class="invalid-feedback">
					{{  $message  }}
					</span>
				@enderror
			  </div>
 --}}				
			  {{-- <div class="col-4">
				<label for="sender_id" >From</label>
				<select class="form-control select2 @error('sender_id') is-invalid @enderror" 
						name="sender_id" id="sender_id" >
					@if (old('sender_id')) 
						@foreach ( Session::get('old_sender_ids') ?? [] as $key =>$value )
							<option value="{{ $key }}" selected="selected">{{ $value }}</option>
						@endforeach
					@endif
				</select>
				@error('sender_id')
					<span class="invalid-feedback">
					{{  $message  }}
					</span>
				@enderror
			  </div> --}}
			</div>
			<div class="form-row mb-2">
			  <div class="col-8">
				<label for="subject">Subject</label>
				<input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
				    placeholder="Subject" value="{{ old('subject') }}">
				@error('subject')
					<span class="invalid-feedback">
						{{  $message  }}
					</span>
			  	@enderror
			  </div>

			  <div class="col-2">
				<label for="alert_format">Alert format</label>
				<select  class="form-control @error('alert_format') is-invalid @enderror" id="alert_format" name="alert_format">
					@foreach ($alert_format_list as $key => $value)
					  <option value="{{ $key }}" {{ old('alert_format') == $value ?? 'selected'}}>{{ $value }}</option>
					@endforeach
				  </select>     
				@error('alert_format')
					<span class="invalid-feedback">
						{{  $message  }}
					</span>
			  	@enderror
			  </div>

			</div>
			<div class="form-row mb-2">
				<div class="col-10">
				  <label for="body">Body</label>
				  <textarea type="text" id="body" name="body" class="form-control  @error('body') is-invalid @enderror" 
				     placeholder="Type the content here" rows="3">{{ old('body') }}</textarea>
				  @error('body')
					<span class="invalid-feedback">
						{{  $message  }}
					</span>
				  @enderror
				</div>
			  </div>

	</div>    
</div>   

<h6 class="text-bold">Step 3. Finish</h6>
<div class="col-md-3 mb-2">
 	<button class="btn btn-primary mt-2" type="button" 
		onclick="confirmSendNotifyModal()"
		name="btn_send" value="btn_send">Notify Employees</button>
	<button class="btn btn-secondary mt-2" type="button" onclick="window.location.reload()">Cancel</button>
</div>

</form>

<h6 class="m-20">&nbsp;</h6>
<h6 class="m-20">&nbsp;</h6>
<h6 class="m-20">&nbsp;</h6>


<x-slot name="css">
<style>

.select2-container .select2-selection--single {
    height: 38px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
	height: 38px !important;
}

.pageLoader{
    /* background: url(../images/loader.gif) no-repeat center center; */
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    z-index: 9999999;
    background-color: #ffffff8c;

}

.pageLoader .spinner {
    /* background: url(../images/loader.gif) no-repeat center center; */
    position: fixed;
    top: 25%;
    left: 47%;
    /* height: 100%;
    width: 100%; */
	width: 10em;
	height: 10em;
    z-index: 9000000;
}


</style>
</x-slot>

<x-slot name="js">
	
    <script src="//cdn.ckeditor.com/4.17.2/standard/ckeditor.js"></script>

    <script>
	let g_matched_employees = {!!json_encode($matched_emp_ids)!!};
	let g_selected_employees = {!!json_encode($old_selected_emp_ids)!!};
	let g_employees_by_org = [];

	function confirmSendNotifyModal(){
		count = g_selected_employees.length;
		if (count == 0) {
			$('#sendNotifyModal .modal-body p').html('Are you sure to send out this message ?');
		} else {
			$('#sendNotifyModal .modal-body p').html('Are you sure to send out this message to this ' + count + ' selected recipient(s) ?');
		}
		$('#sendNotifyModal').modal();
	}

	$(document).ready(function(){

		$('#pageLoader').hide();

		CKEDITOR.replace('body', {
				toolbar: "Custom",
				toolbar_Custom: [
					["Bold", "Italic", "Underline"],
					["NumberedList", "BulletedList"],
					["Outdent", "Indent"],
					["Blockquote", "Source"],
				],
		});

		$('#notify-form').keydown(function (e) {
			if (e.keyCode == 13) {
    			e.preventDefault();
    			return false;
			}
		});

		$('#notify-form').submit(function() {

			console.log('submit trigger');			

			// assign back the selected employees to server
			var text = JSON.stringify(g_selected_employees);
			$('#selected_emp_ids').val( text );
			return true; // return false to cancel form action
		});


		// Tab  -- LIST Page  activate
		$("#nav-list-tab").on("click", function(e) {
			
			table  = $('#employee-list-table').DataTable();
			table.rows().invalidate().draw();
		});


		// Tab  -- TREE activate
		$("#nav-tree-tab").on("click", function(e) {

			target = $('#nav-tree'); 

			// To do -- ajax called to load the tree
			if($.trim($(target).attr('loaded'))=='') {
	            //console.log('loading employees');
				$.when( 
						$.ajax({
							url: '/sysadmin/notifications/org-tree',
							type: 'GET',
							data: $("#notify-form").serialize(),
							dataType: 'html',
							beforeSend: function() {
								$("#tree-loading-spinner").show();                    
							},
							success: function (result) {
								$(target).html(''); 
								$(target).html(result);

								$('#nav-tree').attr('loaded','loaded');
							},
							complete: function() {
								
							$(".tree-loading-spinner").hide();
							},
							error: function () {
								alert("error");
								$(target).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
							}
						})
					
				).then(function( data, textStatus, jqXHR ) {
  					//alert( jqXHR.status ); // Alerts 200
					nodes = $('#accordion-level0 input:checkbox');
					redrawTreeCheckboxes();	
				}); 
			
			} else {
					redrawTreeCheckboxes();
			}
		});

		function redrawTreeCheckboxes() {
			// redraw the selection 
			//console.log('redraw triggered');
			nodes = $('#accordion-level0 input:checkbox');
			$.each( nodes, function( index, chkbox ) {
				if (g_employees_by_org.hasOwnProperty(chkbox.value)) {
					// console.log( 'org checkbox ' + chkbox.value);

					all_emps = g_employees_by_org[ chkbox.value ].map( function(x) {return x.employee_id} );

					// console.log(all_emps);
					// console.log(g_selected_employees);
					b = all_emps.every(v=> g_selected_employees.indexOf(v) !== -1);

					if (all_emps.every(v=> g_selected_employees.indexOf(v) !== -1)) {
						$(chkbox).prop('checked', true);
						$(chkbox).prop("indeterminate", false);
					} else if (all_emps.some(v=> g_selected_employees.indexOf(v) !== -1)) {
						$(chkbox).prop('checked', false);
						$(chkbox).prop("indeterminate", true);
					} else {
						$(chkbox).prop('checked', false);
						$(chkbox).prop("indeterminate", false);
					}
					
				} else {
					if ( $(chkbox).attr('name') == 'userCheck[]') {
						if (g_selected_employees.includes(chkbox.value)) {
							$(chkbox).prop('checked', true);
						} else {
							$(chkbox).prop('checked', false);
						}
					}
				}
			});

			// reset checkbox state
			reverse_list = nodes.get().reverse();
			$.each( reverse_list, function( index, chkbox ) {
				if (g_employees_by_org.hasOwnProperty(chkbox.value)) {
					pid = $(chkbox).attr('pid');
					do {
						value = '#orgCheck' + pid;
						//console.log(  value );
						toggle_indeterminate( value );
						//console.log("parent : " + pid);                
						pid = $('#orgCheck' + pid).attr('pid');    
					} 
					while (pid);
				}
			});

		}

		// Set parent checkbox
		function toggle_indeterminate( prev_input ) {

			// Loop to checked the child
			var c_indeterminated = 0;
			var c_checked = 0;
			var c_unchecked = 0;

			prev_location = $(prev_input).parent().attr('href');
			nodes = $(prev_location).find("input:checkbox[name='orgCheck[]']");
			$.each( nodes, function( index, chkbox ) {
				if (chkbox.checked) {
					c_checked++;
				} else if ( chkbox.indeterminate ) {
					c_indeterminated++;
				} else {
					c_unchecked++;
				}
			});
			
			if (c_indeterminated > 0) {
				$(prev_input).prop('checked', false);
				$(prev_input).prop("indeterminate", true);
			} else if (c_checked > 0 && c_unchecked > 0) {
				$(prev_input).prop('checked', false);
				$(prev_input).prop("indeterminate", true);
			} else if (c_checked > 0 && c_unchecked == 0 ) {
				$(prev_input).prop('checked', true);
				$(prev_input).prop("indeterminate", false);
			} else {
				$(prev_input).prop('checked', false);
				$(prev_input).prop("indeterminate", false);
			}

		}

	});

	$(window).on('beforeunload', function(){
		$('#pageLoader').show();
	});

	// $("#recipients").select2({
    //             width: '100%',
    //             ajax: {
    //                 url: '{{ route('sysadmin.notifications.users.list') }}', // '/users', 
    //                 dataType: 'json',
    //                 data: function (params) {
    //                     const query = {
    //                         search: params.term,
    //                         page: params.page || 1
    //                     };
    //                     return query;
    //                 },
    //                 processResults: function (response, params) {
    //                     return {
    //                         results: $.map(response.data.data, function (item) {
    //                             return {
    //                                 text: item.name,
    //                                 id: item.id
    //                             }
    //                         }),
    //                         pagination: {
    //                             more: response.data.current_page !== response.data.last_page
    //                         }
    //                     }
    //                 }
    //             }
    //         });

	$('#sender_id').select2({
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

	// Model -- Confirmation Box

	var modalConfirm = function(callback){
		$("#btn-confirm").on("click", function(){
			$("#mi-modal").modal('show');
		});

		$("#modal-btn-si").on("click", function(){
			callback(true);
			$("#mi-modal").modal('hide');
		});
		
		$("#modal-btn-no").on("click", function(){
			callback(false);
			$("#mi-modal").modal('hide');
		});
	};

    </script>

</x-slot>
</x-side-layout>
<style>
    .alert-danger {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    
</style>   