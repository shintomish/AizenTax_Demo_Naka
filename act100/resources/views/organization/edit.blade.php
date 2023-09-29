@extends('layouts.customer')

@section('content')
<div class="content-wrapper">
    <!-- ---------------------------------------------------------------------- -->
    <!-- Content Header (Page header)                                           -->
    <!-- ---------------------------------------------------------------------- -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h1 id="page-title" class="m-0 text-dark">システム・メンテナンス</h1> --}}
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- ---------------------------------------------------------------------- -->
    <!-- Main content                                                           -->
    <!-- ---------------------------------------------------------------------- -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- card content 組織の編集 -->
                    <div id="card_organization_list_edit" class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">組織の編集</h3>
                        </div>
                        <!-- /.card-header -->

                        @if ($errors->any())
                          <div class="alert alert-danger">
                            <ul>
                              @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                              @endforeach
                            </ul>
                          </div>
                        @endif

                        <form action="{{ route('organization.update',$organization->id)}}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">

                                {{-- <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">ログイン名</label>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="username" value="{{ old('username',$user->username)}}">
                                        </div>
                                    </div>
                                </div> --}}


                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">ID</label>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" disabled class="form-control" name="id" value="{{ $organization->id }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">組織名</label>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="name" value="{{ old('name', $organization->name) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">組織名(よみ)</label>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="kana" value="{{ old('kana', $organization->kana) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">郵便番号</label>
                                        </div>
                                        <div class="col-3">
                                            <input type="text" class="form-control" name="first_code" value="{{ old('first_code', $organization->first_code) }}">
                                        </div>
                                        <div class="col-3">
                                            <input type="text" class="form-control" name="last_code" value="{{ old('last_code', $organization->last_code) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">住所</label>
                                        </div>
                                        <div class="col-3">
                                            <input type="text" class="form-control" name="prefecture" placeholder="都道府県名を入力してください" value="{{ old('prefecture', $organization->prefecture) }}">
                                        </div>
                                        {{-- <div class="col-1">県</div> --}}

                                        <div class="col-3">
                                            <input type="text" class="form-control" name="city" placeholder="市区町村を入力してください" value="{{ old('city', $organization->city) }}">
                                        </div>
                                        {{-- <div class="col-1">市</div> --}}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2"></div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="address" placeholder="町域/番地を入力してください" value="{{ old('address', $organization->address) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2"></div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="other" placeholder="建物名などを入力してください" value="{{ old('other', $organization->other) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">電話番号</label>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $organization->phone) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">E-メール</label>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="email" value="{{ old('email', $organization->email) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="">コメント</label>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" name="comment" value="{{ old('comment', $organization->comment) }}">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                       <button type="submit" class="btn btn-block btn-primary">保存</button>

                                        <a class="btn btn-block btn-primary" href="{{route('organization.index')}}">戻る</a>
                                    </div>
                                </div>
                            </div>

                          </form>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
<!-- /.content -->
</div>
@endsection

@section('part_javascript')
ChangeSideBar("nav-item-system-organization");

@endsection
