<x-side-layout>
    <h3>Conversations</h3>
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
                $(element_id).toggleClass('d-none');
                $('.btn-conv-save').filter("[data-name=" + elementName + "]").removeClass("d-none");
                $('.btn-conv-cancel').filter("[data-name=" + elementName + "]").removeClass("d-none");
                $('.btn-conv-edit').filter("[data-name=" + elementName + "]").addClass("d-none");
                $('.btn-conv-edit').prop('readonly', true);
                $(element_id).val($("[data-name=" + elementName + "]").val());
                $(element_id).focus();
                // Enable Edit.
                // Disable view
            })

            $(document).on('click', '.btn-conv-save', function(e) {
                // Show Loader Spinner...
                $(this).html("<div class='spinner-border spinner-border-sm' role='status'></div>");
                $(".error-date-alert").hide();
                const that = this;
                $.ajax({
                    url: '/conversation/' + conversation_id
                    , type: 'PUT'
                    , data: {
                        _token: '{{ csrf_token() }}'
                        , field: $(that).data('name'), // e.target.getAttribute('data-name'),
                        value: $("#" + $(that).data('id') + '_edit').val()
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
                            $("#" + $(that).data('id')).val($("#" + $(that).data('id') + '_edit').val());
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

            $(document).on('click', '.btn-sign-off', function(e) {
                const formType = 'employee-';
                const isUnsignOff = $(this).data('action') === 'unsignoff';

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
                const data = ($(this).data('action') === 'unsignoff') ? $('#unsign-off-form').serialize() : $('#'+formType+'sign_off_form').serialize() + '&' +
                    $.param({
                        'employee_id': $('#employee_id').val()
                    });
                $(this).html("<div class='spinner-border spinner-border-sm' role='status'></div>");
                const that = this;
                $("span.error").html("");
                $(".alert.common-error").hide();
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
            $(document).on('hide.bs.modal', '#viewConversationModal', function(e) {
                if (toReloadPage) {
                    window.location.reload();
                } else {
                    if (isContentModified() && !confirm("If you continue you will lose any unsaved information.")) {
                        e.preventDefault();
                    }
                }
            });

            $(document).on('show.bs.modal', '#viewConversationModal', function(e) {
                $("#viewConversationModal").find("textarea").val('');
                $("#viewConversationModal").find("input, textarea").prop("readonly", false);
                $('#viewConversationModal').data('is-frozen', 0);
            });

            $(document).on('change', '#team_member_agreement', function () {
                if ($(this).prop('checked')) {
                    if (!confirm("Ticking this box will send a notification to your supervisor that you disagree with this performance review. Continue/Cancel")) {
                        $(this).prop("checked", false);
                    }
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
                        $("#viewConversationModal").find('textarea').prop('disable', false);
                        isSupervisor = !result.is_with_supervisor;
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
                        $('#info_comment1').val(result.info_comment1);
                        $('#info_comment1_edit').val(result.info_comment1);
                        $('#info_comment2').val(result.info_comment2);
                        $('#info_comment2_edit').val(result.info_comment2);
                        $('#info_comment3').val(result.info_comment3);
                        $('#info_comment3_edit').val(result.info_comment3);
                        $('#info_comment4').val(result.info_comment4);
                        $('#info_comment4_edit').val(result.info_comment4);
                        $('#info_comment5').val(result.info_comment5);
                        $('#info_comment5_edit').val(result.info_comment5);
                        $('#info_comment6').val(result.info_comment6);
                        $('#info_comment6_edit').val(result.info_comment6);
                        $('#team_member_agreement').prop('checked', result.team_member_agreement ? true : false);
                        $('#team_member_agreement_2').prop('checked', result.team_member_agreement ? true : false);

                        $("#locked-message").addClass("d-none");
                        $("#unsignoff-form-block").show();
                        $("#signoff-form-block").show();
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

                            $('#signoff-form-block').find("#signoff-emp-id-input").hide();
                            $('#unsignoff-form-block').hide();
                            isNotThirdPerson = false;
                        }
                        $('#employee-signoff-questions').removeClass('d-none');
                        if (!isSupervisor) {
                            $("#employee-signoff-questions").find('.sup-inputs').find('input').prop('disabled', true);
                            $("#employee-signoff-questions").find('.emp-inputs').find('input').prop('disabled', false);
                            $('#supervisor-signoff-questions').addClass('d-none');
                            //$('#employee-signoff-message').addClass('d-none');
                            $('#supervisor-signoff-message').removeClass('d-none');
                            $('#supervisor-signoff-message').find('.name').html(user2.participant.name);
                            $('#employee-signoff-message').find('.name').html(user1.participant.name);
                            $('#employee-signoff-message').find('.time').html("on " + result.sign_off_time);
                            $('#supervisor-signoff-message').find('.time').html("on " + result.supervisor_signoff_time);
                            $("textarea.supervisor-comment").addClass('enable-not-allowed').prop('readonly', true);
                            
                            if (result.conversation_topic_id == 3) {
                                $("textarea.info_comment2").addClass('enable-not-allowed').prop('readonly', true); 
                                $("textarea.info_comment6").addClass('enable-not-allowed').prop('readonly', true);
                            }
                            
                        } else {
                            $("#team_member_agreement").prop('disabled', true);

//                             $('#employee-signoff-questions').addClass('d-none');
                            $("#employee-signoff-questions").find('.sup-inputs').find('input').prop('disabled', false);
                            $("#employee-signoff-questions").find('.emp-inputs').find('input').prop('disabled', true);

                            $('#supervisor-signoff-questions').removeClass('d-none');
                            $('#employee-signoff-message').removeClass('d-none');
                            // $('#supervisor-signoff-message').addClass('d-none');
                            $('#supervisor-signoff-message').find('.name').html(user1.participant.name);
                            $('#employee-signoff-message').find('.name').html(user2.participant.name);
                            $('#employee-signoff-message').find('.time').html("on " + result.sign_off_time);
                            $('#supervisor-signoff-message').find('.time').html("on " + result.supervisor_signoff_time);
                            $("textarea.employee-comment").addClass('enable-not-allowed').prop('readonly', true);
                            
                            if (result.conversation_topic_id == 3) {
                                $("textarea.info_comment1").addClass('enable-not-allowed').prop('readonly', true);    
                                $("textarea.info_comment3").addClass('enable-not-allowed').prop('readonly', true);
                            }
                        }

                        $("#employee-sign_off_form").find('input:radio[name="check_one"][value="'+result.empl_agree1+'"]').prop('checked', true);
                        $("#employee-sign_off_form").find('input:radio[name="check_two"][value="'+result.empl_agree2+'"]').prop('checked', true);
                        $("#employee-sign_off_form").find('input:radio[name="check_three"][value="'+result.empl_agree3+'"]').prop('checked', true);

                        $("#employee-signoff-questions").find('input:radio[name="check_one_"][value="'+result.supv_agree1+'"]').prop('checked', true);
                        $("#employee-signoff-questions").find('input:radio[name="check_two_"][value="'+result.supv_agree2+'"]').prop('checked', true);
                        $("#employee-signoff-questions").find('input:radio[name="check_three_"][value="'+result.supv_agree3+'"]').prop('checked', true);


                        if (!!result.supervisor_signoff_id) {
                            $('#supervisor-signoff-message').find('.not').addClass('d-none');
                            $('#supervisor-signoff-message').find('.time').removeClass('d-none');
                            $('#viewConversationModal').data('supervisor-signoff', 1);

                        }
                        else {
                            $('#supervisor-signoff-message').find('.not').removeClass('d-none');
                            $('#supervisor-signoff-message').find('.time').addClass('d-none');
                            $('#viewConversationModal').data('supervisor-signoff', 0);
                        }
                        if (!!result.signoff_user_id) {
                            $('#employee-signoff-message').find('.not').addClass('d-none');
                            $('#employee-signoff-message').find('.time').removeClass('d-none');
                            $('#viewConversationModal').data('employee-signoff', 1);
                        } else {
                            $('#employee-signoff-message').find('.not').removeClass('d-none');
                            $('#employee-signoff-message').find('.time').addClass('d-none');
                            $('#viewConversationModal').data('employee-signoff', 0);
                        }

                        if (result.signoff_user_id || result.supervisor_signoff_id) {
                            // Freeze content.
                            $("button.btn-conv-edit").hide();
                            $("button.btn-conv-save").hide();
                            $("button.btn-conv-cancel").hide();
                            $("#viewConversationModal").find('textarea').each((index, e) => $(e).prop('readonly', true));
                            $('#viewConversationModal').data('is-frozen', 1);
                            $("#team_member_agreement").prop('disabled', true);
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

                        //Is Locked
                        if (result.is_locked) {
                            $("#locked-message").removeClass("d-none");
                            $("#unsignoff-form-block").hide();
                            $("#signoff-form-block").hide();
                        }

                        //Additional Info to Capture
                        if (result.conversation_topic_id == 1) {
                          $("#info_capture1").html('<span>Appreciation (optional) - highlight what has gone well </span><i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="hover" data-content="Provide an overview of the actions or results being celebrated. Be as specific as possible about timing, activities, and outcomes achieved. Highlight behaviours, competencies, and corporate values that you feel contributed to the success." ></i>');
                          $("#info_capture2").html('<span>Coaching (optional) - identify areas where things could be (even) better </span><i class="fas fa-info-circle" data-toggle="popover" data-placement="right" data-trigger="hover" data-content="Provide specific examples of actions, outcomes or behaviours where there is opportunity for growth. Capture information on any additional assistance or training offered to support improvement."></i>');
                          $("#info_capture3").html('<span>Evaluation (optional) - provide an overall summary of performance</span> <i class="fas fa-info-circle" data-toggle="popover" data-placement="right" data-trigger="hover" data-content="Be as specific as possible, use examples, and focus on observable behaviours and business results"></i>');
                        }
                        if (result.conversation_topic_id == 3) {
                          $('#info_capture1').html('<span>Strengths (optional) – identify your top 1 to 3 strengths</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="hover" data-content="Employee to indicate areas of strength to build on for career advancement." ></i>');
                          $('#info_capture2').html('<span>Supervisor Comments (optional) – provide feedback on strength(s) identified by employee above</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="hover" data-content="Supervisor to comment on strengths identified by employee, note additional areas of strength as required, and provide examples where appropriate." ></i>');
                          $('#info_capture3').html('<span>Areas for Growth (optional) – identify 1 to 3 areas you’d most like to grow over the next two years</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="hover" data-content="Employee to indicate areas for growth in the short to medium term to assist with career advancement." ></i>');
                          $('#info_capture4').html('<span>Supervisor Comments (optional) – provide feedback on area(s) for growth identified by employee above</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="hover" data-content="Supervisor to comment on areas for growth identified by employee, note additional areas of growth as required, and provide examples where appropriate." ></i>');
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
                        $('#questions-to-consider').append(result.questions);

                        // result.questions
                        $('#template-title').text(result.topic.name + ' Template');
                        // $('#conv_participant_edit').next(".select2-container").hide();

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

        </script>
    </x-slot>

</x-side-layout>
