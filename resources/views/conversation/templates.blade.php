<style>
    th{
        padding:20px;
    }
    td{
        padding:20px;
    }
</style>    

<x-side-layout>
    <h3> 
        @if ((session()->get('original-auth-id') == Auth::id() or session()->get('original-auth-id') == null ))
              My Conversations
          @else
              {{ $user->name }}'s Conversations
          @endif
    </h3>
    
    @if($viewType === 'conversations')
        @include('conversation.partials.compliance-message')
    @endif
    <div class="row">
        <div class="col-md-8"> @include('conversation.partials.tabs')</div>
    </div>
    <div class="mt-4">
        <div class="row">
            <div class="col">
                <p>
                    Review the information below to determine which template best suits your needs. Templates include suggestions for when to select a given conversation topic, questions to consider when having the conversation, and an attestation and sign-off area to formalize the results.
                </p>
                <p>
                    Once you've selected a template for use, select participants and hit "Use this template" to alert perticiapants you want to meet. Conversations will still need to be scheduled independently in your outlook calendar.
                </p>
            </div>
        </div>
        <div class="row d-none">
            <div class="col-8">
                <form action="" id="search-templates-form">
                    <div class="position-relative w-50">
                        <button class="btn btn-primary btn-sm position-absolute" style="right:0;margin:3px;height:2rem">Search</button>
                        <input class="form-control input clearfix float-left" id="search-input" name="search" value="{{$searchValue}}" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <div class="row">
            <div class="col-12">
                @foreach ($templates as $template)
                    @if(strtolower($template->name) === 'performance check-in')                        
                        <div class="card border ">
                            <div class="card-body p-2">
                                <div class="row">
                                    <div class="col-12">
                                        <table>
                                            <tr>
                                                <td colspan="3" style="padding-bottom: 0px;">
                                                    <p><strong>Performance Check-in Template</strong><p/>
                                                    <p>The Performance check-In template can be used in most situations. It includes options to capture progress against goals, celebrate successes, discuss ways to improve future performance outcomes, and record an overall performance evaluation.</p>
                                                </td>
                                            </tr>
                                            <tr style="border-bottom: solid #FCBA19">
                                                <th width="20%">Name</th><th width="60%">When to use</th><th width="20%">&nbsp;</th>
                                            </tr>
                                            <tbody style="border-collapse: collapse;">
                                            <tr style="background-color: #efefef">
                                                <td>{{$template->name}}</td>
                                                <td>{{$template->when_to_use}}</td>
                                                <td>
                                                    <button class="btn d-flex align-items-center w-100 template-btn" data-toggle="modal" data-target="#conversationTemplateDetail" data-id="{{$template->id}}">
                                                    <span class="btn btn-primary btn-sm">View</span>
                                                    </button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="col-12">
                <div class="card border ">
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-12">
                                <table>
                                    <tr>
                                        <td colspan="3" style="padding-bottom: 0px;">
                                            <p><strong>Other Templates</strong><p/>
                                            <p>These templates can be used as required to support conversations that require a more specific focus. Select a topic below to read more in the <em>When to use this template section</em>.</p>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: solid #FCBA19">
                                        <th width="20%">Name</th><th width="60%">When to use</th><th width="20%">&nbsp;</th>
                                    </tr>
                                    <tbody style="border-collapse: collapse;">
                                    <?php $i = 0; ?>    
                                    @foreach ($templates as $template)
                                        @if(strtolower($template->name) !== 'performance check-in')
                                        <tr <?php if($i % 2 != 0){ echo 'style="background-color: #efefef"';} ?>>
                                            <td>{{$template->name}}</td>
                                            <td>{{$template->when_to_use}}</td>
                                            <td>
                                                <button class="btn d-flex align-items-center w-100 template-btn" data-toggle="modal" data-target="#conversationTemplateDetail" data-id="{{$template->id}}">
                                                <span class="btn btn-primary btn-sm">View</span>
                                                </button>
                                            </td>
                                        </tr>
                                        @endif
                                        <?php $i++; ?>
                                    @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
                
                
                
            </div>
        </div>
    </div>
    @include('conversation.partials.template-detail-modal')
    <x-slot name="js">
        <script>
            function loadTemplate(id, cb) {
                $.ajax({
                    url: '/conversation/templates/' + id
                    , success: cb
                });
            }

            function setTemplateModalContent(content) {
                $("#conversationTemplateDetail").find('.modal-content').html(content);
                $("#participant_id").select2({
                    maximumSelectionLength: 1
                });

            }



            $(document).on('click', '.template-btn', function() {
                const id = $(this).data('id');
                loadTemplate(id, setTemplateModalContent);
            });

            $(document).on('change', '#template-select', function() {
                const id = $(this).val();
                loadTemplate(id, setTemplateModalContent);
            });

            $(document).on('click', '.btn-submit', function(e) {
                /*
                 if(!confirm('This will send a notification to all participants that you would like to schedule a conversation and will move this template to your Open Conversations tab. Would you like to continue?')){
                     return false;
                 }
                */

                e.preventDefault();
                $.ajax({
                    url: '/conversation'
                    , type: 'POST'
                    , data: $('#conversation_form').serialize()
                    , success: function(result) {
                        if (result.success) {
                            window.location.href = '/conversation/upcoming';
                        }
                    }
                    , error: function(error) {
                        var errors = error.responseJSON.errors;
                        $('.error-date-alert').hide();
                        $('.text-danger').each(function(i, obj) {
                            $('.text-danger').text('');
                        });
                        Object.entries(errors).forEach(function callback(value, index) {
                            var className = '.error-' + value[0];
                            $(className).text(value[1]);
                            if (value[0] === 'date') {
                                $('.error-date-alert').show();
                            }
                        });
                    }
                });
            });

        </script>
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
            
            
        </style>
    </x-slot>

</x-side-layout>
