<?php
/**
 * include参照で利用されるコンポーネント
 */
?>
<script type="text/javascript">
    /**
     * 施設のチェック状態から選択済み情報や施設グループのラベルのチェックマークを変更する
     */
    function resetHospitalGroupCheck() {
        $(".hospital-group").each(function () {
            const $hospitalGroupElm = $(this);
            const $hospitalGroupLabelElm = $hospitalGroupElm.find(".hospital-group-label");
            $hospitalGroupLabelElm.removeClass("all").removeClass("partially").removeClass("zero");
            const hospitalGroupName = $hospitalGroupLabelElm.text();
            const allSelectedLabelStr = hospitalGroupName + "のすべて";
            const $hospitalElms = $hospitalGroupElm.find("label");
            const $selectedHospitalContainerElm = $("#selected-hospital-container");

            const checkedHospitalNameArr = [];
            $hospitalElms.each(function () {
                const $hospitalElm = $(this);
                const isChecked = $hospitalElm.find("input").prop("checked");
                const hospitalName = $hospitalElm.find(".text").text();
                if (isChecked) {
                    checkedHospitalNameArr.push(hospitalName);
                }
            });

            $selectedHospitalContainerElm.find(":contains('" + allSelectedLabelStr + "')").each(function () {
                // テキスト完全一致チェック
                if ($(this).text() !== allSelectedLabelStr) return;
                $(this).remove();
            });

            checkedHospitalNameArr.forEach(checkedHospitalName => {
                $selectedHospitalContainerElm.find(":contains('" + checkedHospitalName + "')").each(function () {
                    // テキスト完全一致チェック
                    if ($(this).text() !== checkedHospitalName) return;
                    $(this).remove();
                });
                $("<div>")
                    .text(checkedHospitalName)
                    .addClass("flex-row-left-center")
                    .on("click", function() { onClickSelectedHospital(this); })
                    .appendTo($selectedHospitalContainerElm)
            });
            if (checkedHospitalNameArr.length === $hospitalElms.length) {
                $hospitalGroupLabelElm.addClass("all");
                const matchedHospitalNameArr = [];
                const $selectedHospitalElms = $selectedHospitalContainerElm.find("div");
                $selectedHospitalElms.each(function () {
                    const selectedHospitalName = $(this).text();
                    if (checkedHospitalNameArr.indexOf(selectedHospitalName) >= 0) {
                        matchedHospitalNameArr.push(selectedHospitalName);
                    }
                });
                if (matchedHospitalNameArr.length === checkedHospitalNameArr.length) {
                    $selectedHospitalElms.each(function () {
                        const $selectedHospitalElm = $(this);
                        if (matchedHospitalNameArr.indexOf($selectedHospitalElm.text()) >= 0) {
                            $selectedHospitalElm.remove();
                        }
                    })
                    $("<div>")
                        .text(allSelectedLabelStr)
                        .addClass("flex-row-left-center")
                        .on("click", function() {
                            $(".hospital-group-label:contains('" + hospitalGroupName + "')").each(function () {
                                // テキスト完全一致チェック
                                if ($(this).text() !== hospitalGroupName) return;
                                $(this).find("+ .contents input").prop("checked", false);
                                resetHospitalGroupCheck();
                            });
                            $(this).remove();
                        })
                        .appendTo($selectedHospitalContainerElm)
                }
            } else {
                $hospitalGroupLabelElm.addClass(checkedHospitalNameArr.length === 0 ? "zero" : "partially");
            }
        });
        $(".hospital-group .contents label")
    }
    /**
     * 選択済み病院要素をクリックされたときの挙動。
     * 対応するチェックボックスのチェックを外し、クリックされた要素を削除する。
     */
    function onClickSelectedHospital(thisElm) {
        const $thisElm = $(thisElm);
        const hospitalName = $thisElm.text();
        $(".hospital-group .text:contains('" + hospitalName + "')").each(function () {
            // テキスト完全一致チェック
            if ($(this).text() !== hospitalName) return;
            $(this).parent().find("input").prop("checked", false);
        });
        $thisElm.remove();
        resetHospitalGroupCheck();
    }
    /**
     * 病院のグループ名をクリックされたときの挙動。
     * グループに含まれる病院のチェックをまとめて操作する。
     * １つでも未チェック状態のチェックボックスがあれば、全部チェック状態にする。
     * すべてのチェックボックスがチェックされていたら、全部未チェック状態にする。
     */
    function onClickHospitalGroupLabel(thisElm) {
        const $inputElms = $(thisElm).find("+ .contents input");
        const $unCheckedElms = $inputElms.filter(":not(:checked)");
        const $checkedElms = $inputElms.filter(":checked");
        ($unCheckedElms.length > 0 ? $unCheckedElms : $checkedElms).click();
    }
    $(() => {
        // 病院のチェック状態が変化したときの挙動
        $("input[name='user_group[]']").on("change", function () {
            const $checkElm = $(this);
            const hospitalName = $checkElm.parent().find(".text").text();
            if (!$checkElm.prop("checked")) {
                // 選択済みのチェックに対応する要素を削除する
                $("#selected-hospital-container")
                    .find(":contains('" + hospitalName + "')")
                    .each(function () {
                        // テキスト完全一致チェック
                        if ($(this).text() !== hospitalName) return;
                        $(this).remove();
                    });
            }
            resetHospitalGroupCheck();
        });
        resetHospitalGroupCheck();
    });
</script>