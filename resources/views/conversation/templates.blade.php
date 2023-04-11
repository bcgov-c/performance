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
                            <span class="float-right"><i class="fa fa-chevron-down"></i></span>    
                    </h5>
                </h5>
		</div>

		<div id="collapse_0" class="collapse" aria-labelledby="heading_0">
                    <div class="card-body">
                            <p>
                                Review the information below to determine which template best suits your needs. Templates include suggestions for when to select a given conversation topic, questions to consider when having the conversation, and an attestation and sign-off area to formalize the results.
                            </p>
                            <p>
                                Once you've selected a template for use, select participants and hit "Use this template" to alert participants you want to meet. Conversations will still need to be scheduled independently in your outlook calendar.
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
                            <span class="float-right"><i class="fa fa-chevron-down"></i></span> 
                            <button class="btn btn-link text-left">
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
                            <div class="card border ">
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-12">
                                            @csrf
                                            <table>
                                                <tr style="border-bottom: solid #FCBA19">
                                                    <th width="20%">Name</th>
                                                    <th width="45%">When to use</th>
                                                    <th width="15%">Participants</th>
                                                    <th width="20%">&nbsp;</th>
                                                </tr>
                                                <tbody style="border-collapse: collapse;">
                                                <tr style="background-color: #efefef">
                                                    <td>{{$template->name}}</td>
                                                    <td>{{$template->when_to_use}}</td>
                                                    <td>
                                                        <select class="form-control w-100 select" style="width:100%;" name="participant_id[]" id="participant_id" required>
                                                            <option value="">None Selected</option>
                                                            @foreach($participants as $p)
                                                            @if(session()->has('view-profile-as'))
                                                                @if(auth()->user()->id == $p->id)
                                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                                @endif
                                                            @else
                                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
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
                            <span class="float-right"><i class="fa fa-chevron-down"></i></span> 
                            <button class="btn btn-link text-left">
                                <p>These templates can be used as required to support conversations that require a more 
                                    specific focus. Select a topic below to read more in the <em>When to use this template section</em>.</p>
                            </button>   
                    </h5>
                </h5>
		</div>
		
		<div id="collapse_2" class="collapse" aria-labelledby="heading_2">
                    <div class="card-body">
                        @foreach ($templates as $template)
                        @if(strtolower($template->name) !== 'performance check-in')   
                            <form id="conversation_form_{{$template->id}}" action="{{ route ('conversation.store')}}" method="POST">
                            <input type="hidden" name="date" value="{{ \Carbon\Carbon::now() }}">
                            <input type="hidden" name="time" value="{{ \Carbon\Carbon::now() }}">   
                            <input type="hidden" name="conversation_topic_id" value="{{$template->id}}">    
                            <div class="card border ">
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-12">
                                            @csrf
                                            <table>
                                                <tr style="border-bottom: solid #FCBA19">
                                                    <th width="20%">Name</th>
                                                    <th width="45%">When to use</th>
                                                    <th width="15%">Participants</th>
                                                    <th width="20%">&nbsp;</th>
                                                </tr>
                                                <tbody style="border-collapse: collapse;">
                                                <tr style="background-color: #efefef">
                                                    <td>{{$template->name}}</td>
                                                    <td>{{$template->when_to_use}}</td>
                                                    <td>
                                                        <select class="form-control w-100 select" style="width:100%;" name="participant_id[]" id="participant_id_{{$template->id}}" required>
                                                            <option value="">None Selected</option>
                                                            @foreach($participants as $p)
                                                            @if(session()->has('view-profile-as'))
                                                                @if(auth()->user()->id == $p->id)
                                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                                @endif
                                                            @else
                                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                            @endif
                                                            @endforeach
                                                       </select>
                                                    </td>                                                    
                                                    <td>
                                                        <button class="btn d-flex align-items-center" onclick="javascript:otherTemp({{$template->id}})">
                                                            <span class="btn btn-primary" >Start Conversation</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>                                             
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
                        @endif
                        @endforeach
                    </div>
		</div>
                </form> 
	</div>
        
        
        
        
    </div>
    <x-slot name="js">
        <script>
            
            
            

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
