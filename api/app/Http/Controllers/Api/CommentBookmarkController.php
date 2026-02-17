<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;

class CommentBookmarkController extends Controller
{
  public function store(Request $request, Comment $comment)
  {
    $comment->bookmarks()->firstOrCreate([
      'user_id' => $request->user()->id,
    ]);

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
