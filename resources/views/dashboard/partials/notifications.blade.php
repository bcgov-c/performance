<div>

  <div class="btn-group">
    <label for="master">
      <input id="master" type="checkbox" name="select_all">&nbsp;&nbsp;Select All
    </label>
    &nbsp;&nbsp;&nbsp;&nbsp
    <label for="action_btn">
      <button type="button" icon="fas fa-xs fa-ellipsis-v" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Actions
      </button>
      <div class="dropdown-menu"  size="xs">
          <x-button icon="fas fa-xs fa-trash-alt" href="{{ route('dashboard.destroyall') }}" class="dropdown-item delete_all" id='delete_all'>
              Delete selected
          </x-button>
          <x-button icon="fas fa-xs fa-envelope-open" href="{{ route('dashboard.updatestatus') }}" class="dropdown-item update_status" >
              Mark as read
          </x-button>
          <x-button icon="fas fa-xs fa-envelope" href="{{ route('dashboard.resetstatus') }}" class="dropdown-item reset_status" >
              Mark as unread
          </x-button>
      </div>
    </label>
  </div>

  @foreach($notifications as $notification)
  @if ($notification->relatedGoal)
  <div class="rounded shadow-sm" size="xs" >
      <div class="p-2">
        <div class="pl-2 d-flex align-items-center justify-content-center {{$notification->status === null ? "border-left" : ""}}  border-primary" style="border-width:3px !important" id="tr_{{$notification->id}}">
            <input id='ntfyitem' type='checkbox' class='sub_chk' data-id="{{$notification->id}}">
            <div style="cursor:pointer;" onclick="window.location.href = '{{route("goal.show", $notification->relatedGoal->id)}}'">
              <div class="pl-3 d-flex align-items-center justify-content-center flex-row">
                  <x-profile-pic size="36"></x-profile-pic>
                  <div class="d-flex flex-column">
                      <strong>
                            {{$notification->comment}}
                        </strong>
                      <div class="text-muted">
                        Title: {{$notification->relatedGoal->title}} | Goal Type: {{$notification->relatedGoal->goalType->name}} | Date: {{$notification->created_at->format('M d, Y H:i A')}}
                      </div>
                  </div>
              </div>
              </div>
              <div class="flex-fill"></div>
            @if (($notification->notification_type == 'GC') or ($notification->notification_type == 'GR'))
              <x-button
                  size="sm"
                  :href='route("goal.show", $notification->relatedGoal->id)'
                  :tooltip="__('Click to view the details of this goal.')"
                  tooltipPosition="bottom" class="mr-2" aria-label="Show Item">{{__('View')}}

              </x-button>
            @endif
              <x-button
                  size="sm" style="danger" icon="trash"
                  :tooltip="__('Click to delete notification.')"
                  tooltipPosition="bottom" class="btn btn-danger btn-sm float-right mr-2 delete-notification-btn" data-id="{{ $notification->id }}"
                  aria-label="Delete Notification">
              </x-button>
          </div>
      </div>
  </div>
  @endif
  @endforeach
</div>

{{ $notifications->links() }}


@include('dashboard.partials.delete-notification-hidden-form')

<x-slot name="js">

  <script>
      $(document).on('click', '.delete-notification-btn', function() {
          $('#delete-notification-form').attr(
              'action'
              , $('#delete-notification-form').data('action').replace('xxx', $(this).data('id'))
          ).submit();
      });
  </script>

    <script type="text/javascript">

            $(document).ready(function () {

                $('#master').on('click', function(e) {
                if($(this).is(':checked',true))
                 {
                    $(".sub_chk").prop('checked', true);
                 } else {
                    $(".sub_chk").prop('checked',false);
                 }
                });


                $('.delete_all').on('click', function(e) {
                    var allVals = [];
                    $(".sub_chk:checked").each(function() {
                        allVals.push($(this).attr('data-id'));
                    });
                    if(allVals.length <=0)
                    {
                        alert("Please select row(s) to update.");
                    }  else {
                        var check = confirm("Are you sure you want to delete selected row(s)?");
                        if(check == true){
                            var join_selected_values = allVals.join(",");
                            e.preventDefault();
                            $.ajax({
                              // url: $(this).data('url'),
                                url: "{{ route('dashboard.destroyall') }}",
                                // url: ele.href,
                                type: 'DELETE',
                                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                data: {'ids':join_selected_values

                                },
                                success: function (data) {
                                    if (data['success']) {
                                        $(".sub_chk:checked").each(function() {
                                            // $(this).parents("tr").remove();
                                            $("#master").prop('checked',false);
                                            $(".sub_chk").prop('checked',false);
                                        });
                                        alert(data['success']);
                                    } else if (data['error']) {
                                        alert(data['error']);
                                    } else {

                                        //alert('Whoop Something went wrong!!');
                                    }
                                },
                                error: function (data) {
                                    // alert(data.responseText);
                                }
                            });
                          $.each(allVals, function( index, value ) {
                              $('table tr').filter("[data-row-id='" + value + "']").remove();
                          });
                        }
                        // location.reload();

                    }
                });

                // $('[data-toggle=confirmation]').confirmation({
                //     rootSelector: '[data-toggle=confirmation]',
                //     onConfirm: function (event, element) {
                //         element.trigger('confirm');
                //     }
                // });
                $(document).on('confirm', function (e) {
                    var ele = e.target;
                    e.preventDefault();
                    $.ajax({
                        url: ele.href,
                        type: 'DELETE',
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success: function (data) {
                            if (data['success']) {
                                $("#" + data['tr']).slideUp("slow");
                                alert(data['success']);
                            } else if (data['error']) {
                                alert(data['error']);
                            } else {
                                alert('Whoops Something went wrong!!');
                            }
                        },
                        error: function (data) {
                            alert(data.responseText);
                        }
                    });
                    return false;
                });
            });
        </script>

        <script type="text/javascript">

                $(document).ready(function () {

                    $('.update_status').on('click', function(e) {
                        var allVals = [];
                        $(".sub_chk:checked").each(function() {
                            allVals.push($(this).attr('data-id'));
                            $("#master").prop('checked',false);
                            $(".sub_chk").prop('checked',false);
                        });
                        if(allVals.length <=0)
                        {
                            alert("Please select row(s) to update.");
                        }  else {
                                var join_selected_values = allVals.join(",");
                                e.preventDefault();
                                $.ajax({
                                    url: "{{ route('dashboard.updatestatus') }}",
                                    // url: $(this).data('url'),
                                    type: 'POST',
                                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                    data: {'ids':join_selected_values
                                    }
                                });
                                // location.reload();
                        }
                    });

                });
            </script>


            <script type="text/javascript">

                    $(document).ready(function () {

                        $('.reset_status').on('click', function(e) {
                            var allVals = [];
                            $(".sub_chk:checked").each(function() {
                                allVals.push($(this).attr('data-id'));
                                $("#master").prop('checked',false);
                                $(".sub_chk").prop('checked',false);
                            });
                            if(allVals.length <=0)
                            {
                                alert("Please select row(s) to update.");
                            }  else {
                                    var join_selected_values = allVals.join(",");
                                    e.preventDefault();
                                    $.ajax({
                                        url: "{{ route('dashboard.resetstatus') }}",
                                        // url: $(this).data('url'),
                                        type: 'POST',
                                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                        data: {'ids':join_selected_values
                                        }
                                    });
                                    // location.reload();
                            }
                        });

                    });
                </script>


    </x-slot>
