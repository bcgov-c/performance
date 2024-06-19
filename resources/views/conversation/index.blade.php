<style>

.panel-heading  a:before {
   
   float: right;
   transition: all 0.5s;
}
.panel-heading.active a:before {
	-webkit-transform: rotate(180deg);
	-moz-transform: rotate(180deg);
	transform: rotate(180deg);
} 
</style>    
<x-side-layout title="{{ __('My Conversations - Performance Development Platform') }}">
    <h3>
        @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
            My Conversations
        @else
            {{ $user->name }}'s Conversations
        @endif    
    </h3>    
    
    @if($viewType === 'conversations')
        @include('conversation.partials.compliance-message')
    @endif
    <div class="row">
        <div class="col-md-8"> @include('conversation.partials.tabs')</div>
        @if(!$disableEdit && false)
        <div class="col-md-4 text-right">
            <x-button icon="plus-circle" data-toggle="modal" data-target="#addConversationModal">
                Schedule New
            </x-button>
        </div>
        @endif
    </div>

    <div class="mt-2">
        <div class="row">
            <div class="col-12 pb-3">
                {{ $textAboveFilter ?? ''}}
                @include('my-team.partials.conversation-filters')
            </div>
            @if ($type == 'upcoming')
            <b class="p-2">Conversations with My Supervisor</b>
            
            @forelse ($conversations as $c)
            <div class="col-12 col-md-12">
                <div class="d-flex callout callout-info">
                    <div class="flex-fill btn-view-conversation"  style="cursor: pointer;" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">
                        <h6>
                            {{ $c->name }}
                        </h6>
                        <span class="mr-2">
                            With
                            {{ $c->mgrname }} {{ $c->empname }}
                        </span>
                    </div>
                    <div class="d-flex flex-row-reverse align-items-center">
                        <button class="btn btn-danger btn-sm float-right ml-2 delete-btn" data-id="{{ $c->id }}" data-disallowed="{{ (!!$c->signoff_user_id || !!$c->supervisor_signoff_id) ? 'true' : 'false'}}">
                            <i class="fa-trash fa"></i>
                        </button>
                        <button class="btn btn-primary btn-sm float-right ml-2 btn-view-conversation" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">
                            View
                        </button>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12 text-center">
                    No Conversations
                </div>
            @endforelse
            @if($user->hasRole('Supervisor'))
            <b class="p-2">Conversations with My Team</b>
            @forelse ($myTeamConversations as $c)
            @if (!in_array($c->id, $supervisor_conversations)) 
            <div class="col-12 col-md-12">
                <div class="d-flex callout callout-info">
                    <div class="flex-fill btn-view-conversation"  style="cursor: pointer;" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">
                        <h6>
                            {{ $c->name }}
                            {{-- @if ( $c->unlock_until > now() ) 
                                <span class="pl-4 text-danger font-wieght-bold">[Notes: reopened until: {{ $c->unlock_until->format('Y-m-d') }} ]</span>
                            @endif --}}
                        </h6>
                        <span class="mr-2">
                            With
                            {{ $c->mgrname }} {{ $c->empname }}
                        </span>
                    </div>
                    <div class="d-flex flex-row-reverse align-items-center">
                        <button class="btn btn-danger btn-sm float-right ml-2 delete-btn" data-id="{{ $c->id }}" data-disallowed="{{ (!!$c->signoff_user_id || !!$c->supervisor_signoff_id) ? 'true' : 'false'}}">
                            <i class="fa-trash fa"></i>
                        </button>
                        <button class="btn btn-primary btn-sm float-right ml-2 btn-view-conversation" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">
                            View
                        </button>
                    </div>
                </div>
            </div>            
            @endif
            @empty
                <div class="col-12 text-center">
                    No Conversations
                </div>
            @endforelse
            @endif
          @else
            <b class="p-2">Conversations with My Supervisor</b>
            
            @forelse ($conversations as $c)
            <div class="col-12 col-md-12">
                <div class="d-flex callout callout-info">
                    <div class="flex-fill btn-view-conversation"  style="cursor: pointer;" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">
                        <h6>
                            {{ $c->topic->name }}
                        </h6>
                        <span class="mr-2">
                            <i class="fa fa-{{ $c->is_locked ? 'lock' : 'unlock'}}"></i>
                            With
                            @foreach ($c->conversationParticipants as $p)
                                {{$p->participant->name}}&nbsp;
                            @endforeach
                        </span> |
                        <span class="mx-2">
                            <i class="fa fa-calendar text-primary mr-2"></i>
                            {{ $c->last_sign_off_date->format('M d, Y') }}
                        </span>
                    </div>
                    <div class="d-flex flex-row-reverse align-items-center">
                        <button class="btn btn-primary btn-sm float-right ml-2 btn-view-conversation" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">
                            View
                        </button>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12 text-center">
                    No Conversations
                </div>
            @endforelse
            @if($user->hasRole('Supervisor'))
            <b class="p-2">Conversations with My Team</b>
            @forelse ($myTeamConversations as $c)
            <div class="col-12 col-md-12">
                <div class="d-flex callout callout-info">
                    <div class="flex-fill btn-view-conversation"  style="cursor: pointer;" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">
                        <h6>
                            {{ $c->topic->name }}
                        </h6>
                        <span class="mr-2">
                            <i class="fa fa-{{ $c->is_locked ? 'lock' : 'unlock'}}"></i>
                            With
                            @foreach ($c->conversationParticipants as $p)
                                {{$p->participant->name}}&nbsp;
                            @endforeach
                        </span> |
                        <span class="mx-2">
                            <i class="fa fa-calendar text-primary mr-2"></i>
                            {{ $c->last_sign_off_date->format('M d, Y') }}
                        </span>
                    </div>
                    <div class="d-flex flex-row-reverse align-items-center">
                        <button class="btn btn-primary btn-sm float-right ml-2 btn-view-conversation" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">
                            View
                        </button>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12 text-center">
                    No Conversations
                </div>
            @endforelse
            @endif
            @endif
        </div>
        <div class="float-right text-right">       </div>
    </div>

    @include('conversation.partials.view-conversation-modal')

        @include('conversation.partials.delete-hidden-form')

    <x-slot name="js">
        <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
        
        <script>        
            var toReloadPage = false;
            var modal_edit = false;
            var is_viewer = false;
            var after_init = 0;
            var myTimeout;
            
            var db_info_comment1 = '';
            var db_info_comment2 = '';
            var db_info_comment3 = '';
            var db_info_comment4 = '';
            var db_info_comment5 = '';
            var db_info_comment6 = '';
            var db_info_comment7 = '';
            var db_info_comment8 = '';
            var db_info_comment9 = '';
            var db_info_comment10 = '';
            var db_info_comment11 = '';
            
            
            <?php if ($type == 'upcoming'){ ?>
                var modal_edit = true;
            <?php } ?>
            
            document.getElementById("closemodal").onclick = function(e) {myFunction(e)};
            function myFunction(e) {
                if (modal_edit ==  true || !checkIfItIsLocked()){       
                        if (confirm('If you continue you will lose any unsaved changes.')) {
                            modal_open=false;
                            //saveComments();                                
                            $('.modal-body').find('#employee_id').val('');
                            $('.modal-body').find('.error').html('');
                            $('.modal-body').find('input[type=radio]').prop('checked', false);
                            $('#viewConversationModal').modal('toggle');
                            window.location.reload();
                        }else {
                            e.preventDefault();                            
                        }  
                } else if(checkIfItIsLocked()) {
                    $('#viewConversationModal').modal('toggle');
                    window.location.reload();
                }
            } 
        
        
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
            $("#participant_id").select2({
                maximumSelectionLength: 1
            });
            @php
                $authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
                $user = App\Models\User::find($authId);
            @endphp
            var isSupervisor = {{$user->hasRole('Supervisor') ? 'true' : 'false'}};
            var currentUser = {{$authId}};
            var conversation_id = 0;
            var toReloadPage = false;
            $('#conv_participant_edit').select2({
                maximumSelectionLength: 2,
                ajax: {
                    url: '/participant'
                    , dataType: 'json'
                    , delay: 250
                    , data: function(params) {

                        var query = {
                            'search': params.term
                        , }
                        return query;
                    }
                    , processResults: function(data) {

                        return {
                            results: $.map(data.data.data, function(item) {
                                item.text = item.name;
                                return item;
                            })
                        };
                    }
                    , cache: false
                }
            });

            $(function() {
                $('[data-toggle="tooltip"]').tooltip()
            })

            $(document).on('click', '.btn-submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/conversation'
                    , type: 'POST'
                    , data: $('#conversation_form').serialize()
                    , success: function(result) {
                        if (result.success) {
                            window.location.href = '/conversation/upcoming';
                        }
                    }
                    , error: function(error) {
                        var errors = error.responseJSON.errors;
                        $('.error-date-alert').hide();
                        $('.text-danger').each(function(i, obj) {
                            $('.text-danger').text('');
                        });
                        Object.entries(errors).forEach(function callback(value, index) {
                            var className = '.error-' + value[0];
                            $(className).text(value[1]);
                            if (value[0] === 'date') {
                                $('.error-date-alert').show();
                            }
                        });
                    }
                });
            });

            function checkIfItIsLocked() {
                $modal = $("#viewConversationModal");
                if ($modal.data('is-frozen') == 1){
                    if($modal.data('is-not-allowed') == 1) return true;
                    const supervisorSignOffDone = $modal.data('supervisor-signoff') == 1;
                    const employeeSignOffDone = $modal.data('employee-signoff') == 1;
                    let message = "must un-sign before changes can be made to this record of conversation";
                    const supervisor = $("#supervisor-unsignoff-message").find('.name').html();
                    const emp = $("#employee-unsignoff-message").find('.name').html();
                    if (supervisorSignOffDone && employeeSignOffDone) {
                        message = `${supervisor} and ${emp} ${message}`;
                    } else if (supervisorSignOffDone) {
                        message = `${supervisor} ` + message;
                    } else {
                        message = `${emp} ` + message;
                    }
                    //alert(message);
                    return true;
                }
                return false;
            }

            $(document).on('click', '.btn-conv-edit', function(e) {
                if(checkIfItIsLocked()) {
                    return;
                }
                let element_id = '.' + $(this).data('id');
                let elementName = $(this).data('name')
                if($(this).attr("readonly")) {
                    return;
                }
                //$(element_id).toggleClass('d-none');
                $('.btn-conv-save').filter("[data-name=" + elementName + "]").removeClass("d-none");
                $('.btn-conv-cancel').filter("[data-name=" + elementName + "]").removeClass("d-none");
                //$('.btn-conv-edit').filter("[data-name=" + elementName + "]").addClass("d-none");
                $('.btn-conv-edit').prop('readonly', true);
                $('.btn-conv-edit').filter("[data-name=" + elementName + "]").prop('readonly', false);
                //$(element_id).val($("[data-name=" + elementName + "]").val());
                $(element_id).focus();
                // Enable Edit.
                // Disable view
            })

            $(document).on('click', '.btn-conv-save', function(e) {
                // Show Loader Spinner...
                $(this).html("<div class='spinner-border spinner-border-sm' role='status'></div>");
                $(".error-date-alert").hide();
                const that = this;
                const item = $(that).data('id');
                const va = CKEDITOR.instances[item].getData();
                //const va = $("#" + $(that).data('id')).val();
                
                $.ajax({
                    url: '/conversation/' + conversation_id
                    , type: 'PUT'
                    , data: {
                        _token: '{{ csrf_token() }}'
                        , field: $(that).data('name'), // e.target.getAttribute('data-name'),
                        //value: $("#" + $(that).data('id') + '_edit').val()
                        value:va
                    }
                    , success: function(result) {
                        toReloadPage = true;
                        // Disable Edit.
                        $("." + $(that).data('id')).toggleClass('d-none');
                        const elementName = $(that).data('name');
                        $('.btn-conv-save').filter("[data-name=" + elementName + "]").addClass("d-none");
                        $('.btn-conv-cancel').filter("[data-name=" + elementName + "]").addClass("d-none");
                        $('.btn-conv-edit').filter("[data-name=" + elementName + "]").removeClass("d-none");
                        // Update View
                        if ($("#" + $(that).data('id') + '_edit').is('textarea')) {
                            $("#" + $(that).data('id')).val($("#" + $(that).data('id')).val());
                        } else { 
                            updateConversation(conversation_id)
                        }
                    }
                    , error: function(error) {
                        let errors = error.responseJSON.errors;
                        // Ignore for now.
                        if (errors && errors.value && errors.value[0]) {
                            // alert(errors.value[0]);
                            $(".error-date-alert").show();
                        }
                    }
                    , complete: function() {
                        // Remove Spinner
                        $(that).html('Save');
                        $('.btn-conv-edit').prop('readonly', false);
                        $('.enable-not-allowed').prop('readonly', true);
                    }
                });
            });
            
            $(document).on('keypress', '#employee_id', function(e) {                
                if ( e.which == 13 ) {
                   e.preventDefault();
                }
            });

            $(document).on('click', '.btn-sign-off', function(e) {
                const formType = 'employee-';
                const isUnsignOff = $(this).data('action') === 'unsignoff';
                
                const agree_1 = $('#'+formType+'sign_off_form').find('input:radio[name="check_one'+(isSupervisor? '_' : '') +'"]:checked').val();
                const agree_2 = $('#'+formType+'sign_off_form').find('input:radio[name="check_two'+(isSupervisor? '_' : '') +'"]:checked').val();
                const agree_3 = $('#'+formType+'sign_off_form').find('input:radio[name="check_three'+(isSupervisor? '_' : '') +'"]:checked').val();
                
                if(!isUnsignOff) {
                    if (typeof agree_1 === 'undefined' || typeof agree_2 === 'undefined') {
                        alert('Please indicate if you agree or disagree with each of the statements before signing off');
                        return;
                    }
                }

                const agreements = [
                    $('#'+formType+'sign_off_form').find('input:radio[name="check_one'+(isSupervisor? '_' : '') +'"]:checked').val(),
                    $('#'+formType+'sign_off_form').find('input:radio[name="check_two'+(isSupervisor? '_' : '') +'"]:checked').val(),
                    $('#'+formType+'sign_off_form').find('input:radio[name="check_three'+(isSupervisor? '_' : '') +'"]:checked').val()
                ];
                if (!isUnsignOff && agreements.includes("0") && !confirm("Participants should discuss goals and performance expectations and try to come to a shared acceptance of the content in this record before signing off. Do you still want to proceed?")) {
                    return;
                }
                const supervisorSignOffDone = !!$('#viewConversationModal').data('supervisor-signoff');
                const employeeSignOffDone = !!$('#viewConversationModal').data('employee-signoff');
                let confirmMessage = '';

                if (isUnsignOff) {
                    confirmMessage = 'Un-signing will move this record back to the Open Conversations tab. You can click there to access and edit it. Continue?';
                } else {
                    if ((isSupervisor && employeeSignOffDone) || (!isSupervisor && supervisorSignOffDone)) {
                        confirmMessage = "Signing off will move this record to the Completed Conversations tab. You can click there to access it again at any time. Continue?";
                    }
                    else if (isSupervisor && !employeeSignOffDone) {
                        confirmMessage = "ATTENTION: Make sure both supervisor and employee have entered their comments before you sign-off. Signing off will 'lock' both sides of this conversation and prevent additional edits by either participant.";
                    }
                    else if (!isSupervisor && !supervisorSignOffDone) {
                        confirmMessage = "ATTENTION: Make sure both supervisor and employee have entered their comments before you sign-off. Signing off will 'lock' both sides of this conversation and prevent additional edits by either participant.";
                    }
                }

                if (!confirm(confirmMessage)) {
                    return;
                }
                const url = ($(this).data('action') === 'unsignoff') ? '/conversation/unsign-off/' + conversation_id : '/conversation/sign-off/' + conversation_id;
                const data = ($(this).data('action') === 'unsignoff') ? $('#unsign-off-form').serialize() + '&' + $.param({
                        'employee_id': $('#employee_id').val()
                    })
                    : $('#'+formType+'sign_off_form').serialize() + '&' +
                    $.param({
                        'employee_id': $('#employee_id').val()
                    });

                $(this).html("<div class='spinner-border spinner-border-sm' role='status'></div>");
                const that = this;
                $("span.error").html("");
                $(".alert.common-error").hide();
                saveComments();                
                $.ajax({
                    url: url
                    , type: 'POST'
                    , data: data
                    , success: function(result) {
                        if (result.success) {
                            location.reload();
                        } else {
                            if(isSupervisor){
                                $('#signoff-sup-id-input .error').html(result.Message);
                            } else {
                                $('#signoff-emp-id-input .error').html(result.Message);
                            }
                        }
                    }
                    , error: function(error) {
                        const errors = error.responseJSON.errors;
                        const errorElements = Object.keys(errors);
                        if (errorElements.includes('employee_id')) {
                            errorElements.forEach((element) => {
                                $("span.error").filter('[data-error-for="' + element + '"]').html(errors[element][0]);
                            });
                        }
                        delete errors['employee_id'];
                        const commonErrorMessage = Object.values(errors)[0];
                        if (commonErrorMessage) {
                            $(".alert.common-error").find('span').html(commonErrorMessage);
                            $(".alert.common-error").show();
                        }


                    }
                    , complete: function() {
                        const btnText = ($(that).data('action') === 'unsignoff') ? 'Unsign' : 'Sign with my employee ID';
                        $(that).html(btnText)
                    }
                });

            });


            $(document).on('click', '.btn-conv-cancel', function(e) {
                $("." + $(this).data('id')).toggleClass('d-none');
                const elementName = $(this).data('name');
                $('.btn-conv-save').filter("[data-name=" + elementName + "]").addClass("d-none");
                $('.btn-conv-cancel').filter("[data-name=" + elementName + "]").addClass("d-none");
                $('.btn-conv-edit').filter("[data-name=" + elementName + "]").removeClass("d-none");
                $('.btn-conv-edit').prop('readonly', false);
                $('.enable-not-allowed').prop('readonly', true);
                if ($("#"+elementName+"_edit").is('textarea'))
                    $("#"+elementName+"_edit").val($("#"+elementName).val());
            });

            $(document).on('click', '.btn-view-conversation', function(e) {
                conversation_id = e.currentTarget.getAttribute('data-id');
                updateConversation(conversation_id);
                console.log('modal open');                
                setTimeRoll();
            });

            $(document).on('click', '.delete-btn', function() {
                if($(this).data('disallowed')) {
                    alert("This record of conversation cannot be deleted because it has been signed by at least one participant. Un-sign the conversation if you wish to delete it.")
                    return;
                }
                if (!confirm('Are you sure you want to delete this conversation ?')) {
                    return;
                }
                $('#delete-conversation-form').attr(
                    'action'
                    , $('#delete-conversation-form').data('action').replace('xxx', $(this).data('id'))
                ).submit();
            });
            
            
            /*    
            $(document).on('hide.bs.modal', '#viewConversationModal', function(e) {
                if (toReloadPage) {
                    window.location.reload();
                } else {
                    window.location.reload();
                    if (modal_edit ==  true){                        
                        if (isContentModified() && confirm('Click "OK" to save content and exit. Click "Cancel" to exit without saving.')) {
                            modal_open=false;
                            saveComments();
                        }
                    }
                }
            });
            */
            
            function saveComments() {
                if(isSupervisor == 1) {
                        var info_comment1_data = CKEDITOR.instances['info_comment1'].getData();
                        var info_comment2_data = CKEDITOR.instances['info_comment2'].getData();
                        var info_comment3_data = CKEDITOR.instances['info_comment3'].getData();
                        var info_comment5_data = CKEDITOR.instances['info_comment5'].getData();
                        var info_comment6_data = CKEDITOR.instances['info_comment6'].getData();
                        var info_comment11_data = $('#info_comment11').val();
    
                        var comments = {};
                        comments['info_comment1'] = info_comment1_data;
                        comments['info_comment2'] = info_comment2_data;
                        comments['info_comment3'] = info_comment3_data;
                        comments['info_comment5'] = info_comment5_data;
                        comments['info_comment6'] = info_comment6_data;
                        comments['info_comment11'] = info_comment11_data;
                        
                } else {                       
                        var info_comment4_data = CKEDITOR.instances['info_comment4'].getData();                       
                        var info_comment7_data = CKEDITOR.instances['info_comment7'].getData();
                        var info_comment8_data = CKEDITOR.instances['info_comment8'].getData();
                        var info_comment9_data = CKEDITOR.instances['info_comment9'].getData();
                        var info_comment10_data = CKEDITOR.instances['info_comment10'].getData();
    
                        var comments = {};                        
                        comments['info_comment4'] = info_comment4_data;                        
                        comments['info_comment7'] = info_comment7_data;
                        comments['info_comment8'] = info_comment8_data;
                        comments['info_comment9'] = info_comment9_data;
                        comments['info_comment10'] = info_comment10_data;
                        
                }
                        $.ajax({
                                url: '/conversation/' + conversation_id
                                , type: 'PUT'
                                , data: {
                                    _token: '{{ csrf_token() }}'
                                    , field: 'info_comments', // e.target.getAttribute('data-name'),
                                    //value: $("#" + $(that).data('id') + '_edit').val()
                                    value:comments
                                }
                            });
                       
            }
            
            
            
            
            /*
            $(document).on('show.bs.modal', '#viewConversationModal', function(e) {
                $("#viewConversationModal").find("textarea").val('');
                $("#viewConversationModal").find("input, textarea").prop("readonly", false);
                $('#viewConversationModal').data('is-frozen', 0);
            });
            */

            $(document).on('change', '.team_member_agreement', function () {
                if ($(this).prop('checked')) {
                    if (!confirm("Ticking this box will send a notification to your supervisor that you disagree with this performance review. Continue/Cancel")) {
                        $(this).prop("checked", false);
                    } else {
                        const url = '/conversation/disagreement/' + conversation_id;
                        $.ajax({
                            url: url
                            , type: 'GET'
                            , success: function(result) {
                                if (result.success) {
                                    //$('.agree-message').html('Your disagree notification has been sent.');
                                } 
                            }
                        });
                    }
                } else {
                    const url = '/conversation/agreement/' + conversation_id;
                        $.ajax({
                            url: url
                            , type: 'GET'
                            , success: function(result) {
                                if (result.success) {
                                    //$('.agree-message').html('You agreed with this performance review.');
                                } 
                            }
                        });
                }
            });

            function isContentModified() {
                const commentCount = 5;
                for(let i=1; i <= commentCount; i++) {
                    if ($("textarea#info_comment"+i).val() != $("textarea#info_comment"+i+"_edit").val()) {
                        return true;
                    }
                }
                return false;
            }
            
            //include detail conversation modal fill
            @include('conversation.partials.detail-conversation-modal');
            
            function sessionWarning() {
                if (modal_open == true && !is_viewer) {
                    saveComments();                                
                    alert('You have not saved your work in 20 minutes so the PDP has auto-saved to make sure you don\'t lose any information.');
                    after_init = 1;
                    if(isSupervisor == 1) {   
                        $('#info_area1').html('<span style="color:red">Comment saved</span>');
                        $('#info_area2').html('<span style="color:red">Comment saved</span>');
                        $('#info_area3').html('<span style="color:red">Comment saved</span>');
                        $('#info_area5').html('<span style="color:red">Comment saved</span>');
                        $('#info_area6').html('<span style="color:red">Comment saved</span>');
                        $('#info_area11').html('<span style="color:red">Comment saved</span>');
                        
                        var info_comment1_data = CKEDITOR.instances['info_comment1'].getData();
                        var info_comment2_data = CKEDITOR.instances['info_comment2'].getData();
                        var info_comment3_data = CKEDITOR.instances['info_comment3'].getData();
                        var info_comment5_data = CKEDITOR.instances['info_comment5'].getData();
                        var info_comment6_data = CKEDITOR.instances['info_comment6'].getData();
                        var info_comment11_data = $('#info_comment11').val();
                        
                        if (db_info_comment1 != info_comment1_data) {
                            $('#control-info-comment1').show();
                        }
                        if (db_info_comment2 != info_comment2_data) {
                            $('#control-info-comment2').show();
                        }
                        if (db_info_comment3 != info_comment3_data) {
                            $('#control-info-comment3').show();
                        }
                        if (db_info_comment5 != info_comment5_data) {
                            $('#control-info-comment5').show();
                        }
                        if (db_info_comment6 != info_comment6_data) {
                            $('#control-info-comment6').show();
                        }
                        if (db_info_comment11 != info_comment11_data) {
                            $('#control-info-comment11').show();
                        }                                                
                    } else {
                        $('#info_area4').html('<span style="color:red">Comment saved</span>');
                        $('#info_area7').html('<span style="color:red">Comment saved</span>');
                        $('#info_area8').html('<span style="color:red">Comment saved</span>');
                        $('#info_area9').html('<span style="color:red">Comment saved</span>');
                        $('#info_area10').html('<span style="color:red">Comment saved</span>');
                        
                        if (db_info_comment4 != info_comment4_data) {
                            $('#control-info-comment4').show();
                        }
                        if (db_info_comment7 != info_comment7_data) {
                            $('#control-info-comment7').show();
                        }
                        if (db_info_comment8 != info_comment8_data) {
                            $('#control-info-comment8').show();
                        }
                        if (db_info_comment9 != info_comment9_data) {
                            $('#control-info-comment9').show();
                        }
                        if (db_info_comment10 != info_comment10_data) {
                            $('#control-info-comment10').show();
                        }
                    }       
                }
                
            }            
            

            $('.modal').on('hidden.bs.modal', function(){
                $('.modal-body').find('#employee_id').val('');
                $('.modal-body').find('.error').html('');
                $('.modal-body').find('input[type=radio]').prop('checked', false);
            });
            
            
            var info_save1 = 0;
            var info_save2 = 0;
            var info_save3 = 0;
            var info_save4 = 0;
            var info_save5 = 0;
            var info_save6 = 0;
            var info_save7 = 0;
            var info_save8 = 0;
            var info_save9 = 0;
            var info_save10 = 0;
            var info_save11 = 0;           
            
                        
            CKEDITOR.instances['info_comment1'].on('focus', function(e) {
                   $('#info_area1').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment1').show();
                   info_save1 = 0;
            });
            CKEDITOR.instances['info_comment1'].on('key', function(e) { 
                $('#info_area1').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                //$('#control-info-comment1').show();
                info_save1 = 0;       
            });           
        
            CKEDITOR.instances['info_comment1'].on('blur', function(e) {
                if(info_save1 == 0){
                   $('#control-info-comment1').hide();
                }   
            });
            
            $('#control-info-comment1').click(function() {
                saveCkComment('info_comment1');
                info_save1 = 1;
                $('#info_area1').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            }); 
            
            
            CKEDITOR.instances['info_comment2'].on('focus', function(e) {
                   $('#info_area2').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment2').show();
                   info_save2 = 0;
            });
            CKEDITOR.instances['info_comment2'].on('key', function(e) { 
                $('#info_area2').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment2').show();
                   info_save2 = 0;
            });
            CKEDITOR.instances['info_comment2'].on('blur', function(e) {
                if(info_save2 == 0){
                   $('#control-info-comment2').hide();
                }   
            });
            
            $('#control-info-comment2').click(function() {
                saveCkComment('info_comment2');
                info_save2 = 1;
                $('#info_area2').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            }); 
            
            
            CKEDITOR.instances['info_comment3'].on('focus', function(e) {
                   $('#info_area3').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment3').show();
                   info_save3 = 0;
            });
            CKEDITOR.instances['info_comment3'].on('key', function(e) { 
                $('#info_area3').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment3').show();
                   info_save3 = 0;
            });
            CKEDITOR.instances['info_comment3'].on('blur', function(e) {
                if(info_save3 == 0){
                   $('#control-info-comment3').hide();
                }   
            });
            
            $('#control-info-comment3').click(function() {
                saveCkComment('info_comment3');
                info_save3 = 1;
                $('#info_area3').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            }); 
            
            CKEDITOR.instances['info_comment4'].on('focus', function(e) {
                   $('#info_area4').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment4').show();
                   info_save4 = 0;
            });
            CKEDITOR.instances['info_comment4'].on('key', function(e) { 
                $('#info_area4').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment4').show();
                   info_save4 = 0;
            });
            CKEDITOR.instances['info_comment4'].on('blur', function(e) {
                if(info_save4 == 0){
                   $('#control-info-comment4').hide();
                }   
            });
            
            $('#control-info-comment4').click(function() {
                saveCkComment('info_comment4');
                info_save4 = 1;
                $('#info_area4').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            });  
            
            
            
            CKEDITOR.instances['info_comment5'].on('focus', function(e) {
                   $('#info_area5').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment5').show();
                   info_save5 = 0;
            });
            CKEDITOR.instances['info_comment5'].on('key', function(e) { 
                $('#info_area5').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment5').show();
                   info_save5 = 0;
            });
            CKEDITOR.instances['info_comment5'].on('blur', function(e) {
                if(info_save5 == 0){
                   $('#control-info-comment5').hide();
                }   
            });
            
            $('#control-info-comment5').click(function() {
                saveCkComment('info_comment5');
                info_save5 = 1;
                $('#info_area5').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            });  
            
            
            CKEDITOR.instances['info_comment6'].on('focus', function(e) {
                   $('#info_area6').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment6').show();
                   info_save6 = 0;
            });
            CKEDITOR.instances['info_comment6'].on('key', function(e) { 
                $('#info_area6').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment6').show();
                   info_save6 = 0;
            });
            CKEDITOR.instances['info_comment6'].on('blur', function(e) {
                if(info_save6 == 0){
                   $('#control-info-comment6').hide();
                }   
            });
            
            $('#control-info-comment6').click(function() {
                saveCkComment('info_comment6');
                info_save6 = 1;
                $('#info_area6').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            });
            
            CKEDITOR.instances['info_comment7'].on('focus', function(e) {
                   $('#info_area7').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment7').show();
                   info_save7 = 0;
            });
            CKEDITOR.instances['info_comment7'].on('key', function(e) { 
                $('#info_area7').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment7').show();
                   info_save7 = 0;
            });
            CKEDITOR.instances['info_comment7'].on('blur', function(e) {
                if(info_save7 == 0){
                   $('#control-info-comment7').hide();
                }   
            });
            
            $('#control-info-comment7').click(function() {
                saveCkComment('info_comment7');
                info_save7 = 1;
                $('#info_area7').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            });
            
            CKEDITOR.instances['info_comment8'].on('focus', function(e) {
                   $('#info_area8').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment8').show();
                   info_save8 = 0;
            });
            CKEDITOR.instances['info_comment8'].on('key', function(e) { 
                $('#info_area8').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment8').show();
                   info_save8 = 0;
            });
            CKEDITOR.instances['info_comment8'].on('blur', function(e) {
                if(info_save8 == 0){
                   $('#control-info-comment8').hide();
                }   
            });
            
            $('#control-info-comment8').click(function() {
                saveCkComment('info_comment8');
                info_save8 = 1;
                $('#info_area8').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            });
            
            CKEDITOR.instances['info_comment9'].on('focus', function(e) {
                   $('#info_area9').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment9').show();
                   info_save9 = 0;
            });
            CKEDITOR.instances['info_comment9'].on('key', function(e) { 
                $('#info_area9').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment9').show();
                   info_save9 = 0;
            });
            CKEDITOR.instances['info_comment9'].on('blur', function(e) {
                if(info_save9 == 0){
                   $('#control-info-comment9').hide();
                }   
            });
            
            $('#control-info-comment9').click(function() {
                saveCkComment('info_comment9');
                info_save9 = 1;
                $('#info_area9').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            });
            
            
            CKEDITOR.instances['info_comment10'].on('focus', function(e) {
                   $('#info_area10').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment10').show();
                   info_save10 = 0;
            });
            CKEDITOR.instances['info_comment10'].on('key', function(e) { 
                $('#info_area10').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   //$('#control-info-comment10').show();
                   info_save10 = 0;
            });
            CKEDITOR.instances['info_comment10'].on('blur', function(e) {
                if(info_save10 == 0){
                   $('#control-info-comment10').hide();
                }   
            });
            
            $('#control-info-comment10').click(function() {
                saveCkComment('info_comment10');
                info_save10 = 1;
                $('#info_area10').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            });
            
                        
            $('#info_comment11').focus(function() {
                 $('#info_area11').html('<button type="button" class="btn btn-primary">Save</button><br/>'); 
                   $('#control-info-comment11').show();
                   info_save11 = 0;
            });
            $('#info_comment11').blur(function() {
                 $('#control-info-comment11').hide();
            });
            
            $('#control-info-comment11').click(function() {
                saveComment('info_comment11');
                $('#info_area11').html('<span style="color:red">Comment saved</span>');
                setTimeRoll();
            });
            
            function saveCkComment(comment) {
                var info_comment_data = CKEDITOR.instances[comment].getData();
                var comments = {};
                comments[comment] = info_comment_data;
                
                $.ajax({
                                url: '/conversation/' + conversation_id
                                , type: 'PUT'
                                , data: {
                                    _token: '{{ csrf_token() }}'
                                    , field: 'info_comments', // e.target.getAttribute('data-name'),
                                    //value: $("#" + $(that).data('id') + '_edit').val()
                                    value:comments
                                }
                            });

            }
            
            function saveComment(comment) {
                var info_comment_data = $('#'+comment).val();
                var comments = {};
                comments[comment] = info_comment_data;
                
                $.ajax({
                                url: '/conversation/' + conversation_id
                                , type: 'PUT'
                                , data: {
                                    _token: '{{ csrf_token() }}'
                                    , field: 'info_comments', // e.target.getAttribute('data-name'),
                                    //value: $("#" + $(that).data('id') + '_edit').val()
                                    value:comments
                                }
                            });

            }
            
            
            function sessionWarningStop() {
                clearTimeout(SessionTime);
            }
            
            
            function setTimeRoll(){
                const minutes = 20;
                const SessionTime = 1000 * 60 * minutes;
                if (myTimeout) { clearInterval(myTimeout) };
                //const myTimeout = setTimeout(sessionWarning, SessionTime);
                myTimeout = setInterval(function() { 
                    if (modal_open == true) {
                        $('#info_area1').html('');
                        $('#info_area2').html('');
                        $('#info_area3').html('');
                        $('#info_area4').html('');
                        $('#info_area5').html('');
                        $('#info_area6').html('');
                        $('#info_area7').html('');
                        $('#info_area8').html('');
                        $('#info_area9').html('');
                        $('#info_area10').html('');
                        $('#info_area11').html('');
                        saveComments();                                
                        alert('You have not saved your work in 20 minutes so the PDP has auto-saved to make sure you don\'t lose any information.');
                        if(isSupervisor == 1) {                               
                            var info_comment1_data = CKEDITOR.instances['info_comment1'].getData();
                            var info_comment2_data = CKEDITOR.instances['info_comment2'].getData();
                            var info_comment3_data = CKEDITOR.instances['info_comment3'].getData();
                            var info_comment5_data = CKEDITOR.instances['info_comment5'].getData();
                            var info_comment6_data = CKEDITOR.instances['info_comment6'].getData();
                            var info_comment11_data = $('#info_comment11').val();

                            if (db_info_comment1 != info_comment1_data) {
                                $('#info_area1').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment1').show();
                                db_info_comment1 = info_comment1_data;
                            }
                            if (db_info_comment2 != info_comment2_data) {
                                $('#info_area2').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment2').show();
                                db_info_comment2 = info_comment2_data;
                            }
                            if (db_info_comment3 != info_comment3_data) {
                                $('#info_area3').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment3').show();
                                db_info_comment3 = info_comment3_data;
                            }
                            if (db_info_comment5 != info_comment5_data) {
                                $('#info_area5').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment5').show();
                                db_info_comment5 = info_comment5_data;
                            }
                            if (db_info_comment6 != info_comment6_data) {
                                $('#info_area6').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment6').show();
                                db_info_comment6 = info_comment6_data;
                            }
                            if (db_info_comment11 != info_comment11_data) {
                                $('#info_area11').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment11').show();
                                db_info_comment11 = info_comment11_data;
                            }                                                
                        } else {
                            var info_comment4_data = CKEDITOR.instances['info_comment4'].getData();
                            var info_comment7_data = CKEDITOR.instances['info_comment7'].getData();
                            var info_comment8_data = CKEDITOR.instances['info_comment8'].getData();
                            var info_comment9_data = CKEDITOR.instances['info_comment9'].getData();
                            var info_comment10_data = CKEDITOR.instances['info_comment10'].getData();                        
                        
                            if (db_info_comment4 != info_comment4_data) {
                                $('#info_area4').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment4').show();
                                db_info_comment4 = info_comment4_data;
                            }
                            if (db_info_comment7 != info_comment7_data) {
                                $('#info_area7').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment7').show();
                                db_info_comment7 = info_comment7_data;
                            }
                            if (db_info_comment8 != info_comment8_data) {
                                $('#info_area8').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment8').show();
                                db_info_comment8 = info_comment8_data;
                            }
                            if (db_info_comment9 != info_comment9_data) {
                                $('#info_area9').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment9').show();
                                db_info_comment9 = info_comment9_data;
                            }
                            if (db_info_comment10 != info_comment10_data) {
                                $('#info_area10').html('<span style="color:red">Comment saved</span>');
                                $('#control-info-comment10').show();
                                db_info_comment10 = info_comment10_data;
                            }
                        }       
                    }    
                }, SessionTime);                
            }
            
        </script>

@isset($open_modal_id)
        // when redirect from dashboardController, and then open the modal box
        <script>
            $(function() {
                conversation_id = {{ $open_modal_id }};
                updateConversation(conversation_id);
                $('#viewConversationModal').modal('show');                
            });

            
             $('.panel-collapse').on('show.bs.collapse', function () {
                $(this).siblings('.panel-heading').addClass('active');
              });

              $('.panel-collapse').on('hide.bs.collapse', function () {
                $(this).siblings('.panel-heading').removeClass('active');
              });
            
        </script>
@endisset        
        
    </x-slot>

</x-side-layout>

<script>
  $('#collapse_1').on('show.bs.collapse', function () {
    $('#caret_1').html('<i class="fas fa-caret-up"></i>');
  });
  $('#collapse_1').on('hide.bs.collapse', function () {
    $('#caret_1').html('<i class="fas fa-caret-down"></i>');
  });

  $('#collapse_2').on('show.bs.collapse', function () {
    $('#caret_2').html('<i class="fas fa-caret-up"></i>');
  });  
  $('#collapse_2').on('hide.bs.collapse', function () {
    $('#caret_2').html('<i class="fas fa-caret-down"></i>');
  }); 
    
</script>

<style>
    .panel-heading{
        opacity: 0.5;
    }
    .acc-title {
	display: block;
	height: 22px;
	position:absolute;
	top:11px;
	left:20px;
    }
    .acc-status {
	display: block;
	width: 22px;
	height: 22px;
	position:absolute;
	top:11px;
	right:11px;
    }
    
</style>    