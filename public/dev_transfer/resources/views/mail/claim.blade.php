以下の内容が{{ $data['mail_str'] }}になりました。
　　　
施設名：{{ $data['facility'] }}
@if (!empty($data['data_type_name']))
ファイル種別名：{{ $data['data_type_name'] }}
@endif

{!! $data['url'] !!}
