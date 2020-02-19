@CODE:
<div class="fltr_block fltr_block_slider fltr_block[+tv_id+] [+active_block_class+]">
    <span class="fltr_name fltr_name_slider fltr_name[+tv_id+]">[+name+]</span>
    <div class="fltr_inner fltr_inner_slider fltr_inner[+tv_id+]">
        <div class="slider_text slider_text[+tv_id+]">от <span id="minCost[+tv_id+]"></span> до <span id="maxCost[+tv_id+]"></span></div>
        <div id="slider[+tv_id+]"></div>
        [+wrapper+]
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    var minCost[+tv_id+] = 0;
    var maxCost[+tv_id+] = 0;
    var minCostCurr[+tv_id+] = 0;
    var maxCostCurr[+tv_id+] = 0;
    if ($("#minCostInp[+tv_id+]").val() != "") {
        minCostCurr[+tv_id+] = $("#minCostInp[+tv_id+]").val();
    } else {
        minCostCurr[+tv_id+] = $("#minCostInp[+tv_id+]").data("minVal");
    }
    if ($("#maxCostInp[+tv_id+]").val() != "") {
        maxCostCurr[+tv_id+] = $("#maxCostInp[+tv_id+]").val();
    } else {
        maxCostCurr[+tv_id+] = $("#maxCostInp[+tv_id+]").data("maxVal");
    }
    minCost[+tv_id+] = $("#minCostInp[+tv_id+]").data("minVal");
    maxCost[+tv_id+] = $("#maxCostInp[+tv_id+]").data("maxVal");
    $("#minCost[+tv_id+]").html(minCostCurr[+tv_id+]);
    $("#maxCost[+tv_id+]").html(maxCostCurr[+tv_id+]);
    $("#slider[+tv_id+]").slider({
        min: minCost[+tv_id+],
        max: maxCost[+tv_id+],
        values: [ minCostCurr[+tv_id+],maxCostCurr[+tv_id+] ],
        range: true,
        stop: function(event, ui) {
            $("input#minCostInp[+tv_id+]").val($("#slider[+tv_id+]").slider("values",0));
            $("input#maxCostInp[+tv_id+]").val($("#slider[+tv_id+]").slider("values",1));
            $("#minCost[+tv_id+]").text($("#slider[+tv_id+]").slider("values",0));
            $("#maxCost[+tv_id+]").text($("#slider[+tv_id+]").slider("values",1));
            $("input#minCostInp[+tv_id+]").change();
        },
        slide: function(event, ui){
            $("input#minCostInp[+tv_id+]").val($("#slider[+tv_id+]").slider("values",0));
            $("input#maxCostInp[+tv_id+]").val($("#slider[+tv_id+]").slider("values",1));
            $("#minCost[+tv_id+]").text(jQuery("#slider[+tv_id+]").slider("values",0));
            $("#maxCost[+tv_id+]").text(jQuery("#slider[+tv_id+]").slider("values",1));
        }
    });
});
</script>