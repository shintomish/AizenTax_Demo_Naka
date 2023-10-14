{{-- @extends('layouts.app') --}}
@extends('layouts.billdata')
{{-- // 事務所 請求書データ確認 --}}
@section('content')
    <div class="row">

        <!-- 検索エリア -->

        <!-- 検索エリア -->
    </div>

    <div class="table-responsive">

        <table class="table table-striped table-borderd">
            <thead>
                <tr>
                    <th class="text-left"scope="col">ID</th>
                    <th scope="col">会社名</th>
                    <th scope="col">送信ファイル</th>
                    <th class="text-left" scope="col">ファイルサイズ</th>
                    <th scope="col">送信日</th>
                    <th scope="col">未読/既読</th>
                    <th scope="col">操作</th>
                </tr>
            </thead>

            <tbody id="table" >
                @if($billdatas->count())
                    @foreach($billdatas as $billdata)
                    <tr>
                        <td class="text-left">{{ number_format($billdata->id) }}</td>

                        {{-- 会社名 --}}
                        <td>
                            @foreach ($customers as $customers2)
                                @if ($customers2->id==$billdata->customer_id)
                                    {{$customers2['business_name']}}
                                @endif
                            @endforeach
                        </td>

                        <td>{{ $billdata->filename }}</td>
                            @php
                                $str = "";
                                if (isset($billdata->created_at)) {
                                    $str = ( new DateTime($billdata->created_at))->format('Y-m-d');
                                }

                                $insize = $billdata->filesize;
                                if ($insize >= 1073741824) {
                                    $fileSize = round($insize / 1024 / 1024 / 1024,1) . ' GB';
                                } elseif ($insize >= 1048576) {
                                    $fileSize = round($insize / 1024 / 1024,1) . ' MB';
                                } elseif ($insize >= 1024) {
                                    $fileSize = round($insize / 1024,1) . ' KB';
                                } else {
                                    $fileSize = $insize . ' bytes';
                                }
                                $temp = $fileSize;

                                if($billdata->urgent_flg == 2) {
                                    $kidoku = '未読';
                                    $textcolor = 'text-danger';
                                } else {
                                    $kidoku = '既読';
                                    $textcolor = 'text-secondary';
                                }

                                // 至急フラグ(1):通常 (2):至急
                                if($billdata->urgent_flg == 2) {
                                    $strvalue = "ダウンロード";
                                    $clsvalue = "btn btn-danger btn-lg";
                                    $strstyle = "color:red";
                                    $strnews  = "NEW";
                                    $clslight = "light_box";    //点滅
                                } else {
                                    $strvalue = "ダウンロード";
                                    $clsvalue = "btn btn-secondary btn-lg";
                                    $strstyle = "";
                                    $strnews  = "";
                                    $clslight = "";
                                }
                        @endphp
                        {{-- ファイルサイズ --}}
                        <td class="text-left">{{ $temp }}</td>

                        {{-- 送信日 --}}
                        <td >{{ $str }}</td>

                        {{-- 未読/既読 --}}
                        <td>
                            <h6>
                                <p class={{ $textcolor }}>{{ $kidoku }}</p>
                            </h6>
                        </td>
                        <td>
                            <div class="btn-toolbar">
                                <div class="btn-group me-2 mb-0">
                                {{-- <a class="btn btn-primary btn-sm" href="{{ route('invoice_pdf01',$billdata->id)}}">ダウンロード</a> --}}
                                {{--OK <a class="{{$clsvalue}}" href="{{ route('invoice_pdf01',$billdata->id)}}">{{$strvalue}}</a> --}}
                                </div>
                            </div>
                    <input class="btn btn-secondary btn-sm" type="submit" id="btn_del_{{$billdata->id}}" name="btn_del_{{$billdata->id}}" id2="btn_del_{{$billdata->urgent_flg}}" value="ダウンロード" >
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td><p> </p></td>
                        <td><p>0件です。</p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                    </tr>
                @endif
            </tbody>
            <style>
                /* 点滅 */
                .light_box{
                    width: 40px;
                    height: 40px;
                    margin: 5px auto;
                    opacity: 0;
                    background-color:rgb(255, 0, 0);
                    border-radius: 3.0rem;
                    animation: flash 1.5s infinite linear;
                    color:rgb(254, 254, 254);
                }
                @keyframes flash {
                    50% {
                    opacity: 1;
                    }
                }
            </style>

            <script type="text/javascript">
                $('input[name^="btn_del_"]').click( function(e){
                    // alert('ダウンロードbtnClick');
                    var wok_id       = $(this).attr("name").replace('btn_del_', '');
                    var this_id      = $(this).attr("id");
                    var urgent_flg   = $(this).attr("id2").replace('btn_del_', '');
                    var url          = "pdf/" + wok_id;
                    $('#temp_form').method = 'POST';
                    $('#temp_form').submit();
                    var popup = window.open(url,"preview","width=800, height=600, top=200, left=500 scrollbars=yes");
                    // change_invoice_info( this_id        // 対象コントロール
                    //                     , wok_id        // invoiceテーブルのID
                    //                     , urgent_flg    // invoiceテーブルのurgent_flgの値
                    //                     );
                });

                // /**
                // * this_id         : 対象コントロール
                // * wok_id          : invoiceテーブルのID
                // *
                // */
                // function change_invoice_info( this_id
                //                             , wok_id        // invoiceテーブルのID
                //                             , urgent_flg    // invoiceテーブルのurgent_flgの値
                //                             ) {
                //     var reqData = new FormData();
                //                                 reqData.append( "id"             , wok_id      );
                //     if( null != urgent_flg    ) reqData.append( "urgent_flg"     , urgent_flg );

                //     AjaxAPI.callAjax(
                //         "{{ route('invoicehis_upload_api') }}",
                //         reqData,
                //         function (res) {
                //             var shinename = 'shine_'   + wok_id;
                //             var btnname   = 'btn_del_' + wok_id;

                //             // console.log( shinename );
                //             // console.log( btnname );

                //             // 至急フラグ(1):通常 (2):至急
                //             if(urgent_flg == 2) {
                //                 // 点滅のclass削除
                //                 const elem = document.getElementById(shinename);
                //                 if (elem) {
                //                     // クラス名を削除
                //                     elem.classList.remove("light_box");
                //                     // テキストを削除
                //                     elem.textContent = "";
                //                 } else {
                //                     console.log( 'shine_non' );
                //                 }

                //                 // btnのclass変更
                //                 const elem2 = document.getElementById(btnname);
                //                 if (elem2) {
                //                     // クラス名を削除
                //                     elem2.classList.remove("btn-danger");
                //                     // クラス名を追加
                //                     elem2.classList.add("btn-secondary");
                //                 } else {
                //                     console.log( 'btn_del_non' );
                //                 }

                //                 $('#'+this_id).effect("pulsate", { times:2 }, 500);
                //             }
                //         }
                //     )

                //     // 至急フラグ(1):通常 (2):至急
                //     if(urgent_flg == 1) {
                //         // 何もしない
                //         console.log('no repaint');
                //         return;
                //     }
                // };

            </script>

        </table>
    </div>

    {{-- ページネーション / pagination）の表示 --}}
    <ul class="pagination justify-content-center">
    {{-- {{ $billdatas->render() }} --}}
    {{-- {{ $billdatas->appends(request()->query())->links() }} --}}
        {{ $billdatas->appends(request()->query())->render() }}
    </ul>

@endsection

@section('part_javascript')
{{-- ChangeSideBar("nav-item-system-user");
    <script type="text/javascript">
            $('.btn_del').click(function()
                if( !confirm('本当に削除しますか？') ){
                    /* キャンセルの時の処理 */
                    return false;
                }
                else{
                    /*　OKの時の処理 */
                    return true;
                }
            });
    </script> --}}
@endsection
