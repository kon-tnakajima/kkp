$(function ($) {
    $('#output_type').on("input", function () {
        var type = $('#output_type').val();
        if (type == 1) {
            $(".maker_box").hide();
            $(".trader_box").show();
        } else if (type == 2) {
            $(".trader_box").hide();
            $(".maker_box").show();
        } else {
            $(".trader_box").show();
            $(".maker_box").show();
        }
    });
    $(".anchor-clear-search").click(function () {
        $(".maker_box").show();
        $(".trader_box").show();
    });
});
