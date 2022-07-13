@extends('resource.layout')
@section('tab-content')

<b>Welcome!</b> 
<br><br>
<p>The Performance Development Platform (PDP) is a new tool to support you to set effective goals and have meaningful performance conversations. This guide will outline some of the basic functions of the PDP and should help set you up for success.</p>
<p>In addition to the guide, there are specific resources for <a href='/resource/goal-setting' target=\'_blank\'>Goal Setting</a> and <a href='/resource/conversations' target=\'_blank\'>Performance Conversations</a> that go into greater detail about best practice in each area.</p>
<p>There are also helpful tips like this one
<i class="fa fa-info-circle" data-trigger="click" data-toggle="popover" data-placement="top" data-html="true" data-content="See the sections below for more info on the basic functions of the PDP."> </i>
throughout the app. Make sure to click on them if you are looking for more information.
<p>If you have technical trouble, please feel free to <a href='/resource/contact' target=\'_blank\'>Contact Us</a> and we will do our best to help.</p>	

<div id="accordion">
	@foreach($data as $index=> $question)

	<div class="card">
		<div class="card-header" id="heading_{{$index}}">
		<h5 class="mb-0"data-toggle="collapse" data-target="#collapse_{{$index}}" aria-expanded="{{$index === 0 ? 'true' : 'false'}}" aria-controls="collapse_{{$index}}">
			<button class="btn btn-link">
			{{ $question['question'] }}
			</button>
		</h5>
		</div>

		<div id="collapse_{{$index}}" class="collapse {{$index === 0 ? 'hide' : ''}}" aria-labelledby="heading_{{$index}}" data-parent="#accordion">
		<div class="card-body">
			@if (array_key_exists('answer', $question))
				{{ $question['answer']}}
			@else
				@include('resource.partials.user-guide.'.$question['answer_file'])
			@endif
		</div>
		</div>
	</div>
	@endforeach

</div>
@push('css')
<style>
	.card-header{
		cursor: pointer;
	}
</style>
@endpush
	@push('js')
    	<script>
            $(document).ready(function(){
                $('[data-toggle="popover"]').popover();
            });

            $('body').on('click', function (e) {
                $('[data-toggle=popover]').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    }
                });
            });
      	</script>
	@endpush
@endsection


