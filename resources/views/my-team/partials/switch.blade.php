<x-button style="link" class="excused-btn" data-user-row="{{$row}}" data-user-demo="{{$row->employee_demo}}" data-excused-type="{{$excused_type}}"  data-current-status="{{$current_status}}"  
    data-user-id="{{$row->id}}" data-user-name="{{$row->name}}" data-user-excuse_flag="{{$row->excuse_flag}}" data-toggle="modal" data-target="#employee-excused-modal">{{ $yesOrNo }}</x-button>
