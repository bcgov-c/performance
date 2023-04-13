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
    </div>
    <div class="mt-4">
        <div class="card">
		<div class="card-header" id="heading_0">
		<h5 class="mb-0"data-toggle="collapse" data-target="#collapse_0" aria-expanded="1" aria-controls="collapse_0">
                    <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_0" aria-expanded="false" aria-controls="collapse_0">
                            <button class="btn btn-link" >
                                <h4>Instructions</h4> 
                            </button>                        
                            <span class="float-right" style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span>    
                    </h5>
                </h5>
		</div>

		<div id="collapse_0" class="collapse" aria-labelledby="heading_0">
                    <div class="card-body">
                            <p>
                                Review the information below to determine which template best suits your needs. Templates include suggestions for when to select a given conversation topic, questions to consider when having the conversation, and an attestation and sign-off area to formalize the results.
                            </p>
                            <p>
                                Once you've selected a template for use, select participants and hit "Start Conversation" to alert participants you want to meet. Conversations will still need to be scheduled independently in your outlook calendar.
                            </p>
                    </div>
		</div>
	</div>
        
        <div class="card">
		<div class="card-header" id="heading_1" style="border-bottom-width: 0px;">
		<h5 class="mb-1"data-toggle="collapse" data-target="#collapse_1" aria-expanded="1" aria-controls="collapse_1">
                    <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_1" aria-expanded="false" aria-controls="collapse_1">
                            
                            <button class="btn btn-link text-left">
                                <h4>Performance Check-in Template</h4>
                            </button> 
                            <br/>
                            <span class="float-right"  style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span> 
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
                                            <table>
                                                <tr style="border-bottom: solid #FCBA19">
                                                    <th width="20%">Name</th>
                                                    <th width="45%">When to use</th>
                                                    <th width="15%">&nbsp;</th>
                                                    <th width="20%"></th>
                                                </tr>
                                                <tbody style="border-collapse: collapse;">
                                                <tr style="background-color: #efefef">
                                                    <td>{{$template->name}}</td>
                                                    <td>{{$template->when_to_use}}</td>
                                                    <td>
                                                        <span style="font-size:0.9em;"><b>Participants</b></span><br/>
                                                        <select class="form-control w-100 select" style="width:100%;" name="participant_id[]" id="participant_id" required>
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
		<div class="card-header" id="heading_2"  style="border-bottom-width: 0px;">
		<h5 class="mb-2" data-toggle="collapse" data-target="#collapse_2" aria-expanded="1" aria-controls="collapse_2">
                    <h5 class="mb-2" data-toggle="collapse" data-target="#collapse_2" aria-expanded="false" aria-controls="collapse_2">
                            <button class="btn btn-link text-left">
                                <h4>Other Templates</h4>
                            </button> 
                            <br/>
                            <span class="float-right" style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span> 
                            <button class="btn btn-link text-left"  style="color:black">
                                <p>These templates can be used as required to support conversations that require a more 
                                    specific focus.</p>
                            </button>   
                    </h5>
                </h5>
		</div>
		
		<div id="collapse_2" class="collapse" aria-labelledby="heading_2">
                    <div class="card-body">
                        @csrf
                        <table class="table table-striped">
                            <thead>
                                <tr style="border-bottom: solid #FCBA19">
                                    <th width="20%">Name</th>
                                    <th width="45%">When to use</th>
                                    <th width="15%">&nbsp;</th>
                                    <th width="20%">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $template)
                                @if(strtolower($template->name) !== 'performance check-in')
                                <tr>
                                    <td>{{$template->name}}</td>
                                    <td>{{$template->when_to_use}}</td>
                                    <td>
                                        <span style="font-size:0.9em;"><b>Participants</b></span><br/>
                                        <select class="form-control w-100 select" style="width:100%;" name="participant_id_{{$template->id}}[]" id="participant_id_{{$template->id}}" required>
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
                                            <button type="button" class="btn btn-primary d-flex align-items-center" onclick="javascript:conversation_sub({{$template->id}});">
                                                <span>Start Conversation</span>
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
    <x-slot name="js">
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
            
            
        </style>
    </x-slot>

</x-side-layout>
