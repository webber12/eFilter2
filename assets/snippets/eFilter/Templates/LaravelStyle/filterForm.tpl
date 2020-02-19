@B_CODE:
<form id="eFiltr" class="eFiltr eFiltr_form" action="{{ $data['url'] }}" method="{{ $data['form_method'] }}">bzzzz
    <div id="eFiltr_info"><span id="eFiltr_info_cnt">{{ $data['eFilter_ids_cnt'] }}</span><span id="eFiltr_info_cnt_ending">{{ $data['eFilter_ids_cnt_ending'] }}</span></div>
    {!! $data['wrapper'] !!}
    <div class="eFiltr_form_result">{!! $data['form_result_cnt'] !!}</div>
    <div class="eFiltr_btn_wrapper"><input type="submit" class="eFiltr_btn" value="{{ $data['btn_text'] }}"></div>
</form>