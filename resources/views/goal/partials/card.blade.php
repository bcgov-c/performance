    <div class="card card-primary shadow">
        <div class="card-header p-1">
            <div class="d-flex">
                <a href="{{route("goal.show", $goal->id)}}" class="p-2">
                    <p class="card-title">
                        {{ $goal->start_date_human}}
                            <span class="mx-4">&#8212;</span>
                        {{ $goal->target_date_human}}
                    </p>
                </a>
                <div class="flex-fill"></div>
                
                @if (($type ?? '') !== 'supervisor' && !$disableEdit && 1 == 0)
                <form id="delete-goal-{{$goal->id}}" action="{{ route('goal.destroy', $goal->id)}}" method="POST" onsubmit="return confirm('{{ $goalDeleteConfirmationText ?? 'Are you sure you want to permanently delete this goal?' }}')">
                    @csrf
                    @method('DELETE')
                    <x-button icon='trash' class="text-light" aria-label="Delete button" tooltip="Click to permanently delete this goal"></x-button>
                </form>
                @endif
            </div>
        </div>
        <div class="card-body" style="min-height:135px">
            <a href="{{route("goal.show", $goal->id)}}">
                <p class="h5">
                    {{ $goal->title }}
                </p>
            </a>    
                <!-- <p>
                    {{ $goal->what }}
                </p> -->
                <p>
                    <b>Goal Type:&nbsp;&nbsp;&nbsp;</b>{{ $goal->goaltype->name}}<br>
                    <b>Created by:&nbsp;&nbsp;&nbsp;</b>{{ $goal->originalCreatedBy ? $goal->originalCreatedBy->name : $goal->user->name }}<br>
                </p>
            
        </div>
        @if ($goal->last_supervisor_comment == 'Y' and (session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
          <div class="alert alert-default-warning alert-dismissible">
          <span class="h5"><i class="icon fas fa-exclamation-triangle"></i>Your supervisor has added a comment.</span>
          </div>
        @endif
        <div class="card-footer align-items-center">
            @if(Auth::user()->reporteesCount() > 0 && (request()->is('goal/current') || request()->is('goal/library')))
                @include('goal.partials.goal-share-with-dropdown')
            @endif
            <div class="flex-fill"></div>

            @if(($cardDesign ?? 'default') === 'default')
            <div>
                <!-- <b>Goal created by:&nbsp;</b>{{ $goal->user->name}} <br> -->
                @if($goal->is_library)
                <div class="flex-fill"></div>
                <span style="float:right">
                <form id="delete-goal-{{$goal->id}}" action="{{ route('goal.destroy', $goal->id)}}" method="POST" onsubmit="return confirm('{{ $goalDeleteConfirmationText ?? 'Are you sure you want to permanently delete this goal?' }}')">
                    @csrf
                    @method('DELETE')
                    <x-button size="md" icon='trash' class="ml-1 text-light" aria-label="Delete button" tooltip="Click to permanently delete this goal" style="danger"></x-button>
                </form>
                </span>
                @endif
            </div>

            @if(!$goal->is_library)
            <div>
                @if(($type ?? '') !== 'supervisor' && !$disableEdit)                    
                    Status: 
                    @include('goal.partials.status-change')
                @else
                    <x-goal-status :status="$goal->status"></x-goal-status>
                @endif
                <span style="float: right">
                <form id="delete-goal-{{$goal->id}}" action="{{ route('goal.destroy', $goal->id)}}" method="POST" onsubmit="return confirm('{{ $goalDeleteConfirmationText ?? 'Are you sure you want to permanently delete this goal?' }}')">
                    @csrf
                    @method('DELETE')
                    <x-button size="md" icon='trash' class="ml-1 text-light" aria-label="Delete button" tooltip="Click to permanently delete this goal" style="danger"></x-button>
                    <x-button
                    :href='route("goal.show", $goal->id)'
                    :tooltip="__('Click to view the details of this goal.')"
                    tooltipPosition="bottom" class="ml-2">{{__('View')}}</x-button>
                    </span>
                </form>  
                
            </div>
            @endif
            
            @if(($type ?? '') === 'supervisor' && !$disableEdit)
                <form action="{{route('goal.supervisor.copy', $goal->id)}}" method="post" onSubmit="return confirm('This goal will be copied to your Current Goals tab. You can access and edit it there without impacting your supervisor\'s goal. Continue?');">
                    @csrf
                    <x-button
                        :data-id="$goal->id"
                        class="ml-2 copy-goal"
                        style="outline-primary"
                        :tooltip="__('Click to copy this goal to your Current Goals tab and make it your own.')"
                        tooltipPosition="bottom">{{__('Copy')}}</x-button>
                </form>
            @endif
            @elseif (($cardDesign ?? 'default') === 'my-team')
            <select class="form-control is-shared" id="is_shared_{{$goal->id}}"  name="is_shared[{{ $goal->id }}]" data-goal-id="{{ $goal->id }}" >
                <option value="1" {{ count($goal->sharedWith) > 0 ? 'selected' : ''}}>Shared</option>
                <option value="0" {{ count($goal->sharedWith) > 0 ? '' : 'selected'}}>Private</option>
            </select>
            <select multiple class="form-control search-users ml-1" id="search-users-{{$goal->id}}" name="share_with[{{$goal->id}}][]"  {{ count($goal->sharedWith) > 0 ? '' : 'disabled'}} data-goal-id="{{$goal->id}}">
                @php
                    $alreadyAdded = [];
                @endphp
                @foreach ($goal->sharedWith as $employee)
                    <option value="{{ $employee->id }}" selected> {{$employee->name}}</option>
                    @php array_push($alreadyAdded, $employee->id) @endphp
                @endforeach
                @foreach ($employees as $employee)
                    @if (!in_array($employee->id, $alreadyAdded))
                        <option value="{{ $employee->id }}" {{ count($alreadyAdded) === 0 ? 'selected' : ''}}> {{$employee->name}}</option>
                    @endif
                @endforeach
            </select>
            <span style="float:right">
            <x-button size="md" type="button" icon='trash' class="ml-1 text-light" aria-label="Delete button" tooltip="Click to permanently delete this goal" style="danger" data-action="delete-goal" data-goal-id="{{$goal->id}}" data-confirmation="{{ $goalDeleteConfirmationText ?? 'Are you sure you want to permanently delete this goal?' }}"></x-button>
            </span>
            @endif

        </div>
        <!-- /.card-footer -->
    </div>
