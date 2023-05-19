<ul class="list-group">
    @foreach($employees as $employee)
    <li class="list-group-item pl-5 py-1">

        <a role="button" class="disabled collapsed">

            <div class="container" style="vertical-align:middle; float:left">
                <div class="row">
                    <div class="col-1">
                        <input pid="{{ $parent_id }}" 
                        type="checkbox"  id="userCheck{{ $employee->employee_id }}" name="userCheck[]" 
                        {{ (is_array(old('userCheck')) and in_array($employee->employee_id, old('userCheck'))) ? ' checked' : '' }}
                               value="{{ $employee->employee_id }}">
                    </div>
                    <div class="col"><span>{{ $employee->employee_name  }}</span></div>
                    <div class="col"><span>{{ $employee->job_title  }}</span></div>
                    <div class="col"><span>{{ $employee->employee_email  }}</span></div>
                </div>
            </div>

        </a>
 
    </li>
    @endforeach
</ul>