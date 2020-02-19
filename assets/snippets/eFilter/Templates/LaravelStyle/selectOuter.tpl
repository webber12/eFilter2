@B_CODE:
<div class="fltr_block fltr_block_select fltr_block{{ $data['tv_id'] }} {{ $data['active_block_class'] }}">
    <span class="fltr_name fltr_name_select fltr_name{{ $data['tv_id'] }}">{{ $data['name'] }}</span>
    <select name="f[{{ $data['tv_id'] }}][]">
        <option value="0"> - {{ $data['name'] }} - </option>
        {!! $data['wrapper'] !!}
    </select>
</div>