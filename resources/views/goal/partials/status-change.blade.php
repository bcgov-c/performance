<div class="btn-group" tabindex="0">
  <button type="button" aria-label="Current goal is marked as {{ $goal->status }}" class="btn btn-outline-secondary dropdown-toggle text-capitalize" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <x-goal-status :status="$goal->status"></x-goal-status>
  </button>
  <div class="dropdown-menu goal-change">
    @foreach (\Config::get("global.status") as $status => $value)
        @if ($status != $goal->status)
            <a aria-label="Click on the button to mark the goal as {{ $status }}" class="dropdown-item text-capitalize" data-current-status="{{$goal->status}}" data-status="{{$status}}" href="{{ route('goal.update-status', [$goal->id, $status]) }}">
                <x-goal-status :status="$status"></x-goal-status>
            </a>
        @endif
    @endforeach
  </div>
</div>