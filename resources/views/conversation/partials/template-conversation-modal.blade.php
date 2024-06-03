<div class="modal fade" id="viewConversationModal" aria-labelledby="addModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 id="template-header"></h5>
                <button type="button" class="close" id="closemodal" aria-label="Close" style="color:white">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                    <div class="col-6 col-md-6">
                        <div class="d-flex align-items-end row">
                            <div>
                                <label>Topic</label>
                                <span id="conv_title" class="conv_title"></span>
                                <select id="conv_title_edit" name="conversation_topic_id" class="form-control conv_title d-none">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-6">
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
                
                
                
                <div class="card"  id="pfc_card">
                        <div class="card-header panel-heading bg-primary" id="heading_2"  style="opacity: 0.5;">
                        <h5 class="mb-0"data-toggle="collapse" data-target="#collapse_2_modal" aria-expanded="false" aria-controls="collapse_2_modal">
                                <button class="btn btn-link" style="color:white">
                                    <span class="acc-title">Preparing For The Conversation</span>
                                    <span class="acc-status"  id="caret_2"><i class="fas fa-caret-down"></i></span>                                
                                </button>
                        </h5>
                        </div>

                        <div id="collapse_2_modal" class="collapse" aria-labelledby="heading_2">
                        <div class="card-body">
                            <div id="preparing-for-conversation" class="p-3"> </div>
                        </div>
                        </div>
                </div>
                
                
                <div class="card" id="sdq_card">
                        <div class="card-header panel-heading bg-primary" id="heading_1" style="opacity: 0.5;">
                        <h5 class="mb-0"data-toggle="collapse" data-target="#collapse_1_modal" aria-expanded="false" aria-controls="collapse_1_modal">
                                <button class="btn btn-link" style="color:white">
                                <span class="acc-title">Suggested Discussion Questions</span>
                                <span class="acc-status" id="caret_1"><i class="fas fa-caret-down"></i></span>
                                </button>
                        </h5>
                        </div>

                        <div id="collapse_1_modal" class="collapse" aria-labelledby="heading_1">
                        <div class="card-body">
                            <div id="questions-to-consider" class="p-3"> </div>
                        </div>
                        </div>
                </div>
                
                <!-----employee comments: 4,7,8,9,10--------->
                <!-----supervisor comments: 1,2,3,5,6,11--------->
                
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                                <div class="card-header panel-heading bg-primary"  style="opacity: 0.5;">
                                Employee Comments
                                </div>

                                <div class="card-body">
                                    <div id="div-info-comment4">
                                        <h6 id="tip-info-comment4"></h6>
                                        <span id="desc-info-comment4"></span>
                                        <span id="control-info-comment4" style="display:none"><br/><span id="info_area4"></span></span>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <textarea class="form-control info_comment4 mb-4 employee-comment btn-conv-edit" data-name="info_comment4" data-id="info_comment4" name="info_comment4" id="info_comment4"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="div-info-comment7">                                        
                                        <br/>
                                        <h6 id="tip-info-comment7"></h6>
                                        <span id="desc-info-comment7"></span>
                                        <span id="control-info-comment7" style="display:none"><br/><span id="info_area7"></span></span>
                                        <div class="row">
                                            <div class="col-md-12">
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
                                            <div class="col-md-12">
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
                                            <div class="col-md-12">
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
                                            <div class="col-md-12">
                                                <textarea class="form-control info_comment10 mb-4 employee-comment btn-conv-edit" data-name="info_comment10" data-id="info_comment10" name="info_comment10" id="info_comment10"></textarea>
                                            </div>
                                        </div>    
                                    </div>
                                </div>
                        </div>
                        
                    </div>
                
                    <div class="col-6">
                        <div class="card">
                               <div class="card-header panel-heading bg-primary"  style="opacity: 0.5;">
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
                        
                    </div>
                
                </div>
                
                <div id="sign-off-block">
                <form id="employee-sign_off_form" method="post">
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                               <div class="card-header panel-heading bg-primary"  style="opacity: 0.5;">
                                Employee Sign-Off
                                </div>                                
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <p><b>We have reviewed goals and adjusted as necessary.</b></p>
                                            <p><input class="employee-sign-off1" type="radio" name="check_one" value="1"> Yes</p>
                                            <p><input class="employee-sign-off1"  type="radio" name="check_one" value="0"> No</p>
                                            <br/>
                                            <p><b>Performance expectations are clear.</b></p>
                                            <p><input class="employee-sign-off2" type="radio" name="check_two" value="1"> Yes</p>
                                            <p><input class="employee-sign-off2" type="radio" name="check_two" value="0"> No</p>
                                            <br/>
                                            
                                            <div class="mt-3">
                                                    <label style="font-weight: normal;">
                                                        <div tabindex="0"><input type="checkbox" class="team_member_agreement" name="team_member_agreement" id="signoff_team_member_agreement" value="1" aria-label="Team member disagrees with the information contained in this performance review"></div>&nbsp;Team5 member disagrees with the information contained in this performance review.
                                                    </label>
                                                    <p><span class="agree-message text-danger error"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>                        
                    </div>
                    <div class="col-6">
                        <div class="card">
                               <div class="card-header panel-heading bg-primary"  style="opacity: 0.5;">
                                Supervisor Sign-Off
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <p><b>We have reviewed goals and adjusted as necessary.</b></p>
                                            <p><input class="supervisor-sign-off1" type="radio" name="check_one_" value="1"> Yes</p>
                                            <p><input class="supervisor-sign-off1" type="radio" name="check_one_" value="0"> No</p>
                                            <br/>
                                            <p><b>Performance expectations are clear.</b></p>
                                            <p><input class="supervisor-sign-off2" type="radio" name="check_two_" value="1"> Yes</p>
                                            <p><input class="supervisor-sign-off2" type="radio" name="check_two_" value="0"> No</p>
                                            <br/>                                           
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

</div>
