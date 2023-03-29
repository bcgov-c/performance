
<x-side-layout title="{{ __('Notifications - Performance Development Platform') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight" role="banner">
            Notify Users
        </h2> 
		@include('sysadmin.notifications.partials.tabs')
    </x-slot>

<!-- Modal (Notification detail) -->
<div class="modal fade" id="notification-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-charity-name" id="notification-modal-label">Notification Detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <!-- content will be load here -->                          
            <div id="notification-modal-body"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
	<div class="card-body">

			<h2 class="mb-4">Notification Log</h2>

		<form id="search-form">
				@csrf
			<div class="form-row">
				<div class="col-md-2 mb-2">
				<label for="date_sent_from">Date Sent (From)</label>
				<input type="date" class="form-control" id="date_sent_from" name="date_sent_from" placeholder="" value="">
				</div>
				<div class="col-md-2 mb-2">
				<label for="date_sent_to">Date Sent (To)</label>
				<input type="date" class="form-control" id="date_sent_to" name="date_sent_to" placeholder="" value="">
				</div>
				<div class="col-md-3 mb-2">
				<label for="recipients">Recipients</label>
					<input type="text" class="form-control" id="recipients" name="recipients" placeholder="name">
				</div>
				<div class="col-md-2 mb-2">
				<label for="alert_format">Alert Format</label>
				<select  class="form-control @error('alert_format') is-invalid @enderror" id="alert_format" name="alert_format">
					<option value="">All </option>
					@foreach ($alert_format_list as $key => $value)
					  <option value="{{ $key }}" {{ old('alert_format') == $value ?? 'selected'}}>{{ $value }}</option>
					@endforeach
				  </select>     
{{-- 
				<input type="text" class="form-control" id="alert_format" placeholder="" value="">
 --}}				  
				</div>
				{{-- <div class="col-md-1 mb-1">
					<label></label>
					<button class="btn btn-primary mt-2" type="submit">Search</button>
				</div> --}}
				
			</div>


            <div class="form-row">
				<div class="col-md-3 mb-2">
				<label for="notify_user">Notify User</label>
				<input type="text" class="form-control" id="notify_user" name="notify_user" placeholder="notify user name" value="">
				</div>
				<div class="col-md-3 mb-2">
				<label for="overdue_user">Overdue User</label>
				<input type="text" class="form-control" id="overdue_user" name="overdue_user" placeholder="Overdue user name" value="">
				</div>
				<div class="col-md-2 mb-2">
				<label for="notify_due_date">Notify Due Date</label>
					<input type="date" class="form-control" id="notify_due_date" name="notify_due_date" placeholder="">
				</div>
                <div class="col-md-2 mb-2">
                    <label for="alert_format">Notify For Days</label>
                <select  class="form-control id="notify_for_days" name="notify_for_days">
					<option value="">All </option>
					@foreach ( [ '30' => 'Due in Month', '7' => 'Due in Week', '0' => 'Overdue'] as $key => $value)
					  <option value="{{ $key }}">{{ $value }}</option>
					@endforeach
				  </select>
                </div>

                {{-- <div class="col-md-2 mb-2">
                    <label for="notify_for_days">Notify for days</label>
                        <input type="text" class="form-control" id="notify_for_days" name="notify_for_days" placeholder="">
                </div> --}}
  

				<div class="col-md-1 mb-1">
					<label></label>
					<button class="btn btn-primary mt-2" type="submit">Search</button>
				</div>
				
			</div>

		</form>

		<p></p>
		<table class="table table-bordered table-striped" id="notificationlog-table">
			<thead>
				<tr>
                    <th>Tran ID</th>
					<th>Date Sent</th>
					<th>Subject</th>
					<th>Recipients</th>
					<th>Alert Type</th>
					<th>Alert Format</th>
					<th>Action</th>
                    <th>Notify User</th>
                    <th>Overdue User</th>
                    <th>Overdue Date</th>
                    <th>Notify For days</th>
				</tr>
			</thead>
		</table>

	</div>    
</div>   



<x-slot name="css">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<style>
	.text-truncate-30 {
		white-space: wrap; 
		overflow: hidden;
		text-overflow: ellipsis;
		width: 30em;
	}

    .text-truncate-10 {
		white-space: wrap; 
		overflow: hidden;
		text-overflow: ellipsis;
		width: 5em;
	}
	#notificationlog-table_filter label {
		text-align: right !important;
	}
    #notificationlog-table_filter {
        display: none;
    }
    
</style>
</x-slot>

<x-slot name="js">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
    <script>

    var oTable = $('#notificationlog-table').DataTable({
        "scrollX": true,
        retrieve: true,
        processing: true,
        serverSide: true,
        'order': [[0, 'desc']],
        ajax: {
            url: '{!! route('sysadmin.notifications') !!}',
            data: function (d) {
                d.date_sent_from = $('input[name=date_sent_from]').val();
                d.date_sent_to = $('input[name=date_sent_to]').val();
				d.recipients = $('input[name=recipients]').val();
				d.alert_format = $('select[name=alert_format]').val();
                d.notify_user = $('input[name=notify_user]').val();
                d.overdue_user = $('input[name=overdue_user]').val();
                d.notify_due_date = $('input[name=notify_due_date]').val();
                d.notify_for_days = $('select[name=notify_for_days]').val();
			}
        },
        columns: [
            {data: 'id', name: 'id'},
			{data: 'date_sent', name: 'date_sent', className: 'dt-nowrap'},
            {data: 'subject', name: 'subject', className: 'dt-nowrap'},
            {data: 'recipients', name: 'recipients'},
            {data: 'alert_type_name', name: 'alert_type_name'},
            {data: 'alert_format_name', name: 'alert_format_name'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
            {data: 'notify_user.name', name: 'notify_user_id', defaultContent: '',  orderable: false  },
            {data: 'overdue_user.name', name: 'overdue_user_id', defaultContent: '', orderable: false},
            {data: 'notify_due_date', name: 'notify_due_date', defaultContent: '', },
            {data: 'notify_for_days', name: 'notify_for_days', defaultContent: '', },
        ],
		columnDefs: [
                // {
                //     render: function (data, type, full, meta) {
                //         console.log(data);
                //         array_tos = data.split(";");
                //         if (array_tos.length > 5) {
                //             text = '( ' + array_tos.length + ' recipients )';
                //         } else { text = data; }
                //         return '<span>' + text + "</span>";
                //     },
                //     targets: 3
                // },

                {
                    render: function (data, type, full, meta) {
                        return '<div data-toggle="tooltip" class="text-truncate-30" title="' + data + '">' + data + "</div>";
                    },
                    targets: 2
                },
				{
					render: function (data, type, full, meta) {
                        return "<small>" + data + "</small>";
                    },
                    targets: 0
				}
             ]
    });
	
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
		 console.log( $('select[name=alert_format]').val() );
        oTable.draw();
    });

      // dispaly Detail in Modal
      $(document).on("click", ".notification-modal" , function(e) {
                e.preventDefault();

          $.ajax({
            url: '/sysadmin/notifications/detail/' + $(this).attr('value') ,
            type: 'GET',
            dataType: 'html'
          })
          .done(function(data){
          
            //$('notification-modal-label').html('Charity detail');
            $('#notification-modal-body').html('');    
            $('#notification-modal-body').html(data); // load response 
            $('#notification-modal').modal('show');
            //$('#modal-loader').hide();        // hide ajax loader   
          })
          .fail(function(){
              $('#notification-modal-body').html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
              //$('#modal-loader').hide();
              $('#notification-modal').modal('show');
          });

      });




    </script>

</x-slot>


</x-side-layout>