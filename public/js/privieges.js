$(function($) {

    $('#right').click(function() {
        move('#add_privieges', '#privieges');
    });
    
    $('#left').click(function() {
        move('#privieges', '#add_privieges');
    });
    var move = function(_this, target) {
        $(_this + ' option:selected').each(function() {
            $(target).append($(this).clone());
            $(this).remove();
        });
    };

    // 更新実行した場合の後処理
    $('#modal-action').click(function() {
        // 権限セレクトボックス情報を全て選択状態にする
        if($("#add_privieges").length){
            var opts = $('#add_privieges option');
            for( var loop = 0; loop < opts.length; loop++ ) {
                opts[ loop ].selected = true;
            }
        }
    });

    /**
     * 権限情報検索
     */
    $(document).ajaxSend(function() {
        $("#overlay").fadeIn(300);　
    });

     $('#search_priviege').click(function() {
        // 検索する名称を取得
        var str = $('#priviege_name').val()
        
        // APIを呼ぶ
        $.ajax({
            type: 'GET',
            url: '/role/search',
            data: { name: str },
            dataType:"json",
        }).done(function(data,textStatus,jqXHR) {
            $('#privieges option').remove();
            $.each(data, function(loop){
                var str = $('#add_privieges option[value="' + data[loop].key_code + '"]').val();
                if (!str) {
                    $('#privieges').append( $('<option value="'+ data[loop].key_code +'">' + data[loop].key_code + '</option>') );
                }
            });
            setTimeout(function(){
                $("#overlay").fadeOut(300);
            },500);
        }).fail(function(jqXHR, textStatus, errorThrown){
            alert("status["+jqXHR.status + "] [" + textStatus + "]");
        }).always(function(){
        });
        return false;
    });
});