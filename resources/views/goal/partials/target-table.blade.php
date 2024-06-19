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
      <th scope="col">Shared With <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" 
      data-content="You can use this function to share a goal with colleagues. This goal will appear on their “Goals Shared with Me” tab and they will be able view and add comments to the goal. 
      <br/>Type the name of your colleague into the search field to find and select them. You can repeat this process to add additional colleagues if needed. You can also remove a colleague by clicking the &quot;X&quot; next to their name.
      <br/>As the creator of the shared goal, you are the only one that can make major edits or change the status to achieved or archived." 
      data-original-title="" title="" aria-describedby="popover271882"> </i></th>
      @endif
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
      @if ($type == 'current')
      <td>  
        @if(!isset($viewingProfileAs))
          <select multiple class="form-control share-with-users" id="share_with_users" name="share_with_users[]">
              @if($goal->shared_user_id != '' && $goal->shared_user_name != '')
                  @php
                      $share_user_id_arr = explode(',', $goal->shared_user_id);
                      $share_user_name_arr = explode(',', $goal->shared_user_name);
                  @endphp
                  @foreach($share_user_id_arr as $index => $user_id)
                      <option value="{{ $user_id }}" selected>{{ $share_user_name_arr[$index] }}</option>
                  @endforeach
              @endif
          </select>
        @else
        @if($goal->shared_user_id != '' && $goal->shared_user_name != '')
            @php
                $formatted_user_names = str_replace(',', ', ', $goal->shared_user_name);
            @endphp
            {{ $formatted_user_names }}
        @endif
        @endif
      </td>
      @endif
      <td>
        @include('goal.partials.status-change')
      </td>
      <td>
        <div class="d-flex">          
          <form id="delete-goal-{{$goal->id}}" action="{{ route('goal.destroy', $goal->id)}}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this goal?')">
            @csrf
            @method('DELETE')
            <button type="button" aria-label=" Click on the view button to view the goal" alt=" View the goal" 
            onclick="viewGoal({{ $goal->id }})"  class="btn btn-outline-primary btn-sm ml-2 mt-2"  tabindex="0">View <i class="fas fa-eye fa-lg"></i></button>
            @if(!isset($viewingProfileAs))
            <button type="submit" aria-label="Click on the delete button to delete the goal" alt="Delete the goal" 
            class="btn btn-outline-danger btn-sm ml-2 delete-dn mt-2"   tabindex="0">Delete <i class="fas fa-trash-alt fa-lg"></i></button>
            @endif
          </form>
        </div>
      </td>
    </tr>
      @endforeach
  </tbody>

</table>
</div>
