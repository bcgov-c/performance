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
.panel-heading  a:before {
   
   float: right;
   transition: all 0.5s;
}
.panel-heading.active a:before {
	-webkit-transform: rotate(180deg);
	-moz-transform: rotate(180deg);
	transform: rotate(180deg);
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
        @if(!$disableEdit && false)
        <div class="col-md-4 text-right">
            <x-button icon="plus-circle" data-toggle="modal" data-target="#addConversationModal">
                Schedule New
            </x-button>
        </div>
        @endif
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
                                The list below contains all planned conversations that have yet to be signed-off by both employee and supervisor. 
                                Once a conversation has been signed-off by both participants, 
                                it will move to the Completed Conversations tab and become an official performance development record for the employee.
                            </p>
                    </div>
                </div>
        </div>
       
            
        <div class="card">            
                <div class="card-header" id="heading_1" style="border-bottom-width: 0px;">
                    <h5 class="mb-1"data-toggle="collapse" data-target="#collapse_1" aria-expanded="1" aria-controls="collapse_1">
                        <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_1" aria-expanded="false" aria-controls="collapse_1">

                                <button class="btn btn-link text-left">
                                    <h4>Conversations with My Supervisor</h4>
                                </button> 
                                <span class="float-right"  style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span> 
                                <br/>
                                <button class="btn btn-link text-left" style="color:black">
                                    <p>The list enclosed contains all open conversations between you and your supervisor(s).</p>
                                </button>   
                        </h5>
                    </h5>
                </div>

                <div id="collapse_1" class="collapse" aria-labelledby="heading_1">
                    <div class="card-body">
                        <table class="table">
                            <tr style="border-bottom: solid #FCBA19">
                                <th width="30%" style="border-bottom: solid #FCBA19">Conversation Type</th>
                                <th width="40%" style="border-bottom: solid #FCBA19">Participants</th>
                                <th width="10%" style="border-bottom: solid #FCBA19">Employee Signed</th>
                                <th width="10%" style="border-bottom: solid #FCBA19">Supervisor Signed</th>
                                <th width="10%" style="border-bottom: solid #FCBA19">&nbsp;</th>
                            </tr>

                            @forelse ($conversations as $c)
                            <tr>
                                <td><a href="javascript:void();" class="ml-2 btn-view-conversation" data-id="{{ $c->id }}" data-toggle="modal" data-target="#viewConversationModal">{{ $c->name }}</a></td>
                                <td>{{ $c->mgrname }} {{ $c->empname }}</td>
                                <td>
                                    @if($c->signoff_user_id != '' )
                                        Yes
                                    @else
                                        No
                                    @endif    
                                </td>
                                <td>
                                    @if($c->supervisor_signoff_id != '' )
                                        Yes
                                    @else
                                        No
                                    @endif  
                                </td>
                                <td><button class="btn btn-danger btn-sm float-right ml-2 delete-btn" data-id="{{ $c->id }}" data-disallowed="{{ (!!$c->signoff_user_id || !!$c->supervisor_signoff_id) ? 'true' : 'false'}}">
                                                <i class="fa-trash fa"></i>
                                            </button></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    No Conversations
                                </td>
                            </tr>
                            @endforelse
                        </table>
                    </div>
                </div>   
        </div>        
            
        @if($user->hasRole('Supervisor'))
        <div class="card">            
                <div class="card-header" id="heading_2" style="border-bottom-width: 0px;">
                    <h5 class="mb-1"data-toggle="collapse" data-target="#collapse_2" aria-expanded="1" aria-controls="collapse_2">
                        <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_2" aria-expanded="false" aria-controls="collapse_2">

                                <button class="btn btn-link text-left">
                                    <h4>Conversations with My Team</h4>
                                </button> 
                                <span class="float-right"  style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span> 
                                <br/>
                                <button class="btn btn-link text-left" style="color:black">
                                    <p>The list enclosed contains all open conversations between you and your direct reports.</p>
                                </button>   
                        </h5>
                    </h5>
                </div>

                <div id="collapse_2" class="accordion-collapse collapse" aria-labelledby="heading_2">
                    <div class="card-body">
                        <form action="" method="post" id="filter-menu">
                            <input name="sub" id="sub" value="1" type="hidden">
                            <div class="row">
                                <div class="col">
                                    <label>
                                        Conversation Type
                                        <select name="conversation_topic_id" id="conversation_topic_id" class="filtersub form-control">
                                            @foreach($conversationList as $item)
                                            <option value="{{$item['id']}}"
                                                    @if($item['id'] == request()->conversation_topic_id)    
                                                    selected
                                                    @endif
                                                >{{$item['name']}}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                                <div class="col">
                                    <label>
                                        Team Members
                                        <select name="team_members" id="team_members" class="filtersub form-control">
                                            @foreach($team_members as $item)
                                            <option value="{{$item['id']}}"
                                                    @if($item['id'] == request()->team_members)    
                                                    selected
                                                    @endif
                                                >{{$item['name']}}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                                <div class="col">
                                    <label>
                                        Employee Signed
                                        <select name="employee_signed" id="employee_signed" class="filtersub form-control">
                                            <option value="">Any</option>
                                            <option value="1"
                                                    @if(request()->employee_signed == '1')    
                                                    selected
                                                    @endif
                                                >Yes</option>
                                            <option value="0"
                                                    @if(request()->employee_signed == '0')    
                                                    selected
                                                    @endif
                                                >No</option>
                                        </select>
                                    </label>
                                </div>
                                <div class="col">
                                    <label>
                                        Supervisor Signed
                                        <select name="supervisor_signed" id="supervisor_signed" class="filtersub form-control">
                                            <option value="">Any</option>
                                            <option value="1"
                                                    @if(request()->supervisor_signed == '1')    
                                                    selected
                                                    @endif
                                                >Yes</option>
                                            <option value="0"
                                                    @if(request()->supervisor_signed == '0')    
                                                    selected
                                                    @endif
                                                >No</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </form>
                        <table style="width:100%" id='employee_conversations' class="table table-striped"> </table>
                    </div>
                </div>   
        </div>
        @endif
    </div> 
    

    @include('conversation.partials.view-conversation-modal')

        @include('conversation.partials.delete-hidden-form')

    <x-slot name="js">
        <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
        
        @include('conversation.partials.conversations-list-js')    
        
    </x-slot>

</x-side-layout>

@push('css')

    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<style>
        #employee-list-table_filter label {
            text-align: right !important;
            padding-right: 10px;
        } 
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
@endpush    

<script>
  $('#collapse_1').on('show.bs.collapse', function () {
    $('#caret_1').html('<i class="fas fa-caret-up"></i>');
  });
  $('#collapse_1').on('hide.bs.collapse', function () {
    $('#caret_1').html('<i class="fas fa-caret-down"></i>');
  });

  $('#collapse_2').on('show.bs.collapse', function () {
    $('#caret_2').html('<i class="fas fa-caret-up"></i>');
  });  
  $('#collapse_2').on('hide.bs.collapse', function () {
    $('#caret_2').html('<i class="fas fa-caret-down"></i>');
  }); 
    
    
  $('.filtersub').on('change', function() {
    $('#filter-menu').submit();
  });  
    
  @if(request()->sub)  
      $('#collapse_2').slideToggle();
  @endif    
    
</script>

<style>
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
    
</style> 