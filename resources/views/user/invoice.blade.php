<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>御請求書</title>
    <style>
        /* 顧客名・自社名 */
        h2 {
          font-family: ipag;
          font-size: 20px;
          font-weight: normal;
          src:url('{{ storage_path('fonts/ipag.ttf')}}');
        }
        /* 顧客名 */
        h2.customer_name {
          text-decoration: underline;
          font-family: ipag;
          font-size: 20px;
          font-weight: normal;
          src:url('{{ storage_path('fonts/ipag.ttf')}}');
        }
        /* 請求書トップのタイトル名表示部分 */
        div.row1 {
          height: 14mm;
          text-align: center;
          background: #3366cc;
          font-size: 30px;
          letter-spacing: 30px;
          color: #ffffff;
          font-family: ipag;
          font-style: bold;
          font-weight: normal;
          src:url('{{ storage_path('fonts/ipag.ttf')}}');
        }
        /* 請求書トップの2段目部分 */
        div.row2 {
          height: 5mm;
          text-align: right;
          background: #ffffff;
          font-size: 16px;
          letter-spacing: 1px;
          color: #000000;
          font-family: ipag;
          font-style: normal;
          font-weight: normal;
          src:url('{{ storage_path('fonts/ipag.ttf')}}');
        }
        /* 3段目部分 */
        div.row3 {
          height: 55mm;
        }
        div.row3 div.col1 {
          width: 90mm;
          float: left;
        }
        div.row3 div.col2 {
          /* 開始位置をずらすときはpaddingで調整 */
          padding-top: 5mm;
          float: right;
        }
        div.row4 {
          height: 18mm;
        }
        div.row5 {
          height: 120mm;
        }
        div.row_6 {
          height: 18mm;
        }
        /* テーブル 総額欄 */
        table.summary th {
          font-size: 14pt;
          width: 32mm;
          background-color: #ccddff;
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
          border:1px solid #eeeeee;
          border-collapse: collapse;
        }
        table.detail tr {
          height: 6mm;
        }
        table.detail th.item {
          width: 50%;
        }
        table.detail th.unit_price {
          width: 18%;
        }
        table.detail th.amount {
          width: 14%;
        }
        table.detail th.subtotal {
          width: 18%;
        }
        table.detail td.space {
          border-left-style: solid;
        }
        table.detail tr:nth-child(odd) {
          background-color: #ccddff;
        }
        table.detail tr:nth-child(even) {
          background-color: #ffffff;
        }
        /* 社印画像 */
        img.stamp{
          position: absolute;
          z-index: 1;
          top: 40mm;
          left: 48mm;
          height: 17mm;
          width: 17mm;
        }
        body {
          font-family: ipag;
        }
        /* liの「・」を消去  */
        li{
            list-style-type:none;
        }
    </style>
  </head>
  <body>
    <div class="row1">御　請　求　書</div>
    <div class="row2">
        <ul class="text-right">
            <li>No.1234567890-01</li>
            <li>2019年9月25日(水)</li>
        </ul>
    </div>
    <div class="row3">
        <div class="col1">
            <ul>
                <li><h2 class="customer_name">くおんしすてむ株式会社 御中</h2></li>
                <li>〒108-0023</li>
                <li>東京都港区芝浦3-4-1</li>
            </ul>
        </div>
        <div class="col2">
            <ul >
                <li><h2>くおんしすてむ株式会社</h2></li>
                <li>〒{{$userGroup->zip}}</li>
                <li>{{$userGroup->address1}}</li>
                <li>{{$userGroup->address2}}</li>
                <li>担当：{{$user->name}}</li>
                <li>TEL: {{$userGroup->tel}}</li>
                <li>FAX: {{$userGroup->fax}}</li>
            </ul>
            <img class="stamp" src="{{$seal}}">
        </div>
        <div class="clear-element"></div>
    </div>
    <div class="row4">
        <p>下記のとおりご請求申し上げます。</p>
        <table class="summary" border="1" style="border-collapse: collapse; border-color: #eeeeee">
            <tbody>
                <tr>
                    <th>合計金額</th>
                    <td align="right"> \77,760</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="row5">
        <table class="detail" border="1" style="border-collapse: collapse; border-color: #eeeeee">
            <thead>
                <tr>
                    <th class="item">品名</th>
                    <th class="unit_price">単価</th>
                    <th class="amount">数量</th>
                    <th class="subtotal">金額</th>
                </tr>
            </thead>
            <tbody>
                <tr class="dataline">
                    <td class="text-left"> 品名〇〇〇〇〇 </td>
                    <td align="right"> 2,200 </td>
                    <td align="right"> 5 </td>
                    <td align="right"> 11,000 </td>
                </tr>
                <tr class="dataline">
                    <td class="text-left"> 品名〇〇〇〇〇 </td>
                    <td align="right"> 3,200 </td>
                    <td align="right"> 5 </td>
                    <td align="right"> 16,000 </td>
                </tr>
                <tr class="dataline">
                    <td class="text-left"> 品名〇〇〇〇〇 </td>
                    <td align="right"> 9,000 </td>
                    <td align="right"> 5 </td>
                    <td align="right"> 45,000 </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr class="dataline">
                    <td>　</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr>
                    <td class="space" rowspan="3" colspan="2"> </td>
                    <th> 小計 </th>
                    <td align="right"> 72,000 </td>
                </tr>
                <tr>
                    <th> 消費税(8%) </th>
                    <td align="right"> 5,760 </td>
                </tr>
                <tr>
                    <th> 合計 </th>
                    <td align="right"> 77,760 </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="row6">
        <ul>
            <li>振込先</li>
            <li>名義：カ）くおんしすてむ</li>
            <li>〇〇銀行 港区支店 普通 123456789</li>
        </ul>
        <p>※お振込み手数料は御社ご負担にてお願い致します。</p>
    </div>
  </body>
</html>