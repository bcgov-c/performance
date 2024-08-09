<div class="modal fade" id="viewConversationModal" aria-labelledby="addModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static" role="dialog" tabindex="0">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary" tabindex="0">
                <h5 id="template-header"></h5>
                    <div tabindex="0" aria-describedby="closemodal">
                        <button type="button" class="close" id="closemodal" aria-label="Close" style="color:white">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <input type="hidden" name="viewmode" id="viewmode" value="0">
            </div>
            
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-default-danger error-date-alert" style="display:none">
                            <span class="h5"><i class="icon fas fa-exclamation-circle"></i>
                                <span class="error-date">
                                    Conversations must be scheduled every four months, at minimum.
                                </span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-6" tabindex="0">
                        <div class="d-flex align-items-end row">
                            <div>
                                <label>Topic</label>
                                <span id="conv_title" class="conv_title"></span>
                                <select id="conv_title_edit" name="conversation_topic_id" class="form-control conv_title d-none">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-6" tabindex="0">
                        <div class="d-flex align-items-end row">
                            <div class="col-md-9">
                                <label>Participants</label>
                                <span id="conv_participant" class="conv_participant font-weight-bold"></span>
                                <div class="conv_participant  d-none">
                                    <select class="form-control conv_participant_edit select2 w-100" style="width:100%" multiple name="conversation_participant_id[]" id="conv_participant_edit">

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if ($type == 'upcoming' && 1 === 0)
                                <x-edit-cancel-save name="conversation_participant_id" id="conv_participant" />
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <hr>
                
                
                
                <div class="card"  id="pfc_card" tabindex="0">
                    <div class="card-header panel-heading bg-primary" id="heading_2">
                        <h5 class="mb-0"data-toggle="collapse" data-target="#collapse_2" aria-expanded="false" aria-controls="collapse_2">
                            <button class="btn btn-link" style="color:white">
                                <span class="acc-title">Preparing For The Conversation</span>
                                <span class="acc-status"  id="caret_2"><i class="fas fa-caret-down"></i></span>                                
                            </button>
                        </h5>
                    </div>

                    <div id="collapse_2" class="collapse" aria-labelledby="heading_2">
                        <div class="card-body">
                            <div id="preparing-for-conversation" class="p-3"> </div>
                        </div>
                    </div>
                </div>
                
                
                <div class="card" id="sdq_card" tabindex="0">
                    <div class="card-header panel-heading bg-primary" id="heading_1">
                        <h5 class="mb-0"data-toggle="collapse" data-target="#collapse_1" aria-expanded="false" aria-controls="collapse_1">
                                <button class="btn btn-link" style="color:white">
                                <span class="acc-title">Suggested Discussion Questions</span>
                                <span class="acc-status" id="caret_1"><i class="fas fa-caret-down"></i></span>
                                </button>
                        </h5>
                    </div>

                    <div id="collapse_1" class="collapse" aria-labelledby="heading_1" tabindex="0">
                        <div class="card-body">
                            <div id="questions-to-consider" class="p-3"> </div>
                        </div>
                    </div>
                </div>
                
                <!-----employee comments: 4,7,8,9,10--------->
                <!-----supervisor comments: 1,2,3,5,6,11--------->


                @if ($type == 'upcoming')
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success send-notification-info-top" attr-loc="top"  role="alert" style="display:none">
                                Notifications to other participants have been sent.
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-success sup-comment-save-info-top" attr-loc="top"  role="alert" style="display:none">
                                Comments are saved.
                            </div>
                            <div class="alert alert-success emp-comment-save-info-top" attr-loc="top"  role="alert" style="display:none">
                                Comments are saved.
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div tabindex="0">
                                <button type="button" class="btn btn-primary float-right notifyParticipants  notifyParticipantsSup ml-1" attr-loc="top" style="display:none">
                                    <i class="fa fa-info-circle notifyParticipantsInfo" data-trigger="hover" data-toggle="popover" data-placement="right" data-html="true" 
                                        data-content="Use this button to alert the other participant that you have made updates to this conversation." 
                                        data-original-title="" title="" aria-describedby="popover271882"> 
                                    </i>
                                    Send Notification
                                </button>
                            </div>
                            <div tabindex="0">
                                <button type="button" class="btn btn-primary float-right saveAllComments supSaveAllComments" attr-loc="top" style="display:none">Save Comments</button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary float-left saveAllComments empSaveAllComments" attr-loc="top"  style="display:none">Save Comments</button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary float-left notifyParticipants notifyParticipantsEmp ml-1" attr-loc="top" style="display:none">
                                    <i class="fa fa-info-circle notifyParticipantsInfo" data-trigger="hover" data-toggle="popover" data-placement="right" data-html="true" 
                                        data-content="Use this button to alert the other participant that you have made updates to this conversation." 
                                        data-original-title="" title="" aria-describedby="popover271882"> 
                                    </i>
                                    Send Notification
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr>
                @endif   


                
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                                <div class="card-header panel-heading bg-primary" tabindex="0">
                                Employee Comments
                                </div>                                

                                <div class="card-body">
                                    <div id="div-info-comment4">
                                        <h6 id="tip-info-comment4"></h6>
                                        <span id="desc-info-comment4"></span>
                                        <span id="control-info-comment4" style="display:none"><br/><span id="info_area4"></span></span>
                                        <div class="row">
                                            <div class="col-md-12" tabindex="0">
                                                <textarea class="form-control info_comment4 mb-4 employee-comment btn-conv-edit" data-name="info_comment4" data-id="info_comment4" name="info_comment4" id="info_comment4" aria-multiline="true" aria-label="Unknown Box" aria-required="false" aria-labelledby="tip-info-comment4" aria-describedby="tip-info-comment4"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="div-info-comment7">                                        
                                        <br/>
                                        <h6 id="tip-info-comment7"></h6>
                                        <span id="desc-info-comment7"></span>
                                        <span id="control-info-comment7" style="display:none"><br/><span id="info_area7"></span></span>
                                        <div class="row">
                                            <div class="col-md-12" tabindex="0">
                                                <textarea class="form-control info_comment7 mb-4 employee-comment btn-conv-edit" data-name="info_comment7" data-id="info_comment7" name="info_comment7" id="info_comment7"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="div-info-comment8">                                        
                                        <br/>
                                        <h6 id="tip-info-comment8"></h6>
                                        <span id="desc-info-comment8"></span>
                                        <span id="control-info-comment8" style="display:none"><br/><span id="info_area8"></span></span>
                                        <div class="row">
                                            <div class="col-md-12" tabindex="0">
                                                <textarea class="form-control info_comment8 mb-4 employee-comment btn-conv-edit" data-name="info_comment8" data-id="info_comment8" name="info_comment8" id="info_comment8"></textarea>
                                            </div>
                                        </div>    
                                    </div>
                                    <div id="div-info-comment9">
                                        <br/>
                                        <h6 id="tip-info-comment9"></h6>
                                        <span id="desc-info-comment9"></span>
                                        <span id="control-info-comment9" style="display:none"><br/><span id="info_area9"></span></span>
                                        <div class="row">
                                            <div class="col-md-12" tabindex="0">
                                                <textarea class="form-control info_comment9 mb-4 employee-comment btn-conv-edit" data-name="info_comment9" data-id="info_comment9" name="info_comment9" id="info_comment9"></textarea>
                                            </div>
                                        </div>    
                                    </div>
                                    <div id="div-info-comment10">
                                        <br/>
                                        <h6 id="tip-info-comment10"></h6>
                                        <span id="desc-info-comment10"></span>
                                        <span id="control-info-comment10" style="display:none"><br/><span id="info_area10"></span></span>
                                        <div class="row">
                                            <div class="col-md-12" tabindex="0">
                                                <textarea class="form-control info_comment10 mb-4 employee-comment btn-conv-edit" data-name="info_comment10" data-id="info_comment10" name="info_comment10" id="info_comment10"></textarea>
                                            </div>
                                        </div>    
                                    </div>
                                </div>
                        </div>
                        @if ($type == 'upcoming')
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-success send-notification-info-emp" attr-loc="bottom"  role="alert" style="display:none">
                                        Notifications to other participants have been sent.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-success emp-comment-save-info" attr-loc="bottom" role="alert" style="display:none">
                                        Comments are saved.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary float-left saveAllComments empSaveAllComments" attr-loc="bottom" style="display:none">Save Comments</button>
                                    <button type="button" class="btn btn-primary float-left notifyParticipants notifyParticipantsEmp ml-1" attr-loc="bottom" style="display:none">
                                        <i class="fa fa-info-circle notifyParticipantsInfo" data-trigger="hover" data-toggle="popover" data-placement="right" data-html="true" 
                                            data-content="Use this button to alert the other participant that you have made updates to this conversation." 
                                            data-original-title="" title="" aria-describedby="popover271882"> 
                                        </i>
                                        Send Notification
                                    </button>
                                </div>
                            </div>
                            <hr>
                        @endif    
                    </div>
                
                    <div class="col-6">
                        <div class="card">
                               <div class="card-header panel-heading bg-primary">
                                Supervisor Comments
                                </div>

                                <div class="card-body">
                                    <div class="row"  id="div-info-comment1">
                                        <div class="col-12">
                                            <h6 id="tip-info-comment1"></h6>                                    
                                            <span id="desc-info-comment1"></span>
                                            <span id="control-info-comment1" style="display:none"><br/><span id="info_area1"></span></span>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <textarea class="form-control supervisor-comment info_comment1 mb-4 btn-conv-edit" name="info_comment1" id="info_comment1" data-id="info_comment1" data-name="info_comment1"></textarea>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div>
                                    <div class="row"  id="div-info-comment2">
                                        <div class="col-12">   
                                            <br/>
                                            <h6 id="tip-info-comment2"></h6>
                                            <span id="desc-info-comment2"></span>
                                            <span id="control-info-comment2" style="display:none"><br/><span id="info_area2"></span></span>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <textarea class="form-control supervisor-comment info_comment2 mb-4 btn-conv-edit" name="info_comment2" id="info_comment2" data-id="info_comment2" data-name="info_comment2"></textarea>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div>
                                    <div class="row"  id="div-info-comment3">                                     
                                        <div class="col-12">   
                                            <br/>
                                            <h6 id="tip-info-comment3"></h6>
                                            <span id="desc-info-comment3"></span>
                                            <span id="control-info-comment3" style="display:none"><br/><span id="info_area3"></span></span>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <textarea class="form-control supervisor-comment info_comment3 mb-4 btn-conv-edit" name="info_comment3" id="info_comment3" data-id="info_comment3" data-name="info_comment3"></textarea>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div>
                                    <div class="row" id="div-info-comment5">
                                        <div class="col-12">
                                            <br/>   
                                            <h6 id="tip-info-comment5"></h6>
                                            <span id="desc-info-comment5"></span>
                                            <span id="control-info-comment5" style="display:none"><br/><span id="info_area5"></span></span>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <textarea class="form-control supervisor-comment info_comment5 mb-4 btn-conv-edit" name="info_comment5" id="info_comment5" data-id="info_comment5" data-name="info_comment5"></textarea>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div>
                                    <div class="row"  id="div-info-comment6">
                                        <div class="col-12">   
                                            <br/>
                                            <h6 id="tip-info-comment6"></h6>
                                            <span id="desc-info-comment6"></span>
                                            <span id="control-info-comment6" style="display:none"><br/><span id="info_area6"></span></span>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <textarea class="form-control supervisor-comment info_comment6 mb-4 btn-conv-edit" name="info_comment6" id="info_comment6" data-id="info_comment6" data-name="info_comment6"></textarea>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div>
                                    <div class="row"  id="div-info-comment11">
                                        <div class="col-12">
                                            <br/>   
                                            <h6 id="tip-info-comment11"></h6>
                                            <span id="desc-info-comment11"></span>
                                            <span id="control-info-comment11" style="display:none"><br/><span id="info_area11"></span></span>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input class="form-control form-control-md supervisor-comment info_comment11 mb-4 " type="date" name="info_comment11" id="info_comment11" data-id="info_comment11" data-name="info_comment11">                                                 
                                                </div>
                                            </div> 
                                        </div> 
                                    </div>
                                </div>
                        </div>                                 
                        @if ($type == 'upcoming')
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-success send-notification-info-sup" attr-loc="bottom"  role="alert" style="display:none">
                                        Notifications to other participants have been sent.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-success sup-comment-save-info" attr-loc="bottom" role="alert" style="display:none">
                                        Comments are saved.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary float-right notifyParticipants notifyParticipantsSup ml-1" attr-loc="bottom" style="display:none">
                                        <i class="fa fa-info-circle notifyParticipantsInfo" data-trigger="hover" data-toggle="popover" data-placement="right" data-html="true" 
                                            data-content="Use this button to alert the other participant that you have made updates to this conversation." 
                                            data-original-title="" title="" aria-describedby="popover271882"> 
                                        </i>
                                        Send Notification
                                    </button>
                                    <button type="button" class="btn btn-primary float-right saveAllComments supSaveAllComments" attr-loc="bottom" style="display:none">Save Comments</button>
                                </div>
                            </div>
                            <hr>
                        @endif             
                    </div>                                      
                </div>
                
                <div id="sign-off-block">
                <form id="employee-sign_off_form" method="post">
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                               <div class="card-header panel-heading bg-primary">
                                Employee Sign-Off
                                </div>                                
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <p><b>We have reviewed goals and adjusted as necessary.</b></p>
                                            <p><input class="employee-sign-off employee-sign-off1" type="radio" name="check_one" value="1"> Yes</p>
                                            <p><input class="employee-sign-off employee-sign-off1"  type="radio" name="check_one" value="0"> No</p>
                                            <br/>
                                            <p><b>Performance expectations are clear.</b></p>
                                            <p><input class="employee-sign-off employee-sign-off2" type="radio" name="check_two" value="1"> Yes</p>
                                            <p><input class="employee-sign-off employee-sign-off2" type="radio" name="check_two" value="0"> No</p>
                                            <br/>
                                        </div>
                                    </div>
                                            
                                        <!-- <h3 id="id-group-label">Disagree</h3> -->
                                        <!-- <div class="mt-3" role="group" aria-labelledby="id-group-label" tabindex="0">

                                            <ul class="checkboxes">
                                                <label style="font-weight: normal;">
                                                    <li><input type="checkbox" class="team_member_agreement" name="team_member_agreement" id="signoff_team_member_agreement" value="1" aria-label="Team member disagrees with the information contained in this performance review">&nbsp;Team member disagrees with the information contained in this performance review.</li>
                                                </label>
                                            </ul>
                                            <p><span class="agree-message text-danger error"></span></p>

                                        </div> -->

                                        <div class="mt-3">
                                                <label style="font-weight: normal;">
                                                    <div tabindex="0"><input type="checkbox" class="team_member_agreement" name="team_member_agreement" id="signoff_team_member_agreement" value="1" aria-label="Team member disagrees with the information contained in this performance review"></div>&nbsp;Team member disagrees with the information contained in this performance review.
                                                </label>
                                                <p><span class="agree-message text-danger error"></span></p>
                                        </div>
                                            
                                        <div id="emp-signoff-row">                                                
                                            <div id="signoff-emp-id-input" arial-label="Enter employee ID to sign"></div>                                            
                                        </div> 
                                        <div class="mt-3 alert alert-default-warning alert-dismissible" id="employee-signoff-message">
                                                <span class="h5"><i class="icon fas fa-exclamation-circle"></i><b class="name"></b> has <b class="not d-none">not</b> signed this record of conversation <span class="emp-time"></span></span>
                                        </div>
                                </div>
                        </div>                        
                    </div>
                    <div class="col-6">
                        <div class="card">
                               <div class="card-header panel-heading bg-primary">
                                Supervisor Sign-Off
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <p><b>We have reviewed goals and adjusted as necessary.</b></p>
                                            <p><input class="supervisor-sign-off supervisor-sign-off1" type="radio" name="check_one_" value="1"> Yes</p>
                                            <p><input class="supervisor-sign-off supervisor-sign-off1" type="radio" name="check_one_" value="0"> No</p>
                                            <br/>
                                            <p><b>Performance expectations are clear.</b></p>
                                            <p><input class="supervisor-sign-off supervisor-sign-off2" type="radio" name="check_two_" value="1"> Yes</p>
                                            <p><input class="supervisor-sign-off supervisor-sign-off2" type="radio" name="check_two_" value="0"> No</p>
                                            <br/>
                                            <div id="sup-signoff-row">
                                                <div id="signoff-sup-id-input"></div>
                                            </div>      
                                            <div class="mt-3 alert alert-default-warning alert-dismissible" id="supervisor-signoff-message">
                                                <span class="h5"><i class="icon fas fa-exclamation-circle"></i><b class="name"></b> has <b class="not d-none">not</b> signed this record of conversation <span class="sup-time"></span></span>
                                            </div>                                               
                                        </div> 
                                    </div>                                    
                                </div>
                        </div>                        
                    </div>
                </div>
                </form>    
                </div>
                
                <div id="unsign-off-block">
                <form id="unsign-off-form" data-action-url="{{ route('conversation.unsignoff', 'xxx')}}" method="post">    
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                               <div class="card-header panel-heading bg-primary">
                                Employee Unsign-Off
                                </div>                                
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <p><b>We have reviewed goals and adjusted as necessary.</b></p>
                                            <p><input class="employee-sign-off employee-sign-off1" type="radio" name="check_one" value="1"> Yes</p>
                                            <p><input class="employee-sign-off employee-sign-off1"  type="radio" name="check_one" value="0"> No</p>
                                            <br/>
                                            <p><b>Performance expectations are clear.</b></p>
                                            <p><input class="employee-sign-off employee-sign-off2" type="radio" name="check_two" value="1"> Yes</p>
                                            <p><input class="employee-sign-off employee-sign-off2" type="radio" name="check_two" value="0"> No</p>
                                            <br/>
                                            
                                            <div class="mt-3">
                                                    <label style="font-weight: normal;">
                                                        <div tabindex="0"><input type="checkbox" class="team_member_agreement" name="team_member_agreement" id="unsingoff-team_member_agreement" value="1"></div>&nbsp;Team member disagrees with the information contained in this performance review.
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div id="emp-unsignoff-row">                                                
                                                <div id="unsignoff-emp-id-input"></div>                                            
                                            </div> 
                                            <div class="mt-3 alert alert-default-warning alert-dismissible" id="employee-unsignoff-message">
                                                    <span class="h5"><i class="icon fas fa-exclamation-circle"></i><b class="name"></b> has <b class="not d-none">not</b> signed this record of conversation <span class="emp-time"></span></span>
                                            </div>
                                    </div>
                                </div>
                        </div>                        
                    </div>
                    <div class="col-6">
                        <div class="card">
                               <div class="card-header panel-heading bg-primary">
                                Supervisor Unsign-Off
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <p><b>We have reviewed goals and adjusted as necessary.</b></p>
                                            <p><input class="supervisor-sign-off supervisor-sign-off1" type="radio" name="check_one_" value="1"> Yes</p>
                                            <p><input class="supervisor-sign-off supervisor-sign-off1" type="radio" name="check_one_" value="0"> No</p>
                                            <br/>
                                            <p><b>Performance expectations are clear.</b></p>
                                            <p><input class="supervisor-sign-off supervisor-sign-off2" type="radio" name="check_two_" value="1"> Yes</p>
                                            <p><input class="supervisor-sign-off supervisor-sign-off2" type="radio" name="check_two_" value="0"> No</p>
                                            <br/>
                                            <div id="sup-unsignoff-row">
                                                <div id="unsignoff-sup-id-input"></div>
                                            </div>      
                                            <div class="mt-3 alert alert-default-warning alert-dismissible" id="supervisor-unsignoff-message">
                                                <span class="h5"><i class="icon fas fa-exclamation-circle"></i><b class="name"></b> has <b class="not d-none">not</b> signed this record of conversation <span class="sup-time"></span></span>
                                            </div>                                               
                                        </div> 
                                    </div>                                    
                                </div>
                        </div>                        
                    </div>
                </div>
                </form>    
                </div>   
                
            </div>
        </div>
    </div>


    <div class="modal fade" id="unsavedChangesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index:99"> 
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Unsaved Changes</h5>
            </div>
            <div class="modal-body">
                <p>Save changes to this conversation?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveChangesBtn">Save Changes</button>
                <button type="button" class="btn btn-secondary" id="discardChangesBtn">Don't Save</button>
                <button type="button" class="btn btn-secondary" id="cancelChangesBtn">Cancel</button>
            </div>
            </div>
        </div>
    </div>

</div>
