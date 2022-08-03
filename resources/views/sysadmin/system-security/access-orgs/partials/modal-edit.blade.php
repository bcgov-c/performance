{{-- Modal for  --}}
<div class="modal fade" id="accessorg-edit-modal" tabindex="-1" role="dialog" aria-labelledby="accessorgModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
	  <div class="modal-content">
		<div class="modal-header bg-primary">
		  <h5 class="modal-title" id="accessorgModalLabel">Edit an existing access</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
		  <form id="accessorg-edit-model-form">
            <input type="hidden" class="form-control"  name="id" value="" readonly>

			<div class="form-group row">
                <label for="organization" class="col-sm-2 col-form-label">Organization:</label>
                <div class="col-sm-6">
					<input type="text" class="form-control" name="organization" value="" readonly>
                </div>
            </div>

			<div class="form-group row">
                <label for="allow_login" class="col-sm-4 col-form-label">Allow to login:</label>
                <div class="col-sm-2">
					<select name="allow_login" class="form-control">
						<option value="Y">Yes</option>
						<option value="N">No</option>
					</select>
                </div>
            </div>

			<div class="form-group row">
                <label for="allow_inapp_msg" class="col-sm-4 col-form-label">Allow In-App Message received:</label>
                <div class="col-sm-2">
					<select name="allow_inapp_msg" class="form-control">
						<option value="Y">Yes</option>
						<option value="N">No</option>
					</select>
                </div>
            </div>

			<div class="form-group row">
                <label for="allow_email_msg" class="col-sm-4 col-form-label">Allow Email Message received:</label>
                <div class="col-sm-2">
					<select name="allow_email_msg" class="form-control">
						<option value="Y">Yes</option>
						<option value="N">No</option>
					</select>
                </div>
            </div>

		
		  </form>
		</div>
		<div class="modal-footer">
		  <button type="button" id="save-confirm-btn"  class="btn btn-primary" >Save</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
		</div>
	  </div>
	</div>
</div>


