<ul class="list-group">
    @foreach($eemployees as $eemployee)
    <li class="list-group-item pl-5 py-1">

        <a role="button" class="disabled collapsed">

            <div class="container" style="vertical-align:middle; float:left">
                <div class="row">
                    <div class="col-1">
                        <input pid="{{ $eparent_id }}" 
                        type="checkbox"  id="euserCheck{{ $eemployee->employee_id }}" name="euserCheck[]" 
                        {{ (is_array(old('euserCheck')) and in_array($eemployee->employee_id, old('euserCheck'))) ? ' checked' : '' }}
                               value="{{ $eemployee->employee_id }}">
                    </div>
                    <div class="col"><span>{{ $eemployee->employee_name  }}</span></div>
                    <div class="col"><span>{{ $eemployee->jobcode_desc  }}</span></div>
                    <div class="col"><span>{{ $eemployee->employee_email  }}</span></div>
                </div>
            </div>

        </a>
 
    </li>
    @endforeach
</ul>