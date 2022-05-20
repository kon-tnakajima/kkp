@extends('layouts.app')

@php($title = 'トークン管理画面')

@php($category = 'system')

@php($breadcrumbs = ['HOME'=>'/', 'トークン管理画面'=>'javascript:void(0);'])

@section('content')

<style>
.app-contents {
    margin-top: 1em;
    background-color: white;
    padding: 0.5em;
}
label {
    margin-bottom: 0;
}
#ajax-error-message {
    color: red;
    display: inline-block;
}
.token {
    width: 30em;
}
#token-value {
    border: none;
    outline: none;
}
#remake-token-btn-container {
    height: 2em;
}
#copy-clipbord-btn {
    visibility: hidden;
}
#copy-token-dialog {
    position: fixed;
    border: 1px solid gray;
    top: 50%;
    left: 50%;
    background-color: white;
    transform: translate(-50%, -50%);
    padding: 1em;
}
#copy-token-dialog.hide {
    display: none !important;
}
#copy-token-dialog .title {
    font-size: 120%;
}

#remake-confirm-block {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(100, 100, 100, 0.4);
    z-index: 2147483647;
}
#remake-confirm-block.hide {
    display: none;
}
#remake-confirm-block .modal-area {
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 0 30px -4px #777;
    padding: 3em;
    gap: 1em;
}
#remake-confirm-block .modal-area .btn-container {
    gap: 1em;
}

.fadeout {
  animation: fadeout-keyframes 3s ease 0s 1 forwards;
}

@keyframes fadeout-keyframes {
  0% {
    opacity: 1;
  }
  80% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}
</style>

<div class="app-contents flex-column-left-top">
    <div>あなたのトークン</div>
    <div class="flex-row-left-center" id="remake-token-btn-container">
        <button onclick="remakeTokenConfirm()">再発行</button>
    </div>
    <div id="remake-confirm-block" class="flex-row-center-center hide" onclick="remakeToken(false)">
        <div class="modal-area flex-column-stretch-top">
            <h2>トークンを再発行しますか？</h2>
            <p>既存のトークンがすぐに無効化され、新しいトークンがすぐに利用可能になります。<br>この操作は元に戻せません。</p>
            <div class="btn-container flex-row-right-center">
                <button onclick="remakeToken(true)" id="remake-token-btn">再発行を行う</button>
                <span id="remake-token-spinner" class="fa fa-spinner fa-pulse flex-center-self" style="display: none;"></span>
                <button onclick="remakeToken(false)">キャンセル</button>
            </div>
        </div>
    </div>
    <div class="flex-row-left-center">
        <input id="token-value" class="token monospaced" type="text" value="" readonly>
        <button id="copy-clipbord-btn" onclick="copyToClipbord()" disabled>Copy</button>
    </div>
    <div id="ajax-error-message"></div>
    <div id="copy-token-dialog" class="flex-column-center-top hide">
        <span class="title">トークンをコピーしました。</span>
        <span class="message"></span>
    </div>
</div>

<script type="text/javascript">
$(() => {
    $("#remake-confirm-block *").click(e => {
        e.stopPropagation();
    });
});
var timeoutId = null;
function copyToClipbord() {
    const $tokenValueElm = $("#token-value");
    $tokenValueElm.select();
    document.execCommand('copy');
    $tokenValueElm.get(0).selectionStart = 0;
    $tokenValueElm.get(0).selectionEnd = 0;
    $("#copy-clipbord-btn").focus();
    const $dialog = $("#copy-token-dialog");
    $dialog.removeClass("hide").removeClass("fadeout").addClass("fadeout").find(".message").text($tokenValueElm.val());
    timeoutId = window.setTimeout(() => {
        $dialog.addClass("hide");
        timeoutId = null;
    }, 3000);
}
function remakeTokenConfirm() {
    $("#remake-confirm-block").removeClass("hide");
}
async function remakeToken(executeFlg) {
    if (!executeFlg) {
        $("#remake-confirm-block").addClass("hide");
        return false;
    }

    const $btnElm = $("#remake-token-btn").parent().find("button");
    const $spinnerElm = $("#remake-token-spinner");
    const $messageElm = $("#ajax-error-message");

    $btnElm.hide();
    $spinnerElm.show();
    $messageElm.text("");

    // クリップボードダイアログが表示されていたら消す
    if (timeoutId !== null) {
        window.clearTimeout(timeoutId);
        $("#copy-token-dialog").addClass("hide").removeClass("fadeout");
        timeoutId = null;
    }

    $("#token-value").val("");
    $("#copy-clipbord-btn").prop("disabled", true).css("visibility", "hidden");

    const url = "{{ route('account.token.remakeToken') }}";
    let token = "";
    let errorMessage = "";
    try {
        ({ token } = await $.ajax({ url }));
    } catch (error) {
        errorMessage = error.responseJSON.errorMessage;
        throw error;
    } finally {
        $btnElm.show();
        $spinnerElm.hide();
        $messageElm.text(errorMessage);
        $("#remake-confirm-block").addClass("hide");
        $("#token-value").val(token);
        $("#copy-clipbord-btn").prop("disabled", !!errorMessage).css("visibility", !!errorMessage ? "hidden" : "visible").focus();
    }
    return false;
}
</script>
@endsection
