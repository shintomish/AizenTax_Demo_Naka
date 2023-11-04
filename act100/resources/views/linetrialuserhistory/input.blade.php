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
        {{-- <p>作成状況：</p> --}}
            {{-- <div class="progress">
                <div id="pgss2" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                </div>
            </div> --}}
        <!-- 検索エリア -->
    </div>

    {{-- Line --}}
    <hr class="mb-4">

    <div class="table-responsive">

        <table class="table table-striped table-borderd table-scroll">
            <thead>
                <tr>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-secondary text-left">体験者ID</th>
                    <th scope="col" class ="col-xs-3 col-md-4 bg-info text-right">体験者名</th>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-info text-right">パス名</th>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-info text-right">ファイル名</th>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-info text-right">XLS/PDF</th>
                    <th scope="col" class ="col-xs-3 col-md-1 bg-secondary  text-right">操作</th>
                </tr>
            </thead>

            <tbody>
                @if($line_trial_users_historys->count())
                    @foreach($line_trial_users_historys as $line_trial_users_history)
                    <tr>
                        {{-- ID --}}
                        @php
                            $cusid = sprintf("%02d", $line_trial_users_history->user_id);
                        @endphp
                        <td>{{ $cusid }}</td>

                        {{-- 体験者名 --}}
                        @php
                            $str = sprintf("%s", $line_trial_users_history->users_name);
                        @endphp
                        <td>
                            {{ $str }}
                        </td>

                        {{-- パス名  --}}
                        <td>{{$line_trial_users_history->filepath }}</td>

                        {{-- ファイル名  --}}
                        <td>{{$line_trial_users_history->filename }}</td>

                        {{-- XLS/PDF 拡張子フラグ(1):xlsx  (2):pdf --}}
                        @php
                            if($line_trial_users_history->extension_flg == 1) {
                                $str = 'XLS';
                            } else {
                                $str = 'PDF';
                            }
                        @endphp
                        <td>
                            {{ $str }}
                        </td>

                        <td>
                            <div class="btn-toolbar">
                                <div class="btn-group me-2 mb-0">
                                {{-- <a id="start2" class="btn btn-primary btn-sm" href="{{ route('lineexcelexp',$line_trial_users_history->id)}}">詳細</a> --}}
<input class="btn btn-primary btn-sm" type="submit" id="btn_del_{{$line_trial_users_history->id}}" name="btn_del_{{$line_trial_users_history->id}}" value="詳細" >

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
                    var url          = "invoicehistory/pdf/" + wok_id;
                    $('#temp_form').method = 'POST';
                    $('#temp_form').submit();
                    var popup = window.open(url,"preview","width=800, height=600, top=200, left=500 scrollbars=yes");
                });
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
