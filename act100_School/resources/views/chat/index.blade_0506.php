@extends('layouts.api2_index')

@section('content')
    <h2>チャット</h2>
    <div class="text-right">

    </div>

    <div class="row">
        <!-- 検索エリア -->
    </div>

    {{-- Line --}}
    <hr class="mb-4">

    <body>
        <div id="app">
            <example-component></example-component>
        </div>
        
        {{-- <script src="/js/app.js"></script> --}}
        <script src="{{ asset('js/app.js')}}"></script>


    </body>

    {{-- Line --}}
    <hr class="mb-4">

@endsection

@section('part_javascript')
{{-- ChangeSideBar("nav-item-system-user"); --}}
    <script type="text/javascript">

    </script>
@endsection
