<x-side-layout title="{{ __('Welcome Message Editor - Performance Development Platform') }}">
    <div name="header" class="container-header p-n2 "> 
        <div class="container-fluid">
            <h3>Welcome Message Editor</h3>
        </div>
    </div>

    <form id="message-form" action="{{ route(request()->segment(1).'.messageeditor.update', $request->id) }}" method="post" onsubmit="return confirmSaveChanges()">
        @csrf

        <div class="card">
            <div class="card-body">
               {{-- <div class="row">
                    <div class="col-md-12">
                        <b>Title</b>
                        <x-textarea name="title" :value="$message->title" />
                        <small class="text-danger error-title"></small>
                    </div>
                </div> --}}
                <div class="row">
                    <div class="col-md-12">
                        {{-- <b>Message</b> --}}
                        <x-textarea name="message" :value="$message->message" />
                        <small class="text-danger error-message"></small>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <b>Display welcome message? </b> &nbsp 
                        {{-- <x-textarea name="status" :value="$message->status" /> --}}
                        {{-- <input id="status" name="status" data-id="{{$message->status}}" class="toggle-class" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="Inactive" "{{ $message->status ? 'checked' : '' }}"> --}}
                        <input id="status" name="status" data-id="{{$message->status}}" class="toggle-class" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="Inactive" {{ $message->status ? 'checked' : '' }}>
                        <small class="text-danger error-status"></small>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-3 mb-2">
            {{-- <button class="btn btn-primary mt-2" type="submit" name="btn_save" value="btn_save" onclick="confirmSaveChanges()">Save Changes</button> --}}
            <button class="btn btn-primary mt-2" type="submit" name="btn_save" value="btn_save">Save Changes</button>
        </div>

    </form>

	<x-slot name="js">
		<script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
		<script src="//cdn.ckeditor.com/4.17.2/standard/ckeditor.js"></script>

		<script>
			function confirmSaveChangesModal(){
                $('#saveModal .modal-body p').html('Are you sure to update welcome message?');
				$('#saveModal').modal();
			}

			function confirmSaveChanges(){
                return confirm('Are you sure to update welcome message?');
			}

			$(document).ready(function(){

				CKEDITOR.replace('message', {
                    height:['640px'],
					toolbar: [ ["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList", "-", "Outdent", "Indent", "Link"] ],disableNativeSpellChecker: false});

				$(window).on('beforeunload', function(){
					$('#pageLoader').show();
				});

				$(window).resize(function(){
					location.reload();
					return;
				});

			});

		</script>
	</x-slot>

</x-side-layout>