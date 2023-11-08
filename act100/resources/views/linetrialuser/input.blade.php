{{-- @extends('layouts.app') --}}
@extends('layouts.api_index')

@section('content')
    {{-- 体験者名簿 --}}
    <div class="text-right">
        {{-- <a class="btn btn-success btn-sm mr-auto" href="{{route('advisorsfee.create')}}">新規登録</a> --}}
        {{-- <a id="start2" style="margin-bottom:5px;" class="btn btn-success btn-sm mr-auto" href="{{route('excelexp')}}">請求書作成</a> --}}
    </div>

    @if (session('message'))
        @if (session('message') == 'LINEから体験者が登録されました')
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
    @endif

    <div class="row">
        {{-- <div class="col-2">
    <a type="submit" class="btn btn-primary btn-sm" href="{{ route('linetrialuser.input')}}">更新</a>
        </div> --}}
        <!-- 検索エリア -->
        <p>作成状況：</p>
            <div class="progress">
                <div id="pgss2" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                </div>
            </div>
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
    <hr class="mb-4">

    <div class="table-responsive">

        {{-- <table class="table table-striped table-borderd table-scroll"> --}}
        <table class="table table-responsive text-nowrap table-striped table-borderd table_sticky">
            <thead>
                <tr>
                    <th scope="col" class ="fixed01">ID</th>
                    <th scope="col" class ="fixed01">体験者名</th>
                    <th scope="col" class ="fixed01">予約時間</th>
                    <th scope="col" class ="fixed01">登録日</th>
                    <th scope="col" class ="fixed01">請求書作成</th>
                    <th scope="col" class ="fixed01">操作</th>
                </tr>
            </thead>

            <tbody>
                @if($linetrialusers->count())
                    @foreach($linetrialusers as $linetrialuser)
                    <tr>
                        {{-- ID --}}
                        @php
                            $cusid = sprintf("%03d", $linetrialuser->id);
                        @endphp
                        <td>{{ $cusid }}</td>

                        {{-- 体験者名 --}}
                        @php
                            $str = sprintf("%s", $linetrialuser->users_name);
                        @endphp
                        <td>
                            <input type="text" class="form-control" id="users_name_{{$linetrialuser->id}}" name="users_name_{{$linetrialuser->id}}" value="{{$str}}">
                        </td>

                        {{-- 予約時間 reservationed_at--}}
                        @php
                            $str2 = '';
                            if (isset($linetrialuser->reservationed_at)) {
                                $str2 = substr($linetrialuser->reservationed_at,0,5);
                                // $str1 = ( new DateTime($linetrialuser->reservationed_at))->format('Y-m-d');
                            }
                        @endphp
                        <td>
                        <input type="time" class="form-control" id="reservationed_at_{{$linetrialuser->id}}" name="reservationed_at_{{$linetrialuser->id}}" value="{{$str2}}">
                        </td>
                        {{-- <td>{{ $str2 }}</td> --}}

                        {{-- 登録日時 --}}
                        @php
                            if (isset($linetrialuser->created_at)) {
                                $str3 = ( new DateTime($linetrialuser->created_at))->format('Y-m-d H:i:s');
                            }
                        @endphp
                        <td>{{ $str3 }}</td>
                        {{-- 請求書作成状況 1:未 2:済 --}}
                        @php
                            if($linetrialuser->urgent_flg == 1){
                                $str1 = '未作成';
                                $textcolor = 'text-danger';
                            }else{
                                $str1 = '作成済';
                                $textcolor = 'text-secondary';
                            }
                        @endphp
                        <td>
                            <h6>
                                <p class={{ $textcolor }}>{{ $str1 }}</p>
                            </h6>
                        </td>

                        <td>
                            <div class="btn-toolbar">
                                <div class="btn-group me-2 mb-0">
                                <a id="start2" class="btn btn-primary btn-sm" href="{{ route('lineexcelexp',$linetrialuser->id)}}">請求書作成</a>
                                {{-- <a class="btn btn-primary btn-sm" href="{{ route('advisorsfee.edit',$advisorsfee->id)}}">編集</a> --}}
                                </div>
                                {{-- <div class="btn-group me-2 mb-0">
                                    <form action="{{ route('advisorsfee.destroy', $advisorsfee->id)}}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input class="btn btn-danger btn-sm" type="submit" value="削除" id="btn_del"
                                            onclick='return confirm("削除しますか？");'>
                                    </form>
                                </div> --}}
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
                    //---------------------------------------------------------------
                    //--体験者名テキストボックスイベントハンドラ
                    //---------------------------------------------------------------
                    $('input[name^="users_name_"]').change( function(e){
                        // alert('体験者名Click');
                        var wok_id           = $(this).attr("name").replace('users_name_', '');
                        var users_name       = $(this).val();
                        var this_id          = $(this).attr("id");
                        change_wokproc_info(     this_id        // 対象コントロール
                                                , wok_id        // linetrialuserテーブルのID
                                                , users_name    // 体験者名
                                                , null          // 予約時間
                                            );
                    });
                    //---------------------------------------------------------------
                    //--予約時間テキストボックスイベントハンドラ
                    //---------------------------------------------------------------
                    $('input[name^="reservationed_at_"]').change( function(e){
                        // alert('予約時間Click');
                        var wok_id           = $(this).attr("name").replace('reservationed_at_', '');
                        var reservationed_at = $(this).val();
                        var this_id          = $(this).attr("id");
                        change_wokproc_info(     this_id        // 対象コントロール
                                                , wok_id        // linetrialuserテーブルのID
                                                , null          // 体験者名
                                                , reservationed_at    // 予約時間
                                            );
                    });

                    /**
                    * this_id         : 対象コントロール
                    * wok_id          : linetrialuserテーブルのID
                    * users_name      : 体験者名
                    *
                    */
                    function change_wokproc_info(  this_id
                                                , wok_id        // wok_id  linetrialuserテーブルのID
                                                , users_name    // 体験者名
                                                , reservationed_at    // 予約時間
                                                ){
                            var reqData = new FormData();
                                                            reqData.append( "id"         , wok_id     );
                            if( null != users_name        ) reqData.append( "users_name" , users_name );
                            if( null != reservationed_at  ) reqData.append( "reservationed_at" , reservationed_at );

                            console.log(users_name);
                            console.log(reservationed_at);

                            // Ajax通信呼出(データファイルのアップロード)
                            AjaxAPI.callAjax(
                                "{{ route('linetrialuser.update_api') }}",
                                reqData,
                                function (res) {
                                    $('#'+this_id).effect("pulsate", { times:2 }, 500);
                                }
                            )
                    };
                </script>

            </tbody>
        </table>

    </div>

    {{-- ページネーション / pagination）の表示 --}}
    <ul class="pagination justify-content-center">
    {{ $linetrialusers->appends(request()->query())->render() }}
    </ul>

    {{-- 進捗バー --}}
    <script>
        $(function () {
            var count = 0;
            $(document).on('click','#start2',function(){
                if( !confirm('請求書を作成しますか？') ){
                    /* キャンセルの時の処理 */
                    return false;
                }  else {
                    /*　OKの時の処理 */
                    progress(count);

                    // return true;
                }
                    // progress(count);
            });

            function progress(count){
                setTimeout(function(){
                    $("#pgss2").css({'width':count+'%'});
                    $("#pgss2").prop('aria-valuenow', count)
                    $("#pgss2").text(count + '%');
                    count++;
                    if(count == 100) return;
                    progress(count);
                },150);
            }
        })

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
