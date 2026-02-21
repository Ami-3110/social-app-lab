<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


class PostCommentController extends Controller
{
  public function index(Request $request, Post $post)
  {
    $userId = $request->user()?->id;

    $comments = $post->comments()
      ->with([
        'user:id,name',
        'parent.user:id,name'
      ])
      ->withCount([
        'likes',
        'replies',
        'reposts',
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
          ? $comment->reposts()->where('user_id', $userId)->exists()
          : false;

        return $comment;
      })
    );

    return response()->json($comments);
  }

  public function store(Request $request, Post $post)
  {
    $data = $request->validate([
      'body' => ['required', 'string', 'max:280'], // 文字数は好みで
      'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
    ]);

    return DB::transaction(function () use ($data, $post, $request) {
      $userId = $request->user()->id;

      $parent = null;
      if (!empty($data['parent_id'])) {
        $parent = Comment::query()
          ->whereKey($data['parent_id'])
          ->firstOrFail();

        // 別投稿への返信は禁止
        if ((int)$parent->post_id !== (int)$post->id) {
          abort(422, 'parent_id is invalid for this post.');
        }
      }

      // まずコメント作成（root_idは後で確定させる）
      $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $userId,
        'body' => $data['body'],
        'parent_id' => $parent?->id,
        // root_id はこの時点では未確定（トップレベルは自分IDが必要）
      ]);

      // root_id の決定
      if ($parent) {
        // 親がトップレベルなら root_id は親
        // 親が返信なら root_id は親の root_id
        $rootId = $parent->root_id ?? $parent->id;

        $comment->root_id = $rootId;
        $comment->save();
      } else {
        // トップレベル：自分がroot
        $comment->root_id = $comment->id;
        $comment->save();
      }

      // 返却用に必要リレーションをロード
      $comment->load([
        'user:id,name,image', // PublicUser相当
        'parent.user:id,name,image',
      ]);

      // is_liked / is_bookmarked / count系をここで付与するならここで
      // （あなたは最初から数える想定なので後で統一してOK）

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

  private function commentResource(Comment $comment, int $authUserId): array
  {
    return [
      'id' => $comment->id,
      'post_id' => $comment->post_id,
      'body' => $comment->body,
      'parent_id' => $comment->parent_id,
      'root_id' => $comment->root_id,

      'user' => [
        'id' => $comment->user->id,
        'name' => $comment->user->name,
        'image' => $comment->user->image,
      ],

      'parent' => $comment->parent ? [
        'id' => $comment->parent->id,
        'body' => $comment->parent->body,
        'user' => [
          'id' => $comment->parent->user->id,
          'name' => $comment->parent->user->name,
          'image' => $comment->parent->user->image,
        ],
      ] : null,

      // ここは今の実装に合わせて（例）
      'likes_count' => $comment->likes_count ?? 0,
      'replies_count' => $comment->replies_count ?? 0,
      'reposts_count' => $comment->reposts_count ?? 0,
      'bookmarks_count' => $comment->bookmarks_count ?? 0,
      'is_liked' => (bool)($comment->is_liked ?? false),
      'is_bookmarked' => (bool)($comment->is_bookmarked ?? false),
    ];
  }
}