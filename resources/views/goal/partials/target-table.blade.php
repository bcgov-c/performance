       <div class="card card-primary">

   <table class="table ">

  <thead>
    <tr>
      <th scope="col">Title</th>
      <th scope="col">Goal Type</th>
      <th scope="col">Tags</th>
      <th scope="col">Start Date</th>
      <th scope="col">End Date</th>
      <th scope="col">Status</th>
      <th> </th>
    </tr>
  </thead>
  <tbody>
   @foreach ($goals as $goal)
   <tr>
      <th scope="row"  onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">
        <a href="{{route("goal.show", $goal->id)}}">
          {{ $goal->title }}
        </a>
      </th>
      <td onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">{{ $goal->typename }}</td>
      <td onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">{{ $goal->tagnames }}</td>
      <td onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">{{ $goal->start_date_human }}</td>
      <td onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">{{ $goal->target_date_human }}</td>
      <td>
        @include('goal.partials.status-change')
      </td>
      <td>
        <div class="d-flex">
          <x-button :href="route('goal.show', $goal->id)" size='sm' class="mr-2">View</x-button>
          <form id="delete-goal-{{$goal->id}}" action="{{ route('goal.destroy', $goal->id)}}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this goal?')">
            @csrf
            @method('DELETE')
            <x-button size='sm' icon='trash' style="danger"></x-button>
          </form>
        </div>
      </td>
    </tr>
      @endforeach
  </tbody>

</table>
</div>
