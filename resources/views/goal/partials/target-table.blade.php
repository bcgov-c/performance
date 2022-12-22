       <div class="card card-primary">

   <table class="table ">

  <thead>
    <tr>
        <th scope="col"><a href="javascript:sort('title');">Title</a></th>
      <th scope="col"><a href="javascript:sort('goal_type_id');">Goal Type</a></th>
      <th scope="col"><a href="javascript:sort('tagnames');">Tags</a></th>
      <th scope="col"><a href="javascript:sort('start_date');">Start Date</a></th>
      <th scope="col"><a href="javascript:sort('target_date');">End Date</a></th>
      @if ($type == 'current')
      @if(!session()->has('view-profile-as')) 
      @if(session()->has('has_employees') > 0 && (request()->is('goal/current') || request()->is('goal/goalbank')))
      <th scope="col">Shared With</th>
      @endif
      @endif
      @endif
      <th scope="col"><a href="javascript:sort('status');">Status</a></th>
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
      <td onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">
          <?php
          $tags_arr = explode(",", $goal->tagnames);
          $total_tags = count($tags_arr);
          $i = 0;
          if($total_tags > 0) {
            foreach($tags_arr as $tag_item) {
                $i++;
                if ($i < $total_tags) {
                  echo $tag_item."<br/>";
                } else {
                  echo $tag_item;  
                }
            }
          }          
          ?>
          </td>
      <td onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">{{ $goal->start_date_human }}
      </td>
      <td onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">{{ $goal->target_date_human }}</td>
      @if ($type == 'current')
      @if(!session()->has('view-profile-as')) 
      <td>          
            @if(session()->has('has_employees') > 0 && (request()->is('goal/current') || request()->is('goal/goalbank')))
                <div>
                @php $noLabel = true @endphp    
                @include('goal.partials.goal-share-with-dropdown')
                </div>
            @endif          
      </td>
      @endif
      @endif
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
