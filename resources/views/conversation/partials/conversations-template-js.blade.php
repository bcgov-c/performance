       
    
       <script>        
            // Function to initialize CKEditor for a given textarea ID
            var ckeditorInstances = {};
            function initCKEditor(textareaId) {
                if (!ckeditorInstances[textareaId]) {
                    // If the instance is not yet initialized, create a new instance
                    ckeditorInstances[textareaId] = CKEDITOR.replace(textareaId, {
                        toolbar: "Custom",
                        toolbar_Custom: [
                            ["Bold", "Italic", "Underline"],
                            ["NumberedList", "BulletedList"],
                            ["Outdent", "Indent"],
                            ["Link"],
                        ],
                        disableNativeSpellChecker: false
                    });
                } else {
                    // If the instance is already initialized, check if it's ready
                    if (ckeditorInstances[textareaId].status === "ready") {
                        // The instance is fully loaded and ready to use
                        return;
                    } else {
                        // The instance is still loading, try to reinitialize after a short delay
                        setTimeout(function () {
                            initCKEditor(textareaId);
                        }, 200); // Adjust the delay time as needed (e.g., 200ms)
                    }
                }
            }

            function updateConversation(template_id) {
                $.ajax({
                        url: '/conversation-template/' + template_id
                        , success: function(result) {
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

                        $('#conv_title').html(result.data.name);    
                        $('#conv_participant').html('Participant A, Participant B');  
                        $('#preparing-for-conversation').html(result.data.preparing_for_conversation);   
                        $('#questions-to-consider').html(result.data.question_html);   


                        if(template_id == 1){
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
                            
                            
                        }else if(template_id == 2){
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
                                
                        }else if(template_id == 3){
                            
                            
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
                            
                            
                        }else if(template_id == 4){
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
                            
                                
                        }else if(template_id == 5){
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
                        
                        //Additional Info to Capture
                        if (template_id == 1) {
                                $("#info_capture1").html('<span>Appreciation (optional) - supervisor to highlight what has gone well </span><i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Provide an overview of the actions or results being celebrated. Be as specific as possible about timing, activities, and outcomes achieved. Highlight behaviours, competencies, and corporate values that you feel contributed to the success." ></i>');
                                $("#info_capture2").html('<span>Coaching (optional) - supervisor to identify areas where things could be (even) better </span><i class="fas fa-info-circle" data-toggle="popover" data-placement="right" data-trigger="click" data-content="Provide specific examples of actions, outcomes or behaviours where there is opportunity for growth. Capture information on any additional assistance or training offered to support improvement."></i>');
                                $("#info_capture3").html('<span>Evaluation (optional) - supervisor to provide an overall summary of performance</span> <i class="fas fa-info-circle" data-toggle="popover" data-placement="right" data-trigger="click" data-content="Be as specific as possible, use examples, and focus on observable behaviours and business results"></i>');
                        }
                        if (template_id == 3) {
                                $('#info_capture1').html('<span>Strengths (optional) identify your top 1 to 3 strengths</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Employee to indicate areas of strength to build on for career advancement." ></i>');
                                $('#info_capture2').html('<span>Supervisor Comments (optional) provide feedback on strength(s) identified by employee above</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Supervisor to comment on strengths identified by employee, note additional areas of strength as required, and provide examples where appropriate." ></i>');
                                $('#info_capture3').html('<span>Areas for Growth (optional) identify 1 to 3 areas you most like to grow over the next two years</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Employee to indicate areas for growth in the short to medium term to assist with career advancement." ></i>');
                                $('#info_capture4').html('<span>Supervisor Comments (optional) provide feedback on area(s) for growth identified by employee above</span> <i class="fas fa-info-circle"  data-toggle="popover" data-placement="right" data-trigger="click" data-content="Supervisor to comment on areas for growth identified by employee, note additional areas of growth as required, and provide examples where appropriate." ></i>');
                        }
                        if (template_id == 4) {
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
                    }
                });  
            }      
            
            
            $( document ).ready(function() {      
                initCKEditor('info_comment1');
                initCKEditor('info_comment2');
                initCKEditor('info_comment3');
                initCKEditor('info_comment4');
                initCKEditor('info_comment5');
                initCKEditor('info_comment6');
                initCKEditor('info_comment7');
                initCKEditor('info_comment8');
                initCKEditor('info_comment9');
                initCKEditor('info_comment10');    

                $('#viewConversationModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget); 
                    var templateId = button.data('id'); 
                    updateConversation(templateId);
                    var modal = $(this);
                    modal.find('#collapse_1_modal').collapse("hide");
                    modal.find('#collapse_2_modal').collapse("hide");
                    modal.find('#templateId').val(templateId);

                    // Disable comments
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
    
                    // Disable radio buttons and checkboxes
                    modal.find('input[type="radio"]').prop('disabled', true);
                    modal.find('input[type="checkbox"]').prop('disabled', true);
                });


            });

            document.getElementById("closemodal").onclick = function(e) {
                $("#viewConversationModal").modal("hide");
                $("#viewConversationModal").find('#collapse_1_modal').collapse("hide");
                $("#viewConversationModal").find('#collapse_2_modal').collapse("hide");
            };



            
             
        </script>    
        
