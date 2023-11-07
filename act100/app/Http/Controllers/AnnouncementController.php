<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index() {

        return view('announcement.index');

    }

    public function show(Request $request, Announcement $announcement) {

        $user = $request->user();
        $announcement_read = AnnouncementRead::where('user_id', $user->id)
            ->where('announcement_id', $announcement->id)
            ->first();

        if(!is_null($announcement_read)) {

            $announcement_read->read = true;
            $announcement_read->save();

        }

        return $announcement;

    }

    public function list(Request $request) {

        $user = $request->user();
        return Announcement::whereHas('reads', function($query) use($user){

            $query->where('user_id', $user->id)
                ->where('read', false);

        })
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->paginate(7);

    }
}

