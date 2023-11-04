{{-- @extends('layouts.app') --}}
@extends('layouts.api_index')

@section('content')
    {{-- <h2>顧問料一覧</h2> --}}
    <div class="text-right">
        {{-- <a class="btn btn-success btn-sm mr-auto" href="{{route('advisorsfee.create')}}">新規登録</a> --}}
        {{-- <a id="start2" style="margin-bottom:5px;" class="btn btn-success btn-sm mr-auto" href="{{route('excelexp')}}">請求書作成</a> --}}
    </div>

    <div class="row">
        <!-- 検索エリア -->
        <p>作成状況：</p>
            <div class="progress">
                <div id="pgss2" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                </div>
            </div>
        <!-- 検索エリア -->
    </div>

    {{-- Line --}}
    <hr class="mb-4">

    <div class="table-responsive">

        <table class="table table-striped table-borderd table-scroll">
            <thead>
                <tr>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-secondary text-left">ID</th>
                    <th scope="col" class ="col-xs-3 col-md-4 bg-info text-right">@sortablelink('users_name', '体験者名')</th>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-info text-right">@sortablelink('urgent_flg', '請求書作成状況')</th>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-info text-right">@sortablelink('created_at', '登録日時')</th>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-secondary  text-right">操作</th>
                </tr>
            </thead>

            <tbody>
                @if($linemessages->count())
                    @foreach($linemessages as $linemessage)
                    <tr>
                        {{-- ID --}}
                        @php
                            $cusid = sprintf("%03d", $linemessage->id);
                        @endphp
                        <td>{{ $cusid }}</td>

                        {{-- 体験者名 --}}
                        @php
                            $str = sprintf("%s", $linemessage->users_name);
                        @endphp
                        <input type="text" class="form-control" id="users_name_{{$linemessage->id}}" name="users_name_{{$linemessage->id}}" value="{{$str}}">

                        <td>
                            {{-- 請求書作成状況 1:未 2:済 --}}
                            <select class="custom-select" id="contract_entity_{{$linemessage->id}}" name="contract_entity_{{$linemessage->id}}">
                                <option value="1" {{ $linemessage->urgent_flg == 1 ? 'selected' : '' }}>未</option>
                                <option value="2" {{ $linemessage->urgent_flg == 2 ? 'selected' : '' }}>済</option>
                            </select>
                        </td>

                        {{-- 登録日時 --}}
                        @php
                            if (isset($linemessage->created_at)) {
                                $str1 = ( new DateTime($linemessage->created_at))->format('Y-m-d');
                            }
                        @endphp
                        <td>{{ $str1 }}</td>

                        <td>
                            <div class="btn-toolbar">
                                <div class="btn-group me-2 mb-0">
                                <a class="btn btn-primary btn-sm" href="">請求書作成</a>
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
                    </tr>
                @endif
                <script type="text/javascript">
                    //---------------------------------------------------------------
                    //--体験者名テキストボックスイベントハンドラ
                    //---------------------------------------------------------------
                    $('input[name^="users_name_"]').change( function(e){
                        alert('体験者名Click');
                        var wok_id           = $(this).attr("name").replace('users_name_', '');
                        var users_name       = $(this).val();
                        var this_id          = $(this).attr("id");
                        change_wokproc_info(     this_id        // 対象コントロール
                                                , wok_id        // linemessageテーブルのID
                                                , users_name    // 体験者名
                                            );
                    });

                    /**
                    * this_id         : 対象コントロール
                    * wok_id          : linemessageテーブルのID
                    * users_name      : 体験者名
                    *
                    */
                    function change_wokproc_info(  this_id
                                                , wok_id        // wok_id  linemessageテーブルのID
                                                , users_name    // 体験者名
                                                ){
                            var reqData = new FormData();
                                                            reqData.append( "id"         , wok_id     );
                            if( null != users_name        ) reqData.append( "users_name" , users_name );

                            console.log(users_name);
                            // console.log(filing_date);

                            // Ajax通信呼出(データファイルのアップロード)
                            AjaxAPI.callAjax(
                                "{{ route('linemessage.update_api') }}",
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
    {{ $linemessages->appends(request()->query())->render() }}
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
