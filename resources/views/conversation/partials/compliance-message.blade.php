
@if($conversationMessage != '')
<div class="mt-4">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-default-{{$conversationMessage[1]}} alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <span class="h5">{{$conversationMessage[0]}}</span>
            </div>
        </div>
    </div>
</div>
@endif