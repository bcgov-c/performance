<div class="card px-3 pb-3">
    <div class="p-0">
        <div class="accordion-option">
            @error('euserCheck')                
            <span class="text-danger">
                {{  'The recipient is required.'  }}
            </span>
            @enderror
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <h6></h6>
            <table class="table table-bordered" id="eemployee-list-table"></table>
        </div>    
    </div>   
</div>


@push('css')

    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<style>
	#eemployee-list-table_filter label {
		text-align: right !important;
        padding-right: 10px;
	} 
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap4.min.js"></script>

    <script>
    
        $(document).ready(function() {
            // var euser_selected = [];

        });

    </script>
@endpush

