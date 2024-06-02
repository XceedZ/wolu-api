<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'message' => 'required|string',
        ]);

        $forum = Forum::create([
            'user_id' => $request->user_id,
            'class_id' => $request->class_id,
            'message' => $request->message,
        ]);

        $forum->load('user');

        return response()->json([
            'forum' => [
                'userId' => $forum->user->id,
                'fullname' => $forum->user->fullname,
                'message' => $forum->message,
                'send_at' => $forum->created_at,
            ],
        ], 201);
    }

    public function getMessagesByClass($classId)
    {
        $forums = Forum::where('class_id', $classId)->with('user')->get();

        $formattedForums = $forums->map(function($forum) {
            return [
                'userId' => $forum->user->id,
                'fullname' => $forum->user->fullname,
                'message' => $forum->message,
                'send_at' => $forum->created_at,
            ];
        });

        return response()->json($formattedForums, 200);
    }
}
