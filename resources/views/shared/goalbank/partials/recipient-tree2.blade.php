<div class="card px-3 pb-3">
    @error('userCheck')                
        <div class="p-0">
            <span class="text-danger">
                {{  'The recipient is required.'  }}
            </span>
        </div>
    @enderror
    @if ($eorgs->count() > 0)
        <div class="p-0">
            <div class="eaccordion-option">
                <a href="javascript:void(0)" class="toggle-accordion" accordion-id="#eaccordion"></a>
            </div>
        </div>
        <div id="eaccordion-level0">
            @foreach($eorgs as $eorg)
                <div class="card">
                    @if ($eorg->children->count() > 0 )    
                        <div class="card-header" id="heading-{{ $eorg->id }}">
                            <h6 class="mb-0">
                                <a role="button" data-toggle="collapse" href="#ecollapse-{{ $eorg->id }}" aria-expanded="false" class="collapsed"
                                    aria-controls="ecollapse-{{ $eorg->id }}">
                                    <span class="pr-2">{{ $eorg->name }}</span> 
                                    @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($eorg->id, $eauthorizedOrgs))) && $authorizedLevel <= 0)
                                        <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $eorg->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                            {{ (is_array(old('einheritedCheck')) and in_array($eorg->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $eorg->id }}">   
                                        <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                        <input pid="" class="eorgCheck" type="checkbox" id="eorgCheck{{ $eorg->id }}" name="eorgCheck[]" style="vertical-align: middle; float:right; margin-right:50px"
                                            {{ (is_array(old('eorgCheck')) and in_array($eorg->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $eorg->id }}">    
                                        <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                    @endif
                                </a>
                            </h6>
                        </div>
                        <div id="ecollapse-{{ $eorg->id }}" class="collapse" data-parent="#eaccordion-level0" aria-labelledby="eheading-{{ $eorg->id }}">
                            <div class="card-body">
                                {{--  Nested PROGRAM - Start  --}}
                                <div id="eaccordion-1">
                                    @foreach($eorg->children as $eprogram)
                                        <div class="card">
                                            @if ($eprogram->children->count() > 0 )    
                                                <div class="card-header" id="eheading-{{ $eprogram->id }}">
                                                    <h6 class="mb-0">
                                                        <a role="button" data-toggle="collapse" 
                                                            href="#ecollapse-{{ $eprogram->id }}" aria-expanded="false" 
                                                            class="{{ $eprogram->children->count() == 0 ? 'disabled' : '' }} collapsed"
                                                            aria-controls="ecollapse-{{ $eprogram->id }}">
                                                            <span class="pr-1">{{ $eprogram->name }}</span>
                                                            @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($eprogram->id, $eauthorizedOrgs))) && $authorizedLevel <= 1)
                                                                <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $eprogram->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                                                    {{ (is_array(old('einheritedCheck')) and in_array($eprogram->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $eprogram->id }}">   
                                                                <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                                                <input pid="{{ $eorg->id }}"  class="eorgCheck" type="checkbox"  id="eorgCheck{{ $eprogram->id }}" name="eorgCheck[]" style="vertical-align: middle; float:right; margin-right:50px"
                                                                    {{ (is_array(old('eorgCheck')) and in_array($eprogram->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $eprogram->id }}"> 
                                                                <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                                            @endif
                                                        </a>
                                                    </h6>
                                                </div>
                                                <div id="ecollapse-{{ $eprogram->id }}" class="collapse" data-parent="#eaccordion-1" aria-labelledby="eheading-{{ $eprogram->id }}">
                                                    <div class="card-body">
                                                        {{--  Nested DIVISION - Start  --}}
                                                        <div id="eaccordion-2">
                                                            @foreach($eprogram->children as $edivision)
                                                                <div class="card">
                                                                    @if ($edivision->children->count() > 0 )    
                                                                        <div class="card-header" id="eheading-{{ $edivision->id }}">
                                                                            <h6 class="mb-0">
                                                                                <a role="button" data-toggle="collapse" href="#ecollapse-{{ $edivision->id }}" aria-expanded="false" class="collapsed"
                                                                                    aria-controls="ecollapse-{{ $edivision->id }}">
                                                                                    <span class="pr-1">{{ $edivision->name }}</span>
                                                                                    @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($edivision->id, $eauthorizedOrgs))) && $authorizedLevel <= 2)
                                                                                        <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $edivision->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                                                                            {{ (is_array(old('einheritedCheck')) and in_array($edivision->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $edivision->id }}">   
                                                                                        <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                                                                        <input pid="{{ $eprogram->id }}" class="eorgCheck" type="checkbox"  id="eorgCheck{{ $edivision->id }}" name="eorgCheck[]" style="vertical-align: middle; float:right; margin-right:50px"
                                                                                            {{ (is_array(old('eorgCheck')) and in_array($edivision->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $edivision->id }}">
                                                                                        <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                                                                    @endif
                                                                                </a>
                                                                            </h6>
                                                                        </div>    
                                                                        <div id="ecollapse-{{ $edivision->id }}" class="collapse" data-parent="#eaccordion-2" aria-labelledby="eheading-{{ $edivision->id }}">
                                                                            <div class="card-body">
                                                                                {{-- Nested BRANCH - Start --}}
                                                                                <div id="eaccordion-3">
                                                                                    @foreach($edivision->children as $ebranch)
                                                                                        <div class="card">
                                                                                            @if ($ebranch->children->count() > 0 )    
                                                                                                <div class="card-header" id="eheading-{{ $ebranch->id }}">
                                                                                                    <h6 class="mb-0">
                                                                                                        <a role="button" data-toggle="collapse" href="#ecollapse-{{ $ebranch->id }}" aria-expanded="false" class="collapsed"
                                                                                                            aria-controls="ecollapse-{{ $ebranch->id }}">
                                                                                                            <span class="pr-1">{{ $ebranch->name }}</span>
                                                                                                            @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($ebranch->id, $eauthorizedOrgs))) && $authorizedLevel <= 3)
                                                                                                                <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $ebranch->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                                                                                                    {{ (is_array(old('einheritedCheck')) and in_array($ebranch->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $ebranch->id }}">   
                                                                                                                <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                                                                                                <input pid="{{ $edivision->id }}" class="eorgCheck" type="checkbox"  id="eorgCheck{{ $ebranch->id }}" name="eorgCheck[]" style="vertical-align: middle; float:right; margin-right:50px"
                                                                                                                    {{ (is_array(old('eorgCheck')) and in_array($ebranch->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $ebranch->id }}">
                                                                                                                <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                                                                                            @endif
                                                                                                        </a>
                                                                                                    </h6> 
                                                                                                </div>
                                                                                                <div id="ecollapse-{{ $ebranch->id }}" class="collapse" data-parent="#eaccordion-3" aria-labelledby="eheading-{{ $ebranch->id }}">
                                                                                                    <div class="card-body">
                                                                                                        {{--  Nested LEVEL4 - Start --}}
                                                                                                        <div id="eaccordion-4">
                                                                                                            @foreach($ebranch->children as $elevel4)
                                                                                                                <div class="card" style="margin-bottom: 0 !important;">
                                                                                                                    <div class="card-header employees" id="eheading-{{ $elevel4->id }}">
                                                                                                                        <h6 class="mb-0">
                                                                                                                            <a role="button" data-toggle="collapse" href="#ecollapse-{{ $elevel4->id }}" aria-expanded="false" class="collapsed"
                                                                                                                                aria-controls="ecollapse-{{ $elevel4->id }}" data="{{ $elevel4->id }}">
                                                                                                                                <span class="pr-2">{{ $elevel4->name }}</span>
                                                                                                                                @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($elevel4->id, $eauthorizedOrgs))) && $authorizedLevel <= 4)
                                                                                                                                    <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $elevel4->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                                                                                                                        {{ (is_array(old('einheritedCheck')) and in_array($elevel4->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $elevel4->id }}">   
                                                                                                                                    <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                                                                                                                    <input pid="{{ $ebranch->id }}" class="eorgCheck" type="checkbox"  id="eorgCheck{{ $elevel4->id }}" name="eorgCheck[]" style="vertical-align: middle; float:right; margin-right:50px"
                                                                                                                                        {{ (is_array(old('eorgCheck')) and in_array($elevel4->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $elevel4->id }}">
                                                                                                                                    <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                                                                                                                @endif
                                                                                                                            </a>
                                                                                                                        </h6>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            @endforeach 
                                                                                                        </div> 
                                                                                                        {{--  Nested LEVEL4 -- End --}}
                                                                                                    </div>
                                                                                                </div>
                                                                                            @else
                                                                                                <div class="card-header employees" id="eheading-{{ $ebranch->id }}">
                                                                                                    <h6 class="mb-0">
                                                                                                        <a role="button" data-toggle="collapse" href="#ecollapse-{{ $ebranch->id }}" aria-expanded="false" class="collapsed"
                                                                                                            aria-controls="ecollapse-{{ $ebranch->id }}" data="{{ $ebranch->id }}">
                                                                                                            <span class="pr-2">{{ $ebranch->name }}</span>
                                                                                                            @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($ebranch->id, $eauthorizedOrgs))) && $authorizedLevel <= 3)
                                                                                                                <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $ebranch->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                                                                                                    {{ (is_array(old('einheritedCheck')) and in_array($ebranch->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $ebranch->id }}">   
                                                                                                                <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                                                                                                <input pid="{{ $edivision->id }}" class="eorgCheck" type="checkbox"  id="eorgCheck{{ $ebranch->id }}" name="eorgCheck[]" style="vertical-align: middle; float:right; margin-right:50px" 
                                                                                                                    {{ (is_array(old('eorgCheck')) and in_array($ebranch->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $ebranch->id }}">
                                                                                                                <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                                                                                            @endif
                                                                                                        </a>
                                                                                                    </h6>                                                                
                                                                                                </div>
                                                                                            @endif  
                                                                                        </div>
                                                                                    @endforeach 
                                                                                </div>
                                                                                {{-- Nested BRANCH - End --}}        
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div class="card-header employees" id="eheading-{{ $edivision->id }}">
                                                                            <h6 class="mb-0">
                                                                                <a role="button" data-toggle="collapse" href="#ecollapse-{{ $edivision->id }}" aria-expanded="false" class="collapsed"
                                                                                    aria-controls="ecollapse-{{ $edivision->id }}" data="{{ $edivision->id }}">
                                                                                    <span class="pr-2">{{ $edivision->name }}</span>
                                                                                    @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($edivision->id, $eauthorizedOrgs))) && $authorizedLevel <= 2)
                                                                                        <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $edivision->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                                                                            {{ (is_array(old('einheritedCheck')) and in_array($edivision->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $edivision->id }}">   
                                                                                        <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                                                                        <input pid="{{ $eprogram->id }}" class="eorgCheck" type="checkbox"  id="eorgCheck{{ $edivision->id }}" name="eorgCheck[]" style="vertical-align:middle; float:right; margin-right:50px" 
                                                                                            {{ (is_array(old('eorgCheck')) and in_array($edivision->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $edivision->id }}">
                                                                                        <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                                                                    @endif
                                                                                </a>
                                                                            </h6>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach 
                                                        </div>
                                                        {{--  Nested DIVISION - End  --}}        
                                                    </div>
                                                </div>
                                            @else
                                                <div class="card-header employees" id="eheading-{{ $eprogram->id }}">
                                                    <h6 class="mb-0">
                                                        <a role="button" data-toggle="collapse" href="#ecollapse-{{ $eprogram->id }}" aria-expanded="false" class="collapsed"
                                                            aria-controls="ecollapse-{{ $eprogram->id }}" data="{{ $eprogram->id }}">
                                                            <span class="pr-2">{{ $eprogram->name }}</span>
                                                            @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($eprogram->id, $eauthorizedOrgs))) && $authorizedLevel <= 1)
                                                                <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $eprogram->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                                                    {{ (is_array(old('einheritedCheck')) and in_array($eprogram->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $eprogram->id }}">   
                                                                <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                                                <input pid="{{ $eorg->id }}" class="eorgCheck" type="checkbox"  id="eorgCheck{{ $eprogram->id }}" name="eorgCheck[]" style="vertical-align:middle; float:right; margin-right:50px" 
                                                                    {{ (is_array(old('eorgCheck')) and in_array($eprogram->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $eprogram->id }}">
                                                                <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                                            @endif
                                                        </a>
                                                    </h6>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach 
                                </div>
                                {{--  Nested PROGRAM - End  --}}
                            </div>
                        </div>
                    @else
                        <h1>Test</h1>
                        <div class="card-header" id="eheading-{{ $eorg->id }}">
                            <h6 class="mb-0">
                                <a role="button" class="disabled collapsed">
                                    <span class="pr-2">{{ $eorg->name }}</span>
                                    @if (((request()->segment(1) == 'sysadmin') || (request()->segment(1) == 'hradmin' && is_array($eauthorizedOrgs) && in_array($eorg->id, $eauthorizedOrgs))) && $authorizedLevel <= 0)
                                        <input pid="" class="einheritedCheck" type="checkbox" id="einheritedCheck{{ $eorg->id }}" name="einheritedCheck[]" style="vertical-align:middle; float:right; margin-right:100px"
                                            {{ (is_array(old('einheritedCheck')) and in_array($eorg->id, old('einheritedCheck'))) ? ' checked' : '' }} value="{{ $eorg->id }}">   
                                        <span class="pr-2" style="float:right; margin-right:3px">Inherited</span> 
                                        <input pid="" class="eorgCheck" type="checkbox"  id="eorgCheck{{ $eorg->id }}" name="eorgCheck[]" style="vertical-align:middle; float:right; margin-right:50px" 
                                            {{ (is_array(old('eorgCheck')) and in_array($eorg->id, old('eorgCheck'))) ? ' checked' : '' }} value="{{ $eorg->id }}">
                                        <span class="pr-2" style="float:right; margin-right:3px">Static</span> 
                                    @endif
                                    <span class="expandable btn btn-sm btn-secondary">see all employees</span>
                                </a>
                                <div >
                                    <ul>
                                        <li>
                                            Testing
                                        </li>
                                    </ul>
                                </div>

                            </h6>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        {{--  Nested ORGANIZATION - End  --}}
    @else
        <div class="pt-4">
            <p class="text-center">No data available in tree.</p>
        </div>
    @endif
</div>

<style>

    div.card {
        margin-bottom: 5px !important;
    }

    .card-header {
        padding: 0px !important;
        background: #eeeeee;
        color: inherit;
    }

    .card-header input {
        width:16px;
        height:16px;
        margin: 0px 8px 0px 2px ;    
    }

    .card-body {
        padding-top: 0.5em;
        padding-bottom: 0.2em;
        padding-right: 0.3em;
    }

    .mb-0  a {
        display: block;
        background: #668bb1;
        color: #ffffff;
        padding: 8px;
        text-decoration: none;
        position: relative;
    }

    .mb-0  a.collapsed {
        background: #eeeeee;
        color: inherit;
    }

    .mb-0 > a {
        display: block;
        /*position: relative; */
    }

    .mb-0 > a:not([class*="disabled"]):after {
        /* content: "\f078"; */  /* fa-chevron-down */
        content: '+';
        /* font-family: 'FontAwesome'; */
        position: absolute;
        right: 20px;
        top:0px;
        font-size:30px;
    }

    .mb-0 > a[aria-expanded="true"]:after { 
        content: '-';
        /* content: "\f077"; */  /* fa-chevron-up */
    }

    .eaccordion-option {
        width: 100%;
        float: left;
        clear: both;
        margin: 15px 0;
    }

    .eaccordion-option .title {
        font-size: 20px;
        font-weight: bold;
        float: left;
        padding: 0;
        margin: 0;
    }

    .eaccordion-option .toggle-accordion {
        float: right;
        font-size: 16px;
        color: #6a6c6f;
    }

    .eaccordion-option .toggle-accordion:before {
        content: "Expand All";
    }

    .eaccordion-option .toggle-accordion.active:before {
        content: "Collapse All";
    }

</style>

<script>

    $(document).ready(function() {

        eg_employees_by_org = {!!json_encode($eempIdsByOrgId)!!};      

        list = $("input[type=checkbox]:checked");

        $.each(list, function( index, item ) {
            pid = $(item).attr('pid');
            do {
                value = '#eorgCheck' + pid;
                etoggle_indeterminate( value );
                pid = $('#eorgCheck' + pid).attr('pid');    
            } 
            while (pid);
        });

        // Set parent checkbox
        function etoggle_indeterminate( prev_input ) {
            prev_location = $(prev_input).parent().attr('href');
            total = $(prev_location).find('input').length;
            selected = $(prev_location).find('input:checked').length;
            if (selected == 0) {
                $(prev_input).prop("indeterminate", false);
                $(prev_input).prop('checked', false);
            } else if ( total == selected ) {
                $(prev_input).prop("indeterminate", false);
                $(prev_input).prop('checked', true);
            } else if (total > selected ) {
                $(prev_input).prop("indeterminate", true);
            } else {
                $(prev_input).prop("indeterminate", false);
            }
        }

        $("#eaccordion-level0 .card-header").on("click","a", function(e) {
            if (e.target.tagName != "INPUT") {
                // do link
                //alert("Doing link functionality");
            } else {
                e.stopPropagation();
                var location = $(this).attr('href') ;
                if (e.target.className == 'eorgCheck') {
                    if (e.target.checked) {
                        // expand itself
                        $(location).collapse();
                        // to-do : checked all the following 
                        items = $(location).find('input:checkbox');
                        $.each(items, function(index, item) {
                            if(item.name == 'eorgCheck[]'){
                                $(item).prop('checked', true);
                                $(item).prop("indeterminate", false);
                            }
                        })  
                        // TODO : add to selected listed
                        //if no employee class, then have to add all 
                        // User level checkbox 
                        emp_id = $(e.target).val();  
                        if (!g_selected_orgnodes.includes(emp_id)) {
                            g_selected_orgnodes.push(emp_id);    
                        } 
                        node = $(e.target).val();
                        if (eg_employees_by_org.hasOwnProperty(node)) {
                            $.each(eg_employees_by_org[node], function(index, emp) {
                                if (!g_selected_orgnodes.includes(emp.employee_id)) {
                                    g_selected_orgnodes.push(emp.employee_id);    
                                } 
                            })  
                        }
                        nodes = $(location).find('input:checkbox')
                        $.each( nodes, function(index, chkbox) {
                            if(chkbox.name == 'eorgCheck[]'){
                                if (eg_employees_by_org.hasOwnProperty(chkbox.value)) {
                                    $.each(eg_employees_by_org[chkbox.value], function(index, emp) {
                                        if (!g_selected_orgnodes.includes(emp.employee_id)) {
                                            g_selected_orgnodes.push(emp.employee_id);    
                                        }
                                    })
                                } else {
                                    if (!g_selected_orgnodes.includes(chkbox.value)) {
                                        g_selected_orgnodes.push(chkbox.value);    
                                    }
                                }
                            }
                        });
                    } else {
                        // unchecked the children 
                        items = $(location).find('input:checkbox');
                        $.each(items, function(index, item) {
                            if(item.name == 'eorgCheck[]'){
                                $(item).prop('checked', false);
                                $(item).prop("indeterminate", false);
                            }
                        })  
                        emp_id = $(e.target).val();  
                        var index = $.inArray(emp_id, g_selected_orgnodes);
                        if (index > -1) {
                            g_selected_orgnodes.splice(index, 1);
                        }
                        node = $(e.target).val();
                        if (eg_employees_by_org.hasOwnProperty( node )) {
                            $.each(eg_employees_by_org[node], function(index, emp) {
                                if (!g_selected_orgnodes.includes(emp.employee_id)) {
                                    g_selected_orgnodes.push(emp.employee_id);    
                                } 
                            })  
                        }
                        nodes = $(location).find('input:checkbox');
                        $.each( nodes, function( index, chkbox ) {
                            if(chkbox.name == 'eorgCheck[]'){
                                if (eg_employees_by_org.hasOwnProperty(chkbox.value)) {
                                    $.each(eg_employees_by_org[chkbox.value], function(index, emp) {
                                        var index = $.inArray(emp.employee_id, g_selected_orgnodes);
                                        if (index > -1) {
                                            g_selected_orgnodes.splice(index, 1);
                                        }
                                    })
                                } else {
                                        var index = $.inArray(chkbox.value, g_selected_orgnodes);
                                        if (index > -1) {
                                            g_selected_orgnodes.splice(index, 1);
                                        }
                                }
                            }
                        });
                    }      
                    pid = $(this).find('input:first').attr('pid');
                    do {
                        value = '#eorgCheck' + pid;
                        etoggle_indeterminate( value );
                        pid = $('#eorgCheck' + pid).attr('pid');    
                    } 
                    while (pid);
                }
                if (e.target.className == 'einheritedCheck') {
                    if (e.target.checked) {
                        emp_id = $(e.target).val();  
                        if (!eg_selected_inherited.includes(emp_id)) {
                            eg_selected_inherited.push(emp_id);    
                        } 
                    } else {
                        emp_id = $(e.target).val();  
                        var index = $.inArray(emp_id, eg_selected_inherited);
                        if (index > -1) {
                            eg_selected_inherited.splice(index, 1);
                        }
                    }
                }
            }
        });

        $("#eaccordion-level0").on('shown.bs.collapse', function () {
            // do something
            el = $('a.toggle-accordion');
            if ( !el.hasClass("active")) {
                el.addClass( "active");
            }
        });

        $("#eaccordion-level0").on('hidden.bs.collapse', function () {
            count = $('div.collapse.show').length;
            if (count == 0) {
                el = $('a.toggle-accordion');
                if ( el.hasClass("active")) {
                    el.removeClass( "active");
                }
            }
        });

        $(".toggle-accordion").on("click", function(e) {
            b_active =  $( e.target ).hasClass( "active" );
            if (b_active) {
                nodes = $('div.collapse.show');
                $.each( nodes, function( index, item ) {
                    $(item).collapse('hide');
                });
                $( e.target ).removeClass( "active" );
            } else {
                nodes = $('div.collapse');
                $.each( nodes, function( index, item ) {
                    $(item).collapse('show');
                });
                $( e.target ).addClass( "active" );
            }
        })

    });

</script>
