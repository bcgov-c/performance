<ul class="list-group">
    @foreach($aemployees as $employee)
    <li class="list-group-item pl-5 py-1">

        <a role="button" class="disabled collapsed">

            <div class="container" style="vertical-align:middle; float:left">
                <div class="row">
                    <div class="col-1">
                        <input pid="{{ $aparent_id }}" 
                        type="checkbox"  id="auserCheck{{ $employee->employee_id }}" name="auserCheck[]" 
                        {{ (is_array(old('auserCheck')) and in_array($employee->employee_id, old('auserCheck'))) ? ' checked' : '' }}
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