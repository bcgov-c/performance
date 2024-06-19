<x-button style="link" class="excused-btn" data-user-row="{{$row}}" data-excused="{{$excused}}" data-user-demo="{{$row->employee_demo}}" data-excused-type="{{$excused_type}}" data-current_status="{{$current_status}}"  
    data-user-id="{{$row->id}}" data-employee_name="{{$row->employee_name}}" data-toggle="modal" 
    data-target="#editModal">{{ $text }}</x-button>
