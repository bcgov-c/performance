<script src="https://cdn.ckeditor.com/4.20.1/standard-all/ckeditor.js"></script>
<style>
    th{
        padding:20px;
    }
    td{
        padding:20px;
    }
    .float-right {
        float: right;
    }
</style>    

<x-side-layout title="{{ __('My Goals - Performance Development Platform') }}">
    <h3>Goal Bank</h3>
    <div class="row">        
        @include('goal.partials.tabs')
    </div>
    <div class="mt-4">
        <div class="card">
            <div class="card-header" id="heading_0">
                        <h5 class="mb-0"data-toggle="collapse" data-target="#collapse_0" aria-expanded="1" aria-controls="collapse_0">
                            <button class="btn btn-link" >
                                <h4>My Goal Bank</h4> 
                            </button>                        
                            <span class="float-right" style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span>    
                            <br/>                                
                            <button class="btn btn-link text-left" style="color:black">
                                <p>The goals below have been created for you by your supervisor or organization. Click on a goal to view it and add it to your own profile. 
                            If needed, you can edit the goal to personalize it once it is in your profile. </p>
                            </button>  
                        </h5>
            </div>

		    <div id="collapse_0" class="collapse" aria-labelledby="heading_0">
                    <div class="card-body">
                        
                        <form action="" method="get" id="filter-menu">
                            <div class="row">
                                <div class="col">
                                    <label>
                                        Title
                                        <input type="text" id="goal_bank_title" name="goal_bank_title" class="form-control" value="{{request()->goal_bank_title}}">
                                    </label>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$goaltypes_filter" id="goal_bank_types" label="Goal Type" name="goal_bank_types" :selected="request()->goal_bank_types"></x-dropdown>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$tagsList" label="Tags" id="goal_bank_tags" name="goal_bank_tags" :selected="request()->goal_bank_tags"></x-dropdown>
                                </div>
                                <div class="col">
                                    <label>
                                        Date Added
                                        <input class="sup_filtersub form-control form-control-md" id="goal_bank_dateadd" type="date" name="goal_bank_dateadd" value="{{request()->goal_bank_dateadd}}" autocomplete="off">
                                    </label>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$createdBy" id="goal_bank_createdby" name="goal_bank_createdby" :selected="request()->goal_bank_createdby" label="Created by"></x-dropdown>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$mandatoryOrSuggested" id="goal_bank_mandatory" label="Mandatory/Suggested" name="goal_bank_mandatory" :selected="request()->goal_bank_mandatory"></x-dropdown>
                                </div><!-- 
                                <div class="col">
                                    <button class="btn btn-primary mt-4 px-5">Filter</button>
                                </div> -->
                            </div>
                        </form>

                        <form id="multigoals" action="" method="post">
                            @csrf
                            <div class="row">
                                <div class="col">
                                    <div class="card">
                                        <div class="card-body">
                                            <input name="total_count" id="total_count" type="hidden" value="{{$goals_count}}">                                            
                                            <table style="width:100%" id='goalbanks' class="table table-striped"> </table>
                                        </div>
                                    </div>
                                    @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
                                    <div class="text-center">
                                        <x-button id="addMultipleGoalButton" disabled>Add Selected Goals to Your Profile</x-button>
                                        <x-button id="hideMultipleGoalButton" disabled>Hide Selected Goals</x-button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                        
                        
                        
                    </div>
		    </div>
	    </div>
        @include('goal.partials.goal-detail-modal')
        @if(Auth::user()->hasRole('Supervisor'))
        @php $shareWithLabel = 'Audience' @endphp
        @php $doNotShowInfo = true @endphp
        @push('css')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
        @endpush
        <div class="card">
		<div class="card-header" id="heading_1">
		<h5 class="mb-1"data-toggle="collapse" data-target="#collapse_1" aria-expanded="1" aria-controls="collapse_1">
                    <button class="btn btn-link" >
                        <h4>Team Goal Bank</h4> 
                    </button>                        
                    <span class="float-right" style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span>  
                    <br/>                                
                    <button class="btn btn-link text-left" style="color:black">
                        <p>Create a goal for your employees to use in their own profile. Goals can be suggested (for example, 
                    a learning goal to help increase team skill or capacity in a relevant area) or mandatory 
                    (for example, a work goal detailing a new priority that all employees are responsible for). 
                    Employees will be notified when a new goal has been added to their Goal Bank.  </p>
                    </button> 
                </h5>
		</div>
            
		<div id="collapse_1" class="collapse" aria-labelledby="heading_1">
                    <div class="card-body">                        
                        <p>
                            <x-button id="add-goal-to-library-btn" class="my-2">
                                <i class="fas fa-plus-square"></i> Add Goal to Bank
                            </x-button>
                        </p>
                         <form action="" method="get" id="filter-lib-menu">
                            <div class="row">
                                <div class="col">
                                    <label>
                                        Title
                                        <input type="text" name="title" id="title" class="form-control" value="{{request()->title}}">
                                    </label>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$goaltypes_filter" label="Goal Type" id="goal_type"  name="goal_type" :selected="request()->goal_type"></x-dropdown>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$tagsList" label="Tags" id="tag_id" name="tag_id" :selected="request()->tag_id"></x-dropdown>
                                </div>
                                <div class="col">
                                    <label>
                                        Date Added
                                        <input class="form-control form-control-md" type="date" name="date_added" id="date_added" value="{{request()->date_added}}" autocomplete="off">
                                    </label>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$mandatoryOrSuggested" label="Mandatory/Suggested" id="is_mandatory" name="is_mandatory" :selected="request()->is_mandatory"></x-dropdown>
                                </div><!-- 
                                <div class="col">
                                    <button class="btn btn-primary mt-4 px-5">Filter</button>
                                </div> -->
                            </div>
                            <input name="sortby" id="sortby" value="{{$sortby}}" type="hidden">
                            <input name="sortorder" id="sortorder" value="{{$sortorder}}" type="hidden">
                        </form>
                        <br/>
                        <table style="width:100%" id='team_goalbanks' class="table table-striped">  </table>
                        <form action="{{ route('goal.sync-goals')}}" method="POST" id="share-my-goals-form">
                            @csrf
                            <div class="d-none" id="syncGoalSharingData"></div>
                            <input type="hidden" name="sync_goal_id" id="sync_goal_id" value=""> 
                            <input type="hidden" name="sync_users" id="sync_users" value=""> 
                        </form>
                    </div>
		</div>
	</div>

        
        <div class="modal fade" id="addGoalModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title" id="addGoalModalLabel">Select Date</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">

                        <div class="row">
                            <div class="col-sm-6">
                                <x-input label="Start Date" class="error-start" type="date" id="start_date" />
                                <small class="text-danger error-start_date"></small>
                            </div>
                            <div class="col-sm-6">
                                <x-input label="End Date " class="error-target" type="date" id="target_date" />
                                <small class="text-danger error-target_date"></small>
                            </div>

                            <div class="col-12 text-left pb-5 mt-3">
                                <x-button type="button" class="btn-md btn-submit"> Save Changes</x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @push('js')
            <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script>

            $(document).on('change', '.search-users', function() {
                var goalId = $(this).data('goal-id');
                var selectedValues = $(this).val();
                console.log('Goal ID:', goalId);
                console.log('Selected Values:', selectedValues);
                $('#sync_goal_id').val(goalId);
                $('#sync_users').val(selectedValues);
            });
            
            $(document).on('click', '[data-action="delete-goal"]', function () {
                if (confirm($(this).data('confirmation'))) {
                    let action = $("#delete-goal-form").attr('action');
                    action = action.replace('xxx', $(this).data("goal-id"));
                    $("#delete-goal-form").attr('action', action);
                    $("#delete-goal-form").submit();
                }
            });
            $(document).on('change', '.is-shared', function (e) {
                let confirmMessage = "Making this goal private will hide it from all employees. Continue?";
                if ($(this).val() == "1") {
                    confirmMessage = "Sharing this goal will make it visible to the selected employees. Continue?"
                }
                if (!confirm(confirmMessage)) {
                    // this.checked = !this.checked;
                    $(this).val($(this).val() == "1" ? "0" : "1");
                    e.preventDefault();
                    return;
                }
                // $(this).parents("label").find("span").html(this.checked ? "Shared" : "Private");
                const goalId = $(this).data('goal-id');
                $("#search-users-" + goalId).multiselect($(this).val() == "1" ? 'enable' : 'disable');
                const form = $(this).parents('form').get()[0];
                fetch(form.action,{method:'POST', body: new FormData(form)});
            });
            $(document).ready(() => {
                $(".search-users").each(function() {
                    const goalId = $(this).data('goal-id');
                    const selectDropdown = this;
                    $(this).multiselect({
                        allSelectedText: 'All',
                        selectAllText: 'All',
                        includeSelectAllOption: true,
                        onDropdownHide: function () {
                            document.getElementById("syncGoalSharingData").innerHTML = "";
                            finalSelectedOptions = [...selectDropdown.options].filter(option => option.selected).map(option => option.value);
                            finalSelectedOptions.forEach((value) => {
                                const input = document.createElement("input");
                                input.setAttribute('value', value);
                                input.name = "itemsToShare[]";
                                document.getElementById("syncGoalSharingData").appendChild(input);
                            });
                            const input = document.createElement("input");
                            input.setAttribute('value', goalId);
                            input.name = "goal_id";
                            document.getElementById("syncGoalSharingData").appendChild(input);
                            const form = $("#share-my-goals-form").get()[0];
                            fetch(form.action,{method:'POST', body: new FormData(form)});
                        }
                    });
                });
            });
        </script>
        @endpush       
        @endif
        
        
    </div>


    

    <div class="card">
            <div class="card-header" id="heading_2">
                        <h5 class="mb-0"data-toggle="collapse" data-target="#collapse_2" aria-expanded="1" aria-controls="collapse_2">
                            <button class="btn btn-link" >
                                <h4>Hidden Goals</h4> 
                            </button>                        
                            <span class="float-right" style="color:#1a5a96"><i class="fa fa-chevron-down"></i></span>    
                            <br/>                                
                            <button class="btn btn-link text-left" style="color:black">
                                <p>Hidden goals appear here. Clean up your goal bank by hiding goals which are not immediately relevant to you. </p>
                            </button>  
                        </h5>
            </div>

		    <div id="collapse_2" class="collapse" aria-labelledby="heading_2">
                    <div class="card-body">
                        
                        <form action="" method="get" id="filter-menu-hidden">
                            <div class="row">
                                <div class="col">
                                    <label>
                                        Title
                                        <input type="text" id="goal_bank_title_hidden" name="goal_bank_title_hidden" class="form-control" value="{{request()->goal_bank_title_hidden}}">
                                    </label>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$goaltypes_filter" id="goal_bank_types_hidden" label="Goal Type" name="goal_bank_types_hidden" :selected="request()->goal_bank_types_hidden"></x-dropdown>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$tagsList" label="Tags" id="goal_bank_tags_hidden" name="goal_bank_tags_hidden" :selected="request()->goal_bank_tags_hidden"></x-dropdown>
                                </div>
                                <div class="col">
                                    <label>
                                        Date Added
                                        <input class="sup_filtersub form-control form-control-md" id="goal_bank_dateadd_hidden" type="date" name="goal_bank_dateadd_hidden" value="{{request()->goal_bank_dateadd_hidden}}" autocomplete="off">
                                    </label>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$createdBy" id="goal_bank_createdby_hidden" name="goal_bank_createdby_hidden" :selected="request()->goal_bank_createdby_hidden" label="Created by"></x-dropdown>
                                </div>
                                <div class="col">
                                    <x-dropdown :list="$mandatoryOrSuggested" id="goal_bank_mandatory_hidden" label="Mandatory/Suggested" name="goal_bank_mandatory_hidden" :selected="request()->goal_bank_mandatory_hidden"></x-dropdown>
                                </div><!-- 
                                <div class="col">
                                    <button class="btn btn-primary mt-4 px-5">Filter</button>
                                </div> -->
                            </div>
                        </form>

                        <form id="multigoals_hide"  action="{{ route('goal.library.show-multiple') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col">
                                    <div class="card">
                                        <div class="card-body">
                                            <input name="total_count" id="total_count_hidden" type="hidden" value="{{$goals_count}}">                                            
                                            <table style="width:100%" id='goalbanks_hidden' class="table table-striped"> </table>
                                        </div>
                                    </div>
                                    @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
                                    <div class="text-center">
                                        <x-button id="listMultipleGoalButton" disabled>Move Selected Goals to My Goal Bank</x-button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                        
                        
                        
                    </div>
		    </div>
	    </div>
        
        
    
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.min.css') }}">
    @endpush
    @push('js')
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="{{ asset('js/bootstrap-multiselect.min.js')}} "></script>
        <script>
            var modal_open = false;
            var need_fresh = false;
            var autosave = true;
            var no_warning = false;
            var myTimeout;

            $('#filter-menu select, #filter-menu input').change(function () {
                $("#filter-menu").submit();
            });
            
            function sort(v) {
                $('#sortby').val(v);
                $("#filter-menu").submit();
            }
            
            $('#filter-lib-menu select, #filter-lib-menu input').change(function () {
                $("#filter-lib-menu").submit();
            });

            $('#filter-menu-hidden select, #filter-menu-hidden input').change(function () {
                $("#filter-menu-hidden").submit();
            });

        </script>
        <script>
            $(document).on('click', '.show-goal-detail', function(e) {
                e.preventDefault();
                $("#goal_form").find('input[name=selected_goal]').val($(this).data('id'));

                $.get('/goal/goalbank/'+$(this).data('id')+'?add=true', function (data) {
                    $("#goal-detail-modal").find('.data-placeholder').html(data);
                    $("#goal-detail-modal").modal('show');
                });
            });
        </script>
        <script>
            $(document).on('click', '#addBankGoalToUserBtn', function(e) {
                const goalId = $(this).data("id");
                e.preventDefault();
                $.ajax({
                    url: '/goal/goalbank'
                    , type: 'POST'
                    , data: {
                        selected_goal: goalId
                    },
                    beforeSend: function(request) {
                        return request.setRequestHeader('X-CSRF-Token', $(
                            "meta[name='csrf-token']").attr('content'));
                    },

                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            window.location.href = '/goal';
                        }
                    }
                    , error: function(error) {
                        var errors = error.responseJSON.errors;

                    }
                });

            });
            
            $(document).ready(() => {
                $(".tags").multiselect({
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true
                });
            });


@isset($open_modal_id)
            // this is redirect from DashboardController with the related id, then open modal box
            $(function() {
                related_id = {{ $open_modal_id }};
                $("#goal_form").find('input[name=selected_goal]').val ( related_id );

                $.get('/goal/goalbank/'+related_id+'?add=true', function (data) {
                    $("#goal-detail-modal").find('.data-placeholder').html(data);
                    $("#goal-detail-modal").modal('show');
                });
            });
@endisset             

        </script>
    @endpush
    
    
    
    <x-slot name="js">

    </x-slot>
    <x-slot name="css">
        <style>
            i {
                transition: 0.2s ease-in-out;
            }
            [aria-expanded="true"] i{
                transform: rotate(180deg);
            }
            
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #444444;
                border: 1px solid #aaa;
                border-radius: 4px;
                cursor: default;
                float: left;
                margin-right: 5px;
                margin-top: 5px;
                padding: 0 5px;
            }
            
            .headborder{
                border-bottom: solid #FCBA19;
            }
            
        </style>
    </x-slot>

</x-side-layout>


<script>          
        
        $('body').popover({
            selector: '[data-toggle]',
            trigger: 'click',
        });
        
        $('.modal').popover({
            selector: '[data-toggle-select]',
            trigger: 'click',
        });
           
        function setTimeRoll(){
                const minutes = 1;
                const SessionTime = 1000 * 60 * minutes;
                if (myTimeout) { clearInterval(myTimeout) };
                //const myTimeout = setTimeout(sessionWarning, SessionTime);
                myTimeout = setInterval(function() { 
                    if (modal_open == true && autosave == true) {
                        //$(".btn-submit").trigger("click");  
                        for (var i in CKEDITOR.instances){
                            CKEDITOR.instances[i].updateElement();
                        };
                        $.ajax({
                            url:'/my-team/add-goal-to-library',
                            type : 'POST',
                            data: $('#add-goal-to-library-form').serialize(),
                            success: function (result) {
                                console.log(result);
                                need_fresh = true;
                                if(result.success){
                                    autosave = false;
                                    no_warning = true;
                                    alert('You have not saved your work in 20 minutes. To protect your work, it has been automatically saved.');
                                    //window.location.href= '/goal';
                                    $('.alert-danger').show();
                                    $('.alert-danger').html('Your goal has been saved.');
                                    $('#created_id').val(result.goal_id)
                                    //$('.btn-submit').hide();
                                    //$('.text-danger').hide();
                                    //$('.form-control').removeClass('is-invalid');                                    
                                    //$('#addGoalToLibraryModal').modal('toggle');
                                }
                            },
                            error: function (error){
                                console.log(error);
                                need_fresh = false;
                                autosave =  true;
                                $('.btn-submit').show();
                                $('.btn-submit').prop('disabled',false);
                                $('.btn-submit').html('Save Changes');
                                $('.alert-danger').html('<i class="fa fa-info-circle"></i> There are one or more errors on the page. Please review and try again.');
                                $('.alert-danger').show();
                                $('.modal-body').animate({scrollTop: 0},100);
                                var errors = error.responseJSON.errors;
                                $('.text-danger').each(function(i, obj) {
                                    $('.text-danger').text('');
                                });
                                Object.entries(errors).forEach(function callback(value, index) {
                                    var className = '.error-' + value[0];
                                    $('#addGoalModal input[name='+value[0]+']').addClass('is-invalid');
                                    $(className).text(value[1]);
                                });
                                //alert('You have been inactive for more than 15 minutes. Your goal has been automatically saved.');
                            }
                        });
                    }    
                }, SessionTime);                
            }


</script>    
<style>
    .multiselect-container{
        height: 350px; 
        overflow-y: scroll;
    }
</style>   


@push('css')

    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<style>
        #employee-list-table_filter label {
            text-align: right !important;
            padding-right: 10px;
        } 
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
@endpush   

<script>
    $(document).ready(function() {
      const json_goalbanks = <?php echo $json_goalbanks;?>;

      if(json_goalbanks == ''){
        $('#addMultipleGoalButton').prop('disabled', true);
        $('#hideMultipleGoalButton').prop('disabled', true);
      }

      const goalbanks = $('#goalbanks').DataTable({
        data: json_goalbanks,
        columns: [
          {
            title: "<input type='checkbox' id='checkAll'>",
            data: null,
            orderable: false, // Disable sorting
            render: function(data, type, row) {
              return '<input type="checkbox" name="goal_ids[]" value="' + row.id + '" class="row-checkbox goal_ids">';
            }
          },
          { title: "ID", data: "id" },
          {
            title: "Goal Title",
            data: null,
            render: function(data, type, row) {
                return '<a href="#" class="show-goal-detail highlighter" data-id="' + row.id + '">' + data.title + '</a>';
            }
          },
          { title: "Goal Type", data: "typename" },
          { title: "Tags", data: "tagnames" },
          { title: "Date Added", data: "created_at" },
          {
            title: "Created by",
            data: null,
            render: function(data, type, row) {
              if (row.display_name) {
                return row.display_name;
              } else {
                return row.username;
              }
            }
          },
          { title: "Mandatory/Suggested", data: "is_mandatory" }
        ],
        "order": [[0, "desc"]],
        dom: '<"row"<"col-md-12"t>>' + '<"row"<"col-md-6"i><"col-md-6"p>>'
      });

      goalbanks.column(1).visible(false);

      // Add event listener for "check all" checkbox
      $('#checkAll').on('change', function() {
          $('.goal_ids').prop('checked', this.checked);
          if (this.checked) {
            if(json_goalbanks != ''){        
                $('#addMultipleGoalButton').prop('disabled', false);
                $('#hideMultipleGoalButton').prop('disabled', false);
            }
            json_goalbanks.page.len(-1).draw(); // Disable paging temporarily
            json_goalbanks.rows().select(); // Select all rows
          } else {
            $('#addMultipleGoalButton').prop('disabled', true);
            $('#hideMultipleGoalButton').prop('disabled', true);
            json_goalbanks.rows().deselect(); // Deselect all rows
            json_goalbanks.page.len(10).draw(); // Set the original page length and redraw
          }
      });

    var addButton = $('#addMultipleGoalButton');
    var hideButton = $('#hideMultipleGoalButton');
    
    $('#goalbanks').on('change', '.goal_ids', function() {
        var checkboxes = $('.goal_ids'); // Get the updated checkboxes within the current page
        
        var anyChecked = checkboxes.is(':checked');
        
        addButton.prop('disabled', !anyChecked);
        hideButton.prop('disabled', !anyChecked);
        
        if (!anyChecked) {
            $('#checkAll').prop('checked', false);
        }
    });
    
    $('#goalbanks').on('change', '.goal_ids', function() {
        var checkboxes = $('.goal_ids'); // Get the updated checkboxes within the current page
        
        if (checkboxes.length === checkboxes.filter(':checked').length) {
            $('#checkAll').prop('checked', true);
        } else {
            $('#checkAll').prop('checked', false);
        }
    });
    
    $('#goalbanks').on('page.dt', function () {
        $('#checkAll').prop('checked', false);
    });




      const json_goalbanks_hidden = <?php echo $json_goalbanks_hidden;?>;
      console.log(json_goalbanks_hidden);

      if(json_goalbanks_hidden == ''){
        $('#listMultipleGoalButton').prop('disabled', true);
      }

      const goalbanks_hidden = $('#goalbanks_hidden').DataTable({
        data: json_goalbanks_hidden,
        columns: [
          {
            title: "<input type='checkbox' id='checkAll_hide'>",
            data: null,
            orderable: false, // Disable sorting
            render: function(data, type, row) {
              return '<input type="checkbox" name="goal_ids_hide[]" value="' + row.id + '" class="row-checkbox goal_ids_hide">';
            }
          },
          { title: "ID", data: "id" },
          {
            title: "Goal Title",
            data: null,
            render: function(data, type, row) {
                return '<a href="#" class="show-goal-detail highlighter" data-id="' + row.id + '">' + data.title + '</a>';
            }
          },
          { title: "Goal Type", data: "typename" },
          { title: "Tags", data: "tagnames" },
          { title: "Date Added", data: "created_at" },
          {
            title: "Created by",
            data: null,
            render: function(data, type, row) {
              if (row.display_name) {
                return row.display_name;
              } else {
                return row.username;
              }
            }
          },
          { title: "Mandatory/Suggested", data: "is_mandatory" }
        ],
        "order": [[0, "desc"]],
        dom: '<"row"<"col-md-12"t>>' + '<"row"<"col-md-6"i><"col-md-6"p>>'
      });

      goalbanks_hidden.column(1).visible(false);

      // Add event listener for "check all" checkbox
      $('#checkAll_hide').on('change', function() {
          $('.goal_ids_hide').prop('checked', this.checked);
          if (this.checked) {
            if(json_goalbanks_hidden != ''){        
                $('#listMultipleGoalButton').prop('disabled', false);
            }
            goalbanks_hidden.page.len(-1).draw(); // Disable paging temporarily
            goalbanks_hidden.rows().select(); // Select all rows
          } else {
            $('#listMultipleGoalButton').prop('disabled', true);
            goalbanks_hidden.rows().deselect(); // Deselect all rows
            goalbanks_hidden.page.len(10).draw(); // Set the original page length and redraw
          }
      });


    var listButton = $('#listMultipleGoalButton');
    
    $('#goalbanks_hidden').on('change', '.goal_ids_hide', function() {
        var checkboxes = $('.goal_ids_hide'); // Get the updated checkboxes within the current page
        
        var anyChecked = checkboxes.is(':checked');
        
        listButton.prop('disabled', !anyChecked);
        
        if (!anyChecked) {
            $('#checkAll_hide').prop('checked', false);
        }
    });
    
    $('#goalbanks_hidden').on('change', '.goal_ids_hide', function() {
        var checkboxes = $('.goal_ids_hide'); // Get the updated checkboxes within the current page
        
        if (checkboxes.length === checkboxes.filter(':checked').length) {
            $('#checkAll_hide').prop('checked', true);
        } else {
            $('#checkAll_hide').prop('checked', false);
        }
    });
    
    $('#goalbanks_hidden').on('page.dt', function () {
        $('#checkAll_hide').prop('checked', false);
    });

      
      
        const json_team_goalbanks = <?php echo $json_team_goalbanks;?>;
        const team_goalbanks = $('#team_goalbanks').DataTable({
          data: json_team_goalbanks,
          columns: [
            { title: "ID", data: "id" },
            {
              title: "Goal Title",
              data: "title",
              render: function(data, type, row) {
                return '<a href="' + '{{ route("goal.edit", [":id", "from" => "bank"]) }}'.replace(':id', row.id) + '" class="p-2">' + data + '</a>';
              }
            },
            { title: "Goal Type", data: "typename" },
            { title: "Tags", data: "tagnames" },
            { title: "Date Added", data: "created_at" },
            { title: "Mandatory/Suggested", data: "is_mandatory" },
            {
              title: "Audience",
              data: null,
              orderable: false, // Disable sorting
              render: function(data, type, row) {
                // Prepare the options for the multiselect dropdown
                var options = '';
                var sharedUserIds = data.shared_user_id ? data.shared_user_id.split(',') : [];
                var sharedUserNames = data.shared_user_name ? data.shared_user_name.split(',') : [];
                
                var employees = <?php echo json_encode($employees_list); ?>;

                for (var i = 0; i < employees.length; i++) {
                      var selected = '';
                      if(sharedUserIds.length > 0){
                          for (var a = 0; a < sharedUserIds.length; a++) {
                             if(sharedUserIds[a] == employees[i].id) {
                                 selected = 'selected';
                             }
                          }
                      }   
                  
                  options += '<option value="' + employees[i].id + '" ' + selected + '>' + employees[i].name + '</option>';
                  
                }
                // Generate the multiselect dropdown HTML
                var dropdownHtml = '<select multiple class="form-control search-users ml-1"  id="search-users-' + row.id + '" name="share_with[' + row.id + '][]"  data-goal-id="' + row.id + '" >' +
                  options +
                  '</select>';

                return dropdownHtml;
              }
            },
            {
              title: "Actions",
              data: null,
              orderable: false, // Disable sorting
              render: function (data, type, row) {
                var deleteButton = '<button class="btn btn-danger" onclick="confirmDelete(' + row.id + ')"><i class="fas fa-trash-alt"></i></button>';
                return deleteButton;
              }
            }
          ],
          "order": [[0, "desc"]],
          dom: '<"row"<"col-md-12"t>>' + '<"row"<"col-md-6"i><"col-md-6"p>>'
        });

      team_goalbanks.column(0).visible(false);
    });
    
    
    $(document).ready(() => {            
            $(".tags").multiselect({
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
            });
            $(".search-users").each(function() {
                const goalId = $(this).data('goal-id');
                const selectDropdown = this;
                let valueBeforeChange = [];
                $(this).multiselect({
                    allSelectedText: 'All',
                    selectAllText: 'All',
                    nonSelectedText: 'No one',
                    // nonSelectedText: null,
                    includeSelectAllOption: true,
                    onDropdownShow: function () {
                        valueBeforeChange = [...selectDropdown.options].filter(option => option.selected).map(option => option.value);
                    },
                    onDropdownHide: function () {
                        const valueAfterChange = [...selectDropdown.options].filter(option => option.selected).map(option => option.value);
                        let toRevert;
                        if (valueBeforeChange.length === 0 && valueAfterChange.length !== 0) {
                            toRevert = !confirm("Sharing this goal will make it visible to the selected employees. Continue?");
                        }

                        if (valueBeforeChange.length !==0 && valueAfterChange.length === 0) {
                            toRevert = !confirm("Making this goal private will hide it from all employees. Continue?");
                        }

                        if (toRevert) {
                            valueAfterChange.forEach((value) => {
                                if (!valueBeforeChange.includes(value)) {
                                    $(selectDropdown).multiselect('deselect', value);
                                }
                            });
                            valueBeforeChange.forEach((value) => {
                                $(selectDropdown).multiselect('select', value);
                            });
                        }
                        const finalSelectedOptions = [...selectDropdown.options].filter(option => option.selected).map(option => option.value);
                        document.getElementById("syncGoalSharingData").innerHTML = "";
                        finalSelectedOptions.forEach((value) => {
                            const input = document.createElement("input");
                            input.setAttribute('value', value);
                            input.name = $(selectDropdown).attr('name');
                            document.getElementById("syncGoalSharingData").appendChild(input);
                        });
                        const input = document.createElement("input");
                        input.setAttribute('value', finalSelectedOptions.length !== 0 ? "1" : "0");
                        input.name = "is_shared["+goalId+"]";
                        document.getElementById("syncGoalSharingData").appendChild(input);
                        const form = $("#share-my-goals-form").get()[0];
                        fetch(form.action, {method:'POST', body: new FormData(form)});
                    }
                });
            });
            
            $("#goal_title").change(function(){
                var goal_title = $("#goal_title").val();
                var tags = $('.tags').val(); 
                if (goal_title != '' && tags != ''){
                   CKEDITOR.instances['what'].setReadOnly(false);
                   CKEDITOR.instances['measure_of_success'].setReadOnly(false);
                   $('#addGoalToLibraryModal #start_date').prop("readonly",false);
                   $('#addGoalToLibraryModal #target_date').prop("readonly",false);
                } else {
                   CKEDITOR.instances['what'].setReadOnly(true);
                   CKEDITOR.instances['measure_of_success'].setReadOnly(true);
                   $('#addGoalToLibraryModal #start_date').prop("readonly",true);
                   $('#addGoalToLibraryModal #target_date').prop("readonly",true);
                }
            });
            
            $(".tags").change(function(){
                var goal_title = $("#goal_title").val();
                var tags = $('.tags').val(); 
                if (goal_title != '' && tags != ''){ 
                    CKEDITOR.instances['what'].setReadOnly(false);
                    CKEDITOR.instances['measure_of_success'].setReadOnly(false);
                    $('#addGoalToLibraryModal #start_date').prop("readonly",false);
                    $('#addGoalToLibraryModal #target_date').prop("readonly",false);
                } else {
                   CKEDITOR.instances['what'].setReadOnly(true);
                   CKEDITOR.instances['measure_of_success'].setReadOnly(true);
                   $('#addGoalToLibraryModal #start_date').prop("readonly",true);
                   $('#addGoalToLibraryModal #target_date').prop("readonly",true);
                }
            });

            $( "#addGoalToLibraryModal #start_date" ).change(function() {
                var start_date = $( "#addGoalToLibraryModal #start_date" ).val();
                $( "#addGoalToLibraryModal #target_date" ).attr("min",start_date);            
            });

            $( "#addGoalToLibraryModal #target_date" ).change(function() {
                var start_date = $( "#addGoalToLibraryModal #start_date" ).val();
                if (start_date === '') {
                    alert('Please choose start date first.');
                    $( "#addGoalToLibraryModal #target_date" ).val('');
                }           
            });
            
        });  

        $(document).ready(function() {
                $('#hideMultipleGoalButton').click(function(e) {
                    e.preventDefault();
                    
                    var form = $('#multigoals');
                    var actionUrl = '/goal/goalbank/hide-multiple';
                    
                    var selectedGoals = [];
                    
                    // Loop through the checkboxes and collect checked goal_ids
                    $('.goal_ids:checked').each(function() {
                        selectedGoals.push($(this).val());
                    });
                    if (selectedGoals.length > 0) {
                        $.ajax({
                            type: 'POST',
                            url: actionUrl,
                            data: {
                                goal_ids: selectedGoals
                            },
                            success: function(response) {
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                // Handle errors here
                            }
                        });
                    }
                });


                $('#addMultipleGoalButton').click(function(e) {
                    e.preventDefault();
                    
                    var form = $('#multigoals');
                    var actionUrl = '/goal/goalbank/copy-multiple';
                    
                    var selectedGoals = [];
                    
                    // Loop through the checkboxes and collect checked goal_ids
                    $('.goal_ids:checked').each(function() {
                        selectedGoals.push($(this).val());
                    });
                    
                    if (selectedGoals.length > 0) {
                        $.ajax({
                            type: 'POST',
                            url: actionUrl,
                            data: {
                                goal_ids: selectedGoals
                            },
                            success: function(response) {
                                window.location.replace("/goal/current");
                            },
                            error: function(xhr, status, error) {
                                // Handle errors here
                            }
                        });
                    }
                });
            });


    
</script>  

<style>
    .multiselect {
            overflow: hidden;
            text-overflow: ellipsis;
            width: 275px;
    }
    
    .alert-danger {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    
    
    .multiselect-container{
        height: 350px; 
        overflow-y: scroll;
    }
</style>    

<style>
    
    .dataTable > thead > tr > th:nth-child(5)[class*="sort"]:before,
    .dataTable > thead > tr > th:nth-child(5)[class*="sort"]:after {
        content: "" !important;
    }
    
    .panel-heading{
        opacity: 0.5;
    }
    .acc-title {
	display: block;
	height: 22px;
	position:absolute;
	top:11px;
	left:20px;
    }
    .acc-status {
	display: block;
	width: 22px;
	height: 22px;
	position:absolute;
	top:11px;
	right:11px;
    }
    
    #upcoming {
        font-weight: bold;
    }
    
    #employee_conversations {
        width: 100%;
    }  
    
    table.dataTable thead th {
        border-bottom: solid #FCBA19;
    }
</style> 
@include('my-team.partials.add-goal-to-library-modal')

<script src="//cdn.ckeditor.com/4.17.2/basic/ckeditor.js"></script>
<script>
    $(document).on('click', '#add-goal-to-library-btn', function () {
        modal_open = true;
        $("#addGoalToLibraryModal").modal('show');
    });
    $(".items-to-share").multiselect({
        allSelectedText: 'All',
        selectAllText: 'All',
        includeSelectAllOption: true
    });
    $(document).ready(function(){
        CKEDITOR.replace('what', {
                toolbar: "Custom",
                toolbar_Custom: [
                    ["Bold", "Italic", "Underline"],
                    ["NumberedList", "BulletedList"],
                    ["Outdent", "Indent"],
                    ["Link"],
                ],
                extraPlugins: 'editorplaceholder',
                editorplaceholder: 'Please complete goal type, title, and tags before entering this content.',
                disableNativeSpellChecker: false
            });
            CKEDITOR.replace('measure_of_success', {
                toolbar: "Custom",
                toolbar_Custom: [
                    ["Bold", "Italic", "Underline"],
                    ["NumberedList", "BulletedList"],
                    ["Outdent", "Indent"],
                    ["Link"],
                ],
                disableNativeSpellChecker: false
            });
    });
    $('#addGoalToLibraryModal').on('hidden.bs.modal', function (e) {
        $('#what').val('');
        $('#measure_of_success').val('');
        $("#goal_title").val('');
        $('input[name=goal_type_id]').val(1);
        
        modal_open = false;
    })
</script>

<script>

        $('body').popover({
            selector: '[data-toggle]',
            trigger: 'click',
        });
        
        $('.modal').popover({
            selector: '[data-toggle-select]',
            trigger: 'click',
        });

        // $('.tags').multiselect({
        //         	enableFiltering: true,
        //         	enableCaseInsensitiveFiltering: true,
		// 			// nonSelectedText: null,
        //     	});              
                
		$('body').on('click', function (e) {
            $('[data-toggle=popover]').each(function () {
            // hide any open popovers when the anywhere else in the body is clicked
               	if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    	}
             	});
		});
        
        $('body').on('click', function (e) {
            $('[data-toggle=dropdown]').each(function () {
            // hide any open popovers when the anywhere else in the body is clicked
               	if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    	}
             	});
		}); 

        $(document).on('show.bs.modal', '#addGoalToLibraryModal', function(e) {
            modal_open = true;
            $('.alert-danger').hide();
            $('.text-danger').html('');
            $('.form-control').removeClass('is-invalid');
            
            setTimeRoll();  
            
            $('#what').val('');
            $('#measure_of_success').val('');
            $("#goal_title").val('');
            $('input[name=goal_type_id]').val(1);
            //$('.tooltip-dropdown').find('.dropdown-item[data-value="1"]').click();
            $("input[name=start_date]").val('');
            $("input[name=target_date]").val('');
            for (var i in CKEDITOR.instances){
                CKEDITOR.instances[i].setData('');
            };

            CKEDITOR.instances['what'].setReadOnly(true);
            CKEDITOR.instances['measure_of_success'].setReadOnly(true);
            $('#addGoalToLibraryModal #start_date').prop("readonly",true);
            $('#addGoalToLibraryModal #target_date').prop("readonly",true);
                    
        });
        $(document).on('hide.bs.modal', '#addGoalToLibraryModal', function(e) {
            modal_open = false;
            const isContentModified = () => {
                if ($('#what').val() !== '' || $('#measure_of_success').val() !== ''
                    || $("#goal_title").val() !== '' || $('input[name=goal_type_id]').val() != 1 
                    || $("input[name=start_date]").val() !== '' || $("input[name=target_date]").val() != ''
                    ) {
                    return true;
                } 
                return false;
            };
            for (var i in CKEDITOR.instances){
                CKEDITOR.instances[i].updateElement();
            };
            if(no_warning == false) {
                if (isContentModified() && !confirm("If you continue you will lose any unsaved changes.")) {                
                    e.preventDefault();
                } else {
                    location.reload();
                } 
            } else {
                localStorage.setItem('savemsg', 'Your goal is saved');
                alert('Your goal is saved');
                location.reload();            
            }
        });
        
        $("#add-goal-to-library-form").on('submit', function (e) {
            $('#savebtn').prop('disabled', true);
            e.preventDefault();
            for (var i in CKEDITOR.instances){
                CKEDITOR.instances[i].updateElement();
            };
            const form = this;
            $.ajax({
                url: $(form).attr('action'),
                type : 'POST',
                data: $(form).serialize(),
                success: function (result) {
                    if(result.success){
                        $('.alert-danger').hide();
                        window.location.reload();
                    }
                },
                error: function (error){
                    $('#savebtn').prop('disabled', false);
                    var errors = error.responseJSON.errors;
                    $('.alert-danger').show();
                    $('.modal-body').animate({scrollTop: 0},100);
                    $('.text-danger').each(function(i, obj) {
                        $('.text-danger').text('');
                    });
                    Object.entries(errors).forEach(function callback(value, index) {
                         console.log(value[0]);
                        var className = '.error-' + value[0];
                        $('#addGoalToLibraryModal input[name='+value[0]+']').addClass('is-invalid');
                        $(className).text(value[1]);
                    });
                }
            });
        });
        
        
				$('body').on('click', function (e) {
                $('[data-toggle=popover]').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    	if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    	}
                	});
				});
                                
        
       var goal_bank_title = $('#goal_bank_title').val();
       var goal_bank_types = $('#goal_bank_types').val();
       var goal_bank_tags = $('#goal_bank_tags').val();
       var goal_bank_dateadd = $('#goal_bank_dateadd').val();
       var goal_bank_createdby = $('#goal_bank_createdby').val();
       var goal_bank_mandatory = $('#goal_bank_mandatory').val();
       if(goal_bank_title != '' || goal_bank_types != 0 || goal_bank_tags != 0 || goal_bank_dateadd != '' || goal_bank_createdby != 0  || goal_bank_mandatory != '' ){
           $('#collapse_0').collapse('show');
       } else {
           $('#collapse_0').collapse('hide');
       }
       
       var title = $('#title').val();
       var goal_type = $('#goal_type').val();
       var tag_id = $('#tag_id').val();
       var date_added = $('#date_added').val();
       var is_mandatory = $('#is_mandatory').val();
       
       if(title != '' || goal_type != 0 || tag_id != 0 || date_added != ''  || is_mandatory != '' ){
           $('#collapse_1').collapse('show');
       } else {
           $('#collapse_1').collapse('hide');
       }

       var goal_bank_title_hidden = $('#goal_bank_title_hidden').val();
       var goal_bank_types_hidden = $('#goal_bank_types_hidden').val();
       var goal_bank_tags_hidden = $('#goal_bank_tags_hidden').val();
       var goal_bank_dateadd_hidden = $('#goal_bank_dateadd_hidden').val();
       var goal_bank_createdby_hidden = $('#goal_bank_createdby_hidden').val();
       var goal_bank_mandatory_hidden = $('#goal_bank_mandatory_hidden').val();
       if(goal_bank_title_hidden != '' || goal_bank_types_hidden != 0 || goal_bank_tags_hidden != 0 || goal_bank_dateadd_hidden != '' || goal_bank_createdby_hidden != 0  || goal_bank_mandatory_hidden != '' ){
           $('#collapse_2').collapse('show');
       } else {
           $('#collapse_2').collapse('hide');
       }
       
       
       
       
      function confirmDelete(goalId) {
        if (confirm("Are you sure you want to permanently delete this goal?")) {
          deleteGoal(goalId);
        }
      }

      function deleteGoal(goalId) {
        $.ajax({
            url: '{{ route("goal.destroy", ":id") }}'.replace(':id', goalId),
            type: 'POST',
            data: {
              '_token': '{{ csrf_token() }}',
              '_method': 'DELETE'
            },
            success: function (response) {
              alert('Selected goal is deleted');
              var currentUrl = window.location.href;
              // Add the parameter 'open=1' to the URL
              var newUrl = currentUrl + (currentUrl.indexOf('?') === -1 ? '?' : '&') + 'goaldeleted=1';
              // Reload the page with the updated URL
              window.location.href = newUrl;
            },
            error: function (xhr, status, error) {
              // Handle error response
              console.error("Error deleting goal:", error);
              // Optionally, you can display an error message or perform additional actions here
            }
        });
      }

    </script>