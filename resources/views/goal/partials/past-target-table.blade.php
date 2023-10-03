       <div class="card card-primary">

   <table class="table ">

  <thead>
    <tr>
      <th scope="col"><a href="javascript:sort('title');">Title</a></th>
      <th scope="col"><a href="javascript:sort('typename');">Goal Type</a></th>
      <th scope="col"><a href="javascript:sort('tagnames');">Tags</a></th>
      <th scope="col"><a href="javascript:sort('start_date');">Start Date</a></th>
      <th scope="col"><a href="javascript:sort('target_date');">End Date</a></th>
      <th scope="col">Shared With</th>
      <th scope="col"><a href="javascript:sort('status');">Status</a>
      <i class="fa fa-info-circle" id="status_label" data-trigger='click' data-toggle="popover" data-placement="right" data-html="true" data-content="<ul><li><b>Active</b>: currently in progress or scheduled for a future date</li><li><b>Achieved</b>: supervisor and employee agree objectives met</li><li><b>Archived</b>: cancelled, deferred or no longer relevant to your work but you want to save for future reference</li></ul>You can delete goals that do not meet any of the above criteria"></i>				
      </th>
      <th> </th>
    </tr>
  </thead>
  <tbody>
   @foreach ($goals as $goal)
   <tr  data-goal-id="{{$goal->id}}">
      <th scope="row"  onclick="window.location.href = '{{route("goal.show", $goal->id)}}';" style="cursor: pointer">
        <a href="{{route("goal.show", $goal->id)}}">
          {{ $goal->title }}
        </a>
      </th>
      <td >{{ $goal->typename }}</td>
      <td >
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
      <td  >{{ $goal->start_date_human }}
      </td>
      <td >{{ $goal->target_date_human }}</td>
      <td>  
        <select multiple class="form-control share-with-users"  name="share_with_users[]" disabled>
            <?php if ($goal->owner_id == $authId) { ?>
                <?php if($goal->shared_user_id != '' && $goal->shared_user_name != ''){
                  $share_user_id_arr = explode(',', $goal->shared_user_id);
                  $share_user_name_arr = explode(',', $goal->shared_user_name);
                  for ($i=0; $i<count($share_user_id_arr); $i++){
                      echo "<option value='".$share_user_id_arr[$i]."'  selected>".$share_user_name_arr[$i]."</option>";                    
                  }
              }?>
            <?php } else {?>
                  <?php echo "<option value='".$goal->owner_id."'  selected>".$goal->owner_name."</option>";  ?>  
            <?php } ?>
            
            
        </select>
      </td>
      <td>
        @if($goal->login_role == 'owner') 
          @include('goal.partials.status-change')
        @else
          <x-goal-status :status="$goal->status"></x-goal-status>
        @endif
        
      </td>
      <td>
        <div class="d-flex">          
          <form id="delete-goal-{{$goal->id}}" action="{{ route('goal.destroy', $goal->id)}}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this goal?')">
            @csrf
            @method('DELETE')
            <x-button :href="route('goal.show', $goal->id)" size='sm' class="mr-2">View</x-button>
            @if($goal->login_role == 'owner') 
              <x-button size='sm' icon='trash' style="danger"></x-button>
            @endif
          </form>
        </div>
      </td>
    </tr>
      @endforeach
  </tbody>

</table>
</div>
