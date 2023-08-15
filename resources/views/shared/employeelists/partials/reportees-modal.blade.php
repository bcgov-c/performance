<!-- Modal --> 
<ol style="overflow-y: scroll; overflow-x: hidden;"> 
<div class="modal fade reportees-modal" id="reportees-modal" tabindex="-1" aria-labelledby="Employee List Reportees" 
    aria-hidden="true" style="overflow-y: scroll; overflow-x: hidden;"> 
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered"> 
        <div class="modal-content"> 
            <div class="modal-header bg-primary"> 
                <h5 class="modal-title" id="reporteesTitle">{{__('Direct / Shared Reports')}}</h5> 
                <button type="button" class="close" id="close-reportees-modal" name="close-reportees-modal" data-dismiss="modal" aria-label="Close"> 
                    <span aria-hidden="true">&times;</span> 
                </button> 
            </div> 
            <div class="p-3"> 
                <!-- <strong>Below are the reportees</strong> <br>  -->
                <table class="table table-bordered reporteesTable table-striped" id="reporteesTable" name="reporteesTable" style="width: 100%; overflow-x: auto; "></table>
            </div> 
        </div> 
    </div> 
</div> 
</ol> 
@push('css') 
    <style> 
 
        .modal-dialog {  
            overflow-y: initial !important; 
        } 
 
        .modal-body { 
            max-height: 80vh; 
            overflow-y: auto; 
        } 
        .p-3{ 
            max-height: 95vh; 
            overflow-y: auto; 
        } 
         
    </style> 
@endpush 
 

