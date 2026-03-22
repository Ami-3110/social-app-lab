<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Notification;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
  public function store(Request $request, Post $post)
  {
    $userId = $request->user()->id;

    $request->user()
      ->bookmarks()
      ->syncWithoutDetaching([$post->id]);

    if ($post->user_id !== $userId) {
      Notification::firstOrCreate(
        [
          'user_id' => $post->user_id,
          'actor_id' => $userId,
          'type' => 'bookmark',
          'post_id' => $post->id,
          'comment_id' => null,
        ],
        [
          'read_at' => null,
        ]
      );
    }

    return response()->json([
      'bookmarked' => true,
    ]);
  }

  // 解除
  public function destroy(Request $request, Post $post)
  {
    $request->user()
      ->bookmarks()
      ->detach($post->id);

    return response()->json([
      'bookmarked' => false,
    ]);
  }

  // 保存一覧
  public function index(Request $request)
  {
    $user = $request->user();

    $posts = $user->bookmarks()
      ->with('user:id,name,avatar_path')
      ->withCount([
        'bookmarkedBy as is_bookmarked' => function ($q) use ($user) {
          $q->where('user_id', $user->id);
        }
      ])
      ->latest()
      ->paginate(10)
      ->withQueryString();

    return $posts;
  }
}
