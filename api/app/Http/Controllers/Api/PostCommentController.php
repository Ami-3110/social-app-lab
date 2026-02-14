<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
  public function index(Request $request, Post $post)
  {
    $userId = $request->user()?->id;

    $comments = $post->comments()
      ->with('user:id,name')
      ->withCount('likes')
      ->latest()
      ->paginate(20);

    // paginatorの各要素に is_liked を付与
    $comments->setCollection(
      $comments->getCollection()->map(function ($comment) use ($userId) {
        $comment->is_liked = $userId
          ? $comment->likes()->where('user_id', $userId)->exists()
          : false;

        return $comment;
      })
    );

    return response()->json($comments);
  }
    
  public function store(Request $request, Post $post)
  {
      $data = $request->validate([
          'body' => ['required', 'string', 'max:2000'],
      ]);

      $comment = $post->comments()->create([
          'user_id' => $request->user()->id,
          'body' => $data['body'],
      ]);

      return response()->json($comment->load('user:id,name'), 201);
  }

  public function destroy(Request $request, Comment $comment)
  {
      // destroy only own comment
      abort_if($comment->user_id !== $request->user()->id, 403);

      $comment->delete();

      return response()->json([
          'ok' => true,
      ]);
  }
}
