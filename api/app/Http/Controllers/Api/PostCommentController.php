<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreCommentRequest;

class PostCommentController extends Controller
{
  public function index(Request $request, Post $post)
  {
    $userId = $request->user()?->id;

    $comments = $post->comments()
      ->with([
        'user:id,name,avatar_path',
        'parent.user:id,name,avatar_path',
        'media',
      ])
      ->withCount([
        'likes',
        'replies',
        'repostPosts as reposts_count',
        ])
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

        //comment-repost
        $comment->is_reposted = $userId
          ? $comment->repostPosts()
          ->where('user_id', $userId)
          ->exists()
          : false;

        //comment-media
        $comment->setRelation('media', $comment->media->map(function ($media) {
          return (object) [
            'id' => $media->id,
            'path' => $media->path,
            'url' => asset('storage/' . $media->path),
            'sort_order' => $media->sort_order,
          ];
        })->values());

        return $comment;
      })
    );

    return response()->json($comments);
  }

  public function store(StoreCommentRequest $request, Post $post)
  {
    $data = $request->validated();

    return DB::transaction(function () use ($data, $post, $request) {
      $userId = $request->user()->id;

      $parent = null;
      if (!empty($data['parent_id'])) {
        $parent = Comment::query()
          ->whereKey($data['parent_id'])
          ->firstOrFail();

        if ((int)$parent->post_id !== (int)$post->id) {
          abort(422, 'parent_id is invalid for this post.');
        }
      }

      $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $userId,
        'body' => $data['body'] ?? null,
        'parent_id' => $parent?->id,
      ]);

      if ($parent) {
        $comment->root_id = $parent->root_id ?? $parent->id;
        $comment->save();

        $recipientIds = Comment::query()
          ->where('post_id', $post->id)
          ->where('root_id', $comment->root_id)
          ->pluck('user_id')
          ->push($post->user_id)
          ->unique()
          ->reject(fn($id) => (int) $id === (int) $userId);

        foreach ($recipientIds as $recipientId) {
          Notification::create([
            'user_id' => $recipientId,
            'actor_id' => $userId,
            'type' => 'comment_reply',
            'post_id' => $post->id,
            'comment_id' => $comment->id,
            'read_at' => null,
          ]);
        }
      } else {
        $comment->root_id = $comment->id;
        $comment->save();

        if ($post->user_id !== $userId) {
          Notification::create([
            'user_id' => $post->user_id,
            'actor_id' => $userId,
            'type' => 'comment',
            'post_id' => $post->id,
            'comment_id' => $comment->id,
            'read_at' => null,
          ]);
        }
      }
      
      if ($request->hasFile('media')) {
        foreach ($request->file('media') as $index => $file) {
          $path = $file->store('comments', 'public');

          $comment->media()->create([
            'path' => $path,
            'sort_order' => $index,
          ]);
        }
      }

      $comment->load([
        'user:id,name,avatar_path',
        'parent.user:id,name,avatar_path',
        'media',
      ]);

      return response()->json([
        'data' => $this->commentResource($comment, $request->user()->id),
      ], 201);
    });
  }

  public function update(Request $request, Comment $comment)
  {
    // 認可（後述）
    $this->authorize('update', $comment);

    $data = $request->validate([
      'body' => ['required', 'string', 'max:2000'],
    ]);

    $comment->update(['body' => $data['body']]);
    $comment->load('user:id,name,avatar_path');

    return response()->json([
      'data' => $this->commentResource($comment, $request->user()->id),
    ]);
  }

  public function destroy(Request $request, Comment $comment)
  {
    $this->authorize('delete', $comment);

    $comment->delete();
    return response()->noContent();
  }

  private function commentResource(Comment $comment, int $authUserId): array
  {
    return [
      'id' => $comment->id,
      'post_id' => $comment->post_id,
      'body' => $comment->body,
      'parent_id' => $comment->parent_id,
      'root_id' => $comment->root_id,
      'created_at' => $comment->created_at?->toISOString(),
      'updated_at' => $comment->updated_at?->toISOString(),

      'user' => [
        'id' => $comment->user->id,
        'name' => $comment->user->name,
        'avatar_path' => $comment->user->avatar_path,
      ],

      'parent' => $comment->parent ? [
        'id' => $comment->parent->id,
        'body' => $comment->parent->body,
        'user' => [
          'id' => $comment->parent->user->id,
          'name' => $comment->parent->user->name,
          'avatar_path' => $comment->parent->user->avatar_path,
        ],
      ] : null,
      
      'media' => $comment->media->map(fn($media) => [
        'id' => $media->id,
        'path' => $media->path,
        'url' => asset('storage/' . $media->path),
        'sort_order' => $media->sort_order,
      ])->values(),
      
      'likes_count' => $comment->likes_count ?? 0,
      'replies_count' => $comment->replies_count ?? 0,
      'reposts_count' => $comment->reposts_count ?? 0,
      'bookmarks_count' => $comment->bookmarks_count ?? 0,
      'is_liked' => (bool)($comment->is_liked ?? false),
      'is_bookmarked' => (bool)($comment->is_bookmarked ?? false),
    ];
  }

}