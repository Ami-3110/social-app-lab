<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
  // 保存
  public function store(Request $request, Post $post)
  {
    $request->user()
      ->bookmarks()
      ->syncWithoutDetaching([$post->id]);

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
      ->with('user:id,name')
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
