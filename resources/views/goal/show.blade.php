<x-side-layout title="{{ __('My Goals - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
		{{ $goal['title'] }}
            @if(!$disableEdit && $goal->user_id === Auth::Id() && $goal['status'] == 'active')
            <button class="btn btn-outline-primary btn-md" id="edit-btn" href="{{ route('goal.edit', $goal->id) }}" tabindex="0">
                        <i class="fa fa-edit"></i>&nbsp;        Edit
            </button>
            @endif
        
        @if(session('from_share'))
        <button class="btn btn-outline-primary btn-md" id="back-share-btn" href="{{ route('goal.share') }}">
                        <i class="fa fa-backward"></i>&nbsp;        Back to list
            </button>
        @else
        <button class="btn btn-outline-primary btn-md" id="back-list-btn" href="{{ route('goal.index') }}">
                        <i class="fa fa-backward"></i>&nbsp;        Back to list
            </button>
        @endif
        </h2>
    </x-slot>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-7">
                        <div class="card">
                            @include("goal.partials.show")
                            <hr>
                            <div class="px-3 pb-3">
                                {{ $goal->user->name}}
                                <span class="float-right">
                                    @if ($goal->user_id === Auth::id() && (session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
                                    @include("goal.partials.status-change")
                                    @else
                                    <x-goal-status :status="$goal->status"></x-goal-status>
                                    @endif
                                </span>
                            </div>
                            {{--<hr>
                                <div class="px-3">
                                    <b>Linked Goals</b>
                                    <span class="float-right">
                                        <button class="btn  btn-sm link-goal" data-id="{{ $goal->id }}" data-toggle="modal" data-target="#supervisorGoalModal"> <i class="fa fa-plus"></i></button>

                                    </span>
                                </div>
                                @forelse ($linkedGoals as $l)
                                <div class="card mx-3">
                                    <div class="card-body py-2">
                                        <p class="mb-0"><a href="{{route("goal.show", $l->id)}}" > {{ $l->title }}</a></p>

                                        <span class="mr-2">{{ $l->user->name }}</span>
                                        <span class="mx-3">  | &nbsp; &nbsp; &nbsp;

                                            <div class="d-inline-flex flex-row align-items-center">
                                                <div class="bg-{{ \Config::get("global.status.$l->status.color") }} rounded-circle mr-2" style="width:15px; height:15px;"></div>
                                                <div class="text-capitalize">
                                                    {{ $l->status }}
                                                </div>
                                            </div>
                                        </span>
                                        <span class="mx-3">| &nbsp;&nbsp; &nbsp; {{\Carbon\Carbon::now()->format('M d, Y') }}</span>
                                    </div>

                                </div>
                                @empty

                                <div class="p-3 text-center">

                                    <p class="text-center">No Goals to Display</p>
                                    <p class="text-center font-weight-bold">Start linking to your supervisor's goal</p>
                                    <button class="btn btn-outline-primary btn-sm link-goal" data-id="{{ $goal->id }}" data-toggle="modal" data-target="#supervisorGoalModal">Link Goals</button>
                                </div>
                                @endforelse --}}


                            </div>
                        </div>
                        @if (!$goal->is_library)
                        <div class="col-12 col-sm-5">
                            <b>Comments</b>
                            @foreach ($goal->comments as $comment)
                            <div class="d-flex flex-row my-2">
                                {{-- <x-profile-pic></x-profile-pic> --}}
                                <div class="border flex-fill p-2 rounded">
                                    <b>{{$comment->user->name}}</b> on 
                                        @if ($comment->updated_at != $comment->created_at)
                                            {{$comment->updated_at->format('M d, Y H:i A')}} - (edited)<br>
                                        @else
                                            {{$comment->created_at->format('M d, Y H:i A')}}<br>
                                        @endif
                                    <div class="comment-text">
                                        {!! (!$comment->trashed()) ? $comment->comment : '<i>Comment is deleted.</i>' !!}
                                    </div>
                                    <x-button class="btn edit-save d-none" action="submit" :data-comment-id="$comment->id" size="sm" tabindex="0">Save</x-button>
                                    <div>
                                        @if($comment->canBeEdited())<x-button icon='edit' style="link" class="comment-edit" :data-comment-id="$comment->id" size="sm" tabindex="0">Edit</x-button>@endif
                                        @if(!$comment->trashed() && $comment->canBeDeleted())<x-button icon='trash' style="link" class="comment-delete" :data-comment-id="$comment->id" size="sm" tabindex="0">Delete</x-button>@endif
                                    </div>
                                    <div>
                                        @foreach($comment->replies as $reply)
                                        <div class="card mt-2 p-2 d-flex flex-row bg-light">
                                            {{-- <x-profile-pic></x-profile-pic> --}}
                                            <div class="flex-fill">
                                                <b>{{$reply->user->name}}</b> on 
                                                    @if ($reply->updated_at != $reply->created_at)
                                                        {{$reply->updated_at->format('M d, Y H:i A')}} - (edited)<br>
                                                    @else
                                                        {{$reply->created_at->format('M d, Y H:i A')}}<br>
                                                    @endif
                                               
                                                <div class="comment-text">
                                                    {!! (!$reply->trashed()) ? $reply->comment : '<i>Comment is deleted.</i>' !!}
                                                </div>
                                                <x-button class="btn edit-save d-none" action="submit" :data-comment-id="$reply->id" size="sm" tabindex="0">Save</x-button>
                                                <div>
                                                    @if($reply->canBeEdited())<x-button icon='edit' style="link" class="comment-edit" :data-comment-id="$reply->id" size="sm" tabindex="0">Edit</x-button>@endif
                                                    @if(!$reply->trashed() && $reply->canBeDeleted())<x-button icon='trash' style="link" class="comment-delete" :data-comment-id="$reply->id" size="sm" tabindex="0">Delete</x-button>@endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <x-button icon='reply' style="link" class="comment-reply" :data-comment-id="$comment->id" size="sm" tabindex="0">Reply</x-button>
                                        <div class="reply-box d-none">
                                            <form action="{{route('goal.add-comment', $goal->id)}}" method="POST">
                                                @csrf
                                                <input type="hidden" name="parent_id" value="{{$comment->id}}">
                                                <div class="d-flex flex-row my-2">
                                                    {{-- <x-profile-pic></x-profile-pic> --}}
                                                    <div class="border flex-fill p-2 rounded">
                                                        <!-- <x-textarea class="ckeditor" name="comment" id="addreply"/> -->
                                                        <textarea class="addreply" id="addreply" name="comment"></textarea>
                                                        <div class="d-flex flex-row my-2">
                                                        <button role="button" class="btn btn-outline-primary btn-sm btn" action="submit" :data-comment-id="$comment->id" tabindex="0">
                                                                Add Comment
                                                        </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <form action="{{route('goal.add-comment', $goal->id)}}" method="POST">
                                @csrf
                                <div class="d-flex flex-row my-2">
                                    {{-- <x-profile-pic></x-profile-pic> --}}
                                    <div class="border flex-fill p-2 rounded">
                                        <textarea name="comment" id="addcomment"></textarea>
                                        <!-- <x-textarea class="ckeditor" name="comment" id="addcomment"/> -->
                                        <div class="d-flex flex-row my-2">
                                        <button role="button" class="btn btn-outline-primary btn-sm btn" action="submit" :data-comment-id="$comment->id">
                                                Add Comment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <form action="{{route('goal.comment.delete', 'xxx')}}" method="POST" id="delete-comment-form">
            @csrf
            @method("DELETE")
        </form>
        <form action="{{route('goal.comment.edit', 'xxx')}}" method="POST" id="edit-comment-form">
            @csrf
            <input type="hidden" class="comment" name="comment">
            @method("PUT")
        </form>
        @include('goal.partials.supervisor-goal')

    @push('js')
    <script>
        if(!!window.performance && window.performance.navigation.type === 2)
        {
            console.log('Reloading');
            window.location.reload();
        }
        window.isDirty = true;
        $('form').on('submit', () => {
            window.isDirty = false;
        });
        window.onbeforeunload = function () {
            if (!window.isDirty) {
                return;
            }
            for (var i in CKEDITOR.instances){
                CKEDITOR.instances[i].updateElement();
            };
            let isDirty = false;
            $("textarea").each((i, e) => {
                if ($(e).val() !== '') {
                    isDirty = true;
                }
            });
            if (isDirty) {
                return "If you continue you will lose any unsaved information";
            }
        };
    </script>
    <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){
        CKEDITOR.replace('addcomment', {
            toolbar: "Custom",
            toolbar_Custom: [
                ["Bold", "Italic", "Underline"],
                ["NumberedList", "BulletedList"],
                ["Outdent", "Indent"],
                ["Link"],
            ],
        });
    });
    
    $(document).ready(function(){
        CKEDITOR.replaceAll('addreply', {
            toolbar: "Custom",
            toolbar_Custom: [
                ["Bold", "Italic", "Underline"],
                ["NumberedList", "BulletedList"],
                ["Outdent", "Indent"],
                ["Link"],
            ],
        });
    });
    
    </script>

    <script>
    $('form').keydown(function (event) {
        if (event.ctrlKey && event.keyCode === 13) {
            $(this).trigger('submit');
        }
    });
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(document).on('click', ".link-goal", function () {
        $.get('/goal/supervisor/'+$(this).data('id'), function (data) {
            $("#supervisorGoalModal").find('.data-placeholder').html(data);
            $("#supervisorGoalModal").modal('show');
        });
    });

    let linkedGoals = [];
    $(document).on('click','.comment-reply', function(e){
        const commentId = $(this).data("comment-id");
        $(this).siblings(".reply-box").toggleClass("d-none");
    });
    $(document).on('click','.comment-delete', function(e){
        if (confirm("Are you sure you want to delete this comment ?")) {
            const commentId = $(this).data("comment-id");
            const form = document.getElementById("delete-comment-form");
            fetch(form.action.replace("xxx", commentId),{method:'POST', body: new FormData(form)});
            setTimeout(function(){
                window.location.reload();
             }, 1000);
        }
    });

    $(document).on('click','.comment-edit', function(e) {
        //if (confirm("Are you sure you want to delete this comment ?")) {
        const commentId = $(this).data("comment-id");
        const instance = CKEDITOR.replace($(this).parent().parent().find(".comment-text").get(0), {
            toolbar: "Custom",
            toolbar_Custom: [
                ["Bold", "Italic", "Underline"],
                ["NumberedList", "BulletedList"],
                ["Outdent", "Indent"],
                ["Link"],
            ],
        });
        $(this).parent().parent().find(".edit-save").data("editor", instance.name);
        $(this).parent().parent().find(".edit-save").removeClass("d-none");
    });
    $(document).on('click','.edit-save', function(e) {
        //if (confirm("Are you sure you want to delete this comment ?")) {
        const commentId = $(this).data("comment-id");
        $(this).parent().parent().find(".edit-save").addClass("d-none");
        const editor = $(this).parent().parent().find(".edit-save").data("editor");
        const data = CKEDITOR.instances[editor].getData();

        CKEDITOR.instances[editor].destroy();

        const form = document.getElementById("edit-comment-form");
        $(form).find(".comment").val(data);
        fetch(form.action.replace("xxx", commentId), {method:'POST', body: new FormData(form)});
        
        setTimeout(function(){
                window.location.reload();
             }, 1000);
    });


    
    $('body').popover({
        selector: '[data-toggle]',
        trigger: 'hover',
    });

    $('#edit-btn').click(function(){
        window.location.href = "{{ route('goal.edit', $goal->id) }}";
    }); 

    $('#back-share-btn').click(function(){
        window.location.href = "{{ route('goal.share') }}";
    });

    $('#back-list-btn').click(function(){
        window.location.href = "{{ route('goal.index') }}";
    });
    </script>
    @endpush

</x-side-layout>


@include('goal.partials.accessibility')