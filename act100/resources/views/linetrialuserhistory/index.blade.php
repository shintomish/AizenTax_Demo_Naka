{{-- @extends('layouts.app') --}}
@extends('layouts.api_index')

@section('content')
    {{-- 体験者請求書 --}}
    <div class="text-right">
        {{-- <a class="btn btn-success btn-sm mr-auto" href="{{route('advisorsfee.create')}}">新規登録</a> --}}
        {{-- <a id="start2" style="margin-bottom:5px;" class="btn btn-success btn-sm mr-auto" href="{{route('excelexp')}}">請求書作成</a> --}}
    </div>

    <div class="row">
        <!-- 検索エリア -->
        {{-- <p>作成状況：</p> --}}
            {{-- <div class="progress">
                <div id="pgss2" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                </div>
            </div> --}}
        <!-- 検索エリア -->
    </div>
    <style>
        /* スクロールバーの実装 */
        .table_sticky {
            display: block;
            overflow-y: scroll;
            /* height: calc(100vh/2); */
            height: 600px;
            border:1px solid;
            border-collapse: collapse;
        }
        .table_sticky thead th {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            left: 0;
            color: #fff;
            background: rgb(180, 226, 11);
            &:before{
                content: "";
                position: absolute;
                top: -1px;
                left: -1px;
                width: 100%;
                /* height: 100%; 2023/06/12 sortablelink対応 */
                height: 10%;
                border: 1px solid #ccc;
            }
        }

        table{
            width: 1800px;
        }
        th,td{
            width: 250px;   /* 200->250 */
            height: 10px;
            vertical-align: middle;
            padding: 0 15px;
            border: 1px solid #ccc;
        }
        .fixed01,
        .fixed02{
            /* position: -webkit-sticky; */
            position: sticky;
            top: 0;
            left: 0;
            color: rgb(8, 8, 8);
            background: #333;
            &:before{
                content: "";
                position: absolute;
                top: -1px;
                left: -1px;
                width: 100%;
                height: 100%;
                border: 1px solid #ccc;
            }
        }
        .fixed01{
            z-index: 2;
        }
        .fixed02{
            z-index: 1;
        }
        .fixed03{
            color: red;
            font-weight: bold;
        }
    </style>

    {{-- Line --}}
    {{-- <hr class="mb-4"> --}}

    <div class="table-responsive">

        <table class="table table-responsive text-nowrap table-striped table-borderd table_sticky">
            <thead>
                <tr>
                    <th scope="col" class ="fixed01">ID</th>
                    <th scope="col" class ="fixed01">体験者名</th>
                    <th scope="col" class ="fixed01">パス名</th>
                    <th scope="col" class ="fixed01">ファイル名</th>
                    <th scope="col">@sortablelink('urgent_flg', '発行確認')</th>
                    <th scope="col" class ="fixed01">操作</th>
                </tr>
            </thead>

            <tbody>
                @if($line_trial_users_historys->count())
                    @foreach($line_trial_users_historys as $line_trial_users_history)
                    <tr>
                        {{-- ID --}}
                        @php
                            $cusid = sprintf("%03d", $line_trial_users_history->id);
                        @endphp
                        <td>{{ $cusid }}</td>

                        {{-- 体験者名 --}}
                        @php
                            $str = sprintf("%s", $line_trial_users_history->user_name);
                        @endphp
                        <td>{{ $str }}</td>

                        {{-- パス名  --}}
                        <td>{{$line_trial_users_history->filepath }}</td>

                        {{-- ファイル名  --}}
                        <td>{{$line_trial_users_history->filename }}</td>

                        {{-- XLS/PDF 拡張子フラグ(1):xlsx  (2):pdf --}}
                        {{-- @php
                            if($line_trial_users_history->extension_flg == 1) {
                                $str = 'XLS';
                            } else {
                                $str = 'PDF';
                            }
                        @endphp --}}
                        {{-- <td>urgent_flg
                            {{ $str }}
                        </td> --}}

                        {{-- 領収書ダウンロード 発行フラグ(1):済  (2):未 --}}
                        @php
                            if($line_trial_users_history->urgent_flg == 2) {
                                $str = '未発行';
                                $strstyle = "color:red";
                            } else {
                                $str = '発行済';
                                $strstyle = "color:blue";
                            }
                        @endphp
                        <td>
                            <h6 >
            <p name="shine_{{$line_trial_users_history->id}}" id="shine_{{$line_trial_users_history->id}}" style="{{$strstyle}}" ><label name="label_{{$line_trial_users_history->id}}" style="margin-top:10px;" >{{$str}}</label>
                                </p>
                            </h6>
                            {{-- {{ $str }} --}}
                        </td>

                        <td>
                            <div class="btn-toolbar">
                                <div class="btn-group me-2 mb-0">
        {{-- <a id="start2" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm" href="{{ route('linetrialuserhistory_pdf01',$line_trial_users_history->id)}}">DownLoad</a> --}}
<input class="btn btn-primary btn-sm" type="submit" id="btn_del_{{$line_trial_users_history->id}}" name="btn_del_{{$line_trial_users_history->id}}" id2="btn_del_{{$line_trial_users_history->urgent_flg}}" value="DownLoad" >

                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td><p>0件です。</p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                        <td><p> </p></td>
                    </tr>
                @endif
                <script type="text/javascript">
                    $('input[name^="btn_del_"]').click( function(e){
                        // alert('ダウンロードbtnClick');
                        var wok_id       = $(this).attr("name").replace('btn_del_', '');
                        var this_id      = $(this).attr("id");
                        var urgent_flg   = $(this).attr("id2").replace('btn_del_', '');
                        var url          = "linetrialuserhistory/pdf/" + wok_id;
                        $('#temp_form').method = 'POST';
                        $('#temp_form').submit();
                        var popup = window.open(url,"preview","width=800, height=600, top=200, left=500 scrollbars=yes");
                        change_invoice_info( this_id        // 対象コントロール
                                            , wok_id        // line_trial_users_historyテーブルのID
                                            , urgent_flg    // line_trial_users_historyテーブルのurgent_flgの値
                                            );
                    });
    
                    /**
                    * this_id         : 対象コントロール
                    * wok_id          : line_trial_users_historyテーブルのID
                    *
                    */
                    function change_invoice_info( this_id
                                                , wok_id        // line_trial_users_historyテーブルのID
                                                , urgent_flg    // line_trial_users_historyテーブルのurgent_flgの値
                                                ) {
                        var reqData = new FormData();
                                                    reqData.append( "id"             , wok_id      );
                        if( null != urgent_flg    ) reqData.append( "urgent_flg"     , urgent_flg );
    
                        AjaxAPI.callAjax(
                            "{{ route('linetrialuserhistory_upload_api') }}",
                            reqData,
                            function (res) {
                                var shinename = 'shine_'   + wok_id;
                                // var btnname   = 'btn_del_' + wok_id;
    
                                // console.log( shinename );
                                // console.log( btnname );
    
                                // 領収書ダウンロード 発行フラグ(1):済  (2):未
                                if(urgent_flg == 2) {
                                    // 
                                    const elem = document.getElementById(shinename);
                                    if (elem) {
                                        // テキスト・スタイルを変更
                                        elem.textContent = "発行済";
                                        elem.style.color = "#0000FF";   //blue
                                    } else {
                                        console.log( 'shine_non' );
                                    }

                                    // // btnのclass変更
                                    // const elem2 = document.getElementById(btnname);
                                    // if (elem2) {
                                    //     // クラス名を削除
                                    //     elem2.classList.remove("btn-danger");
                                    //     // クラス名を追加
                                    //     elem2.classList.add("btn-secondary");
                                    // } else {
                                    //     console.log( 'btn_del_non' );
                                    // }

                                $('#'+this_id).effect("pulsate", { times:2 }, 500);
                            }
                            }
                        )
    
                        // 領収書ダウンロード 発行フラグ(1):済  (2):未
                        if(urgent_flg == 1) {
                            // 何もしない
                            console.log('no repaint');
                            return;
                        }
    
                    };
    
                </script>
            </tbody>
        </table>

    </div>

    {{-- ページネーション / pagination）の表示 --}}
    <ul class="pagination justify-content-center">
    {{ $line_trial_users_historys->appends(request()->query())->render() }}
    </ul>

    {{-- 進捗バー --}}
    <script>
        // $(function () {
        //     var count = 0;
        //     $(document).on('click','#start2',function(){
        //         if( !confirm('請求書を作成しますか？') ){
        //             /* キャンセルの時の処理 */
        //             return false;
        //         }  else {
        //             /*　OKの時の処理 */
        //             progress(count);

        //             // return true;
        //         }
        //             // progress(count);
        //     });

        //     function progress(count){
        //         setTimeout(function(){
        //             $("#pgss2").css({'width':count+'%'});
        //             $("#pgss2").prop('aria-valuenow', count)
        //             $("#pgss2").text(count + '%');
        //             count++;
        //             if(count == 100) return;
        //             progress(count);
        //         },150);
        //     }
        // })

    </script>

@endsection

@section('part_javascript')
{{-- ChangeSideBar("nav-item-system-user"); --}}
    {{-- <script type="text/javascript">
            $('.btn_del').click(function()
                if( !confirm('請求書を作成しますか？') ){
                    /* キャンセルの時の処理 */
                    return false;
                }  else {
                    /*　OKの時の処理 */
                    return true;
                }
            );
    </script> --}}
@endsection
