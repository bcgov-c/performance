@extends('resource.layout')
@section('tab-content')

<div><p>If you need assistance and can't find the answer in the resources section, please send an email to your organization's PDP contact listed below.</p></div>

<div id="accordion">
	@foreach($data as $index=> $question)
		<div class="card">
			<div class="card-header" id="heading_{{$index}}">		
			<h5 class="mb-0"data-toggle="collapse" data-target="#collapse_{{$index}}" aria-expanded="{{$index === 0 && ($t === '' || $t === 0) ? 'true' : 'false'}}" aria-controls="collapse_{{$index}}">
				<button class="btn btn-link">
				{{ $question['question'] }}
				</button>
			</h5>
			</div>
			
			<div id="collapse_{{$index}}" class="collapse" aria-labelledby="heading_{{$index}}" data-parent="#accordion">
			<div class="card-body">
				@if (array_key_exists('answer', $question))
					{!! $question['answer'] !!}
				@else
					@include('resource.partials.contact.'.$question['answer_file'])
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
