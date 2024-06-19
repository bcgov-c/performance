<ul class="list-group">
    @foreach($eemployees as $employee)
    <li class="list-group-item pl-5 py-1">

        <a role="button" class="disabled collapsed">

            <div class="container" style="vertical-align:middle; float:left">
                <div class="row">
                    <div class="col-1">
                        <input pid="{{ $eparent_id }}" 
                        type="checkbox"  id="euserCheck{{ $employee->employee_id }}" name="euserCheck[]" 
                        {{ (is_array(old('euserCheck')) and in_array($employee->employee_id, old('euserCheck'))) ? ' checked' : '' }}
                               value="{{ $employee->employee_id }}">
                    </div>
                    <div class="col"><span>{{ $employee->employee_name  }}</span></div>
                    <div class="col"><span>{{ $employee->jobcode_desc  }}</span></div>
                    <div class="col"><span>{{ $employee->employee_email  }}</span></div>
                </div>
            </div>

        </a>
 
    </li>
    @endforeach
</ul>