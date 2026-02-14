<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
  public function store(Request $request, Post $post)
  {
    $userId = $request->user()->id;

    // unique制約あるので、firstOrCreateで安全に
    $post->likes()->firstOrCreate([
      'user_id' => $userId,
      'post_id' => $post->id,
    ]);

    return response()->json([
      'ok' => true,
    ]);
  }

  public function destroy(Request $request, Post $post)
  {
    $userId = $request->user()->id;

    $post->likes()
      ->where('user_id', $userId)
      ->delete();

    return response()->json([
      'ok' => true,
    ]);
  }
}