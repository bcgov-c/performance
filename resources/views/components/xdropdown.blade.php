@props(['list' => [], 'label', 'showError' => true, 'selected' => null, 'blankOptionText' => null])
<label>
    {{ __($label ?? '') }}
    <!-- {!! $label ?? '' ? ' <i id="tag-info" class="fas fa-info-circle" data-trigger=" click " data-toggle-select="popover" data-placement="right" data-html="true" data-content="Tags help to more accurately identity, sort, and report on your goals. You can add more than one tag to a goal. The list of tags will change and grow over time. <br/><br/><a href=\'/resource/goal-setting?t=5\' target=\'_blank\'><u>View full list of tag descriptions.</u></a>" data-original-title=""></i><br>': ''!!} -->
    {!! $label ?? '' ? ' <i id="tag-info" class="fas fa-info-circle" data-trigger=" focus " data-toggle-select="popover" data-placement="right" data-html="true" data-content="Tags help to more accurately identity, sort, and report on your goals. You can add more than one tag to a goal. The list of tags will change and grow over time. <br/><br/><a href=\'/resources/goal-setting?t=8\' target=\'_blank\'><u>View full list of tag descriptions.</u></a>" data-original-title=""></i><br>': ''!!}
    <span id="tagLabel" class="sr-only">goal tags field</span>
    <select {!! $attributes->merge(['class' => 'form-control']) !!}>
        @if($blankOptionText != null)
            <option value="" >{{$blankOptionText}}</option>
        @endif
        @foreach ($list as $item)
            <option value="{{ $item['id'] }}" 
                data-toggle="tooltip"  title="{{ $item['description'] }}"
                {{ ($item['selected'] ?? '') || $selected != null && ($item['id'] == $selected || (is_array($selected) && (in_array($item['id'], $selected)))) ? 'selected': '' }} >
                {{ $item['name'] }}
            </option>
        @endforeach
    </select>
</label>