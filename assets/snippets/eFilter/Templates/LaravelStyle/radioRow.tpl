@B_CODE:
<label class="{{ $data['disabled'] }} {{ $data['label_selected'] }}">
    <input type="radio" name="f[{{ $data['tv_id'] }}][]" value="{{ $data['value'] }}" {{ $data['selected'] }} {{ $data['disabled'] }}> {!! $data['name'] !!} <span class="fltr_count">{{ $data['count'] }}</span>
</label>