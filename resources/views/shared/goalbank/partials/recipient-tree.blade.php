
<div class="card px-3 pb-3">

@error('userCheck')                
<div class="p-0">
<span class="text-danger">
    {{  'The recipient is required.'  }}
</span>
</div>
@enderror

@if ($orgs->count() > 0)
<div class="p-0">
    <div class="accordion-option">
        <a href="javascript:void(0)" class="toggle-accordion" 
        accordion-id="#accordion"></a>
    </div>
</div>


<div id="accordion-level0">
    @foreach($orgs as $org)
    <div class="card">
        @if ($org->children->count() > 0 )    
        <div class="card-header" id="heading-{{ $org->id }}">
            <h6 class="mb-0">
            
            <a role="button" data-toggle="collapse" href="#collapse-{{ $org->id }}" aria-expanded="false" class="collapsed"
                        aria-controls="collapse-{{ $org->id }}">
                <input pid="" class="" type="checkbox"  id="orgCheck{{ $org->id }}" name="orgCheck[]" 
                    {{ (is_array(old('orgCheck')) and in_array($org->id, old('orgCheck'))) ? ' checked' : '' }}
                    value="{{ $org->id }}">    
                    <span class="pr-2">{{ $org->name }}</span>
                <span class="badge badge-pill badge-primary">{{ $countByOrg[$org->id] }}</span>
            </a>
            </h6>
        </div>

        <div id="collapse-{{ $org->id }}" class="collapse" data-parent="#accordion-level0" aria-labelledby="heading-{{ $org->id }}">
            <div class="card-body">
                {{--  Nested PROGRAM - Start  --}}
                <div id="accordion-1">
                    @foreach($org->children as $program)
                    <div class="card">
                        @if ($program->children->count() > 0 )    
                        <div class="card-header" id="heading-{{ $program->id }}">
                            <h6 class="mb-0">
                            <a role="button" data-toggle="collapse" 
                                href="#collapse-{{ $program->id }}" aria-expanded="false" 
                                class="{{ $program->children->count() == 0 ? 'disabled' : '' }} collapsed"
                                        aria-controls="collapse-{{ $program->id }}">
                                    <input pid="{{ $org->id }}"  class="" type="checkbox"  id="orgCheck{{ $program->id }}" 
                                        {{ (is_array(old('orgCheck')) and in_array($program->id, old('orgCheck'))) ? ' checked' : '' }}
                                        name="orgCheck[]" value="{{ $program->id }}"> 
                                    <span class="pr-1">{{ $program->name }}</span>
                                    <span class="badge badge-pill badge-primary">{{ $countByOrg[$program->id] }}</span>
                            </a>
                            </h6>
                        </div>

                        <div id="collapse-{{ $program->id }}" class="collapse" data-parent="#accordion-1" aria-labelledby="heading-{{ $program->id }}">
                            <div class="card-body">
                                {{--  Nested DIVISION - Start  --}}
                                <div id="accordion-2">
                                    @foreach($program->children as $division)
                                    <div class="card">
                                        @if ($division->children->count() > 0 )    
                                        <div class="card-header" id="heading-{{ $division->id }}">
                                            <h6 class="mb-0">
                                            <a role="button" data-toggle="collapse" href="#collapse-{{ $division->id }}" aria-expanded="false" class="collapsed"
                                                        aria-controls="collapse-{{ $division->id }}">
                                                <input pid="{{ $program->id }}" class="" type="checkbox"  id="orgCheck{{ $division->id }}" name="orgCheck[]" 
                                                    {{ (is_array(old('orgCheck')) and in_array($division->id, old('orgCheck'))) ? ' checked' : '' }}
                                                    value="{{ $division->id }}">
                                                <span class="pr-1">{{ $division->name }}</span>
                                                <span class="badge badge-pill badge-primary">{{ $countByOrg[$division->id] }}</span>
                                            </a>
                                            </h6>
                                        </div>    
                                    
                                        <div id="collapse-{{ $division->id }}" class="collapse" data-parent="#accordion-2" aria-labelledby="heading-{{ $division->id }}">
                                            <div class="card-body">
                                                {{-- Nested BRANCH - Start --}}
                                                <div id="accordion-3">
                                                    @foreach($division->children as $branch)
                                                    <div class="card">
                                                        @if ($branch->children->count() > 0 )    
                                                            <div class="card-header" id="heading-{{ $branch->id }}">
                                                                <h6 class="mb-0">
                                                                <a role="button" data-toggle="collapse" href="#collapse-{{ $branch->id }}" aria-expanded="false" class="collapsed"
                                                                            aria-controls="collapse-{{ $branch->id }}">
                                                                    <input pid="{{ $division->id }}" class="" type="checkbox"  id="orgCheck{{ $branch->id }}" name="orgCheck[]" 
                                                                    {{ (is_array(old('orgCheck')) and in_array($branch->id, old('orgCheck'))) ? ' checked' : '' }}
                                                                        value="{{ $branch->id }}">
                                                                    <span class="pr-1">{{ $branch->name }}</span>
                                                                    <span class="badge badge-pill badge-primary">{{ $countByOrg[$branch->id] }}</span>
                                                                </a>
                                                                </h6> 
                                                            </div>
                                                            <div id="collapse-{{ $branch->id }}" class="collapse" data-parent="#accordion-3" aria-labelledby="heading-{{ $branch->id }}">
                                                                <div class="card-body">
                                                                    {{--  Nested LEVEL4 - Start --}}
                                                                    <div id="accordion-4">
                                                                        @foreach($branch->children as $level4)
                                                                            <div class="card" style="margin-bottom: 0 !important;">
                                                                                <div class="card-header employees" id="heading-{{ $level4->id }}">
                                                                                    <h6 class="mb-0">
                                                                                        <a role="button" data-toggle="collapse" href="#collapse-{{ $level4->id }}" aria-expanded="false" class="collapsed"
                                                                                            aria-controls="collapse-{{ $level4->id }}" data="{{ $level4->id }}">
                                                                                        <input pid="{{ $branch->id }}"  class="" type="checkbox"  id="orgCheck{{ $level4->id }}" name="orgCheck[]" 
                                                                                        {{ (is_array(old('orgCheck')) and in_array($level4->id, old('orgCheck'))) ? ' checked' : '' }}
                                                                                        value="{{ $level4->id }}">
                                                                                        <span class="pr-1">{{ $level4->name }}</span>
                                                                                        <span class="badge clickable badge-pill badge-primary">{{ $countByOrg[$level4->id] }}</span>
                                                                                    </a>
                                                                                    </h6>
                                                                                </div>
                                                                            </div>

                                                                            {{-- level4 -- Employee Listing - Start --}}                                              
                                                                            <div id="collapse-{{ $level4->id }}" class="collapse" data-parent="#accordion-4" aria-labelledby="heading-{{ $level4->id }}">
                                                                                <div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" role="status" style="display:none">
                                                                                    <span class="sr-only">Loading...</span>
                                                                                </div>
                                                                                <div class="card-header employee-list" id="employees-{{ $level4->id }}" value="{{ $level4->id }}"></div>
                                                                            </div>
                                                                            {{-- LEVEL4 -- Employee Listing - End --}}                                                                                         

                                                                        @endforeach 
                                                                    </div> 
                                                                    {{--  Nested LEVEL4 -- End --}}
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="card-header employees" id="heading-{{ $branch->id }}">
                                                                <h6 class="mb-0">
                                                                <a role="button" data-toggle="collapse" href="#collapse-{{ $branch->id }}" aria-expanded="false" class="collapsed"
                                                                    aria-controls="collapse-{{ $branch->id }}" data="{{ $branch->id }}">
                                                                    <input pid="{{ $division->id }}" class="" type="checkbox"  id="orgCheck{{ $branch->id }}" name="orgCheck[]" 
                                                                        {{ (is_array(old('orgCheck')) and in_array($branch->id, old('orgCheck'))) ? ' checked' : '' }}
                                                                            value="{{ $branch->id }}">
                
                                                                    <span class="pr-1">{{ $branch->name }}</span>
                                                                    <span class="badge clickable badge-pill badge-primary">{{ $countByOrg[$branch->id] }}</span>
                                                                </a>
                                                                </h6>                                                                
                                                            </div>

                                                            {{-- BRANCH -- Employee Listing - Start --}}                                                                                         
                                                            <div id="collapse-{{ $branch->id }}" class="collapse" data-parent="#accordion-3" aria-labelledby="heading-{{ $branch->id }}">
                                                                <div class="card-header employee-list" id="employees-{{ $branch->id }}" value="{{ $branch->id }}"></div>
                                                            </div>
                                                            {{-- BRANCH -- Employee Listing - End --}}                                                                                         
                                                        @endif  
                                                    </div>
                                                    @endforeach 
                                                </div>
                                                {{-- Nested BRANCH - End --}}        
                                            </div>
                                        </div>

                                        @else

                                        <div class="card-header employees" id="heading-{{ $division->id }}">
                                            <h6 class="mb-0">
                                            <a role="button" data-toggle="collapse" href="#collapse-{{ $division->id }}" aria-expanded="false" class="collapsed"
                                                aria-controls="collapse-{{ $division->id }}" data="{{ $division->id }}">

                                                <input pid="{{ $program->id }}" class="" type="checkbox"  id="orgCheck{{ $division->id }}" name="orgCheck[]" 
                                                  {{ (is_array(old('orgCheck')) and in_array($division->id, old('orgCheck'))) ? ' checked' : '' }}
                                                   value="{{ $division->id }}">

                                                <span class="pr-1">{{ $division->name }}</span>
                                                <span class="badge clickable badge-pill badge-primary">{{ $countByOrg[$division->id] }}</span>
                                            </a>
                                            </h6>
                                        </div>

                                        {{-- DIVISION -- Employee Listing - Start --}}      
                                        <div id="collapse-{{ $division->id }}" class="collapse" data-parent="#accordion-2" aria-labelledby="heading-{{ $division->id }}">
                                            <div class="card-header employee-list" id="employees-{{ $division->id }}" value="{{ $division->id }}"></div>
                                        </div>
                                        {{-- DIVISION -- Employee Listing - End --}}                                                                                         

                                        @endif
                                    </div>
                                    @endforeach 
                                </div>
                                {{--  Nested DIVISION - End  --}}        
                            </div>
                        </div>
                        @else
                        <div class="card-header employees" id="heading-{{ $program->id }}">
                            <h6 class="mb-0">
                            <a role="button" data-toggle="collapse" href="#collapse-{{ $program->id }}" aria-expanded="false" class="collapsed"
                                aria-controls="collapse-{{ $program->id }}" data="{{ $program->id }}">

                                <input pid="{{ $org->id }}" class="" type="checkbox"  id="orgCheck{{ $program->id }}" name="orgCheck[]" 
                                    {{ (is_array(old('orgCheck')) and in_array($program->id, old('orgCheck'))) ? ' checked' : '' }}
                                    value="{{ $program->id }}">

                                <span class="pr-1">{{ $program->name }}</span>
                                <span class="badge clickable badge-pill badge-primary">{{ $countByOrg[$program->id] }}</span>
                            </a>
                            </h6>
                        </div>

                        {{-- PROGRAM -- Employee Listing - Start --}}    
                        <div id="collapse-{{ $program->id }}" class="collapse" data-parent="#accordion-1" aria-labelledby="heading-{{ $program->id }}">
                            <div class="card-header employee-list" id="employees-{{ $program->id }}" value="{{ $program->id }}"></div>
                        </div>
                        {{-- PROGRAM -- Employee Listing - End --}}                                                                                         

                        @endif
                    </div>
                    @endforeach 
                </div>
                {{--  Nested PROGRAM - End  --}}
            </div>
        </div>

        @else
        <h1>Test</h1>
        <div class="card-header" id="heading-{{ $org->id }}">
            <h6 class="mb-0">
            <a role="button" class="disabled collapsed">

                <input pid="" class="" type="checkbox"  id="orgCheck{{ $org->id }}" name="orgCheck[]" 
                    {{ (is_array(old('orgCheck')) and in_array($org->id, old('orgCheck'))) ? ' checked' : '' }}
                    value="{{ $org->id }}">

                <span class="pr-1">{{ $org->name }}</span>
                <span class="badge clickable badge-pill badge-primary">{{ $countByOrg[$org->id] }}</span>
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
.accordion-option {
    width: 100%;
    float: left;
    clear: both;
    margin: 15px 0;
}
.accordion-option .title {
    font-size: 20px;
    font-weight: bold;
    float: left;
    padding: 0;
    margin: 0;
}
.accordion-option .toggle-accordion {
    float: right;
    font-size: 16px;
    color: #6a6c6f;
}
.accordion-option .toggle-accordion:before {
    content: "Expand All";
}
.accordion-option .toggle-accordion.active:before {
    content: "Collapse All";
}

</style>


<script>
$(document).ready(function() {

g_employees_by_org = {!!json_encode($empIdsByOrgId)!!};      

list = $("input[type=checkbox]:checked");

$.each(list, function( index, item ) {

    pid = $(item).attr('pid');

    do {
        value = '#orgCheck' + pid;
        toggle_indeterminate( value );
        pid = $('#orgCheck' + pid).attr('pid');    
    } 
    while (pid);

});



// Set parent checkbox
function toggle_indeterminate( prev_input ) {
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

function load_employees_on_node( tree_id ) {
    var target = '#employees-' + tree_id;
    if($.trim($(target).html())=='') {
        $.ajax({
            url: '/'+'{{ request()->segment(1) }}'+'/goalbank/employees/'+tree_id,
            type: 'GET',
            data: $("#notify-form").serialize(),
            dataType: 'html',
            beforeSend: function() {
                $(".loading-spinner").show();                    
            },
            success: function (result) {
                $(target).html(''); 
                $(target).html(result);

                nodes = $(target).find('input:checkbox');
                $.each( nodes, function( index, chkbox ) {
                    if (g_selected_employees.includes(chkbox.value)) {
                        $(chkbox).prop('checked', true);
                    } 
                });
            },
            complete: function() {
                $(".loading-spinner").hide();
            },
            error: function () {
                alert("error");
                $(target).html('<i class="glyphicon glyphicon-info-sign"></i> Something went wrong, Please try again...');
            }
        });
    }
}

$("#accordion-level0 .card-header.employees").on("click", "a", function(e) {
    var tree_id = $(this).attr('data');
    load_employees_on_node(tree_id);
});

$("#accordion-level0 .card-header").on("click","a", function(e) {
    //e.preventDefault(); 	
    if (e.target.tagName != "INPUT") {
        // do link
        //alert("Doing link functionality");
    } else {
        e.stopPropagation();
        //var location  = '#collapse-' + $(e.target).val();
        var location = $(this).attr('href') ;
        if (e.target.checked) {
            // expand itself
            $(location).collapse();
            // to-do : checked all the following 
            items = $(location).find('input:checkbox');
            $.each(items, function(index, item) {
                $(item).prop('checked', true);
                $(item).prop("indeterminate", false);
            })  
            // TODO : add to selected listed
            //if no employee class, then have to add all 
            // User level checkbox 
            if ( $(e.target).attr('name') == 'userCheck[]') {
                emp_id = $(e.target).val();  
                if (!g_selected_employees.includes(emp_id)) {
                        g_selected_employees.push( emp_id );    
                } 
            }
            node  = $(e.target).val();
            if (g_employees_by_org.hasOwnProperty( node )) {
                $.each(g_employees_by_org[ node  ], function(index, emp) {
                    if (!g_selected_employees.includes(emp.employee_id)) {
                        g_selected_employees.push( emp.employee_id );    
                    } 
                })  
            }
            nodes = $(location).find('input:checkbox')
            $.each( nodes, function( index, chkbox ) {
                if (g_employees_by_org.hasOwnProperty(chkbox.value)) {
                    $.each(g_employees_by_org[chkbox.value], function(index, emp) {
                        if (!g_selected_employees.includes(emp.employee_id)) {
                            g_selected_employees.push( emp.employee_id );    
                        }
                    })
                } else {
                    if (chkbox.name == 'userCheck[]') {
                        if (!g_selected_employees.includes(chkbox.value)) {
                            g_selected_employees.push( chkbox.value);    
                        }
                    }
                }
            });
        } else {
            // unchecked the children 
            items = $(location).find('input:checkbox');
            $.each(items, function(index, item) {
                $(item).prop('checked', false);
                $(item).prop("indeterminate", false);
            })  
            // User level checkbox 
            if ( $(e.target).attr('name') == 'userCheck[]') {
                emp_id = $(e.target).val();  
                var index = $.inArray(emp_id, g_selected_employees);
                if (index > -1) {
                    g_selected_employees.splice( index, 1 );
                }
            }
            node = $(e.target).val();
            if (g_employees_by_org.hasOwnProperty( node )) {
                $.each(g_employees_by_org[ node  ], function(index, emp) {
                    if (!g_selected_employees.includes(emp.employee_id)) {
                        g_selected_employees.push( emp.employee_id );    
                    } 
                })  
            }
            nodes = $(location).find('input:checkbox');
            $.each( nodes, function( index, chkbox ) {
                if (g_employees_by_org.hasOwnProperty(chkbox.value)) {
                    $.each(g_employees_by_org[chkbox.value], function(index, emp) {
                        var index = $.inArray(emp.employee_id, g_selected_employees);
                        if (index > -1) {
                            g_selected_employees.splice( index, 1 );
                        }
                    })
                } else {
                    if (chkbox.name == 'userCheck[]') {
                        var index = $.inArray(chkbox.value, g_selected_employees);
                        if (index > -1) {
                            g_selected_employees.splice( index, 1 );
                        }
                    }
                }
            });
        }      
        pid = $(this).find('input:first').attr('pid');
        do {
            value = '#orgCheck' + pid;
            toggle_indeterminate( value );
            pid = $('#orgCheck' + pid).attr('pid');    
        } 
        while (pid);
    }
});

$("#accordion-level0").on('shown.bs.collapse', function () {
    // do something
    el = $('a.toggle-accordion');
    if ( !el.hasClass("active")) {
        el.addClass( "active");
    }
});

$("#accordion-level0").on('hidden.bs.collapse', function () {
    
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
