
<div class="modal fade" id="profileSharedWithViewModal" tabindex="0" aria-labelledby="profileSharedWithViewLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="profileSharedWithViewLabel">Profile shared with</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="close-white" style="border: 1px solid transparent; padding: 3px;" tabindex="0" onfocus="this.style.border = '1px solid white';" onblur="this.style.border = '1px solid transparent';">×</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <table class="table table-sm" aria-describedby="Profile Shared with details">
            <thead>
                <th>Shared With</th>
                <th>Comment</th>
            </thead>
            <tbody>
            @foreach($sharedList as $item)
                <tr>
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