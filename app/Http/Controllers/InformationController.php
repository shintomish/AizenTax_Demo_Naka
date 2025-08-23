<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\InformationNotification;

class InformationController extends Controller
{
    public function store(Request $request)
    {
        // お知らせテーブルへ登録
        $information = Information::create([
            'date' => $request->get('date'),
            'title' => $request->get('title'),
            'content' => $request->get('content'),
        ]);

        // お知らせ内容を対象ユーザー宛てに通知登録
        $user = User::find($request->get('user_id'));
        $user->notify(
            new InformationNotification($information)
        );

        // // お知らせ内容を全ユーザー宛てに通知登録
        // $users = User::all();
        // Notification::send($users, new InformationNotification($information));
        
    }
}
