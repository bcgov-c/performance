<div class="modal fade" id="viewConversationModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                <div>
                    <h5 id="template-title"></h5>
                    <br>
                    <h6><u>Suggested Discussion Questions</u></h6>
                    <div id="questions-to-consider" class="p-3">

                    </div>
                </div>

                <div class=" d-none" id="info_to_capture">
                    <h6><u>Information to Capture</u></h6>
                    
                    <h6 id="info_capture1">What date will a follow up meeting occur?</h6>

                    <div class="row">
                        <div class="col-md-8">
                            <textarea class="form-control supervisor-comment info_comment1 mb-4 btn-conv-edit" name="info_comment1" id="info_comment1" data-id="info_comment1" data-name="info_comment1"></textarea>
                            <span id="info_comment1" class="info_comment1"></span>
                        </div>
                        <div class="col-md-4">
                            @if ($type == 'upcoming')
                            <x-edit-cancel-save name="info_comment1" id="info_comment1" hideEdit="true" />
                            @endif
                        </div>
                    </div>

                    <h6 id="info_capture2">What must the employee accomplish? By when?</h6>

                    <div class="row">
                        <div class="col-md-8">
                            <textarea class="form-control supervisor-comment info_comment2 mb-4 btn-conv-edit" name="info_comment2" id="info_comment2" data-id="info_comment2" data-name="info_comment2"></textarea>
                            <span id="info_comment2" class="info_comment2"></span>
                        </div>
                        <div class="col-md-4">
                            @if ($type == 'upcoming')
                            <x-edit-cancel-save name="info_comment2" id="info_comment2" hideEdit="true" />
                            @endif
                        </div>
                    </div>

                    <h6 id="info_capture3">What support will the supervisor (and others) provide? By when?</h6>
                    <div class="row">
                        <div class="col-md-8">
                           <textarea class="form-control supervisor-comment info_comment3 mb-4 btn-conv-edit" name="info_comment3" id="info_comment3" data-id="info_comment3" data-name="info_comment3"></textarea>

                        </div>
                        <div class="col-md-4">
                            @if ($type == 'upcoming')
                            <x-edit-cancel-save name="info_comment3" id="info_comment3" hideEdit="true" />
                            @endif
                        </div>
                    </div>
                    
                    <div id="comment_area6" style="display: none">
                    <h6 id="info_capture4" >Supervisor Comments (optional) – provide feedback on area(s) for growth identified by employee above </h6>
                    <div class="row" >
                        <div class="col-md-8">
                             <textarea class="form-control supervisor-comment info_comment6 mb-4 btn-conv-edit" name="info_comment6" id="info_comment6" data-id="info_comment6" data-name="info_comment6"></textarea>

                        </div>
                        <div class="col-md-4">
                             @if ($type == 'upcoming')
                             <x-edit-cancel-save name="info_comment6" id="info_comment6" hideEdit="true" />
                             @endif
                        </div>
                    </div>
                    </div>
                
                </div>

                <div>

                    <h6><u>Comments</u></h6>

                    <h6>Employee Comments and Action Items</h6>

                    <div class="row">
                        <div class="col-md-8">
                            <textarea class="form-control info_comment4 mb-4 employee-comment btn-conv-edit" data-name="info_comment4" data-id="info_comment4" name="info_comment4" id="info_comment4"></textarea>
                        </div>
                        <div class="col-md-4">
                            @if ($type == 'upcoming')
                            <x-edit-cancel-save name="info_comment4" id="info_comment4" hideEdit="true" />
                            @endif
                        </div>
                    </div>
                    <h6>Supervisor Comments and Action Items</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <textarea class="form-control info_comment5 mb-4 supervisor-comment btn-conv-edit" name="info_comment5" id="info_comment5" data-id="info_comment5" data-name="info_comment5"></textarea>
                            <span id="info_comment5" class="info_comment5"></span>
                        </div>
                        <div class="col-md-4">
                            @if ($type == 'upcoming')
                            <x-edit-cancel-save name="info_comment5" id="info_comment5" hideEdit="true" />
                            @endif
                        </div>
                    </div>
                </div>
                <hr>
                @if ($showSignoff ?? true)
                <div id="signoff-form-block">
                    <div id="employee-signoff-questions" class="d-none">
                        <h5><u>Sign-off</u></h5>
                        <div class="alert alert-default-danger common-error" style="display:none">
                            <span class="h5">

                            </span>
                        </div>
                        <form id="employee-sign_off_form" method="post">
                            @csrf
                            <table class="table table-borderless text-center">
                                <tr>
                                    <th></th>
                                    <th colspan="2" class="p-1 bg-dark border border-dark">Employee</th>
                                    <th class="p-1"></th>
                                    <th colspan="2" class="p-1 bg-dark border border-dark">Supervisor</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td class="border-left border-dark emp-inputs" style="width:85px">Yes</td>
                                    <td class="border-left border-right border-dark emp-inputs" style="width:85px">No</td>
                                    <td></td>
                                    <td class="border-left border-dark sup-inputs" style="width:85px">Yes</td>
                                    <td class="border-left border-right border-dark sup-inputs" style="width:85px">No</td>
                                </tr>
                                <tr>
                                    <td class="text-left">We reviewed progress of goals and adjusted as necessary.</td>
                                    <td class="border-left border-dark emp-inputs"><input type="radio" name="check_one" value="1"></td>
                                    <td class="border-left border-right border-dark emp-inputs"><input type="radio" name="check_one" value="0"></td>
                                    <td></td>
                                    <td class="border-left border-dark sup-inputs"><input type="radio" name="check_one_" value="1"></td>
                                    <td class="border-left border-right border-dark sup-inputs"><input type="radio" name="check_one_" value="0"></td>
                                </tr>
                                <tr>
                                    <td class="text-left">Performance expectations have been clearly communicated.</td>
                                    <td class="border border-top-0 border-dark emp-inputs"><input type="radio" name="check_two" value="1"></td>
                                    <td class="border border-top-0 border-dark emp-inputs"><input type="radio" name="check_two" value="0"></td>
                                    <td></td>
                                    <td class="border border-top-0 border-dark sup-inputs"><input type="radio" name="check_two_" value="1"></td>
                                    <td class="border border-top-0 border-dark sup-inputs"><input type="radio" name="check_two_" value="0"></td>
                                </tr>
                                <!-- <tr>
                                    <td class="text-left">I accept the content of this record of conversation.</td>
                                    <td class="border border-top-0 border-dark emp-inputs"><input type="radio" name="check_three" value="1"></td>
                                    <td class="border border-top-0 border-dark emp-inputs"><input type="radio" name="check_three" value="0"></td>
                                    <td></td>
                                    <td class="border border-top-0 border-dark sup-inputs"><input type="radio" name="check_three_" value="1"></td>
                                    <td class="border border-top-0 border-dark sup-inputs"><input type="radio" name="check_three_" value="0"></td>
                                </tr> -->
                            </table>
                            <div id="signoff-emp-id-input">
                                <div class="my-2">Enter your 6 digit employee ID to indicate you have read and accept the performance review:</div>

                                <input type="text" id="employee_id" class="form-control d-inline w-50">
                                <button class="btn btn-primary btn-sign-off ml-2" type="button">Sign with my employee ID</button>
                                <br>
                                <span class="text-danger error" data-error-for="employee_id"></span>

                                <div class="mt-3">
                                    <input type="hidden" name="team_member_agreement" value="0">
                                    <label style="font-weight: normal;">
                                        <input type="checkbox" name="team_member_agreement" id="team_member_agreement" value="1">&nbsp;Team member disagrees with the information contained in this performance review.
                                    </label>
                                </div>
                            </div>
                        </form>

                    </div>
                    
                            
                </div>

                <div id="unsignoff-form-block">
                    <div class="my-2">Enter 6 digit employee ID to unsign:</div>
                    <form id="unsign-off-form" data-action-url="{{ route('conversation.unsignoff', 'xxx')}}" method="post">
                        @csrf
                        <input type="text" name="employee_id" id="employee_id" class="form-control d-inline w-50">
                        <button data-action="unsignoff" class="btn btn-primary btn-sign-off ml-2" type="button">Un-Sign</button>
                        <br>
                        <span class="text-danger error" data-error-for="employee_id"></span>

                        <div class="mt-3">
                            <label style="font-weight: normal;">
                                <input type="checkbox" name="team_member_agreement" id="team_member_agreement_2" value="1" disabled>&nbsp;Team member disagrees with the information contained in this performance review.
                            </label>
                        </div>
                    </form>
                </div>
                <div class="mt-3 alert alert-default-warning d-none" id="locked-message">
                    <!-- Conversation records can be un-signed and edited for up to two weeks from initial completion date. After the two week period, a request to unlock the conversation record will need to be submitted to your HR Administrator if changes are required. -->
                    Conversation records can be un-signed and edited for up to two weeks from initial completion date. After the two week period, an AskMyHR service request to unlock the conversation record will need to be submitted to Myself > HR Software Systems Support > Performance Development Platform.
                </div>
                @endif
                <div class="mt-3 alert alert-default-warning alert-dismissible" id="supervisor-signoff-message">
                    <span class="h5"><i class="icon fas fa-exclamation-circle"></i><b class="name"></b> has <b class="not d-none">not</b> signed this record of conversation <span class="time"></span></span>
                </div>
                <div class="mt-3 alert alert-default-warning alert-dismissible" id="employee-signoff-message">
                    <span class="h5"><i class="icon fas fa-exclamation-circle"></i><b class="name"></b> has <b class="not d-none">not</b> signed this record of conversation <span class="time"></span></span>
                </div>
            </div>
        </div>

    </div>
</div>