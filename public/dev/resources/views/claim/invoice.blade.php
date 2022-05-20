<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>御請求書</title>
    <style>
      /* 印刷時の用紙設定 */
      @page {
        size: A4; /* 用紙サイズ */
        margin: 0; /* ヘッダー・フッダーを無効化 */
      }
      /* 要素の初期化 */
      * {
        /* マージン・パディングをリセットした方がデザインしやすい */
        margin: 0;
        padding: 0;
        /* デフォルトのフォント */
        color: #191970;
        font-family: ipag;
        font-size: 12px;
        font-weight: normal;
        src:url('{{ storage_path('fonts/ipag.ttf')}}');
        /* 背景色・背景画像を印刷する（Chromeのみで有効） */
        -webkit-print-color-adjust: exact;
      }
      /* リスト初期化 */
      ul {
        list-style: none;
        padding-left: 0;
      }
      /* プレビュー用のスタイル */
      @media screen {
        body {
          background: #ffffff;
          position: relative;

          /* 余白サイズ */
          padding-top: 8mm;
          padding-left: 8mm;
          padding-right: 8mm;
        }
      }
      /* 汎用クラス */
      .text-left {
        text-align: left;
      }
      .text-center {
        text-align: center;
      }
      .text-right {
        text-align: right;
      }
      .clear-element {
        clear: both;
      }
      /* 大枠の指定 */
      div.row1 {
        height: 14mm;
      }
      div.row2 {
        height: 12mm;
      }
      div.row3 {
        height: auto;
      }
      div.row3 div.col1 {
        width: 90mm;
        float: left;
      }
      div.row3 div.col2 {
        position: relative;
        z-index: 2;
        padding-top: 10mm;
        float: right;
      }
      div.row4 {
        height: 18mm;
      }
      div.row5 {
        height: 120mm;
      }
      div.row6 {
        position: absolute;
        bottom: 20px;
        height: 18mm;
      }
      /* タイトル */
      h1 {
        background: #3366cc;
        font-size: 30px;
        font-weight: normal;
        letter-spacing: 30px;
        color: #ffffff;
      }
      /* 顧客名・自社名 */
      h2 {
        font-size: 20px;
        font-weight: normal;
      }
      /* 顧客名 */
      h2.customer_name {
        text-decoration: underline;
      }
      img.stamp{
        position: absolute;
        z-index: 1;
        top: 10mm;
        left: 36mm;
        height: 17mm;
        width: 17mm;
      }
      /* テーブル共通 */
      table,
      th,
      td {
        border: 1px #264d99 solid;
        border-collapse: collapse;
        padding: 1px 5px;
      }
      table th {
        background: #3366cc;
        font-weight: normal;
        color: #ffffff;
      }
      table td {
        text-align: right;
      }
      /* テーブル 総額欄 */
      table.summary th {
        font-size: 14pt;
        width: 32mm;
      }
      table.summary td {
        font-size: 14pt;
        width: 40mm;
      }
      /* テーブル 明細欄 */
      table.detail {
        width: 100%;
        page-break-inside：avoid;
        page-break-after：always;
        page-break-before：always;
      }
      table.detail tr {
        height: 6mm;
      }
      table.detail th.hospital {
        width: 15%;
      }
      table.detail th.supply {
        width: 5%;
      }
      table.detail th.date {
        width: 5%;
      }
      table.detail th.purchase {
        width: 20%;
      }
      table.detail th.sales {
        width: 20%;
      }
      table.detail th.refund {
        width: 20%;
      }
      table.detail td.space {
        border-left-style: hidden;
        border-bottom-style: hidden;
      }
      table.detail tr.dataline:nth-child(odd) td {
        background-color: #ccddff;
      }
      table.detail tr.dataline:nth-child(even) td {
        background-color: #ffffff;
      }
    </style>
  </head>
  <body>
    <div class="row2">
        <ul class="text-right">
            <li>出力日時【{{ $output_datetime }}】</li>
        </ul>
    </div>
    <div class="row3">
        <ul class="text-left">
            <li>{{ $title }}</li>
        </ul>
    </div>
    <div class="row5">
        <table class="detail" border="1" style="border-collapse: collapse; border-color: #eeeeee">
            <thead>
                <tr>
                    <th class="hospital">施設</th>
                    <th class="supply">供給区分</th>
                    <th class="date">年月</th>
                    <th class="purchase">{{ $output_type_name }}</th>
                    <th class="sales">薬価額</th>
                    <th class="refund">納入額</th>
                </tr>
            </thead>
            <tbody>
@foreach($result as $invoice)
                <tr class="dataline">
                    <td class="text-left">{{$invoice['sale_name']}}</td>
                    <td align="right">{{$invoice['supply_division_name'] }}</td>
                    <td align="right">{{$invoice['claim_date']}}</td>
                    <td align="right">{{$invoice['comon_name']}}</td>
                    <td align="right">{{$invoice['refund_price']}}</td>
                    <td align="right">{{$invoice['sales_price']}}</td>
                </tr>
@endforeach
            </tbody>
        </table>
    </div>
  </body>
</html>