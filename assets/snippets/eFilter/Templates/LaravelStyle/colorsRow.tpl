@B_CODE:
<label class="{{ $data['disabled'] }} {{ $data['label_selected'] }}" style="background:{{ $data['value'] }}" title="{{ $data['name'] }} ({{ $data['count'] }})">
    <input type="checkbox" name="f[{{ $data['tv_id'] }}][]" value="{{ $data['value'] }}" [+selected+] {{ $data['disabled'] }}> {{ $data['name'] }} <span class="fltr_count">{{ $data['count'] }}</span>
</label>