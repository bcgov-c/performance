
<div class="container">
    <div class="row mt-2">
        <div class="col col-sm-2">Subject:</div>
        <div class="col col-sm-8 font-weight-bold">{{ $notificationLog->subject }}</div>
    </div>
    <div class="row mt-2">
        <div class="col col-sm-2">Sender:</div>
        <div class="col col-sm-8 font-weight-bold">{{ $notificationLog->sender ? $notificationLog->sender->name : $notificationLog->sender_email }}</div>
    </div>
    <div class="row mt-2">
        <div class="col col-sm-2">Recipients</div>
        <div class="col col-sm-8 font-weight-bold">{{ $notificationLog->recipientNames() }}</div>
    </div>
    <div class="row mt-2">
        <div class="col col-sm-2">Body:</div>
        <div class="col col-sm-8 font-weight-bold">
            <textarea rows="5" type="text" class="form-control" id="description" name="description"
                readonly>{{ $notificationLog->description }}</textarea>
        </div>    
    </div>


    @if ( $notificationLog->template_id )
    <div class="row mt-2">
        <div class="col col-sm-2">Template:</div>
        <div class="col col-sm-8 font-weight-bold">{{ $notificationLog->Template->template }}</div>
    </div>
    @endif

    <div class="row mt-2">
        <div class="col col-sm-2">Sent on:</div>
        <div class="col col-sm-8 font-weight-bold">{{ $notificationLog->date_sent }}</div>
    </div>


</div>

    
<script>
    $(document).ready(function(){ 
        
        CKEDITOR.replace('description', {
                toolbar: "Custom",
                toolbar_Custom: [],
        });

    });

</script>