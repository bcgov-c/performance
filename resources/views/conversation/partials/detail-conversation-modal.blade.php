function updateConversation(conversation_id) {
                $.ajax({
                    url: '/conversation/' + conversation_id
                    , success: function(result) {
                        comment_changed = false;
                        modal_open=true;
                        is_viewer = result.is_viewer;
                        isSupervisor = result.view_as_supervisor;
                        topic_id = result.topic.id;
                        disable_signoff = result.disable_signoff;                        
                        is_locked = result.is_locked;
                        
                        type = 'current';
                        @if($type == 'past')
                            type = 'past';
                        @endif
                        
                        if(type == 'past') {
                            $('#sdq_card').hide();
                            $('#pfc_card').hide();
                        }
                                
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
                        
                        //set comments based on topic id. for swtich old template to new version
                        var comment1 = result.info_comment1;
                        var comment2 = result.info_comment2;
                        var comment3 = result.info_comment3;
                        var comment4 = result.info_comment4;
                        var comment5 = result.info_comment5;
                        var comment6 = result.info_comment6;
                        var comment7 = result.info_comment7;
                        var comment8 = result.info_comment8;
                        var comment9 = result.info_comment9;
                        var comment10 = result.info_comment10;
                        var comment11 = result.info_comment11;
                        
                        db_info_comment1 = comment1;
                        db_info_comment2 = comment2;
                        db_info_comment3 = comment3;
                        db_info_comment4 = comment4;
                        db_info_comment5 = comment5;
                        db_info_comment6 = comment6;
                        db_info_comment7 = comment7;
                        db_info_comment8 = comment8;
                        db_info_comment9 = comment9;
                        db_info_comment10 = comment10;
                        db_info_comment11 = comment11;                        
                        
                        if (topic_id == 2 || topic_id == 4  || topic_id == 5) {
                            if (comment1 == '') {
                                comment1 = result.info_comment5;
                            }
                        }
                        if (topic_id == 3) {
                            if (comment7 == '') {
                                comment7 = result.info_comment1;
                            }
                            if (comment8 == '') {
                                comment8 = result.info_comment3;
                            }
                            if (comment9 == '') {
                                comment9 = result.info_comment4;
                            }
                            if (comment1 == '') {
                                comment1 = result.info_comment2;
                            }
                            if (comment2 == '') {
                                comment2 = result.info_comment6;
                            }
                            if (comment3 == '') {
                                comment3 = result.info_comment5;
                            }
                        }
                                                
                        CKEDITOR.instances['info_comment1'].setData(comment1);
                        CKEDITOR.instances['info_comment2'].setData(comment2);
                        CKEDITOR.instances['info_comment3'].setData(comment3);
                        CKEDITOR.instances['info_comment4'].setData(comment4);
                        CKEDITOR.instances['info_comment5'].setData(comment5);
                        CKEDITOR.instances['info_comment6'].setData(comment6);
                        CKEDITOR.instances['info_comment7'].setData(comment7);
                        CKEDITOR.instances['info_comment8'].setData(comment8);
                        CKEDITOR.instances['info_comment9'].setData(comment9);
                        CKEDITOR.instances['info_comment10'].setData(comment10);
                        $('#info_comment11').val(comment11);
                        
                        
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
                        

                        if (!isSupervisor) {
                            $('.empSaveAllComments').show();
                        } else {
                            $('.supSaveAllComments').show();
                        }  

                        $("#locked-message").addClass("d-none");
                        
                        user1 = result.conversation_participants.find((p) => p.role === 'emp');
                        user2 = result.conversation_participants.find((p) => p.role === 'mgr');
                        
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
                            $('#employee-signoff-message').find('.name').html(user1.participant.name);
                            $('#supervisor-signoff-message').find('.name').html(user2.participant.name);
                            
                            $('#employee-unsignoff-message').find('.name').html(user1.participant.name);
                            $('#supervisor-unsignoff-message').find('.name').html(user2.participant.name);
                        } else {
                            $('#employee-signoff-message').find('.name').html(user1.participant.name);
                            $('#supervisor-signoff-message').find('.name').html(user2.participant.name);
                            
                            $('#employee-unsignoff-message').find('.name').html(user1.participant.name);
                            $('#supervisor-unsignoff-message').find('.name').html(user2.participant.name);
                        }
                        
                        if(!is_viewer){
                            if (!isSupervisor) {
                                $('#viewmode').val(0);

                                                       
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
                                    $('#signoff-emp-id-input').html('<div id="emp-signoff-row"><div class="my-2">Enter employee ID to sign :</div><input type="text" id="employee_id" class="form-control d-inline w-50"><button class="btn btn-primary btn-sign-off ml-2" type="button">Sign with my employee ID</button><br><span class="text-danger error" data-error-for="employee_id"></span></div>');                                
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
                                $('#viewmode').val(1);

                                CKEDITOR.instances['info_comment1'].setReadOnly(false);
                                CKEDITOR.instances['info_comment2'].setReadOnly(false);
                                CKEDITOR.instances['info_comment3'].setReadOnly(false);
                                CKEDITOR.instances['info_comment5'].setReadOnly(false);
                                CKEDITOR.instances['info_comment6'].setReadOnly(false);
                                $('#info_comment11').prop('disabled', false);
                                $('.supervisor-sign-off').prop('disabled', false);

                                
                                $('.employee-sign-off').prop('disabled', true);
                                $('.team_member_agreement').prop('disabled', true);

                                if(supervisor_signed == false) {
                                    $('#signoff-sup-id-input').html('<div class="my-2">Enter employee ID to sign: </div><input type="text" id="employee_id" class="form-control d-inline w-50"><button class="btn btn-primary btn-sign-off ml-2" type="button">Sign with my employee ID</button><br><span class="text-danger error" data-error-for="employee_id"></span>');                                
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
                        } else {
                                $('#viewmode').val(0);
                                
                                $('#info_comment11').prop('disabled', true);
                                $('.supervisor-sign-off').prop('disabled', true);

                                
                                $('.employee-sign-off').prop('disabled', true);
                                $('.team_member_agreement').prop('disabled', true);

                                
                                $('.saveAllComments').prop('disabled', true);
                                $('.notifyParticipants').prop('disabled', true);
                                $('.saveAllComments').hide();
                                $('.notifyParticipants').hide();
                                
                                $('#unsign-off-block').html('');
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
                        } else {
                            <!-- $('.notifyParticipants').show(); -->
                        }      
                        
                        <?php if ($type == 'past'){ ?>
                        if(is_locked) {
                            $('#emp-signoff-row').hide();
                            $('#employee-signoff-message').show();
                            $('#sup-signoff-row').hide();
                            $('#supervisor-signoff-message').show();
                            $('#emp-unsignoff-row').hide();
                            $('#employee-unsignoff-message').show();
                            $('#sup-unsignoff-row').hide();
                            $('#supervisor-unsignoff-message').show();
                        }
                        <?php } ?>
                        
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
                           $('#desc-info-comment7').html('Identify your top 1 to 3 strengths.');
                           $('#tip-info-comment8').html('<b>Areas for Growth (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate areas for growth in the short to medium term to assist with career advancement."> </i>');
                           $('#desc-info-comment8').html('Identify 1 to 3 areas you\'d most like to grow.');
                           $('#tip-info-comment9').html('<b>Additional Comments (optional)</b>');
                           $('#tip-info-comment10').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activities and areas for further discussion. Consider creating a goal in My Goals to track progress."> </i>');
                           $('#desc-info-comment10').html('Caputre actions to take as a result of this conversation.');
                           
                           $('#tip-info-comment1').html('<b>Employee Strengths (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Comment on strengths identified by employee, note additonal areas of strength as required, and provide examples where appropriate."> </i>');
                           $('#desc-info-comment1').html('Provide feedback on strength(s) identified by employee.');
                           $('#tip-info-comment2').html('<b>Employee Growth (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Comment on areas for growth identified by employee, note additional areas of growth as required, and provide examples where appropriate."> </i>');
                           $('#desc-info-comment2').html('Provide feedback on area(s) for growth identified by employee.');
                           $('#tip-info-comment3').html('<b>Additional Comments (optional)</b>');
                           $('#tip-info-comment5').html('<b>Action Items (optional)</b> <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="Indicate follow-up activites and areas for further discussion. Consider creating a goal in My Goals for yourself or the Goal Bank for your employee to track progress."> </i>');
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
                        
                        //sign off time's
                        if(result.signoff_user_id && result.sign_off_time){
                            $(".emp-time").html(" at " + result.sign_off_time);
                        }
                        if(result.supervisor_signoff_id && result.supervisor_signoff_time){
                            $(".sup-time").html(" at " + result.supervisor_signoff_time);
                        }

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
                    }
                    , error: function(error) {
                        var errors = error.responseJSON.errors;
                    }
                });
            }
            