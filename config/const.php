<?php

return [
    // 権限区分一覧(privileges.privilege_type)
    'privilege_type' => [
        ['id' => 1, 'name' => '採用申請'],
        ['id' => 2, 'name' => '請求'],
        ['id' => 3, 'name' => 'システム管理'],
        ['id' => 4, 'name' => 'グループ'],
        ['id' => 5, 'name' => 'アカウント'],
        ['id' => 6, 'name' => 'メニュー'],
    ],
    // ユーザ一覧表示権限の条件(ユーザ一覧権限無し,文化連管理者)
    'disp_user_attr' => ['NO_AUTHORITY' => -1, 'ALL_AUTHORIZED' => -2],
    // 0='', ALL_AUTHORIZED='文化連管理者', GROUP_AUTHORIZED='グループ管理者'
    'user_attribute' => [
        'ALL_AUTHORIZED'          => '文化連管理者',
        'GROUP_AUTHORIZED'        => 'グループ管理者',
        'HEADGUARTERS_AUTHORIZED' => '本部管理者',
        'NO_AUTHORITY'            => '権限なし',
    ],
    // 規約未来期限日
    'agreement_to_date' => '2038-01-01',
    'bunkaren_name'     => '文化連',
    'headquarters_name' => '本部',
    'hospital_name'     => '病院',
    'trader_name'       => '業者',
    'kojin_name'        => '個人',
    // アカウント申請状態
    'account_status' => [
        'application' => 1, // アカウント申請
        'permission'  => 2, // 許可
        'start'       => 3, // 開始
        'rejection'   => 4, // 却下
    ],
    // 新規利用申請方法【アカウント申請】
    'create_application_mode' => 9,
    // 過去3年分の年月(3*12=36月数)
    'past_year_limit' => 36,
    // 個人のグループキー
    'kojin_group_key' => 'Independent',
];
