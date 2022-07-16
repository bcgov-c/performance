<!-- Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModal"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary" id="messageHeader">
                <h5 class="modal-title" id="messageDashboardModal"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body data-placeholder" id="messageDashboardContent">
                {!! $content !!}
            </div>
            {{-- <div class="modal-footer" id="messageFooter">
                <div>
                    <x-button type="button" class="btn btn-secondary float-right"> Close </x-button>
                </div>
            </div> --}}
        </div>
    </div>
</div>
<!-- End Modal -->
