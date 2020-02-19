@B_CODE:
<div class="fltr_block fltr_block_multy fltr_block{{ $data['tv_id'] }} {{ $data['active_block_class'] }}">
    <span class="fltr_name fltr_name_multy fltr_name{{ $data['tv_id'] }}">{{ $data['name'] }}</span>
    <select name="f[{{ $data['tv_id'] }}][]" multiple size="5">
        <option value="0"> - {{ $data['name'] }} - </option>
        {!! $data['wrapper'] !!}
    </select>
</div>