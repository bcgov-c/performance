
<div class="card px-3 pb-3">

@error('auserCheck')                
<div class="p-0">
<span class="text-danger">
    {{  'The recipient is required.'  }}
</span>
</div>
@enderror

@if ($aorgs->count() > 0)
<div class="p-0">
    <div class="accordion-option">
        <a href="javascript:void(0)" class="toggle-accordion" 
        accordion-id="#aaccordion"></a>
    </div>
</div>


<div id="aaccordion-level0">
    @foreach($aorgs as $org)
    <div class="card">
        @if ($org->children->count() > 0 )    
        <div class="card-header" id="aheading-{{ $org->id }}">
            <h6 class="mb-0">
            
            <a role="button" data-toggle="collapse" href="#acollapse-{{ $org->id }}" aria-expanded="false" class="collapsed"
                        aria-controls="collapse-{{ $org->id }}">
                <input pid="" class="" type="checkbox"  id="aorgCheck{{ $org->id }}" name="aorgCheck[]" 
                    {{ (is_array(old('aorgCheck')) and in_array($org->id, old('aorgCheck'))) ? ' checked' : '' }}
                    value="{{ $org->id }}">    
                    <span class="pr-2">{{ $org->name }}</span>
                <span class="badge badge-pill badge-primary">{{ $acountByOrg[$org->id] }}</span>
            </a>
            </h6>
        </div>

        <div id="acollapse-{{ $org->id }}" class="collapse" data-parent="#aaccordion-level0" aria-labelledby="aheading-{{ $org->id }}">
            <div class="card-body">
                {{--  Nested PROGRAM - Start  --}}
                <div id="aaccordion-1">
                    @foreach($org->children as $program)
                    <div class="card">
                        @if ($program->children->count() > 0 )    
                        <div class="card-header" id="aheading-{{ $program->id }}">
                            <h6 class="mb-0">
                            <a role="button" data-toggle="collapse" 
                                href="#acollapse-{{ $program->id }}" aria-expanded="false" 
                                class="{{ $program->children->count() == 0 ? 'disabled' : '' }} collapsed"
                                        aria-controls="acollapse-{{ $program->id }}">
                                    <input pid="{{ $org->id }}"  class="" type="checkbox"  id="aorgCheck{{ $program->id }}" 
                                        {{ (is_array(old('aorgCheck')) and in_array($program->id, old('aorgCheck'))) ? ' checked' : '' }}
                                        name="aorgCheck[]" value="{{ $program->id }}"> 
                                    <span class="pr-1">{{ $program->name }}</span>
                                    <span class="badge badge-pill badge-primary">{{ $acountByOrg[$program->id] }}</span>
                            </a>
                            </h6>
                        </div>

                        <div id="acollapse-{{ $program->id }}" class="collapse" data-parent="#aaccordion-1" aria-labelledby="aheading-{{ $program->id }}">
                            <div class="card-body">
                                {{--  Nested DIVISION - Start  --}}
                                <div id="aaccordion-2">
                                    @foreach($program->children as $division)
                                    <div class="card">
                                        @if ($division->children->count() > 0 )    
                                        <div class="card-header" id="aheading-{{ $division->id }}">
                                            <h6 class="mb-0">
                                            <a role="button" data-toggle="collapse" href="#acollapse-{{ $division->id }}" aria-expanded="false" class="collapsed"
                                                        aria-controls="acollapse-{{ $division->id }}">
                                                <input pid="{{ $program->id }}" class="" type="checkbox"  id="aorgCheck{{ $division->id }}" name="aorgCheck[]" 
                                                    {{ (is_array(old('aorgCheck')) and in_array($division->id, old('aorgCheck'))) ? ' checked' : '' }}
                                                    value="{{ $division->id }}">
                                                <span class="pr-1">{{ $division->name }}</span>
                                                <span class="badge badge-pill badge-primary">{{ $acountByOrg[$division->id] }}</span>
                                            </a>
                                            </h6>
                                        </div>    
                                    
                                        <div id="acollapse-{{ $division->id }}" class="collapse" data-parent="#aaccordion-2" aria-labelledby="aheading-{{ $division->id }}">
                                            <div class="card-body">
                                                {{-- Nested BRANCH - Start --}}
                                                <div id="aaccordion-3">
                                                    @foreach($division->children as $branch)
                                                    <div class="card">
                                                        @if ($branch->children->count() > 0 )    
                                                            <div class="card-header" id="aheading-{{ $branch->id }}">
                                                                <h6 class="mb-0">
                                                                <a role="button" data-toggle="collapse" href="#acollapse-{{ $branch->id }}" aria-expanded="false" class="collapsed"
                                                                            aria-controls="acollapse-{{ $branch->id }}">
                                                                    <input pid="{{ $division->id }}" class="" type="checkbox"  id="aorgCheck{{ $branch->id }}" name="aorgCheck[]" 
                                                                    {{ (is_array(old('aorgCheck')) and in_array($branch->id, old('aorgCheck'))) ? ' checked' : '' }}
                                                                        value="{{ $branch->id }}">
                                                                    <span class="pr-1">{{ $branch->name }}</span>
                                                                    <span class="badge badge-pill badge-primary">{{ $acountByOrg[$branch->id] }}</span>
                                                                </a>
                                                                </h6> 
                                                            </div>
                                                            <div id="acollapse-{{ $branch->id }}" class="collapse" data-parent="#aaccordion-3" aria-labelledby="aheading-{{ $branch->id }}">
                                                                <div class="card-body">
                                                                    {{--  Nested LEVEL4 - Start --}}
                                                                    <div id="aaccordion-4">
                                                                        @foreach($branch->children as $level4)
                                                                            <div class="card" style="margin-bottom: 0 !important;">
                                                                                <div class="card-header employees" id="aheading-{{ $level4->id }}">
                                                                                    <h6 class="mb-0">
                                                                                        <a role="button" data-toggle="collapse" href="#acollapse-{{ $level4->id }}" aria-expanded="false" class="collapsed"
                                                                                            aria-controls="acollapse-{{ $level4->id }}" data="{{ $level4->id }}">
                                                                                        <input pid="{{ $branch->id }}"  class="" type="checkbox"  id="aorgCheck{{ $level4->id }}" name="aorgCheck[]" 
                                                                                        {{ (is_array(old('aorgCheck')) and in_array($level4->id, old('aorgCheck'))) ? ' checked' : '' }}
                                                                                        value="{{ $level4->id }}">
                                                                                        <span class="pr-1">{{ $level4->name }}</span>
                                                                                        <span class="badge clickable badge-pill badge-primary">{{ $acountByOrg[$level4->id] }}</span>
                                                                                    </a>
                                                                                    </h6>
                                                                                </div>
                                                                            </div>

                                                                            {{-- level4 -- Employee Listing - Start --}}                                              
                                                                            <div id="acollapse-{{ $level4->id }}" class="collapse" data-parent="#aaccordion-4" aria-labelledby="aheading-{{ $level4->id }}">
                                                                                {{-- <div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" role="status" style="display:none">
                                                                                    <span class="sr-only">Loading...</span>
                                                                                </div> --}}
                                                                                <div class="card-header employee-list" id="aemployees-{{ $level4->id }}" value="{{ $level4->id }}"></div>
                                                                            </div>
                                                                            {{-- LEVEL4 -- Employee Listing - End --}}                                                                                         

                                                                        @endforeach 
                                                                    </div> 
                                                                    {{--  Nested LEVEL4 -- End --}}
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="card-header employees" id="aheading-{{ $branch->id }}">
                                                                <h6 class="mb-0">
                                                                <a role="button" data-toggle="collapse" href="#acollapse-{{ $branch->id }}" aria-expanded="false" class="collapsed"
                                                                    aria-controls="acollapse-{{ $branch->id }}" data="{{ $branch->id }}">
                                                                    <input pid="{{ $division->id }}" class="" type="checkbox"  id="aorgCheck{{ $branch->id }}" name="aorgCheck[]" 
                                                                        {{ (is_array(old('orgCheck')) and in_array($branch->id, old('orgCheck'))) ? ' checked' : '' }}
                                                                            value="{{ $branch->id }}">
                
                                                                    <span class="pr-1">{{ $branch->name }}</span>
                                                                    <span class="badge clickable badge-pill badge-primary">{{ $acountByOrg[$branch->id] }}</span>
                                                                </a>
                                                                </h6>                                                                
                                                            </div>

                                                            {{-- BRANCH -- Employee Listing - Start --}}                                                                                         
                                                            <div id="acollapse-{{ $branch->id }}" class="collapse" data-parent="#aaccordion-3" aria-labelledby="aheading-{{ $branch->id }}">
                                                                <div class="card-header employee-list" id="aemployees-{{ $branch->id }}" value="{{ $branch->id }}"></div>
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

                                        <div class="card-header employees" id="aheading-{{ $division->id }}">
                                            <h6 class="mb-0">
                                            <a role="button" data-toggle="collapse" href="#acollapse-{{ $division->id }}" aria-expanded="false" class="collapsed"
                                                aria-controls="acollapse-{{ $division->id }}" data="{{ $division->id }}">

                                                <input pid="{{ $program->id }}" class="" type="checkbox"  id="aorgCheck{{ $division->id }}" name="aorgCheck[]" 
                                                  {{ (is_array(old('aorgCheck')) and in_array($division->id, old('aorgCheck'))) ? ' checked' : '' }}
                                                   value="{{ $division->id }}">

                                                <span class="pr-1">{{ $division->name }}</span>
                                                <span class="badge clickable badge-pill badge-primary">{{ $acountByOrg[$division->id] }}</span>
                                            </a>
                                            </h6>
                                        </div>

                                        {{-- DIVISION -- Employee Listing - Start --}}                                                                                         
                                        <div id="acollapse-{{ $division->id }}" class="collapse" data-parent="#aaccordion-2" aria-labelledby="aheading-{{ $division->id }}">
                                            <div class="card-header employee-list" id="aemployees-{{ $division->id }}" value="{{ $division->id }}"></div>
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
                        <div class="card-header employees" id="aheading-{{ $program->id }}">
                            <h6 class="mb-0">
                            <a role="button" data-toggle="collapse" href="#acollapse-{{ $program->id }}" aria-expanded="false" class="collapsed"
                                aria-controls="acollapse-{{ $program->id }}" data="{{ $program->id }}">

                                <input pid="{{ $org->id }}" class="" type="checkbox"  id="aorgCheck{{ $program->id }}" name="aorgCheck[]" 
                                    {{ (is_array(old('aorgCheck')) and in_array($program->id, old('aorgCheck'))) ? ' checked' : '' }}
                                    value="{{ $program->id }}">

                                <span class="pr-1">{{ $program->name }}</span>
                                <span class="badge clickable badge-pill badge-primary">{{ $acountByOrg[$program->id] }}</span>
                            </a>
                            </h6>
                        </div>

                        {{-- PROGRAM -- Employee Listing - Start --}}                                                                                         
                        <div id="acollapse-{{ $program->id }}" class="collapse" data-parent="#aaccordion-1" aria-labelledby="aheading-{{ $program->id }}">
                            <div class="card-header employee-list" id="aemployees-{{ $program->id }}" value="{{ $program->id }}"></div>
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
        <div class="card-header" id="aheading-{{ $org->id }}">
            <h6 class="mb-0">
            <a role="button" class="disabled collapsed">

                <input pid="" class="" type="checkbox"  id="aorgCheck{{ $org->id }}" name="aorgCheck[]" 
                    {{ (is_array(old('aorgCheck')) and in_array($org->id, old('aorgCheck'))) ? ' checked' : '' }}
                    value="{{ $org->id }}">

                <span class="pr-1">{{ $org->name }}</span>
                <span class="badge clickable badge-pill badge-primary">{{ $acountByOrg[$org->id] }}</span>
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

ag_employees_by_org = {!!json_encode($aempIdsByOrgId)!!};      

alist = $("input[type=checkbox]:checked");

$.each(alist, function( index, item ) {

    pid = $(item).attr('pid');

    do {
        value = '#aorgCheck' + pid;
        //console.log(  value );
        atoggle_indeterminate( value );
        //console.log("parent : " + pid);                
        pid = $('#aorgCheck' + pid).attr('pid');    
    } 
    while (pid);

});



// Set parent checkbox
function atoggle_indeterminate( prev_input ) {

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

function aload_employees_on_node( tree_id ) {
    var target = '#aemployees-' + tree_id;
    if($.trim($(target).html())=='') {
        $.ajax({
            url: '/'+'{{ request()->segment(1) }}'+'/goalbank/employees/'+tree_id+'/a',
            type: 'GET',
            data: $("#notify-form").serialize(),
            dataType: 'html',
            beforeSend: function() {
                $(".loading-spinner").show();                    
            },
            success: function (result) {
                $(target).html(''); 
                $(target).html(result);
                anodes = $(target).find('input:checkbox');
                $.each( anodes, function( index, chkbox ) {
                    if (ag_selected_employees.includes(chkbox.value)) {
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

$("#aaccordion-level0 .card-header.employees").on("click","a", function(e) 
{
    var tree_id = $(this).attr('data');
    aload_employees_on_node(tree_id);
});

$("#aaccordion-level0 .card-header").on("click","a", function(e) {
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
            if ( $(e.target).attr('name') == 'auserCheck[]') {
                emp_id = $(e.target).val();  
                if (!ag_selected_employees.includes(emp_id)) {
                        ag_selected_employees.push( emp_id );    
                } 
            }

            anode  = $(e.target).val();
            if (ag_employees_by_org.hasOwnProperty( anode )) {
                $.each(ag_employees_by_org[ anode  ], function(index, emp) {
                    if (!ag_selected_employees.includes(emp.employee_id)) {
                        ag_selected_employees.push( emp.employee_id );    
                    } 
                })  
            }

            anodes = $(location).find('input:checkbox')
            $.each( anodes, function( index, chkbox ) {
                if (ag_employees_by_org.hasOwnProperty(chkbox.value)) {
                    $.each(ag_employees_by_org[chkbox.value], function(index, emp) {
                        if (!ag_selected_employees.includes(emp.employee_id)) {
                            ag_selected_employees.push( emp.employee_id );    
                        }
                    })
                } else {
                    if (chkbox.name == 'userCheck[]') {
                        if (!ag_selected_employees.includes(chkbox.value)) {
                            ag_selected_employees.push( chkbox.value);    
                        }
                    }
                }
            });

        } else {

            //$(location).collapse('hide');

            // unchecked the children 
            items = $(location).find('input:checkbox');
            $.each(items, function(index, item) {
                $(item).prop('checked', false);
                $(item).prop("indeterminate", false);
            })  


            // User level checkbox 
            if ( $(e.target).attr('name') == 'userCheck[]') {
                emp_id = $(e.target).val();  
                var index = $.inArray(emp_id, ag_selected_employees);
                if (index > -1) {
                    ag_selected_employees.splice( index, 1 );
                }
            }

            anode = $(e.target).val();
            if (ag_employees_by_org.hasOwnProperty( anode )) {
                $.each(ag_employees_by_org[ anode  ], function(index, emp) {
                    if (!ag_selected_employees.includes(emp.employee_id)) {
                        ag_selected_employees.push( emp.employee_id );    
                    } 
                })  
            }

            anodes = $(location).find('input:checkbox');
            $.each( anodes, function( index, chkbox ) {
                if (ag_employees_by_org.hasOwnProperty(chkbox.value)) {
                    $.each(ag_employees_by_org[chkbox.value], function(index, emp) {
                        var index = $.inArray(emp.employee_id, ag_selected_employees);
                        if (index > -1) {
                            ag_selected_employees.splice( index, 1 );
                        }
                    })
                } else {
                    if (chkbox.name == 'userCheck[]') {
                        var index = $.inArray(chkbox.value, ag_selected_employees);
                        if (index > -1) {
                            ag_selected_employees.splice( index, 1 );
                        }
                    }
                }
            });
            
        }      

        // console.log( ag_selected_employees);     

        pid = $(this).find('input:first').attr('pid');
        do {
            value = '#aorgCheck' + pid;
            //console.log(  value );
            atoggle_indeterminate( value );
            //console.log("parent : " + pid);                
            pid = $('#aorgCheck' + pid).attr('pid');    
        } 
        while (pid);

    }

});

$("#aaccordion-level0").on('shown.bs.collapse', function () {
    // do something
    el = $('a.toggle-accordion');
    if ( !el.hasClass("active")) {
        el.addClass( "active");
    }
});

$("#aaccordion-level0").on('hidden.bs.collapse', function () {
    
    count = $('div.collapse.show').length;
    if (count == 0) {
        el = $('a.toggle-accordion');
        if ( el.hasClass("active")) {
            el.removeClass( "active");
        }
    }
});

$("#atoggle-accordion").on("click", function(e) {

    b_active =  $( e.target ).hasClass( "active" );
    
    if (b_active) {
        anodes = $('div.collapse.show');
        $.each( nodes, function( index, item ) {
            $(item).collapse('hide');
        });
        $( e.target ).removeClass( "active" );
    } else {
        anodes = $('div.collapse');
        $.each( anodes, function( index, item ) {
            $(item).collapse('show');
        });
        $( e.target ).addClass( "active" );
    }

})


});
</script>
