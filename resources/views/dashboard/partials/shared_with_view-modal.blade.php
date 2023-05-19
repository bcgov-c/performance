
<div class="modal fade" id="profileSharedWithViewModal" tabindex="-1" aria-labelledby="profileSharedWithViewLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="profileSharedWithViewLabel">Profile shared with</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <table class="table table-sm">
            <thead>
                <th>Shared Elements</th>
                <th>Shared With</th>
                <th>Comment</th>
            </thead>
            <tbody>
            @foreach($sharedList as $item)
                <tr>
                    <td>
                        @if(count($item->shared_item) == 2)
                            All
                        @elseif(in_array(1, $item->shared_item))
                            Goals
                        @else
                            Conversations
                        @endif
                    </td>
                    <td>{{$item->sharedWithUser->name}}</td>
                    <td>{{$item->comment}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>