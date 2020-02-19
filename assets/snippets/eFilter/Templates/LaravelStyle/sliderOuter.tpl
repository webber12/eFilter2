@B_CODE:
<div class="fltr_block fltr_block_slider fltr_block{{ $data['tv_id'] }} {{ $data['active_block_class'] }}">
    <span class="fltr_name fltr_name_slider fltr_name{{ $data['tv_id'] }}">{{ $data['name'] }}</span>
    <div class="fltr_inner fltr_inner_slider fltr_inner{{ $data['tv_id'] }}">
        <div class="slider_text slider_text{{ $data['tv_id'] }}">от <span id="minCost{{ $data['tv_id'] }}"></span> до <span id="maxCost{{ $data['tv_id'] }}"></span></div>
        <div id="slider{{ $data['tv_id'] }}"></div>
        {!! $data['wrapper'] !!}
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    var minCost{{ $data['tv_id'] }} = 0;
    var maxCost{{ $data['tv_id'] }} = 0;
    var minCostCurr{{ $data['tv_id'] }} = 0;
    var maxCostCurr{{ $data['tv_id'] }} = 0;
    if ($("#minCostInp{{ $data['tv_id'] }}").val() != "") {
        minCostCurr{{ $data['tv_id'] }} = $("#minCostInp{{ $data['tv_id'] }}").val();
    } else {
        minCostCurr{{ $data['tv_id'] }} = $("#minCostInp{{ $data['tv_id'] }}").data("minVal");
    }
    if ($("#maxCostInp{{ $data['tv_id'] }}").val() != "") {
        maxCostCurr{{ $data['tv_id'] }} = $("#maxCostInp{{ $data['tv_id'] }}").val();
    } else {
        maxCostCurr{{ $data['tv_id'] }} = $("#maxCostInp{{ $data['tv_id'] }}").data("maxVal");
    }
    minCost{{ $data['tv_id'] }} = $("#minCostInp{{ $data['tv_id'] }}").data("minVal");
    maxCost{{ $data['tv_id'] }} = $("#maxCostInp{{ $data['tv_id'] }}").data("maxVal");
    $("#minCost{{ $data['tv_id'] }}").html(minCostCurr{{ $data['tv_id'] }});
    $("#maxCost{{ $data['tv_id'] }}").html(maxCostCurr{{ $data['tv_id'] }});
    $("#slider{{ $data['tv_id'] }}").slider({
        min: minCost{{ $data['tv_id'] }},
        max: maxCost{{ $data['tv_id'] }},
        values: [ minCostCurr{{ $data['tv_id'] }},maxCostCurr{{ $data['tv_id'] }} ],
        range: true,
        stop: function(event, ui) {
            $("input#minCostInp{{ $data['tv_id'] }}").val($("#slider{{ $data['tv_id'] }}").slider("values",0));
            $("input#maxCostInp{{ $data['tv_id'] }}").val($("#slider{{ $data['tv_id'] }}").slider("values",1));
            $("#minCost{{ $data['tv_id'] }}").text($("#slider{{ $data['tv_id'] }}").slider("values",0));
            $("#maxCost{{ $data['tv_id'] }}").text($("#slider{{ $data['tv_id'] }}").slider("values",1));
            $("input#minCostInp{{ $data['tv_id'] }}").change();
        },
        slide: function(event, ui){
            $("input#minCostInp{{ $data['tv_id'] }}").val($("#slider{{ $data['tv_id'] }}").slider("values",0));
            $("input#maxCostInp{{ $data['tv_id'] }}").val($("#slider{{ $data['tv_id'] }}").slider("values",1));
            $("#minCost{{ $data['tv_id'] }}").text(jQuery("#slider{{ $data['tv_id'] }}").slider("values",0));
            $("#maxCost{{ $data['tv_id'] }}").text(jQuery("#slider{{ $data['tv_id'] }}").slider("values",1));
        }
    });
});
</script>