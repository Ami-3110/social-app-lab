<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentRepostController extends Controller
{
  public function store(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;
    $quoteBody = trim((string) $request->input('quote_body', ''));

    Post::create([
      'user_id' => $userId,
      'repost_of_comment_id' => $comment->id,
      'quote_body' => $quoteBody !== '' ? $quoteBody : null,
      'title' => '[repost]',
      'body' => '[repost]',
    ]);

    return response()->json([
      'message' => $quoteBody !== '' ? 'Quoted' : 'Reposted',
    ]);
  }

  public function destroy(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;

    Post::where('user_id', $userId)
      ->where('repost_of_comment_id', $comment->id)
      ->delete();

    return response()->json(['message' => 'Unreposted']);
  }
}
