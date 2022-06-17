<span style="float:left">
<label class="d-flex justify-content-left align-items-center" style="font-weight: normal;" 
    @if(!(isset($doNotShowInfo) && $doNotShowInfo)) 
        data-trigger="hover" data-toggle="popover" data-placement="left" data-html="true" data-content="By default, all of your goals are private. Use the &quot;Share with&quot; option to make a goal visible to selected employees. This lets team members know what you are working on and may help team members' set their own goals." 
    @endif
    >
    {{ $shareWithLabel ?? "Shared with"}}:&nbsp; 
    @if(!(isset($doNotShowInfo) && $doNotShowInfo))
        <i class="fa fa-info-circle">
        </i>
        &nbsp;
    @endif
    <select multiple class="form-control search-users ml-1" id="search-users-{{$goal->id}}" name="share_with[{{$goal->id}}][]" data-goal-id="{{$goal->id}}">
        @php
            $alreadyAdded = [];
        @endphp
        @foreach ($goal->sharedWith as $employee)
            <option value="{{ $employee->id }}" selected> {{$employee->name}}</option>
            @php array_push($alreadyAdded, $employee->id) @endphp
        @endforeach
        @foreach ($employees as $employee)
            @if (!in_array($employee->id, $alreadyAdded))
                <option value="{{ $employee->id }}"> {{$employee->name}}</option>
            @endif
        @endforeach
    </select>
    
</label>
</span>

