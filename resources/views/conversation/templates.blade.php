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
    </div>
    
    <div class="row">
        <div class="col-12">
            <br/>
            <button
            id="toggleCardButton"
            class="btn btn-primary float-left"
            data-trigger = "click"
            data-toggle="popover"
            data-placement="right"  
            data-html="true"    
            data-original-title="
            <p><br/>Review the information below to determine which template best suits your needs. Click on a template name to view a sample. If you need more assistance, refer to the
             <a href='/resources/conversations?t=1' target=\'_blank\'>Performance Conversations</a> 
             resource page. </p>
            <p>Once you've decided on a template for use, select the participant from the dropdown list and 
                hit 'Start Conversation' to alert participants you want to meet. Conversations will still need to be scheduled independently in your outlook calendar.</p>">
            <i class="fa fa-info-circle"> </i> Instructions
            </button>
        </div>
    </div>


    <div class="mt-4">
        
        <div class="card">
            
            <div class="card-header" id="heading_1" style="border-bottom-width: 0px;">
                    <h5 class="mb-1"data-toggle="collapse" data-target="#collapse_1" aria-expanded="1" aria-controls="collapse_1">
                        <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_1" aria-expanded="false" aria-controls="collapse_1">

                                <button class="btn btn-link text-left">
                                    <h4>Performance Check-in Template</h4>
                                </button> 
                                <span class="float-right"  style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span> 
                                <br/>                                
                                <button class="btn btn-link text-left" style="color:black">
                                    <p>The Performance check-In template can be used in most situations. 
                                        It includes options to capture progress against goals, celebrate successes, 
                                        discuss ways to improve future performance outcomes, and record an overall performance evaluation.</p>
                                </button>   
                        </h5>
                    </h5>
		</div>
                
                <form id="conversation_form_1" action="{{ route ('conversation.store')}}" method="POST">
                <input type="hidden" name="date" value="{{ \Carbon\Carbon::now() }}">
                <input type="hidden" name="time" value="{{ \Carbon\Carbon::now() }}">      
		<div id="collapse_1" class="collapse" aria-labelledby="heading_1">
                    <div class="card-body">
                        @foreach ($templates as $template)
                        @if(strtolower($template->name) === 'performance check-in')   
                            <input type="hidden" name="conversation_topic_id" value="{{$template->id}}">    
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-12">
                                            @csrf
                                            <table class="table">
                                                <thead class="headborder">
                                                    <tr style="border-bottom: solid #FCBA19">
                                                        <th width="20%" style="border-bottom: solid #FCBA19">Name</th>
                                                        <th width="45%" style="border-bottom: solid #FCBA19">When to use</th>
                                                        <th width="15%" style="border-bottom: solid #FCBA19">Participants</th>
                                                        <th width="20%" style="border-bottom: solid #FCBA19">&nbsp;</th>
                                                    </tr>
                                                </tdhead>
                                                <tbody style="border-collapse: collapse;">
                                                <tr style="background-color: #efefef">
                                                    <td><a class="btn btn-link ml-2 btn-view-conversation" data-id="{{$template->id}}" data-toggle="modal" data-target="#viewConversationModal">{{$template->name}}</a></td>
                                                    <td>{{$template->when_to_use}}</td>
                                                    <td>
                                                        <select class="form-control w-100 select" style="width:100%; margin-top: 8px;" name="participant_id[]" id="participant_id" required>
                                                            <option value="">None Selected</option>
                                                            @foreach($participant_users as $p)
                                                            @if(session()->has('view-profile-as'))
                                                                @if(auth()->user()->id == $p['id'])
                                                                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                                                @endif
                                                            @else
                                                            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                                            @endif
                                                            @endforeach
                                                       </select>
                                                    </td>                                                    
                                                    <td>
                                                        <button class="btn d-flex align-items-center" >
                                                        <span class="btn btn-primary">Start Conversation</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>                                             
                                        </div>
                                    </div>
                                </div>
                        @endif
                        @endforeach
                    </div>
		</div>
                </form>    
	</div>
        
        
        <div class="card">
		
            <div class="card-header" id="heading_2" style="border-bottom-width: 0px;">
                    <h5 class="mb-1" data-toggle="collapse" data-target="#collapse_2" aria-expanded="1" aria-controls="collapse_2">
                        <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_2" aria-expanded="false" aria-controls="collapse_2">
                                <button class="btn btn-link text-left">
                                    <h4>Other Templates</h4>
                                </button> 
                                <span class="float-right" style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span> 
                                <br/>                                
                                <button class="btn btn-link text-left"  style="color:black">
                                    <p>These templates can be used as required to support conversations that require a more 
                                        specific focus.</p>
                                </button>   
                        </h5>
                    </h5>
		</div>
		
		<div id="collapse_2" class="collapse" aria-labelledby="heading_2">
                    <div class="card-body">
                        <div class="card-body p-2">
                        <div class="row">
                        <div class="col-12">
                        @csrf
                        <table class="table table-striped">
                            <thead class="headborder">
                                <tr style="border-bottom: solid #FCBA19">
                                    <th width="20%" style="border-bottom: solid #FCBA19">Name</th>
                                    <th width="45%" style="border-bottom: solid #FCBA19">When to use</th>
                                    <th width="15%" style="border-bottom: solid #FCBA19">Participants</th>
                                    <th width="20%" style="border-bottom: solid #FCBA19">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody style="border-collapse: collapse;">
                                @foreach ($templates as $template)
                                @if(strtolower($template->name) !== 'performance check-in')
                                <tr>
                                    <td><a class="btn btn-link ml-2 btn-view-conversation" data-id="{{$template->id}}" data-toggle="modal" data-target="#viewConversationModal">{{$template->name}}</a></td>
                                    <td>{{$template->when_to_use}}</td>
                                    <td>
                                        <select class="form-control w-100 select" style="width:100%; margin-top: 8px;"" name="participant_id_{{$template->id}}[]" id="participant_id_{{$template->id}}" required>
                                            <option value="">None Selected</option>
                                            @foreach($participant_users as $p)
                                            @if(session()->has('view-profile-as'))
                                                @if(auth()->user()->id == $p['id'])
                                                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                                @endif
                                            @else
                                            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>   
                                       
                                        <button class="btn d-flex align-items-center" onclick="javascript:conversation_sub({{$template->id}});">
                                            <span class="btn btn-primary">Start Conversation</span>
                                        </button>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                        <form id="conversation_form_2" action="{{ route ('conversation.store')}}" method="POST">
                        <input type="hidden" name="date" value="{{ \Carbon\Carbon::now() }}">
                        <input type="hidden" name="time" value="{{ \Carbon\Carbon::now() }}">  
                        <input type="hidden" name="conversation_topic_id">  
                        <input type="hidden" name="participant_id">
                        </form>
                        </div>
                        </div>
                        </div>
                    </div>
		</div>
	</div>
    </div>


        
    @include('conversation.partials.template-conversation-modal')   
    <x-slot name="js">
        
        <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>     
        @include('conversation.partials.conversations-template-js')    


    </x-slot>
    <x-slot name="css">
        <style>
            i {
                transition: 0.2s ease-in-out;
            }
            [aria-expanded="true"] i{
                transform: rotate(180deg);
            }
            
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #444444;
                border: 1px solid #aaa;
                border-radius: 4px;
                cursor: default;
                float: left;
                margin-right: 5px;
                margin-top: 5px;
                padding: 0 5px;
            }
            
            .headborder{
                border-bottom: solid #FCBA19;
            }
            
        </style>
    </x-slot>

</x-side-layout>

<style>
    
    .dataTable > thead > tr > th:nth-child(5)[class*="sort"]:before,
    .dataTable > thead > tr > th:nth-child(5)[class*="sort"]:after {
        content: "" !important;
    }
    
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
    #employee_conversations {
        width: 100%;
    }  
    
    table.dataTable thead th {
        border-bottom: solid #FCBA19;
    }
</style> 

<script>

function conversation_sub(topic_id){
                $('#conversation_form_2 input[name="conversation_topic_id"]').val(topic_id);
                var allow_submit = true;
                if(topic_id === 1){
                    if ($('#participant_id_1')[0].checkValidity()) {
                        var participant_id = $('#participant_id_1').val();     
                    } else {
                        $('#participant_id_1')[0].reportValidity();
                        allow_submit = false;
                    }             
                }else if(topic_id === 2){
                    if ($('#participant_id_2')[0].checkValidity()) {                        
                        var participant_id = $('#participant_id_2').val();     
                    } else {
                        $('#participant_id_2')[0].reportValidity();
                        allow_submit = false;
                    } 
                }else if(topic_id === 3){                    
                    if ($('#participant_id_3')[0].checkValidity()) {                        
                        var participant_id = $('#participant_id_3').val();    
                    } else {
                        $('#participant_id_3')[0].reportValidity();
                        allow_submit = false;
                    } 
                }else if(topic_id === 4){
                    if ($('#participant_id_4')[0].checkValidity()) {                        
                        var participant_id = $('#participant_id_4').val();    
                    } else {
                        $('#participant_id_4')[0].reportValidity();
                        allow_submit = false;
                    } 
                }else if(topic_id === 5){
                    if ($('#participant_id_5')[0].checkValidity()) {                        
                        var participant_id = $('#participant_id_5').val();    
                    } else {
                        $('#participant_id_5')[0].reportValidity();
                        allow_submit = false;
                    } 
                }
                $('#conversation_form_2 input[name="participant_id"]').val(participant_id);
                if(allow_submit) {
                    $('#conversation_form_2').submit();
                }                
            }

</script>

<style>
    th{
        padding:20px;
    }
    td{
        padding:20px;
    }
    .float-right {
        float: right;
    }
    .popover {
        max-width: 400px; /* Adjust the width as needed */
    }
    .popover .popover-body {
        display: none;
    }
</style>    
