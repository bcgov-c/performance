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
            data-content="
            <p>The list below contains all planned conversations that have yet to be signed-off by both employee and supervisor. 
                                Once a conversation has been signed-off by both participants, 
                                it will move to the Completed Conversations tab and become an official performance development record for the employee.</p>">
            <i class="fa fa-info-circle"> </i> Instructions
            </button>
        </div>
    </div>
    
    <div class="mt-4">       
            
        <div class="card">            
                <div class="card-header" id="heading_sup" style="border-bottom-width: 0px;">
                    <h5 class="mb-1"data-toggle="collapse" data-target="#collapse_sup" aria-expanded="1" aria-controls="collapse_sup">
                        <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_sup" aria-expanded="false" aria-controls="collapse_sup">

                                <button class="btn btn-link text-left">
                                    <h4>Open Conversations with my Supervisor</h4>
                                </button> 
                                <span class="float-right"  style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span> 
                                <br/>
                                <button class="btn btn-link text-left" style="color:black">
                                    <p>This area contains all open conversations between you and your supervisor(s).</p>
                                </button>   
                        </h5>
                    </h5>
                </div>

                <div id="collapse_sup" class="collapse" aria-labelledby="heading_sup">
                    <div class="card-body">
                        <form action="" method="post" id="sup-filter-menu">
                            <div class="row">
                                <div class="col">
                                    <label>
                                        Conversation Type
                                        <select name="sup_conversation_topic_id" id="sup_conversation_topic_id" class="sup_filtersub form-control">
                                            @foreach($conversationList as $item)
                                            <option value="{{$item['id']}}"
                                                    @if($item['id'] == request()->sup_conversation_topic_id)    
                                                    selected
                                                    @endif
                                                >{{$item['name']}}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                                <div class="col">
                                    <label>
                                        Supervisors
                                        <select name="supervisors" id="supervisors" class="sup_filtersub form-control">
                                            @foreach($supervisor_members as $item)
                                            <option value="{{$item['id']}}"
                                                    @if($item['id'] == request()->supervisors)    
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
                                        <select name="sup_employee_signed" id="sup_employee_signed" class="sup_filtersub form-control">
                                                <option value="any">Any</option><option value="1"
                                                    @if(request()->sup_employee_signed == '1')    
                                                    selected
                                                    @endif
                                                >Yes</option>
                                            <option value="0"
                                                    @if(request()->sup_employee_signed == '0')    
                                                    selected
                                                    @endif
                                                >No</option>
                                        </select>
                                    </label>
                                </div>
                                <div class="col">
                                    <label>
                                        Supervisor Signed
                                        <select name="sup_supervisor_signed" id="sup_supervisor_signed" class="sup_filtersub form-control">
                                                <option value="any">Any</option><option value="1"
                                                    @if(request()->sup_supervisor_signed == '1')    
                                                    selected
                                                    @endif
                                                >Yes</option>
                                            <option value="0"
                                                    @if(request()->sup_supervisor_signed == '0')    
                                                    selected
                                                    @endif
                                                >No</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </form>
                        <table style="width:100%" id='supervisor_conversations' class="table table-striped"> </table>
                    </div>
                </div>   
        </div>        
            
        @if($user->hasRole('Supervisor'))
        <div class="card">            
                <div class="card-header" id="heading_emp" style="border-bottom-width: 0px;">
                    <h5 class="mb-1"data-toggle="collapse" data-target="#collapse_emp" aria-expanded="1" aria-controls="collapse_emp">
                        <h5 class="mb-0" data-toggle="collapse" data-target="#collapse_emp" aria-expanded="false" aria-controls="collapse_emp">

                                <button class="btn btn-link text-left">
                                    <h4>Open Conversations with my Team</h4>
                                </button> 
                                <span class="float-right" id="caret_2"  style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span> 
                                <br/>
                                <button class="btn btn-link text-left" style="color:black">
                                    <p>This area contains all open conversations between you and your direct reports.</p>
                                </button>   
                        </h5>
                    </h5>
                </div>

                <div id="collapse_emp" class="accordion-collapse collapse" aria-labelledby="heading_emp">
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
                                                <option value="any">Any</option><option value="1"
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
                                                <option value="any">Any</option><option value="1"
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
    
  $('.filtersub').on('change', function() {
    $('#filter-menu').submit();
  });
  
  $('.sup_filtersub').on('change', function() {
    $('#sup-filter-menu').submit();
  }); 

  var show_collapse = false;
  var conversation_topic_id = $('#conversation_topic_id').val();
  var team_members = $('#team_members').val();
  var employee_signed = $('#employee_signed').val();
  var supervisor_signed = $('#supervisor_signed').val();
  if(conversation_topic_id != 0 || team_members != '' || employee_signed != 'any' || supervisor_signed != 'any'){
      var show_collapse = true;
  }  
  if(show_collapse){
      $('#collapse_emp').collapse('show');
      var show_collapse = false;
      $('#heading_emp').click(function() {
         $('#caret_emp').css('transform', 'rotate(180deg)');
      });
  } else {
      $('#collapse_emp').collapse('hide');
  }
  
  var show_collapse_1 = false;
  var sup_conversation_topic_id = $('#sup_conversation_topic_id').val();
  var supervisors = $('#supervisors').val();
  var sup_employee_signed = $('#sup_employee_signed').val();
  var sup_supervisor_signed = $('#sup_supervisor_signed').val();
  var unsave_warning = false;
  
  var comment_changed = false;
  var comment1_changed = false;
  var comment2_changed = false;
  var comment3_changed = false;
  var comment4_changed = false;
  var comment5_changed = false;
  var comment6_changed = false;
  var comment7_changed = false;
  var comment8_changed = false;
  var comment9_changed = false;
  var comment10_changed = false;
  var comment11_changed = false;
   

  if(sup_conversation_topic_id != 0 || supervisors != '' || sup_employee_signed != 'any' || sup_supervisor_signed != 'any'){
      var show_collapse_1 = true;
  }  
  if(show_collapse_1){
      $('#collapse_sup').collapse('show');
      var show_collapse_1 = false;
      $('#heading_sup').click(function() {
         $('#caret_sup').css('transform', 'rotate(180deg)');
      });
  } else {
      $('#collapse_sup').collapse('hide');
  }
  
  $(document).ready(function() {
        const json_myTeamConversations = <?php echo $json_myTeamConversations;?>;
        const employee_table = $('#employee_conversations').DataTable({
            data: json_myTeamConversations,
            columns: [
              { title: "ID", data: "id" },
              { title: "Employee ID", data: "signoff_user_id" },
              { title: "Supervisor ID", data: "supervisor_signoff_id" },
              {
                title: '<div style="padding-left: 20px;">Name</div>', // add left padding to header cell
                render: function(data, type, row) {
                  return '<a class="btn btn-link ml-2 btn-view-conversation" data-id="'+row.id+'" data-toggle="modal" data-target="#viewConversationModal">'+row.name+'</a>';
                }
              },
              { title: "Participants", data: "participants" },
              { title: "Employee Signed", data: "employee_signed" },
              { title: "Supervisor Signed", data: "supervisor_signed" },
              { title: "Created At", data: "create_date" },
              { title: "Last Updated", data: "update_date" },
              {
                title: "",
                render: function(data, type, row) {
                  var disallowed = true;
                  if(row.signoff_user_id == null && row.supervisor_signoff_id == null){
                      disallowed = false;
                  }
                  return '<button class="btn btn-danger btn-sm float-right ml-2 delete-btn" data-id="' + row.id + '" data-disallowed="'+ disallowed +'"><i class="fa-trash fa"></i></button>';
                }
              }
            ],
            "order": [[0, "desc"]],
            dom: '<"row"<"col-md-12"t>>' + '<"row"<"col-md-6"i><"col-md-6"p>>'
         });
         employee_table.column(0).visible(false); 
         employee_table.column(1).visible(false);
         employee_table.column(2).visible(false);
         
         
        const json_conversations = <?php echo $json_conversations;?>;
        const supervisor_table = $('#supervisor_conversations').DataTable({
            data: json_conversations,
            columns: [
              { title: "ID", data: "id" },
              { title: "Employee ID", data: "signoff_user_id" },
              { title: "Supervisor ID", data: "supervisor_signoff_id" },              
              {
                title: '<div style="padding-left: 20px;">Name</div>', // add left padding to header cell
                render: function(data, type, row) {
                  return '<a class="btn btn-link ml-2 btn-view-conversation" data-id="'+row.id+'" data-toggle="modal" data-target="#viewConversationModal">'+row.name+'</a>';
                }
              },
              { title: "Participants", data: "participants" },
              { title: "Employee Signed", data: "employee_signed" },
              { title: "Supervisor Signed", data: "supervisor_signed" },
              { title: "Created At", data: "create_date" },
              { title: "Last Updated", data: "update_date" },
              {
                title: "",
                render: function(data, type, row) {
                  var disallowed = true;
                  if(row.signoff_user_id == null && row.supervisor_signoff_id == null){
                      disallowed = false;
                  }
                  return '<button class="btn btn-danger btn-sm float-right ml-2 delete-btn" data-id="' + row.id + '" data-disallowed="'+ disallowed +'"><i class="fa-trash fa"></i></button>';
                }
              }
            ],
            "order": [[0, "desc"]],
            dom: '<"row"<"col-md-12"t>>' + '<"row"<"col-md-6"i><"col-md-6"p>>'
         });
         supervisor_table.column(0).visible(false); 
         supervisor_table.column(1).visible(false); 
         supervisor_table.column(2).visible(false); 
         
         $(".upcoming-border").addClass('border-primary');

         $('#collapse_ins').collapse('show');


         <?php if ($open_modal_id): ?>
            setTimeout(function() {
                $('a[data-id="{{$open_modal_id}}"]').click();
            }, 1000); 
        <?php endif; ?>  
         
  });  

  <?php if($owner_role == 'emp'){ ?>
    $('#collapse_sup').collapse('show');
  <?php } elseif ($owner_role == 'mgr'){ ?>
    $('#collapse_emp').collapse('show');
  <?php } ?>
</script>

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
    
    #upcoming {
        font-weight: bold;
    }
    
    #employee_conversations {
        width: 100%;
    }  
    
    table.dataTable thead th {
        border-bottom: solid #FCBA19;
    }
    .popover {
        max-width: 400px; /* Adjust the width as needed */
    }
    
</style> 