<?php
// app/Http/Controllers/Api/CommentLikeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Notification;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
  public function store(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;

    $comment->likes()->firstOrCreate([
      'user_id' => $userId,
    ]);

    if ($comment->user_id !== $userId) {
      Notification::firstOrCreate(
        [
          'user_id' => $comment->user_id,
          'actor_id' => $userId,
          'type' => 'comment_like',
          'post_id' => $comment->post_id,
          'comment_id' => $comment->id,
        ],
        [
          'read_at' => null,
        ]
      );
    }

    return response()->noContent(); // 204
  }
  public function destroy(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;

    $comment->likes()->where('user_id', $userId)->delete();

    return response()->noContent(); // 204
  }
}
