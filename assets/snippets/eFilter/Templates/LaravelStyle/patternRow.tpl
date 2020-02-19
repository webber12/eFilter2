@B_CODE:
<label class="{{ $data['disabled'] }} {{ $data['label_selected'] }}" title="{{ $data['name'] }} ({{ $data['count'] }})">
    <input type="checkbox" name="f[{{ $data['tv_id'] }}][]" value="{{ $data['value'] }}" {{ $data['selected'] }} {{ $data['disabled'] }}> 
    <img src="{{ $data['pattern_folder'] }}{{ $data['value'] }}" alt="{{ $data['name'] }}"> {{ $data['name'] }} 
    <span class="fltr_count">{{ $data['count'] }}</span>
</label>