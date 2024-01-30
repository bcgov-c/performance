@extends('resource.layout')
@section('tab-content')


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

		<div id="collapse_{{$index}}" class="collapse {{$index == $t ? 'show' : ''}}" aria-labelledby="heading_{{$index}}" data-parent="#accordion">
		<div class="card-body">
			@if (array_key_exists('answer', $question))
				{!! $question['answer'] !!}
			@else
				@include('resource.partials.goal-settings.'.$question['answer_file'])
			@endif
		</div>
		</div>
	</div>
	@endforeach

	<div class="card">
		<div class="card-header" id="heading_{{$index+1}}">
		<h5 class="mb-0"data-toggle="collapse" data-target="#collapse_{{$index+1}}" aria-expanded="{{$index === 0 && ($t === '' || $t === 0) ? 'true' : 'false'}}" aria-controls="collapse_{{$index}}">
			<button class="btn btn-link">
			Goal Tags
			</button>
		</h5>
		</div>

		<div id="collapse_{{$index+1}}" class="collapse {{$index+1 == $t ? 'show' : ''}}" aria-labelledby="heading_{{$index+1}}" data-parent="#accordion">
		<div class="card-body">
		<table class="table table-condensed table-bordered table-striped">
		@foreach($tags as $tag)
		<tr>
			<th>{{ $tag["name"] }}</th>
			<td>{{ $tag["description"] }}</td>
		</tr>
		@endforeach
		</table>
		</div>
		</div>
	</div>

</div>
@push('css')
<style>
	.card-header{
		cursor: pointer;
	}
</style>
@endpush
@endsection
