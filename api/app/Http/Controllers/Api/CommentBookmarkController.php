<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Notification;

class CommentBookmarkController extends Controller
{
  public function store(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;
    $comment->bookmarks()->firstOrCreate([
      'user_id' => $request->user()->id,
    ]);

    if ($comment->user_id !== $userId) {
      Notification::firstOrCreate(
        [
          'user_id' => $comment->user_id,
          'actor_id' => $userId,
          'type' => 'comment_bookmark',
          'post_id' => $comment->post_id,
          'comment_id' => $comment->id,
        ],
        [
          'read_at' => null,
        ]
      );
    }

    return response()->json([
      'is_bookmarked' => true,
    ]);
  }
  
  public function destroy(Request $request, Comment $comment)
  {
    $comment->bookmarks()
      ->where('user_id', $request->user()->id)
      ->delete();

    return response()->json([
      'is_bookmarked' => false,
    ]);
  }
}
