<div class="modal-header bg-primary">
    <h5 class="modal-title" id="conversationTemplateDetailLabel">{{$template->name}}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<!----
<div class="mt-4 p-3">
    <div class="row">
        <div class="col-12">
            <p>
                Review the information below to determine if this is the best template for you to use. Then select participants and hit "Use this template" to alert participants you want to meet. Conversations will still need to be scheduled independently in your Outlook calendar.
            </p>
        </div>
    </div>
</div>
----->

<div class="modal-body p-3">
    <button class="btn w-100 d-flex align-items-center text-primary p-2" style="background-color: #ddd;" data-toggle="collapse" data-target="#when_to_use">
        <strong>When to Use This Template</strong>
        <div class="flex-fill"></div>
        <i class="fa fa-chevron-down"></i>
    </button>
    <div id="when_to_use"  class="collapse p-3 border show">
        {!!$template->when_to_use!!}
    </div>


    <button class="btn w-100 d-flex align-items-center text-primary p-2 mt-2" style="background-color: #ddd;" data-toggle="collapse" data-target="#preparing_for_conversation">
        <strong>Preparing for the Conversation</strong>
        <div class="flex-fill"></div>
        <i class="fa fa-chevron-down"></i>
    </button>
    <div id="preparing_for_conversation"  class="collapse p-3 border">
        {!!$template->preparing_for_conversation!!}
    </div>

    <button class="btn w-100 d-flex align-items-center text-primary p-2 mt-2" style="background-color: #ddd;" data-toggle="collapse" data-target="#question_html">
        <strong>Suggested Discussion Questions</strong>
        <div class="flex-fill"></div>
        <i class="fa fa-chevron-down"></i>
    </button>
    <div id="question_html"  class="collapse p-3 border">
        {!!$template->question_html!!}
    </div>

    <button class="btn w-100 d-flex align-items-center text-primary p-2 mt-2" style="background-color: #ddd;" data-toggle="collapse" data-target="#supporting_material">
        <strong>Supporting Materials</strong>
        <div class="flex-fill"></div>
        <i class="fa fa-chevron-down"></i>
    </button>
    <div id="supporting_material"  class="collapse p-3 border">
      <p><a target="_blank" href="{{route('resource.conversations')}}">Learn more about effective performance conversations</a></p>
    </div>
    <div class="rounded text-primary p-2 mt-2" style="background-color: #ddd;">
        <strong>
            Meeting info
        </strong>
         <form id="conversation_form" action="{{ route ('conversation.store')}}" method="POST">
             @csrf
              <div class="mt-2 d-flex justify-content-between align-items-end">

            <div>

                <label style="font-weight: 400;" class="w-100">
                    Topic
                    <select name="conversation_topic_id" class="form-control" id="template-select">

                        @foreach($allTemplates as $topic)
                        <option value="{{$topic->id}}" {{$topic->id == $template->id ? 'selected' : ''}}>{{$topic->name}}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div>
                <label style="font-weight: 400;" class="w-100">
                    Participants
                   <select class="form-control w-100 select2" style="width:100%;" multiple name="participant_id[]" id="participant_id" required>
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
                </label>
            </div>
            <div>
            <input type="hidden" name="date" value="{{ \Carbon\Carbon::now() }}">
            <input type="hidden" name="time" value="{{ \Carbon\Carbon::now() }}">
            <x-button  size="md" class="mb-2">Use this template</x-button>

            </div>


        </div>
        </form>
    </div>
</div>
