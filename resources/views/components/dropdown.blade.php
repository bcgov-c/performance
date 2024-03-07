@props(['list' => [], 'label', 'showError' => true, 'selected' => null, 'blankOptionText' => null])
<label>
    {{ __($label ?? '') }}
    {!! $label ?? '' ? '<br>': ''!!}
    <select {!! $attributes->merge(['class' => 'form-control']) !!}  aria-haspopup="true" aria-expanded="false">>
        @if($blankOptionText != null)
            <option value="" aria-labelledby="{{$blankOptionText}}">{{$blankOptionText}}</option>
        @endif
        @foreach ($list as $item)
            <option aria-labelledby="{{ $item['name'] }}" value="{{ $item['id'] }}" {{ ($item['selected'] ?? '') || $selected != null && ($item['id'] == $selected || (is_array($selected) && (in_array($item['id'], $selected)))) ? 'selected': '' }}>
                {{ $item['name'] }}
            </option>
        @endforeach
    </select>
    <small class="text-danger error-{{$attributes['name'] ?? '' ? preg_replace('/\[.*?\]/', '', $attributes['name']) : ''}}">
        @if ($showError !== 'false') 
            {{ $errors->first($attributes['name']) }}
        @endif
    </small>
</label>