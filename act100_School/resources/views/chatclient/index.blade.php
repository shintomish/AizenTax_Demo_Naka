@extends('layouts.api3_index')

@section('content')
    <h2>チャット</h2>
    <div class="text-right">

    </div>

    <div class="row">
        <!-- 検索エリア -->
    </div>

    {{-- Line --}}
    <hr class="mb-4">

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
                height: 100%;
                border: 1px solid #ccc;
            }
        }

        table{
            width: 400px;
        }
        th,td{
            width: 100px;   /* 200->250 */
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
    </style>

    <style>
        /** ５行ピッタシに調整 6行*/
        .row-5 {
            height: calc( 1.4em * 4 );
            line-height: 1.3;
            /* max-width: 600px; */
            width: 500px; 
        }
        .row-6 {
            overflow:auto;
            width:500px;
            height:400px;
            padding:5px;
            border:2px dotted #ffffff;
            color:#e8e8e8;
            /* background-color:#5bc6ed; */
            line-height:1.5em;
        }
    </style>

    <script src="{{ asset('js/app.js')}}"></script>

    <body>
        <div id="chat">
            {{-- <button @click="send()" :disabled="!textExists">送信</button> --}}
            <br>
            <div class="col-2">
                <label for="comment">コメント</label>
            </div>

            <textarea  style="" class="row-5" v-model="message"></textarea>

            <br>
            <button type="button" @click="send()">送信</button>

            {{-- Line --}}
            <hr>
            {{--  チャットルーム  --}}
            <div class="row-6" id="room">
                <ul class="" v-for="(m, key) in messages" :key="key">
                    {{-- 事務所はグリーン --}}
                    <template v-if="m.customer_id === 1">
                        <div class="recieve" style="text-align: right">
                        <span style="color: green" v-text="m.user.name"></span>
                        <span style="color: green"> :</span>&nbsp;
                        <span style="color: green" v-text="m.created_at"></span>
                        <span style="color: green">  </span>&nbsp;
                        <div><span class="u-pre-wrap" style="color: green" v-text="m.body"></span></div>
                        </div>
                    </template >
                    <template v-else>
                        <div class="send" style="text-align: left">
                        <span style="color: rgb(238, 104, 8)" v-text="m.user.name"></span>
                        <span style="color: rgb(238, 104, 8)"> :</span>&nbsp;
                        <span style="color: rgb(238, 104, 8)" v-text="m.created_at"></span>
                        <span style="color: rgb(238, 104, 8)">  </span>&nbsp;
                        <div><span class="u-pre-wrap" style="color: rgb(238, 104, 8)" v-text="m.body"></span></div>
                        </div>
                    </template >
                </ul>
            </div>
            {{-- <table class="table table-striped table-borderd"> --}}
                {{-- <table class="table table-striped table-borderd table_sticky"> --}}
                {{-- <thead> --}}
                    {{-- <tr>
                        <th scope="col" class ="fixed01">日時</th>
                        <th scope="col" class ="fixed01">@sortablelink('users_name', 'ユーザー名')</th>
                        <th scope="col" class ="fixed01">メッセージ</th>
                    </tr> --}}
                {{-- </thead> --}}

                {{-- <tbody>
                    @if($messages->count())
                        @foreach($messages as $message)
                        <tr> --}}
                            {{-- <td>{{ $message->id }}</td> --}}
                            {{-- @php
                                $str = "";
                                $str = ( new DateTime($message->m_created_at))->format('Y-m-d H:i:s');
                            @endphp
                            <td>{{ $str }}</td>
                            <td>
                                @foreach ($users as $users2)
                                    @if ($users2->id==$message->user_id)
                                        {{$users2->name}}
                                    @endif
                                @endforeach
                            </td> --}}
                            {{-- <td>
                                @foreach ($customers as $customers2)
                                    @if ($customers2->id==$message->customer_id)
                                        {{$customers2->business_name}}
                                    @endif
                                @endforeach
                            </td> --}}

                            {{-- <td>{{ $message->m_body }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td><p>0件です。</p></td>
                            <td><p> </p></td>
                            <td><p> </p></td>
                        </tr>
                    @endif
                </tbody>
            </table> --}}

            {{-- ページネーション / pagination）の表示 --}}
            {{-- <ul class="pagination justify-content-center">
                {{ $messages->appends(request()->query())->render() }}
            </ul> --}}
        </div>

        {{-- <script src="/js/app.js"></script> --}}
        {{-- <script src="{{ asset('js/app.js')}}"></script> --}}
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

        {{-- 2022/05/06 --}}
        {{-- js/app.jsでは、本番環境でvueが表示されないので、cdnと併用した。 --}}
        {{-- 本番：Uncaught TypeError: Vue is not a constructor --}}
        {{-- UT環境：[Vue warn]: Cannot find element: #app --}}
        {{-- <script src="{{ mix('js/app.js')}}" defer></script> --}}
        {{-- <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js"></script> --}}
        {{-- <script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script> --}}
        {{-- <script src="https://cdn.jsdelivr.net/npm/vue@2.6.0"></script> --}}
        <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>

        <script>
            new Vue({
                el: '#chat',
                data: {
                    message: '',
                    messages: []
                },
                methods: {
                    getMessages() {
                        // const url = '/ajax/chat';
                        const url = "{{ route('ajaxchatclientin') }}";
                        axios.get(url)
                        .then((response) => {

                            this.messages = response.data;

//                             $param = [this.messages];
//                             $i = 0;
//                             for( ; ; ){
//                                 if(($param[0][$i]['created_at']) == null) break;
//                                 if(($param[0][$i]['created_at']) == '') break;
//                                 // console.log($i);
//                                 // console.log($param[0][$i]['created_at']);

//                                 const str = String($param[0][$i]['created_at'])
//                                 // 表示用に加工 2022-10-26T05:24:29.000000Z 2022/10/26 05:24:29
// $param[0][$i]['created_at'] = `${str.slice(0, 4)}/${str.slice(5,7)}/${str.slice(8,10)} ${str.slice(11,13)}:${str.slice(14,16)}:${str.slice(17,19)} `
//                                 // console.log($param[0][$i]['created_at']);
//                                 $i++;
//                                 // if($i==10) break;
//                             }

                        });
                    },
                    send() {

                        // const url = '/ajax/chat';
                        const url = "{{ route('ajaxchatclientcr') }}";
                        // const params = { message: this.message, user: this.user };
                        const params = { message: this.message};
                        axios.post(url, params)
                        .then((response) => {

                            // 成功したらメッセージをクリア
                            this.message = '';

                        });
                        console.log('send');
                    }
                },
                mounted() {
                    console.log('mounted');
                    this.getMessages();
                    Echo.channel('chat')
                    .listen('MessageCreated', (e) => {
                        this.getMessages(); // メッセージを再読込
                    });

                }
            });
            // Vue.createApp(app).mount('#app')
        </script>
        {{-- 2022/11/01 --}}
        <style lang=scss scoped>
            .u-pre-wrap{
                white-space: pre-wrap;
                margin-left: 0px;
            }
        </style>
    </body>

    {{-- Line --}}
    <hr class="mb-4">

@endsection

@section('part_javascript')
{{-- ChangeSideBar("nav-item-system-user"); --}}
    <script type="text/javascript">

    </script>
@endsection
