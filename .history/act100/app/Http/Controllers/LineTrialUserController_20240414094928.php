<?php

// 事務所 体験者データ確認
namespace App\Http\Controllers;

use App\Models\Line_Trial_Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LineTrialUserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function input()
    {
        Log::info('linetrialuser input START');
        $linetrialusers = Line_Trial_Users::whereNull('deleted_at')
                        ->sortable()
                        ->orderByRaw('created_at DESC')
                        ->paginate(100);

        $common_no = 'linetrialuser';

        Log::info('linetrialuser input END');

        $compacts = compact( 'common_no', 'linetrialusers' );

        return view( 'linetrialuser.input', $compacts );
    }

    /**
     * [webapi] linetrialuserテーブルの更新
     */
    public function update_api(Request $request)
    {
        Log::info('update_api linetrialuser START');

        // Log::debug('update_api request = ' .print_r($request->all(),true));
        $id = $request->input('id');
        $users_name       = $request->input('users_name');
        $reservationed_at = $request->input('reservationed_at');

        $counts = array();
        $update = [];
        if( $request->exists('users_name')       ) $update['users_name']       = $request->input('users_name');
        if( $request->exists('reservationed_at') ) $update['reservationed_at'] = $request->input('reservationed_at');
        $update['updated_at']       = date('Y-m-d H:i:s');
        // Log::debug('update_api linetrialuser update : ' . print_r($update,true));

        $status = array();
        DB::beginTransaction();
        Log::info('update_api linetrialuser beginTransaction - start');
        try{
            // 更新処理
            Line_Trial_Users::where( 'id', $id )->update($update);

            $status = array( 'error_code' => 0, 'message'  => 'Your data has been changed!' );
            $counts = 1;
            DB::commit();
            Log::info('update_api linetrialuser beginTransaction - end');
        }
        catch(Exception $e){
            Log::error('update_api linetrialuser exception : ' . $e->getMessage());
            DB::rollback();
            Log::info('update_api linetrialuser beginTransaction - end(rollback)');
            echo "エラー：" . $e->getMessage();
            $counts = 0;
            $status = array( 'error_code' => 501, 'message'  => $e->getMessage() );
        }

        Log::info('update_api linetrialuser END');
        return response()->json([ compact('status','counts') ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Log::info('controluser create START');

        $organization = $this->auth_user_organization();
        $organization_id = $organization->id;
        $organizations = DB::table('organizations')
                            // 削除されていない
                            ->whereNull('deleted_at')
                            // 組織の絞り込み
                            ->when($organization_id != 0, function ($query) use ($organization_id) {
                                return $query->where( 'id', $organization_id );
                            })
                            ->get();

        if($organization_id == 0) {
            $users = User::whereNull('deleted_at')
                            ->where('login_flg','=', 1 )  //顧客
                            // 2021/12/13
                            ->orderBy('name', 'asc')
                            ->get();
            $customers = Customer::whereNull('deleted_at')
                            // 2021/12/13
                            ->orderBy('business_name', 'asc')
                            ->get();
            $controlusers = ControlUser::whereNull('deleted_at')
                            ->get();
        } else {
            $users = User::where('organization_id','=',$organization_id)
                            ->where('login_flg','=', 1 )  //顧客
                            ->whereNull('deleted_at')
                            // 2021/12/13
                            ->orderBy('name', 'asc')
                            ->get();
            $customers = Customer::where('organization_id','=',$organization_id)
                            ->whereNull('deleted_at')
                            // 2021/12/13
                            ->orderBy('business_name', 'asc')
                            ->get();
            $controlusers = ControlUser::where('organization_id','=',$organization_id)
                            ->whereNull('deleted_at')
                            ->get();
        }

        $common_no ='00_4';
        $compacts = compact( 'common_no','organizations', 'controlusers','users','customers' );

        Log::info('controluser create END');
        return view( 'controluser.create', $compacts );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::info('controluser store START');

        $organization = $this->auth_user_organization();

        $request->merge(
            ['organization_id' => $organization->id],
            ['user_id'         => $request->user_id],
            ['customer_id'     => $request->customer_id],
        );

        $validator = $this->get_validator($request,$request->id);
        if ($validator->fails()) {
            return redirect('ctluser/create')->withErrors($validator)->withInput();
        }

// Log::debug('controluser store $request = ' . print_r($request->all(), true));

        DB::beginTransaction();
        Log::info('beginTransaction - controluser store start');
        try {
            $controluser = new ControlUser();
            $controluser->organization_id   = $request->organization_id;
            $controluser->user_id           = $request->user_id;
            $controluser->customer_id       = $request->customer_id;
            $controluser->save();           //  Inserts
            DB::commit();

            Log::info('beginTransaction - controluser store end(commit)');
        }
        catch(\QueryException $e) {
            Log::error('exception : ' . $e->getMessage());
            DB::rollback();
            Log::info('beginTransaction - controluser store end(rollback)');
        }

        Log::info('controluser store END');
        // return redirect()->route('customer.index')->with('msg_success', '新規登録完了');
        // toastrというキーでメッセージを格納
        session()->flash('toastr', config('toastr.create'));
        return redirect()->route('ctluser.index');

    }

}
