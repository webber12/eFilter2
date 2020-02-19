@B_CODE:
<div class="fltr_block fltr_block_radio fltr_block{{ $data['tv_id'] }} {{ $data['active_block_class'] }}">
    <span class="fltr_name fltr_name_radio fltr_name{{ $data['tv_id'] }}">{{ $data['name'] }}</span>
    <input type="radio" name="f[{{ $data['tv_id'] }}][]" value="0"> Все
    {!! $data['wrapper'] !!}
</div>