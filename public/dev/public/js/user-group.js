$(function($) {

    $('#right_trader').click(function() {
        move('#add_traders', '#traders');
    });
    
    $('#left_trader').click(function() {
        move('#traders', '#add_traders');
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
        if($("#add_traders").length){
            var opts = $('#add_traders option');
            for( var loop = 0; loop < opts.length; loop++ ) {
                opts[ loop ].selected = true;
            }
        }
    });

    /**
     * 業者情報検索
     */
    $(document).ajaxSend(function() {
        $("#overlay").fadeIn(300);　
    });

     $('#search_trader').click(function() {
        // 検索する名称を取得
        var str = $('#trader_name').val()
        
        // APIを呼ぶ
        $.ajax({
            type: 'GET',
            url: '/usergroup/trader/search',
            data: { name: str },
            dataType:"json",
        }).done(function(data,textStatus,jqXHR) {
            $('#traders option').remove();
            $('#traders').append( $('<option disabled>業者名　　　　　　　　　住所　　　　　　　　　　　　　　　</option>') );
            $.each(data, function(loop){
                var str = $('#add_traders option[value="' + data[loop].id + '"]').val();
                if (!str) {
                    $('#traders').append( $('<option value="'+ data[loop].id +'">' + data[loop].name + '</option>') );
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

    /**
     * 使用データ区分処理
     */
    // 使用データ区分のセレクトボックス内クリック
    $('#select-types').change(function() {
        var str = $.trim($('#select-types option:selected').text());
        //選択した値をテキストボックスに反映
        $('#type-input').val(str);
    });
    // キー動作
    $('#select-types').keydown(function(e){
        var selected = $("#select-types > option:selected");
        // キーコード46は削除キー
        if (e.keyCode == 46) {
            //選択されたものを取り除くとき
            if (selected.val()) {
                selected.remove();
            }
        }
        if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
            return false;
        } else {
            return true;
        }
    });
    // 使用データ区分テキストボックスのエントリーキーは誤動作する為除外する
    $("#type-input"). keydown(function(e) {
        if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
            return false;
        } else {
            return true;
        }
    });
    // 使用データ区分テキストボックス内の文字列をセレクトボックスに新規追加
    $('#type-add').click(function() {
        var str = $('#type-input').val();
        var found_str = '';
        if (str == '') {
            return false;
        }
        // 同じものが存在する場合は処理は行わない                
        $('#select-types option').each(function() {
            if ($(this).val() == str) {
                found_str = $(this).val();
            }
        });
        // 一致したら終了
        if (found_str == str) {
            return false;
        }
        $('#select-types').append($('<option>').html(str).val(str));
        $('#type-input').val("");
        return false;
    });
    // 使用データ区分テキストボックス内の文字列をセレクトボックスの選択しているオプションに更新
    $('#type-fix').click(function() {
        var str = $('#type-input').val();
        //選択されたものを取り除くとき
        var selected = $("#select-types > option:selected").val();
        if (selected) {
            $('#select-types > option:selected').remove();
            $('#select-types').append($('<option>').html(str).val(str));
        }
        return false;
    });
    // 使用データ区分セレクトボックスの選択しているオプション削除
    $('#type-delete').click(function() {
        //選択されたものを取り除くとき
        var selected = $("#select-types > option:selected").val();
        if (selected) {
            $('#select-types > option:selected').remove();
            $('#type-input').val("");
        }
        return false;
    });

    /**
     * 使用供給区分処理
     */
    // 使用供給区分のセレクトボックス内クリック
    $('#select-supplies').change(function() {
        var str = $.trim($('#select-supplies option:selected').text());
        //選択した値をテキストボックスに反映
        $('#supply-input').val(str);
    });
    // キー動作
    $('#select-supplies').keydown(function(e){
        var selected = $("#select-suppies > option:selected");
        // キーコード46は削除キー
        if (e.keyCode == 46) {
            //選択されたものを取り除くとき
            if (selected.val()) {
                selected.remove();
            }
        }
        if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
            return false;
        } else {
            return true;
        }
    });
    // 使用供給区分テキストボックスのエントリーキーは誤動作する為除外する
    $("#supply-input"). keydown(function(e) {
        if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
            return false;
        } else {
            return true;
        }
    });
    // 使用供給区分テキストボックス内の文字列をセレクトボックスに新規追加
    $('#supply-add').click(function() {
        var str = $('#supply-input').val();
        var found_str = '';
        if (str == '') {
            return false;
        }
        // 同じものが存在する場合は処理は行わない                
        $('#select-supplies option').each(function() {
            if ($(this).val() == str) {
                found_str = $(this).val();
            }
        });
        // 一致したら終了
        if (found_str == str) {
            return false;
        }
        $('#supply-input').val("");
        $('#select-supplies').append($('<option>').html(str).val(str));
        return false;
    });
    // 使用供給区分テキストボックス内の文字列をセレクトボックスの選択しているオプションに更新
    $('#supply-fix').click(function() {
        var str = $('#supply-input').val();
        //選択されたものを取り除くとき
        var selected = $("#select-supplies > option:selected").val();
        if (selected) {
            $('#select-supplies > option:selected').remove();
            $('#select-supplies').append($('<option>').html(str).val(str));
        }
        return false;
    });
    // 使用供給区分セレクトボックスの選択しているオプション削除
    $('#supply-delete').click(function() {
        //選択されたものを取り除くとき
        var selected = $("#select-supplies > option:selected").val();
        if (selected) {
            $('#select-supplies > option:selected').remove();
            $('#supply-input').val("");
        }
        return false;
    });
    // 更新実行した場合の後処理
    $('#modal-action').click(function() {
        // 使用データ区分のセレクトボックス情報を全て選択状態にする
        if($("#select-types").length){
            var opts = $('#select-types option');
            for( var loop = 0; loop < opts.length; loop++ ) {
                opts[ loop ].selected = true;
            }
        }
        // 使用供給区分のセレクトボックス情報を全て選択状態にする
        if($("#select-supplies").length){
            var opts = $('#select-supplies option');
            for( var loop = 0; loop < opts.length; loop++ ) {
                opts[ loop ].selected = true;
            }
        }
    });

    $('#transition_button').click(function() {
        $('#transition_state').val(1);
    });
});
