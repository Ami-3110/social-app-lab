<?php
// app/Http/Controllers/Api/CommentLikeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
  public function store(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;

    $comment->likes()->firstOrCreate([
      'user_id' => $userId,
    ]);

    return response()->noContent(); // 204
  }

  public function destroy(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;

    $comment->likes()->where('user_id', $userId)->delete();

    return response()->noContent(); // 204
  }
}
