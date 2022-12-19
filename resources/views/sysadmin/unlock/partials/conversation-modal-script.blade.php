

@php
$authId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
$user = App\Models\User::find($authId);
@endphp
var isSupervisor = {{$user->hasRole('Supervisor') ? 'true' : 'false'}};
var currentUser = {{$authId}};
var conversation_id = 0;
var toReloadPage = false;



  $(function() {


    $(document).on('click', '.btn-view-conversation', function(e) {
        console.log('testing');
                conversation_id = e.currentTarget.getAttribute('data-id');
                updateConversation(conversation_id);
            });
    });

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
            }
            if (result.conversation_topic_id == 4) {
              $("#info_capture1").html("What date will a follow up meeting occur?");
              $("#info_capture2").html("What must the employee accomplish? By when?");
              $("#info_capture3").html("What support will the supervisor (and others) provide? By when?");
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
