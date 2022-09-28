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
                            {{ $c->topic->name }}
                        </h6>
                        <span class="mr-2">
                            With
                            @foreach ($c->conversationParticipants as $p)
                                {{$p->participant->name}}&nbsp;
                            @endforeach
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
                            {{ $c->topic->name }}
                        </h6>
                        <span class="mr-2">
                            With
                            @foreach ($c->conversationParticipants as $p)
                                {{$p->participant->name}}&nbsp;
                            @endforeach
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
        <div class="float-right text-right">
            {{ $conversations->links() }}
        </div>
    </div>

    @include('conversation.partials.view-conversation-modal')

        @include('conversation.partials.delete-hidden-form')

    <x-slot name="js">
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
                    const supervisor = $("#supervisor-signoff-message").find('.name').html();
                    const emp = $("#employee-signoff-message").find('.name').html();
                    if (supervisorSignOffDone && employeeSignOffDone) {
                        message = `${supervisor} and ${emp} ${message}`;
                    } else if (supervisorSignOffDone) {
                        message = `${supervisor} ` + message;
                    } else {
                        message = `${emp} ` + message;
                    }
                    alert(message);
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
                        confirmMessage = "Signing off will lock the content of this record. Employee signature is still required.";
                    }
                    else if (!isSupervisor && !supervisorSignOffDone) {
                        confirmMessage = "Signing off will lock the content of this record. Supervisor signature is still required.";
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
                            $('.error').html(result.Message);
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
            
            var modal_edit = false;
            <?php if ($type == 'upcoming'){ ?>
                var modal_edit = true;
            <?php } ?>
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
            
            function saveComments() {
                        var info_comment1_data = CKEDITOR.instances['info_comment1'].getData();
                        var info_comment2_data = CKEDITOR.instances['info_comment2'].getData();
                        var info_comment3_data = CKEDITOR.instances['info_comment3'].getData();
                        var info_comment4_data = CKEDITOR.instances['info_comment4'].getData();
                        var info_comment5_data = CKEDITOR.instances['info_comment5'].getData();
                        var info_comment6_data = CKEDITOR.instances['info_comment6'].getData();
                        var info_comment7_data = CKEDITOR.instances['info_comment7'].getData();
                        var info_comment8_data = CKEDITOR.instances['info_comment8'].getData();
                        var info_comment9_data = CKEDITOR.instances['info_comment9'].getData();
                        var info_comment10_data = CKEDITOR.instances['info_comment10'].getData();
                        var info_comment11_data = $('#info_comment11').val();
    
                        var comments = {};
                        comments['info_comment1'] = info_comment1_data;
                        comments['info_comment2'] = info_comment2_data;
                        comments['info_comment3'] = info_comment3_data;
                        comments['info_comment4'] = info_comment4_data;
                        comments['info_comment5'] = info_comment5_data;
                        comments['info_comment6'] = info_comment6_data;
                        comments['info_comment7'] = info_comment7_data;
                        comments['info_comment8'] = info_comment8_data;
                        comments['info_comment9'] = info_comment9_data;
                        comments['info_comment10'] = info_comment10_data;
                        comments['info_comment11'] = info_comment11_data;

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

            $(document).on('show.bs.modal', '#viewConversationModal', function(e) {
                $("#viewConversationModal").find("textarea").val('');
                $("#viewConversationModal").find("input, textarea").prop("readonly", false);
                $('#viewConversationModal').data('is-frozen', 0);
            });
            

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
                                    $('.agree-message').html('Your disagree notification has been sent.');
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
                                    $('.agree-message').html('You agreed with this performance review.');
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

            function updateConversation(conversation_id) {
                $.ajax({
                    url: '/conversation/' + conversation_id
                    , success: function(result) {
                         modal_open=true;
                        isSupervisor = result.view_as_supervisor;
                        topic_id = result.topic.id;
                        disable_signoff = result.disable_signoff;
                                
                        employee_signed = false;
                        supervisor_signed = false;
                        
                        if (typeof result.signoff_user_id === 'number') {
                            employee_signed = true;                            
                        }
                        if (typeof result.supervisor_signoff_id === 'number') {
                            supervisor_signed = true;                            
                        }    
                                                
                        $('#conv_participant_edit').val('');
                        $('#conv_participant').val('');
                        $('#conv_title').text(result.topic.name);
                        $('#conv_title_edit').val(result.topic.name);
                        $('#conv_date').text(result.c_date);
                        $('#conv_date_edit').val(result.date);
                        $('#conv_time').text(result.c_time);
                        $('#conv_time_edit').val(result.time);
                        $('#conv_comment').text(result.comment);
                        $('#conv_comment_edit').text(result.comment);
                        //$('#info_comment1').val(result.info_comment1);
                        CKEDITOR.instances['info_comment1'].setData(result.info_comment1);
                        CKEDITOR.instances['info_comment2'].setData(result.info_comment2);
                        CKEDITOR.instances['info_comment3'].setData(result.info_comment3);
                        CKEDITOR.instances['info_comment4'].setData(result.info_comment4);
                        CKEDITOR.instances['info_comment5'].setData(result.info_comment5);
                        CKEDITOR.instances['info_comment6'].setData(result.info_comment6);
                        CKEDITOR.instances['info_comment7'].setData(result.info_comment7);
                        CKEDITOR.instances['info_comment8'].setData(result.info_comment8);
                        CKEDITOR.instances['info_comment9'].setData(result.info_comment9);
                        CKEDITOR.instances['info_comment10'].setData(result.info_comment10);
                        $('#info_comment11').val(result.info_comment11);
                        
                        
                        $('[name="check_one"]').removeAttr('checked');
                        $('[name="check_two"]').removeAttr('checked');
                        $('[name="check_one_"]').removeAttr('checked');
                        $('[name="check_two_"]').removeAttr('checked');
                        
                        if (result.empl_agree1 == 1) {
                            $("input[name=check_one][value=1]").prop('checked', true);
                        } 
                        if (result.empl_agree1 == 0) {
                            $("input[name=check_one][value=0]").prop('checked', true);
                        }
                        if (result.empl_agree2 == 1) {
                            $("input[name=check_two][value=1]").prop('checked', true);
                        } 
                        if (result.empl_agree2 == 0) {
                            $("input[name=check_two][value=0]").prop('checked', true);
                        }
                        
                        if (result.supv_agree1 == 1) {
                            $("input[name=check_one_][value=1]").prop('checked', true);
                        } 
                        if (result.supv_agree1 == 0) {
                            $("input[name=check_one_][value=0]").prop('checked', true);
                        }
                        if (result.supv_agree2 == 1) {
                            $("input[name=check_two_][value=1]").prop('checked', true);
                        } 
                        if (result.supv_agree2 == 0) {
                            $("input[name=check_two_][value=0]").prop('checked', true);
                        }
                        

                        $("#locked-message").addClass("d-none");
                        
                        user1 = result.conversation_participants.find((p) => p.participant_id === currentUser);
                        user2 = result.conversation_participants.find((p) => p.participant_id !== currentUser);
                        let isNotThirdPerson = true;
                        if (!user1 || !user2) {
                            user1 = result.conversation_participants[0];
                            user2 = result.conversation_participants[1];

                            // Disable everything.
                            $("button.btn-conv-edit").hide();
                            $("button.btn-conv-save").hide();
                            $("button.btn-conv-cancel").hide();
                            $("#viewConversationModal").find('textarea').each((index, e) => $(e).prop('readonly', true));
                            $('#viewConversationModal').data('is-frozen', 1);
                            $('#viewConversationModal').data('is-not-allowed', 1);

                            isNotThirdPerson = false;
                        }
                        $('#employee-signoff-questions').removeClass('d-none');
                        if (isSupervisor) {
                            $('#employee-signoff-message').find('.name').html(user2.participant.name);
                            $('#supervisor-signoff-message').find('.name').html(user1.participant.name);
                            
                            $('#employee-unsignoff-message').find('.name').html(user2.participant.name);
                            $('#supervisor-unsignoff-message').find('.name').html(user1.participant.name);
                        } else {
                            $('#employee-signoff-message').find('.name').html(user1.participant.name);
                            $('#supervisor-signoff-message').find('.name').html(user2.participant.name);
                            
                            $('#employee-unsignoff-message').find('.name').html(user1.participant.name);
                            $('#supervisor-unsignoff-message').find('.name').html(user2.participant.name);
                        }
 
                        if (!isSupervisor) {
                            CKEDITOR.instances['info_comment1'].setReadOnly(true);
                            CKEDITOR.instances['info_comment2'].setReadOnly(true);
                            CKEDITOR.instances['info_comment3'].setReadOnly(true);
                            CKEDITOR.instances['info_comment5'].setReadOnly(true);
                            CKEDITOR.instances['info_comment6'].setReadOnly(true);                            
                            $('#info_comment11').prop('disabled', true);
                            $('.supervisor-sign-off').prop('disabled', true);
                            
                            CKEDITOR.instances['info_comment4'].setReadOnly(false);
                            CKEDITOR.instances['info_comment7'].setReadOnly(false);
                            CKEDITOR.instances['info_comment8'].setReadOnly(false);
                            CKEDITOR.instances['info_comment9'].setReadOnly(false);
                            CKEDITOR.instances['info_comment10'].setReadOnly(false);
                            $('.employee-sign-off').prop('disabled', false);
                            $('.team_member_agreement').prop('disabled', false);
                            
                            if(employee_signed == false) {                                
                                $('#signoff-emp-id-input').html('<div id="emp-signoff-row"><div class="my-2">Enter your 6 digit employee ID to indicate you have read and accept the performance review:</div><input type="text" id="employee_id" class="form-control d-inline w-50"><button class="btn btn-primary btn-sign-off ml-2" type="button">Sign with my employee ID</button><br><span class="text-danger error" data-error-for="employee_id"></span></div>');                                
                                $('#unsign-off-block').html('');
                            } else {
                                CKEDITOR.instances['info_comment4'].setReadOnly( true );
                                CKEDITOR.instances['info_comment7'].setReadOnly( true );
                                CKEDITOR.instances['info_comment8'].setReadOnly( true );
                                CKEDITOR.instances['info_comment9'].setReadOnly( true );
                                CKEDITOR.instances['info_comment10'].setReadOnly( true );
                                $('.employee-sign-off').prop( 'disabled', true );
                                $('.team_member_agreement').prop( 'disabled', true )
                                modal_open=false;
                                $("input[name=check_one]").prop( 'disabled', true );
                                $("input[name=check_two]").prop( 'disabled', true );
                                
                                $('#unsignoff-emp-id-input').html('<input type="text" id="employee_id" class="form-control d-inline w-50"><button data-action="unsignoff" class="btn btn-primary btn-sign-off ml-2" type="button">Un-Sign</button><br><span class="text-danger error" data-error-for="employee_id"></span>');
                                $('#sign-off-block').html('');
                            }
                            
                            $("input[name=check_one_]").prop('disabled', true);
                            $("input[name=check_two_]").prop('disabled', true);
                            
                        } else {
                            CKEDITOR.instances['info_comment1'].setReadOnly(false);
                            CKEDITOR.instances['info_comment2'].setReadOnly(false);
                            CKEDITOR.instances['info_comment3'].setReadOnly(false);
                            CKEDITOR.instances['info_comment5'].setReadOnly(false);
                            CKEDITOR.instances['info_comment6'].setReadOnly(false);
                            $('#info_comment11').prop('disabled', false);
                            $('.supervisor-sign-off').prop('disabled', false);
                            
                            CKEDITOR.instances['info_comment4'].setReadOnly(true);
                            CKEDITOR.instances['info_comment7'].setReadOnly(true);
                            CKEDITOR.instances['info_comment8'].setReadOnly(true);
                            CKEDITOR.instances['info_comment9'].setReadOnly(true);
                            CKEDITOR.instances['info_comment10'].setReadOnly(true);
                            $('.employee-sign-off').prop('disabled', true);
                            $('.team_member_agreement').prop('disabled', true);
      
                            if(supervisor_signed == false) {
                                $('#signoff-sup-id-input').html('<div id="emp-signoff-row"><div class="my-2">Enter your 6 digit employee ID to indicate you have read and accept the performance review:</div><input type="text" id="employee_id" class="form-control d-inline w-50"><button class="btn btn-primary btn-sign-off ml-2" type="button">Sign with my employee ID</button><br><span class="text-danger error" data-error-for="employee_id"></span></div>');                                
                                $('#unsign-off-block').html('');
                            } else {
                                CKEDITOR.instances['info_comment1'].setReadOnly( true );
                                CKEDITOR.instances['info_comment2'].setReadOnly( true );
                                CKEDITOR.instances['info_comment3'].setReadOnly( true );
                                CKEDITOR.instances['info_comment5'].setReadOnly( true );
                                CKEDITOR.instances['info_comment6'].setReadOnly( true );
                                $('#info_comment11').prop( 'disabled', true );
                                $('.supervisor-sign-off').prop( 'disabled', true );   
                                modal_open=false;
                                $("input[name=check_one_]").prop( 'disabled', true );
                                $("input[name=check_two_]").prop( 'disabled', true );
                                
                                $('#unsignoff-sup-id-input').html('<input type="text" id="employee_id" class="form-control d-inline w-50"><button data-action="unsignoff" class="btn btn-primary btn-sign-off ml-2" type="button">Un-Sign</button><br><span class="text-danger error" data-error-for="employee_id"></span>');
                                $('#sign-off-block').html('');
                            }
                            
                            $("input[name=check_one]").prop('disabled', true);
                            $("input[name=check_two]").prop('disabled', true);
                            
                        }
                        

                        $('.team_member_agreement').prop('checked', result.team_member_agreement ? true : false);
                        //$('#team_member_agreement_2').prop('checked', result.team_member_agreement ? true : false);

                        $("#employee-sign_off_form").find('input:radio[name="check_one"][value="'+result.empl_agree1+'"]').prop('checked', true);
                        $("#employee-sign_off_form").find('input:radio[name="check_two"][value="'+result.empl_agree2+'"]').prop('checked', true);
                        $("#employee-sign_off_form").find('input:radio[name="check_three"][value="'+result.empl_agree3+'"]').prop('checked', true);

                        $("#employee-signoff-questions").find('input:radio[name="check_one_"][value="'+result.supv_agree1+'"]').prop('checked', true);
                        $("#employee-signoff-questions").find('input:radio[name="check_two_"][value="'+result.supv_agree2+'"]').prop('checked', true);
                        $("#employee-signoff-questions").find('input:radio[name="check_three_"][value="'+result.supv_agree3+'"]').prop('checked', true);

                        if (disable_signoff) {
                            $("#employee-sign_off_form").find('input:radio[name="check_one"]').prop('disabled', true);
                            $("#employee-sign_off_form").find('input:radio[name="check_two"]').prop('disabled', true);
                            $("#employee-sign_off_form").find('input:radio[name="check_three"]').prop('disabled', true);

                            $("#employee-signoff-questions").find('input:radio[name="check_one_"]').prop('disabled', true);
                            $("#employee-signoff-questions").find('input:radio[name="check_two_"]').prop('disabled', true);
                            $("#employee-signoff-questions").find('input:radio[name="check_three_"]').prop('disabled', true);
                        }

                        if (!!result.supervisor_signoff_id) {
                            $('#supervisor-signoff-message').find('.not').addClass('d-none');
                            $('#supervisor-signoff-message').find('.time').removeClass('d-none');
                            $('#viewConversationModal').data('supervisor-signoff', 1);

                        }
                        else {
                            $('#supervisor-signoff-message').find('.not').removeClass('d-none');
                            $('#supervisor-signoff-message').find('.time').addClass('d-none');
                            $('#viewConversationModal').data('supervisor-signoff', 0);
                            
                            $('#supervisor-unsignoff-message').find('.not').removeClass('d-none');
                        }
                        if (!!result.signoff_user_id) {
                            $('#employee-signoff-message').find('.not').addClass('d-none');
                            $('#employee-signoff-message').find('.time').removeClass('d-none');
                            $('#viewConversationModal').data('employee-signoff', 1);
                        } else {
                            $('#employee-signoff-message').find('.not').removeClass('d-none');
                            $('#employee-signoff-message').find('.time').addClass('d-none');
                            $('#viewConversationModal').data('employee-signoff', 0);
                            
                            
                            $('#employee-unsignoff-message').find('.not').removeClass('d-none');
                        }
                        
                        if (result.signoff_user_id) {                            
                            $(".team_member_agreement").prop('disabled', true);
                        }
                        
                        if (employee_signed || supervisor_signed) {
                            // Freeze content.
                            $("button.btn-conv-edit").hide();
                            $("button.btn-conv-save").hide();
                            $("button.btn-conv-cancel").hide();
                            $("#viewConversationModal").find('textarea').each((index, e) => $(e).prop('readonly', true));
                            $('#viewConversationModal').data('is-frozen', 1);
                            if (result.supervisor_signoff_id && isSupervisor) {
                                $("#viewConversationModal .sup-inputs").find('input:radio').each((index, e) => $(e).prop('disabled', true));
                            } 
                            if (result.signoff_user_id && !isSupervisor) {
                                $("#viewConversationModal .emp-inputs").find('input:radio').each((index, e) => $(e).prop('disabled', true));
                            }
                            if (result.signoff_user_id && result.supervisor_signoff_id) {
                                $("#questions-to-consider").hide();
                                $("#questions-to-consider").prev().hide();
                            }
                            
                            //either employee or supervisor signed off, both of them cannot edit/add comment anymore
                            CKEDITOR.instances['info_comment1'].setReadOnly( true );
                            CKEDITOR.instances['info_comment2'].setReadOnly( true );
                            CKEDITOR.instances['info_comment3'].setReadOnly( true );
                            CKEDITOR.instances['info_comment4'].setReadOnly( true );
                            CKEDITOR.instances['info_comment5'].setReadOnly( true );
                            CKEDITOR.instances['info_comment6'].setReadOnly( true );  
                            CKEDITOR.instances['info_comment7'].setReadOnly( true );  
                            CKEDITOR.instances['info_comment8'].setReadOnly( true );  
                            CKEDITOR.instances['info_comment9'].setReadOnly( true );  
                            CKEDITOR.instances['info_comment10'].setReadOnly( true );  
                            $('#info_comment11').prop( 'disabled', true );
                        }
                        if (isNotThirdPerson) {
                            const currentEmpSignoffDone = isSupervisor ? !!result.supervisor_signoff_id : !!result.signoff_user_id
                            if (currentEmpSignoffDone) {
                                $("#signoff-form-block").find("#signoff-emp-id-input").hide();
                                $("#unsignoff-form-block").show();
                            } else {
                                $("#unsignoff-form-block").hide();
                                $("#signoff-form-block").find("#signoff-emp-id-input").show();
                            }
                        }

                        if(!!$('#unsign-off-form').length) {
                            $('#unsign-off-form').attr('action', $('#unsign-off-form').data('action-url').replace('xxx', conversation_id));
                        }
                        $('#questions-to-consider').html('');
                        /*
                        if(result.topic.id == 4){
                            $('#comment_area6').hide();   
                            $('#info_to_capture').removeClass('d-none');
                        }else if(result.topic.id == 3){
                            $('#comment_area6').show();    
                            $('#info_to_capture').removeClass('d-none');                            
                        }else if(result.topic.id == 1){
                            $('#comment_area6').hide(); 
                            $('#info_to_capture').removeClass('d-none');
                        }else {
                            $('#comment_area6').hide(); 
                            $('#info_to_capture').addClass('d-none');
                        }
                        */
                       $('#div-info-comment1').show();
                       $('#div-info-comment2').show();
                       $('#div-info-comment3').show();
                       $('#div-info-comment4').show();
                       $('#div-info-comment5').show();
                       $('#div-info-comment6').show();
                       $('#div-info-comment7').show();
                       $('#div-info-comment8').show();
                       $('#div-info-comment9').show();
                       $('#div-info-comment10').show();
                       $('#div-info-comment11').show();
                       if(result.topic.id == 1){
                           $('#div-info-comment9').hide();
                           $('#div-info-comment10').hide();
                           
                           $('#div-info-comment11').hide();
                           
                           $('#tip-info-comment4').html('<b>Self Summary (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Consider accomplishments, areas for growth, changes to your goals, learning opportunities, etc."> </i>');
                           $('#desc-info-comment4').html('Describe your performance since your last check-in and identify areas to focus on moving forward.');
                           $('#tip-info-comment7').html('<b>Additional Comments (optional)</b>');
                           $('#tip-info-comment8').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           
                           $('#tip-info-comment1').html('<b>Appreciation (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Provide an overview of the actions or results being celebrated. Be as specific as possible about timing, activities, and outcomes achieved. Highlight behaviours, competencies, and corporate values that you feel contributed to the success."> </i>');
                           $('#desc-info-comment1').html('Highlight what has gone well.');
                           $('#tip-info-comment2').html('<b>Coaching (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Provide specific examples of actions, outcomes or behaviours where there is opportunity for growth. Capture information on any additional assistance or training offered to support improvement."> </i>');
                           $('#desc-info-comment2').html('Identify areas where things could be (even) better.');
                           $('#tip-info-comment3').html('<b>Evaluation (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Be as specific as possible, use examples, and focus on observable behaviours and business results."> </i>');
                           $('#desc-info-comment3').html('Provide an overall summary of performance.');
                           $('#tip-info-comment5').html('<b>Additional Comments (optional)</b>');
                           $('#tip-info-comment6').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           $('#desc-info-comment6').html('Capture actions to take as a result of this conversation.');
                           
                           
                       }else if(result.topic.id == 2){
                           $('#div-info-comment8').hide();
                           $('#div-info-comment9').hide();
                           $('#div-info-comment10').hide();
                           
                           $('#div-info-comment3').hide();
                           $('#div-info-comment5').hide();
                           $('#div-info-comment6').hide();
                           $('#div-info-comment11').hide();                           
                           
                           $('#tip-info-comment4').html('<b>Comments (optional)</b>');
                           $('#tip-info-comment7').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           $('#desc-info-comment7').html('Capture actions to take a result of this conversation.');
                           
                           $('#tip-info-comment1').html('<b>Comments (optional)</b>');
                           $('#tip-info-comment2').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           $('#desc-info-comment2').html('Capture actions to take a result of this conversation.');
                            
                       }else if(result.topic.id == 3){
                           
                           
                           $('#div-info-comment6').hide();
                           $('#div-info-comment11').hide();
                           
                           $('#tip-info-comment4').html('<b>Career Goal Statement (Optional)</b>');
                           $('#desc-info-comment4').html('Your personal vision for the future of your career.');
                           $('#tip-info-comment7').html('<b>Strengths (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate areas of strength to build on for career advancement."> </i>');
                           $('#desc-info-comment7').html('Identity your top 1 to 3 strengths.');
                           $('#tip-info-comment8').html('<b>Areas for Growth (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate areas for growth in the short to medium term to assist with career advancement."> </i>');
                           $('#desc-info-comment8').html('Identity 1 to 3 areas you\'d most like to grow.');
                           $('#tip-info-comment9').html('<b>Additional Comments (optional)</b>');
                           $('#tip-info-comment10').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           $('#desc-info-comment10').html('Caputre actions to take as a result of this conversation.');
                           
                           $('#tip-info-comment1').html('<b>Employee Strengths (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Comment on strengths identified by employee, note additonal areas of strength as required, and provide examples where appropriate."> </i>');
                           $('#desc-info-comment1').html('Provide feedback on strength(s) identified by employee.');
                           $('#tip-info-comment2').html('<b>Employee Growth (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Comment on areas for growth identified by employee, note additional areas of growth as required, and provide examples where appropriate."> </i>');
                           $('#desc-info-comment2').html('Provide feedback on area(s) for growth identified by employee.');
                           $('#tip-info-comment3').html('<b>Additional Comments (optional)</b>');
                           $('#tip-info-comment5').html('<b>Aciton Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activites and areas for further discussion. Consider creating a goal in My Goals for yourself or the Goal Bank for your employee to track progress."> </i>');
                           $('#desc-info-comment5').html('Capture actions to take as a result of this conversation.');
                           
                           
                       }else if(result.topic.id == 4){
                           $('#div-info-comment9').hide();
                           $('#div-info-comment10').hide();
                           
                           $('#div-info-comment5').hide();
                           $('#div-info-comment6').hide();
                           
                           $('#tip-info-comment4').html('<b>Self Summary (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Consider accomplishments, areas for improvement, support required to succeed, etc."> </i>');
                           $('#desc-info-comment4').html('Describe your performance since your last check-in and areas to focus on moving forward.');
                           $('#tip-info-comment7').html('<b>Additional Comments (optional)</b>');
                           $('#tip-info-comment8').html('<b>Action Items (optional) </b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           $('#desc-info-comment8').html('Capture actions to take as a result of this conversation.');
                           
                           $('#tip-info-comment1').html('<b>Evaluation</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Be as specific as possible, use examples, and focus on observable behaviours and business results."> </i>');
                           $('#desc-info-comment1').html('Provide an overall summary of performance.');
                           $('#tip-info-comment2').html('<b>What must the employee accomplish? By when?</b>');
                           $('#tip-info-comment3').html('<b>What support will the supervisor (and others) provide? By When?</b>');
                           $('#tip-info-comment11').html('<b>When will a follow up meeting occur?</b>');
                           
                            
                       }else if(result.topic.id == 5){
                           $('#div-info-comment8').hide();
                           $('#div-info-comment9').hide();
                           $('#div-info-comment10').hide();
                           
                           $('#div-info-comment3').hide();
                           $('#div-info-comment5').hide();
                           $('#div-info-comment6').hide();
                           $('#div-info-comment11').hide();
                           
                           $('#tip-info-comment4').html('<b>Comments (optional)</b>');
                           $('#tip-info-comment7').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           $('#desc-info-comment7').html('Capture actions to take a result of this conversation.');
                           
                           $('#tip-info-comment1').html('<b>Comments (optional)</b>');
                           $('#tip-info-comment2').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           $('#desc-info-comment2').html('Capture actions to take a result of this conversation.');
                       }else{
                            $('#div-info-comment1').hide();
                            $('#div-info-comment2').hide();
                            $('#div-info-comment3').hide();
                            $('#div-info-comment4').hide();
                            $('#div-info-comment5').hide();
                            $('#div-info-comment6').hide();
                            $('#div-info-comment7').hide();
                            $('#div-info-comment8').hide();
                            $('#div-info-comment9').hide();
                            $('#div-info-comment10').hide();
                            $('#div-info-comment11').hide();
                       }
                       

                        //Is Locked
                        /*
                        if (result.is_locked) {
                            $("#locked-message").removeClass("d-none");
                            $("#unsignoff-form-block").hide();
                            $("#signoff-form-block").hide();
                        }
                        */

                        //Additional Info to Capture
                        if (result.conversation_topic_id == 1) {
                          $("#info_capture1").html('<span>Appreciation (optional) - supervisor to highlight what has gone well </span><i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Provide an overview of the actions or results being celebrated. Be as specific as possible about timing, activities, and outcomes achieved. Highlight behaviours, competencies, and corporate values that you feel contributed to the success." ></i>');
                          $("#info_capture2").html('<span>Coaching (optional) - supervisor to identify areas where things could be (even) better </span><i class="fas fa-info-circle" data-toggle="popover" data-placement="right" data-trigger="click" data-content="Provide specific examples of actions, outcomes or behaviours where there is opportunity for growth. Capture information on any additional assistance or training offered to support improvement."></i>');
                          $("#info_capture3").html('<span>Evaluation (optional) - supervisor to provide an overall summary of performance</span> <i class="fas fa-info-circle" data-toggle="popover" data-placement="right" data-trigger="click" data-content="Be as specific as possible, use examples, and focus on observable behaviours and business results"></i>');
                        }
                        if (result.conversation_topic_id == 3) {
                          $('#info_capture1').html('<span>Strengths (optional) identify your top 1 to 3 strengths</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Employee to indicate areas of strength to build on for career advancement." ></i>');
                          $('#info_capture2').html('<span>Supervisor Comments (optional) provide feedback on strength(s) identified by employee above</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Supervisor to comment on strengths identified by employee, note additional areas of strength as required, and provide examples where appropriate." ></i>');
                          $('#info_capture3').html('<span>Areas for Growth (optional) identify 1 to 3 areas you most like to grow over the next two years</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Employee to indicate areas for growth in the short to medium term to assist with career advancement." ></i>');
                          $('#info_capture4').html('<span>Supervisor Comments (optional) provide feedback on area(s) for growth identified by employee above</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Supervisor to comment on areas for growth identified by employee, note additional areas of growth as required, and provide examples where appropriate." ></i>');
                        }
                        if (result.conversation_topic_id == 4) {
                          $("#info_capture1").html("What date will a follow up meeting occur?");
                          $("#info_capture2").html("What must the employee accomplish? By when?");
                          $("#info_capture3").html("What support will the supervisor (and others) provide? By when?");
                          
                          
                          $('#info_comment1').prop('required',true);
                          $('#info_comment1_edit').prop('required',true);
                          $('#info_comment2').prop('required',true);
                          $('#info_comment2_edit').prop('required',true);
                          $('#info_comment3').prop('required',true);
                          $('#info_comment3_edit').prop('required',true);
                        }
                        $('[data-toggle="popover"]').popover();
                        /* result.questions?.forEach((question) => {
                          // $('#questions-to-consider').append('<li>' + question + '</li>');
                          $('#questions-to-consider').append(question);
                        }); */
                        $('#questions-to-consider').html(result.questions);
                        $('#preparing-for-conversation').html(result.preparing_for_conversation);
                      
                        // result.questions
                        $('#template-title').text(result.topic.name + ' Template');
                        $('#template-header').text(result.topic.name);
                        // $('#conv_participant_edit').next(".select2-container").hide();

                        $('body').on('click', function (e) {
                            $('[data-toggle=popover]').each(function () {
                            // hide any open popovers when the anywhere else in the body is clicked
                            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                                $(this).popover('hide');
                                }
                            });
                        });

                        var participants = '';
                        $.each(result.topics, function(key, value) {
                            var selected = '';
                            if (value.id == result.conversation_topic_id) {
                                selected = 'selected';
                            }
                            $('#conv_title_edit').append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                        });
                        $.each(result.conversation_participants, function(key, value) {
                            var data = {
                                id: value.participant_id
                                , text: value.participant.name
                            , };
                            var comma = ', ';
                            if (result.conversation_participants.length == (key + 1)) {
                                comma = '';
                            }
                            participants = participants + value.participant.name + comma;
                            var newOption = new Option(value.participant.name, value.participant_id, true, true);
                            $('#conv_participant_edit').append(newOption).trigger('change');
                            $('#conv_participant_edit').trigger({
                                type: 'select2:select'
                                , params: {
                                    data: data
                                }
                            });
                        });
                        $('#conv_participant').text(participants);
                        //originalData = result.info_comment1+result.info_comment2+result.info_comment3+result.info_comment4+result.info_comment5+'';
                    }
                    , error: function(error) {
                        var errors = error.responseJSON.errors;
                    }
                });
            }
            
            function sessionWarning() {
                if (modal_open == true) {
                    saveComments();
                    alert('Your comments have been autosaved.');
                }
                
            }            
            

            $('.modal').on('hidden.bs.modal', function(){
                $('.modal-body').find('#employee_id').val('');
                $('.modal-body').find('.error').html('');
                $('.modal-body').find('input[type=radio]').prop('checked', false);
            });
            
            
            const minutes = 15;
            const SessionTime = 1000 * 60 * minutes;
            $(document).ready(function () {                
                const myTimeout = setTimeout(sessionWarning, SessionTime);                
            });
            
            function sessionWarningStop() {
                clearTimeout(SessionTime);
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