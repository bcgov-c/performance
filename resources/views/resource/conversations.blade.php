@extends('resource.layout')
@section('tab-content')
<div id="accordion">
	@foreach($data as $index=> $question)

	<div class="card">
		<div class="card-header" id="heading_{{$index}}">
		<!-- <h5 class="mb-0"data-toggle="collapse" data-target="#collapse_{{$index}}" aria-expanded="{{$index === 0 ? 'true' : 'false'}}" aria-controls="collapse_{{$index}}"> -->
		<h5 class="mb-0"data-toggle="collapse" data-target="#collapse_{{$index}}" aria-expanded="{{$index === 0 && ($t === '' || $t === 0) ? 'true' : 'false'}}" aria-controls="collapse_{{$index}}">			
			<button class="btn btn-link">
			{{ $question['question'] }}
			</button>
		</h5>
		</div>

		<!-- <div id="collapse_{{$index}}" class="collapse {{$index === 0 ? 'show' : ''}}" aria-labelledby="heading_{{$index}}" data-parent="#accordion"> -->
		<div id="collapse_{{$index}}" class="collapse {{$index == $t ? 'show' : ''}}" aria-labelledby="heading_{{$index}}" data-parent="#accordion">			
		<div class="card-body">
			@if (array_key_exists('answer', $question))
				{!! $question['answer'] !!}
			@else
				@include('resource.partials.conversations.'.$question['answer_file'])
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
@endsection
