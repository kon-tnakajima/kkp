$(document).ready(function(){
    $('#belong_user_group').click(function(){
        move('belong_user_group', 'not_belong_user_group');
    })
    $('#not_belong_user_group').click(function(){
        move('not_belong_user_group', 'belong_user_group');
    })
    $('#belong_role').click(function(){
        move('belong_role', 'role');
    })
    $('#role').click(function(){
        move('role', 'belong_role');
    })
    var move = function(_this, target) {
        $('select[id=' + _this + '] option:selected').each(function() {
            $('select[id=' + target + ']').append($(this).clone());
            $(this).remove();
        });
    };
    $('#modal-action').click(function() {
        // 所属ユーザグループ情報
        var opts = $('#belong_user_group option');
        for( var i = 0; i < opts.length; i++ )
        {
            opts[ i ].selected = true;
        }
        // 所属ロール情報
        var opts = $('#belong_role option');
        for( var i = 0; i < opts.length; i++ )
        {
            opts[ i ].selected = true;
        }
    });
});
