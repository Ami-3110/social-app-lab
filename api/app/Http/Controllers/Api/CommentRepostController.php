<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentRepost;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CommentRepostController extends Controller
{
  public function store(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;

    CommentRepost::firstOrCreate([
      'comment_id' => $comment->id,
      'user_id' => $userId,
    ]);

    $post = Post::firstOrCreate(
      [
        'user_id' => $userId,
        'repost_of_comment_id' => $comment->id,
      ],
      [
        'title' => '[repost]',
        'body' => '[repost]', // NOT NULL対策（あとでUI分岐するから中身は何でもいい）
      ]
    );

    Log::info('created repost post', $post->toArray());

    return response()->json(['message' => 'Reposted']);
  }

  public function destroy(Request $request, Comment $comment)
  {
    $userId = $request->user()->id;

    $comment->reposts()->where('user_id', $userId)->delete();

    Post::where('user_id', $userId)
      ->where('repost_of_comment_id', $comment->id)
      ->delete();

    return response()->json(['message' => 'Unreposted']);
  }
}
