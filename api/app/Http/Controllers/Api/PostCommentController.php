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

    // paginatorの各要素に ActionBarの状態を付与
    $comments->setCollection(
      $comments->getCollection()->map(function ($comment) use ($userId) {
        // Comment-like
          $comment->is_liked = $userId
          ? $comment->likes()->where('user_id', $userId)->exists()
          : false;

          // Comment-bookmark
          $comment->is_bookmarked = $userId
          ? $comment->bookmarks()->where('user_id', $userId)->exists()
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

      $comment->load('user:id,name');
      
      return response()->json([
        'comment' => [
          'id' => $comment->id,
          'body' => $comment->body,
          'created_at' => $comment->created_at,
          'user' => [
            'id' => $comment->user->id,
            'name' => $comment->user->name,
          ],
        ],
      ], 201);
  }

  public function update(Request $request, Comment $comment)
  {
    // 認可（後述）
    $this->authorize('update', $comment);

    $data = $request->validate([
      'body' => ['required', 'string', 'max:2000'],
    ]);

    $comment->update(['body' => $data['body']]);
    $comment->load('user:id,name');

    return response()->json([
      'comment' => [
        'id' => $comment->id,
        'body' => $comment->body,
        'created_at' => $comment->created_at,
        'user' => [
          'id' => $comment->user->id,
          'name' => $comment->user->name,
        ],
      ],
    ]);
  }

  public function destroy(Request $request, Comment $comment)
  {
    $this->authorize('delete', $comment);

    $comment->delete();
    return response()->noContent();
  }
}