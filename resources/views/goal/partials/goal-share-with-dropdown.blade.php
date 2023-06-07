<span style="float:left">
<!-- <label class="d-flex justify-content-left align-items-center" style="font-weight: normal;" 
    @if(!(isset($doNotShowInfo) && $doNotShowInfo)) 
        data-trigger="click" data-toggle="popover" data-placement="left" data-html="true" data-content="By default, all of your goals are private. Use the &quot;Share with&quot; dropdown menu to make a goal visible to selected employees. This lets team members know what you are working on and may help team members set their own goals." 
    @endif
    > -->   
    @if(!(isset($noLabel)) || (isset($noLabel) && $noLabel == false))        
    {{ $shareWithLabel ?? "Shared with"}}:&nbsp; 
    @if(!(isset($doNotShowInfo) && $doNotShowInfo))
        <i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="right" data-html="true" data-content="By default, all of your goals are private. Use the &quot;Share with&quot; dropdown menu to make a goal visible to selected employees. This lets team members know what you are working on and may help team members set their own goals." >
        </i>        
    @endif    
    @endif
         
    <select multiple class="form-control search-users ml-1" id="search-users-{{$goal->id}}" name="share_with[{{$goal->id}}][]" data-goal-id="{{$goal->id}}">
        @php
            $alreadyAdded = [];
        @endphp      
        <?php
        $employee_list = array();
        $i = 0;
        foreach ($goal->sharedWith as $employee){
            $employee_list[$i]['id'] = $employee->id;
            $employee_list[$i]['name'] = $employee->name;
            array_push($alreadyAdded, $employee->id);
            $i++;
        }
        foreach ($employees as $employee){
            if (!in_array($employee->id, $alreadyAdded)){
                $employee_list[$i]['id'] = $employee->id;
                $employee_list[$i]['name'] = $employee->name;
                $i++;
            }
        }        
        
        if(isset($from) && $from == 'bank') {
            foreach ($shared_employees as $employee){
                if (!in_array($employee->shared_id, $alreadyAdded)){
                    $employee_list[$i]['id'] = $employee->shared_id;
                    $employee_list[$i]['name'] = $employee->name;
                    $i++;
                }
            }
            
            if (!in_array(auth()->user()->id, $alreadyAdded)){
                $employee_list[$i]['id'] = auth()->user()->id;
                $employee_list[$i]['name'] = auth()->user()->name;
            }
            
            //asort($employee_list);
            //error_log(print_r($employee_list,true));
            usort($employee_list, function($a, $b){ return strcmp($a["name"], $b["name"]); });
        }
        ?>
        
        @foreach ($employee_list as $employee)
            @if(in_array($employee['id'], $alreadyAdded))
                <option value="{{ $employee['id'] }}" selected> {{$employee['name']}}</option>
            @else
                <option value="{{ $employee['id'] }}"> {{$employee['name']}}</option>
            @endif
        @endforeach
        
    </select>
    
<!-- </label>   -->
</span>


