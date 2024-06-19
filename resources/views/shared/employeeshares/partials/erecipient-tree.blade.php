
<div class="card px-3 pb-3">

    @error('euserCheck')                
    <div class="p-0">
    <span class="text-danger">
        {{  'The recipient is required.'  }}
    </span>
    </div>
    @enderror

@if ($eorgs->count() > 0)
    <div class="p-0">
        <div class="accordion-option">
            <a href="javascript:void(0)" class="toggle-accordion" 
            accordion-id="#eaccordion"></a>
        </div>
    </div>

    
    <div id="eaccordion-level0">
        @foreach($eorgs as $eorg)
        <div class="card">
            @if ($eorg->children->count() > 0 )    
            <div class="card-header" id="eheading-{{ $eorg->id }}">
                <h6 class="mb-0">
                
                <a role="button" data-toggle="collapse" href="#ecollapse-{{ $eorg->id }}" aria-expanded="false" class="collapsed"
                            aria-controls="ecollapse-{{ $eorg->id }}">
                    <input pid="" class="" type="checkbox"  id="eorgCheck{{ $eorg->id }}" name="eorgCheck[]" 
                        {{ (is_array(old('eorgCheck')) and in_array($eorg->id, old('eorgCheck'))) ? ' checked' : '' }}
                        value="{{ $eorg->id }}">    
                        <span class="pr-2">{{ $eorg->name }}</span>
                    <span class="badge badge-pill badge-primary">{{ $ecountByOrg[$eorg->id] }}</span>
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
                                        <input pid="{{ $eorg->id }}"  class="" type="checkbox"  id="eorgCheck{{ $eprogram->id }}" 
                                            {{ (is_array(old('eorgCheck')) and in_array($eprogram->id, old('eorgCheck'))) ? ' checked' : '' }}
                                            name="eorgCheck[]" value="{{ $eprogram->id }}"> 
                                        <span class="pr-1">{{ $eprogram->name }}</span>
                                        <span class="badge badge-pill badge-primary">{{ $ecountByOrg[$eprogram->id] }}</span>
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
                                                    <input pid="{{ $eprogram->id }}" class="" type="checkbox"  id="eorgCheck{{ $edivision->id }}" name="eorgCheck[]" 
                                                        {{ (is_array(old('eorgCheck')) and in_array($edivision->id, old('eorgCheck'))) ? ' checked' : '' }}
                                                        value="{{ $edivision->id }}">
                                                    <span class="pr-1">{{ $edivision->name }}</span>
                                                    <span class="badge badge-pill badge-primary">{{ $ecountByOrg[$edivision->id] }}</span>
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
                                                                        <input pid="{{ $edivision->id }}" class="" type="checkbox"  id="eorgCheck{{ $ebranch->id }}" name="eorgCheck[]" 
                                                                        {{ (is_array(old('eorgCheck')) and in_array($ebranch->id, old('eorgCheck'))) ? ' checked' : '' }}
                                                                            value="{{ $ebranch->id }}">
                                                                        <span class="pr-1">{{ $ebranch->name }}</span>
                                                                        <span class="badge badge-pill badge-primary">{{ $ecountByOrg[$ebranch->id] }}</span>
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
                                                                                            <input pid="{{ $ebranch->id }}"  class="" type="checkbox"  id="eorgCheck{{ $elevel4->id }}" name="eorgCheck[]" 
                                                                                            {{ (is_array(old('eorgCheck')) and in_array($elevel4->id, old('eorgCheck'))) ? ' checked' : '' }}
                                                                                            value="{{ $elevel4->id }}">
                                                                                            <span class="pr-1">{{ $elevel4->name }}</span>
                                                                                            <span class="badge clickable badge-pill badge-primary">{{ $ecountByOrg[$elevel4->id] }}</span>
                                                                                        </a>
                                                                                        </h6>
                                                                                    </div>
                                                                                </div>

                                                                                {{-- level4 -- Employee Listing - Start --}}                                              
                                                                                <div id="ecollapse-{{ $elevel4->id }}" class="collapse" data-parent="#eaccordion-4" aria-labelledby="eheading-{{ $elevel4->id }}">
                                                                                    <div class="mt-2 fas fa-spinner fa-spin fa-3x fa-fw loading-spinner" role="status" style="display:none">
                                                                                        <span class="sr-only">Loading...</span>
                                                                                    </div>
                                                                                    <div class="card-header eemployee-list" id="eemployees-{{ $elevel4->id }}" value="{{ $elevel4->id }}"></div>
                                                                                </div>
                                                                                {{-- LEVEL4 -- Employee Listing - End --}}                                                                                         

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
                                                                        <input pid="{{ $edivision->id }}" class="" type="checkbox"  id="eorgCheck{{ $ebranch->id }}" name="eorgCheck[]" 
                                                                            {{ (is_array(old('eorgCheck')) and in_array($ebranch->id, old('eorgCheck'))) ? ' checked' : '' }}
                                                                                value="{{ $ebranch->id }}">
                    
                                                                        <span class="pr-1">{{ $ebranch->name }}</span>
                                                                        <span class="badge clickable badge-pill badge-primary">{{ $ecountByOrg[$ebranch->id] }}</span>
                                                                    </a>
                                                                    </h6>                                                                
                                                                </div>

                                                                {{-- BRANCH -- Employee Listing - Start --}}                                                                                         
                                                                <div id="ecollapse-{{ $ebranch->id }}" class="collapse" data-parent="#eaccordion-3" aria-labelledby="eheading-{{ $ebranch->id }}">
                                                                    <div class="card-header eemployee-list" id="eemployees-{{ $ebranch->id }}" value="{{ $ebranch->id }}"></div>
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

                                            <div class="card-header employees" id="eheading-{{ $edivision->id }}">
                                                <h6 class="mb-0">
                                                <a role="button" data-toggle="collapse" href="#ecollapse-{{ $edivision->id }}" aria-expanded="false" class="collapsed"
                                                    aria-controls="ecollapse-{{ $edivision->id }}" data="{{ $edivision->id }}">

                                                    <input pid="{{ $eprogram->id }}" class="" type="checkbox"  id="eorgCheck{{ $edivision->id }}" name="eorgCheck[]" 
                                                      {{ (is_array(old('eorgCheck')) and in_array($edivision->id, old('eorgCheck'))) ? ' checked' : '' }}
                                                       value="{{ $edivision->id }}">

                                                    <span class="pr-1">{{ $edivision->name }}</span>
                                                    <span class="badge clickable badge-pill badge-primary">{{ $ecountByOrg[$edivision->id] }}</span>
                                                </a>
                                                </h6>
                                            </div>

                                            {{-- DIVISION -- Employee Listing - Start --}}                                                                                         
                                            <div id="ecollapse-{{ $edivision->id }}" class="collapse" data-parent="#eaccordion-2" aria-labelledby="eheading-{{ $edivision->id }}">
                                                <div class="card-header eemployee-list" id="eemployees-{{ $edivision->id }}" value="{{ $edivision->id }}"></div>
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
                            <div class="card-header employees" id="eheading-{{ $eprogram->id }}">
                                <h6 class="mb-0">
                                <a role="button" data-toggle="collapse" href="#ecollapse-{{ $eprogram->id }}" aria-expanded="false" class="collapsed"
                                    aria-controls="ecollapse-{{ $eprogram->id }}" data="{{ $eprogram->id }}">

                                    <input pid="{{ $eorg->id }}" class="" type="checkbox"  id="eorgCheck{{ $eprogram->id }}" name="eorgCheck[]" 
                                        {{ (is_array(old('eorgCheck')) and in_array($eprogram->id, old('eorgCheck'))) ? ' checked' : '' }}
                                        value="{{ $eprogram->id }}">

                                    <span class="pr-1">{{ $eprogram->name }}</span>
                                    <span class="badge clickable badge-pill badge-primary">{{ $ecountByOrg[$eprogram->id] }}</span>
                                </a>
                                </h6>
                            </div>

                            {{-- PROGRAM -- Employee Listing - Start --}}                                                                                         
                            <div id="ecollapse-{{ $eprogram->id }}" class="collapse" data-parent="#eaccordion-1" aria-labelledby="eheading-{{ $eprogram->id }}">
                                <div class="card-header eemployee-list" id="eemployees-{{ $eprogram->id }}" value="{{ $eprogram->id }}"></div>
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
            <div class="card-header" id="eheading-{{ $eorg->id }}">
                <h6 class="mb-0">
                <a role="button" class="disabled collapsed">

                    <input pid="" class="" type="checkbox"  id="eorgCheck{{ $eorg->id }}" name="eorgCheck[]" 
                        {{ (is_array(old('eorgCheck')) and in_array($eorg->id, old('eorgCheck'))) ? ' checked' : '' }}
                        value="{{ $eorg->id }}">

                    <span class="pr-1">{{ $eorg->name }}</span>
                    <span class="badge clickable badge-pill badge-primary">{{ $ecountByOrg[$eorg->id] }}</span>
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

    eg_employees_by_org = {!!json_encode($eempIdsByOrgId)!!};      

    elist = $("input[type=checkbox]:checked");

    $.each(elist, function( index, item ) {

        pid = $(item).attr('pid');

        do {
            value = '#eorgCheck' + pid;
            toggle_indeterminate( value );
            pid = $('#eorgCheck' + pid).attr('pid');    
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
        var target = '#eemployees-' + tree_id;
        if($.trim($(target).html())=='') {
            $.ajax({
                url: '/'+'{{ request()->segment(1) }}'+'/employeeshares/eemployees/'+tree_id,
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
						if (eg_selected_employees.includes(chkbox.value)) {
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

    $("#eaccordion-level0 .card-header.employees").on("click","a", function(e) 
    {
        var tree_id = $(this).attr('data');
        load_employees_on_node(tree_id);
    });

    $("#eaccordion-level0 .card-header").on("click","a", function(e) {
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
                if ( $(e.target).attr('name') == 'euserCheck[]') {
                    emp_id = $(e.target).val();  
                    if (!eg_selected_employees.includes(emp_id)) {
                            eg_selected_employees.push( emp_id );    
                    } 
                }

                node  = $(e.target).val();
                if (eg_employees_by_org.hasOwnProperty( node )) {
                    $.each(eg_employees_by_org[ node  ], function(index, emp) {
                        if (!eg_selected_employees.includes(emp.employee_id)) {
                            eg_selected_employees.push( emp.employee_id );    
                        } 
                    })  
                }

                nodes = $(location).find('input:checkbox')
                $.each( nodes, function( index, chkbox ) {
                    if (eg_employees_by_org.hasOwnProperty(chkbox.value)) {
                        $.each(eg_employees_by_org[chkbox.value], function(index, emp) {
                            if (!eg_selected_employees.includes(emp.employee_id)) {
                                eg_selected_employees.push( emp.employee_id );    
                            }
                        })
                    } else {
                        if (chkbox.name == 'euserCheck[]') {
                            if (!eg_selected_employees.includes(chkbox.value)) {
                                eg_selected_employees.push( chkbox.value);    
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
                if ( $(e.target).attr('name') == 'euserCheck[]') {
                    emp_id = $(e.target).val();  
                    var index = $.inArray(emp_id, eg_selected_employees);
                    if (index > -1) {
                        eg_selected_employees.splice( index, 1 );
                    }
                }

                node = $(e.target).val();
                if (eg_employees_by_org.hasOwnProperty( node )) {
                    $.each(g_employees_by_org[ node  ], function(index, emp) {
                        if (!eg_selected_employees.includes(emp.employee_id)) {
                            eg_selected_employees.push( emp.employee_id );    
                        } 
                    })  
                }

                nodes = $(location).find('input:checkbox');
                $.each( nodes, function( index, chkbox ) {
                    if (eg_employees_by_org.hasOwnProperty(chkbox.value)) {
                        $.each(eg_employees_by_org[chkbox.value], function(index, emp) {
                            var index = $.inArray(emp.employee_id, eg_selected_employees);
                            if (index > -1) {
                                eg_selected_employees.splice( index, 1 );
                            }
                        })
                    } else {
                        if (chkbox.name == 'euserCheck[]') {
                            var index = $.inArray(chkbox.value, eg_selected_employees);
                            if (index > -1) {
                                eg_selected_employees.splice( index, 1 );
                            }
                        }
                    }
                });
                
            }      

            pid = $(this).find('input:first').attr('pid');
            do {
                value = '#eorgCheck' + pid;
                toggle_indeterminate( value );
                pid = $('#eorgCheck' + pid).attr('pid');    
            } 
            while (pid);

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
